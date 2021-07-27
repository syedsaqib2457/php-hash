<?php
	class SystemMethods extends System {

		protected function _authenticate($parameters) {
			$response = array(
				'message' => 'Error authenticating request, please try again.',
				'status_valid' => (
					(empty($parameters['settings']['session_id']) === false) &&
					($this->_verifyKeys() === true)
				)
			);

			if ($response['status_valid'] === true) {
				$userCount = $this->count(array(
					'in' => 'users',
					'where' => array(
						'authentication_expires >' => date('Y-m-d H:i:s', time()),
						'authentication_username' => sha1($this->settings['keys']['start'] . '_' . $parameters['settings']['session_id'])
						'id' => 1
					)
				));
				$response['status_valid'] = (
					(is_int($userCount) === true) &&
					($userCount > 0)
				);
			}

			$response['user'] = $this->fetch(array(
				'fields' => array(
					'authentication_password',
					'authentication_whitelist'
				),
				'from' => 'users',
				'where' => array(
					'id' => 1
				)
			));

			if ($response['status_valid'] === false) {
				$response = $this->_authenticateEndpoint($response);
				$response['user']['endpoint'] = $response['status_valid'];
			}

			return $response;
		}

		protected function _authenticateEndpoint($parameters) {
			$response = array(
				'message' => 'Error authenticating endpoint request, please try again.',
				'status_valid' => (
					($parameters['user'] !== false) &&
					(empty($parameters['user']) === false)
				)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$nodeCount = $this->count(array(
				'in' => 'nodes',
				'where' => array(
					'node_id' => null,
					'OR' => array(
						'external_ip_version_4' => $_SERVER['REMOTE_ADDR'],
						'external_ip_version_6' => $_SERVER['REMOTE_ADDR']
					)
				)
			));
			$response['status_valid'] = (
				(
					(is_int($nodeCount) === true) &&
					($nodeCount === 1)
				) ||
				(in_array($_SERVER['REMOTE_ADDR'], explode("\n", $parameters['user']['authentication_whitelist'])) === true)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid source IP, please try again.';
			}

			return $response;
		}

		protected function _call($parameters = array()) {
			$response = false;
			$methodFromParts = explode('_', $parameters['method_from']);
			$methodObjectName = implode('', array_map(function($methodFrom) {
				return ucwords($methodFrom);
			}, $methodFromParts)) . 'Methods';

			if (class_exists($methodObjectName) === false) {
				$system = new System();
				require_once($this->settings['base_path'] . '/methods/' . $parameters['method_from'] . '.php');
			}

			if (empty($this->$methodObjectName) === true) {
				$this->$methodObjectName = new $methodObjectName();
			}

			if (
				(empty($parameters['method_name']) === false) &&
				(method_exists($this->$methodObjectName, $parameters['method_name']) === true)
			) {
				$response = call_user_func_array(array($this->$methodObjectName, $parameters['method_name']), $parameters['method_parameters']);
			}

			return $response;
		}

		protected function _fetchIpType($ip, $ipVersion) {
			$response = 'public';

			switch ($ipVersion) {
				case 4:
					$ipInteger = ip2long($ip);

					foreach ($this->settings['private_ip_ranges'][4] as $privateIpRangeIntegerStart => $privateIpRangeIntegerEnd) {
						if (
							($ipInteger >= $privateIpRangeIntegerStart) &&
							($ipInteger <= $privateIpRangeIntegerEnd)
						) {
							$response = 'private';
						}
					}

					break;
				case 6:
					$ipParts = explode(':', $ip);

					foreach ($ipParts as $ipPartKey => $ipPart) {
						$ipParts[$ipPartKey] = str_pad($ipPart, 4, '0', STR_PAD_LEFT);
					}

					$ipBlocks = array(
						implode(':', $ipParts)
					);

					if (count($ipParts) === 7) {
						array_pop($ipParts);
						$ipBlocks[] = $ipParts . ':y';
					} else {
						$ipBlockVariables = str_repeat(':x', 4);
						$ipParts = array_slice($ipParts, 0, count($ipParts) - 4);
						$ipBlocks[] = implode(':', $ipParts) . $ipBlockVariables;
						$ipBlockVariables .= str_repeat(':x', 2);
						$ipParts = array_slice($ipParts, 0, count($ipParts) - 2);
						$ipBlocks = array_merge($ipBlocks, array(
							implode(':', $ipParts) . $ipBlockVariables,
							$ipParts[0] . ':' . substr($ipParts[1], 0, 3) . 'x' . $ipBlockVariables
						);
						$ipBlockVariables .= ':x';
						$ipBlocks = array_merge($ipBlocks, array(
							$ipParts[0] . $ipBlockVariables,
							substr($ipParts[0], 0, 2) . 'x' . $ipBlockVariables
						));

						if (array_intersect($ipBlocks, $this->settings['private_ip_ranges'][6]) !== array()) {
							$response = 'private';
						}
					}

					break;
			}

			return $response;
		}

		protected function _logInvalidRequest() {
			$requestLogs = $this->fetch(array(
				'fields' => array(
					'id',
					'request_attempts'
				),
				'from' => 'request_logs',
				'where' => array(
					'node_user_id' => null,
					'source_ip' => $_SERVER['REQUEST_URI']
				)
			));
			$requestLogData = array(
				'source_ip' => $_SERVER['REQUEST_URI'],
				'request_attempts' => 1
			);

			if (
				($requestLogs !== false) &&
				(empty($requestLogs) === false)
			) {
				$requestLogs['request_attempts']++;
				$requestLogData = $requestLogs;
			}

			$this->save(array(
				'data' => array(
					$requestLogData
				),
				'to' => 'request_logs'
			));
			return;
		}

		protected function _parseFormDataItem($formDataItemKey, $formDataItemValue) {
			$parsedFormDataItem = array(
				$formDataItemKey => $formDataItemValue
			);

			if (!empty($formDataItemKey) {
				$closingBracketPosition = strrpos($formDataItemKey, ']');
				$openingBracketPosition = stripos($formDataItemKey, '[');

				if (
					($closingBracketPosition !== false) &&
					($openingBracketPosition !== false) &&
					($closingBracketPosition === (strlen($formDataItemKey) - 1))
				) {
					$parsedFormDataItemKey = substr_replace(substr($formDataItemKey, $openingBracketPosition), '', -1);
					$parsedFormDataItemKey = substr($parsedFormDataItemKey, 1);
					$parsedFormDataItem = array(
						substr_replace($formDataItemKey, '', $openingBracketPosition) => $this->_parseFormDataItem($parsedFormDataItemKey, $formDataItemValue)
					);
				}
			}

			$response = $parsedFormDataItem;
			return $response;
		}

		protected function _parseFormDataItems($formDataItems) {
			foreach ($formDataItems as $formDataItemKey => $formDataItemValue) {
				$formDataItem = array(
					$formDataItemKey => $formDataItemValue
				);
				$parsedFormDataItem = $this->_parseFormDataItem($formDataItemKey, $formDataItemValue);

				if ($formDataItem !== $parsedFormDataItem) {
					unset($formDataItems[$formDataItemKey]);
					$formDataItems = array_merge_recursive($formDataItems, $parsedFormDataItem);
				}
			}

			$response = $formDataItems;
			return $response;
		}

		protected function _parseParameterizedQuery($query) {
			$queryParts = explode($this->keys['start'], $query);
			$parameterValues = array();

			foreach ($queryParts as $queryPartKey => $queryPart) {
				$position = strpos($queryPart, $this->settings['keys']['stop']);

				if ($position !== false) {
					$queryPart = str_replace($this->settings['keys']['stop'], '?', $queryPart);
					$queryPartValue = substr($queryPart, 0, $position);
					$queryParts[$queryPartKey] = str_replace($queryPartValue, '', $queryPart);
					$parameterValues[] = $queryPartValue;
				}
			}

			$response = array(
				'parameterizedQuery' => implode('', $queryParts),
				'parameterizedValues' => $parameterValues
			);
			return $response;
		}

		protected function _parseParametersToCamelCase($parameters) {
			$response = array();

			foreach ($parameters as $parameterKey => $parameterValue) {
				unset($parameters[$parameterKey]);

				if (strpos($parameterKey, '_') !== false) {
					$parameterKeyParts = explode('_', $parameterKey);
					$parameterKeyFirstPart = array_shift($parameterKeyParts);
					$parameterKey = $parameterKeyFirstPart . implode('', array_map(function($parameterKeyPart) {
						return ucwords($parameterKeyPart);
					}, $parameterKeyParts));
				}

				if (is_string($parameterValue) === true) {
					$jsonString = trim($parameterValue);
					$jsonStringLength = strlen($jsonString);

					if (
						(
							(stripos($jsonString, '{') === 0) &&
							($jsonStringLength === (strrpos($jsonString, '}') + 1))
						) ||
						(
							(stripos($jsonString, '[{') === 0) &&
							($jsonStringLength === (strrpos($jsonString, '}]') + 2))
						)
					) {
						$decodedJsonString = json_decode($jsonString, true);

						if (is_array($decodedJsonString) === true) {
							$parameterValue = $decodedJsonString;
						}
					}
				}

				$parameters[$parameterKey] = $parameterValue;

				if (is_array($parameterValue) === true) {
					$parameters[$parameterKey] = $this->_parseParametersToCamelCase($parameterValue);
				}
			}

			$response = $parameters;
			return $response;
		}

		protected function _parseParametersToSnakeCase($parameters) {
			$response = array();
			$exceptionKeyValues = array(
				'AND' => '[&&]',
				' LIKE' => ' [%=]',
				'OR' => '[||]'
			);

			foreach ($parameters as $parameterKey => $parameterValue) {
				unset($parameters[$parameterKey]);

				if (strtolower($parameterKey) !== $parameterKey) {
					foreach ($exceptionKeyValues as $exceptionKeyKey => $exceptionKeyValue) {
						if (strpos($parameterKey, $exceptionKeyKey) !== false) {
							$parameterKey = str_replace($exceptionKeyKey, $exceptionKeyValue, $parameterKey);
						}
					}

					$parameterKeyCharacters = str_split($parameterKey);

					if (empty($exceptionKeyValues[$parameterKey]) === true) {
						$parameterKeyCharacters[0] = strtolower($parameterKeyCharacters[0]);
					}

					$parameterKey = implode('', array_map(function($parameterKeyCharacter, $parameterKeyKey) {
						if (ctype_upper($parameterKeyCharacter) === true) {
							$parameterKeyCharacter = '_' . strtolower($parameterKeyCharacter);
						}

						return $parameterKeyCharacter;
					}, $parameterKeyCharacters, array_keys($parameterKeyCharacters)));
				}

				foreach ($exceptionKeyValues as $exceptionKeyKey => $exceptionKeyValue) {
					if (strpos($parameterKey, $exceptionKeyValue) !== false) {
						$parameterKey = str_replace($exceptionKeyValue, $exceptionKeyKey, $parameterKey);
					}
				}

				$parameters[$parameterKey] = $parameterValue;

				if (is_array($parameterValue) === true) {
					$parameters[$parameterKey] = $this->_parseParametersToSnakeCase($parameterValue);
				}
			}

			$response = $parameters;
			return $response;
		}

		protected function _parseParameters($parameters, $caseType) {
			$response = array();
			$methodName = '_parseParametersTo' . ucwords($caseType) . 'Case';

			if (method_exists($this, $methodName) === true) {
				foreach ($parameters as $parameterKey => $parameterValue) {
					$parameter = array(
						$parameterKey => $parameterValue
					);
					$parsedParameter = $this->$methodName($parameter);
					unset($parameters[$parameterKey]);
					$parameters = array_merge($parameters, $parsedParameter);
				}
			}

			$response = $parameters;
			return $response;
		}

		protected function _parseQueryConditions($from, $where = array(), $conjunction = 'OR') {
			foreach ($where as $key => $value) {
				if (
					empty($key) === false &&
					(in_array($key, array(
						'AND',
						'OR'
					)) === true)
				) {
					$conjunction = $key;
				}

				$validQuery = true;

				if (
					(is_array($value) === true) &&
					(count($value) != count($value, COUNT_RECURSIVE))
				) {
					$where[$key] = '(' . implode(') ' . $conjunction . ' (', $this->_parseQueryConditions($from, $value, $conjunction)) . ')';
				} else {
					if (is_array($value) === false) {
						$value = array(
							$value
						);
					}

					array_walk($value, function(&$fieldValue, $fieldKey) use ($key) {
						if (
							(is_string($fieldKey) === true) &&
							(strlen($fieldKey) > 1)
						) {
							$key = $fieldKey;
						}

						if (is_null($fieldValue) === true) {
							$fieldValue = ' IS NULL';
						} else {
							$fieldValue = $key . ' ' . $this->_parseQueryValue($fieldValue);
						}
					});
					$keyParts = explode(' ', $key);

					if (
						(empty($this->settings['database']['structure'][$from][$keyParts[0]]) === true) &&
						(in_array($key, array(
							'AND',
							'OR'
						)) === false)
					) {
						unset($where[$key]);
						$validQuery = false;
					}

					if ($validQuery === true) {
						if (strpos($key, '!=') !== false) {
							$conjunction = 'AND';
						}

						$where[$key] = implode(' ' . $conjunction) . ' ', $value);
					}
				}
			}

			$response = $where;
			return $response;
		}

		protected function _parseQueryValue($value) {
			if (is_bool($value) === true) {
				$value = intval($value);
			}

			$response = $this->settings['keys']['start'] . $value . $this->settings['keys']['stop'];
			return $response;
		}

		protected function _query($query, $parameters = array()) {
			$database = new PDO('mysql:host=' . $this->settings['database']['hostname'] . '; dbname=' . $this->settings['database']['name'] . ';', $this->settings['database']['username'], $this->settings['database']['password']);
			$database->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
			$database->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$parameterized = $this->_parseParameterizedQuery($query);

			if (empty($parameterized['parameterizedQuery']) === true) {
				return false;
			}

			$database = $database->prepare($parameterized['parameterizedQuery']);
			$data = array();

			if (
				(empty($database) === true) ||
				(is_object($database) === false)
			) {
				return false;
			}

			if (
				(empty($parameterized['parameterizedValues']) === false) &&
				(is_array($parameterized['parameterizedValues']) === true)
			) {
				foreach ($parameterized['parameterizedValues'] as $parameterizedValueKey => $parameterizedValue) {
					if ($parameterizedValue === '_is_null_') {
						$parameterized['parameterizedValues'][$parameterizedValueKey] = null;
					}
				}
			}

			$response = $database->execute($parameterized['parameterizedValues']);
			$data = $database->fetchAll(PDO::FETCH_ASSOC);

			if (empty($data) === false) {
				if (empty($data[1]) === true) {
					$data = current($data);
				}

				$response = $data;
			}

			return $response;
		}

		protected function _request($parameters) {
			$response = array(
				'message' => 'Invalid request parameters, please try again.',
				'status_valid' => (
					(empty($_POST['json']) === false) &&
					(is_string($_POST['json']) === true)
				)
			);

			if ($parameters['status_valid'] === false) {
				$this->_logInvalidRequest();
				return $response;
			}

			$parameters = $this->_parseParameters(json_decode($_POST['json'], true), 'snake');

			if (empty($parameters['from']) === true) {
				$parameters['from'] = $_SERVER['REQUEST_URI'];

				if (empty($parameters['url']) === false) {
					$parameters['from'] = $parameters['url'];
				}

				$parameters['from'] = str_replace('-', '_', basename($parameters['from']));
			}

			$response['status_valid'] = (
				(
					(empty($parameters['from']) === false) &&
					(
						($parameters['from'] === 'system') ||
						(empty($this->settings['database']['structure'][$parameters['from']]) === false)
					)
				) &&
				(
					(isset($parameters['limit']) === false) ||
					(is_int($parameters['limit']) === true)
				) &&
				(
					(empty($parameters['method']) === false) &&
					(method_exists($this, $parameters['method']) === true)
				) &&
				(
					(isset($parameters['offset']) === false) ||
					(is_int($parameters['offset']) === true)
				) &&
				(
					(isset($parameters['sort']) === false) ||
					(
						(empty($parameters['sort']['field']) === false) &&
						(empty($this->settings['database']['structure'][$parameters['from']][$parameters['sort']['field']]) === false) &&
						(empty($parameters['sort']['order']) === false) &&
						(in_array(strtoupper($parameters['sort']['order']), array('ASC', 'DESC')) === true)
					)
				) &&
				(
					(isset($parameters['where']) === false) ||
					(
						(empty($parameters['where']) === false) &&
						(is_array($parameters['where']) === true)
					)
				)
			);

			if ($parameters['status_valid'] === false) {
				$this->_logInvalidRequest();
			} else {
				$response = $this->_authenticate($parameters);
				$parameters['user'] = $response['user'];

				if (
					(empty($parameters['user']) === true) &&
					($parameters['method'] !== 'login')
				) {
					if ($response['status_valid'] === false) {
						$this->_logInvalidRequest();
					}
				} else {
					$methodName = $parameters['method'];
					$response = $this->$methodName($parameters);

					if (
						($response['status_valid'] === false) &&
						($parameters['method'] === 'login')
					) {
						$this->_logInvalidRequest();
					} else {
						$response = array_merge($response, array(
							'user' => $parameters['user']
						));
						$this->delete(array(
							'from' => 'request_logs',
							'where' => array(
								'node_user_id' => null,
								'source_ip' => $_SERVER['REQUEST_URI']
							)
						));
					}
				}
			}

			if (empty($parameters['camel_case_response_keys']) === false) {
				$response = $this->_parseParameters($response, 'camel');
			}

			return $response;
		}

		protected function _sanitizeIps($ips = array(), $allowSubnets = false, $allowSubnetParts = false) {
			$validatedIps = array();

			if (is_array($ips) === false) {
				$ips = explode("\n", $ips);
			}

			$ips = array_values(array_filter($ips));

			foreach ($ips as $ip) {
				$ipVersion = 4;
				$validatedIp = false;

				if (empty($ip) === false) {
					$ip = trim($ip, '.');

					if (
						(strpos($ip, ':') !== false) &&
						(strpos($ip, ':::') === false)
					) {
						$ipVersion = 6;
					}

					$validatedIp = $this->_validateIp($ip, $ipVersion, $allowSubnets, $allowSubnetParts);

					if ($validatedIp === false) {
						continue;
					}

					$validatedIps[$ipVersion][$validatedIp] = $validatedIp;
				}
			}

			$response = $validatedIps;
			return $response;
		}

		protected function _validateIp($ip, $ipVersion, $allowRanges = false, $allowRangeParts = false) {
			$response = false;

			switch ($ipVersion) {
				case 4:
					$ipParts = explode('.', $ip);

					if (
						(count($ipParts) === 4) ||
						($allowRangeParts === true)
					) {
						foreach ($ipParts as $ipPartKey => $ipPart) {
							if (
								(is_numeric($ipPart) === false) ||
								(strlen(intval($ipPart)) >= 4) ||
								($ipPart > 255) ||
								($ipPart < 0)
							) {
								if (
									($allowRanges === true) &&
									($ipPart === end($ipParts)) &&
									(substr_count($ipPart, '/') === 1)
								) {
									$ipBlockParts = explode('/', $ipPart);

									if (is_numeric($ipBlockParts[0]) === true) {
										$ipPart = $ipBlockParts[0];

										if (
											($ipPart !== false) &&
											(strlen($ipPart) >= 1) &&
											(strlen($ipPart) <= 3) &&
											($ipPart <= 255) &&
											($ipPart >= 0) &&
											(is_numeric($ipBlockParts[1]) === true) &&
											($ipBlockParts[1] <= 30) &&
											($ipBloclParts[1] >= 8)
										) {
											$ipPart .= '/' . $ipBlockParts[1];
										} else {
											return false;
										}
									} else {
										return false;
									}
								} else {
									return false;
								}
							} else {
								$ipPart = intval($ipPart);
							}

							$ipParts[$ipPartKey] = $ipPart;
						}

						$response = implode('.', $ipParts);
					}

					break;
				case 6:
					// todo: validate ipv6 prefix formatting

					if (strpos($ip, '::') !== false) {
						$ip = str_replace('::', str_repeat(':0', 7 - (substr_count($ip, ':') - 1)) . ':', $ip);

						if ($ip[0] === ':') {
							$ip = '0' . $ip;
						}
					}

					$ipParts = explode(':', $ip);
					$mappedIpVersion4 = false;

					if (count($ipParts) === 7) {
						$mappedIpVersion4 = $this->_validateIp(end($ipParts), 4);
					}

					if (
						(count($ipParts) === 8) ||
						($mappedIpVersion4 !== false)
					) {
						foreach ($ipParts as $ipPart) {
							if (
								(strlen($ipPart) > 4) &&
								($ipPart !== $mappedIpVersion4)
							) {
								return false;
							}

							if (is_numeric($ipPart) === false) {
								foreach (range(0, strlen($ipPart) - 1) as $ipPartIndex) {
									if (strpos('0123456789ABCDEF', $ipPart[$ipPartIndex]) === false) {
										return false;
									}
								}
							}
						}

						$response = $ip;
					}

					break;
			}

			return $response;
		}

		protected function _validatePort($port) {
			$response = false;

			if (
				(is_numeric($port) === true) &&
				(
					($port >= 1) &&
					($port <= 65535)
				)
			) {
				$response = intval(trim($port));
			}

			return $response;
		}

		protected function _validateHostname($hostname) {
			$response = false;

			if (
				(strpos($hostname, '://') === false) &&
				(filter_var('http://' . $hostname, FILTER_VALIDATE_URL) === filter_var('http://' . $hostname, FILTER_SANITIZE_URL))
			) {
				$response = $hostname;
			}

			return $response;
		}

		protected function _verifyKeys() {
			$response = (
				(empty($this->settings['keys']['start']) === false) &&
				(empty($this->settings['keys']['stop']) === false)
			);

			if ($response === false) {
				return $response;
			}

			$setting = $this->fetch(array(
				'fields' => array(
					'id',
					'value'
				),
				'from' => 'settings',
				'sort' => array(
					'field' => 'modified',
					'order' => 'DESC'
				),
				'where' => array(
					'id' => 'keys',
					'value' => ($keys = sha1($this->settings['keys']['start'] . $this->settings['keys']['stop']))
				)
			));
			$response = ($setting !== false);

			if ($response === false) {
				return $response;
			}

			$response = ($keys === $setting['value']);

			if ($response === false) {
				$this->update(array(
					'data' => array(
						'value' => $keys
					),
					'in' => 'settings',
					'where' => array(
						'id' => 'keys'
					)
				));
				$this->update(array(
					'data' => array(
						'authentication_expires' => null,
						'authentication_username' => ''
					),
					'in' => 'users',
					'where' => array(
						'id' => 1
					)
				));
			}

			return $response;
		}

		public function configure($parameters = array()) {
			$response = array(
				'message' => 'Error configuring system, please try again.',
				'status_valid' => (
					(isset($parameters['data']['authentication_password']) === true) &&
					(isset($parameters['data']['authentication_whitelist']) === true)
				)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$parameters['data']['authentication_whitelist'] = $this->_sanitizeIps($parameters['data']['authentication_whitelist']);

			foreach ($parameters['data']['authentication_whitelist'] as $authenticationWhitelistIpVersion => $authenticationWhitelistIpVersionIps) {
				unset($parameters['data']['authentication_whitelist'][$authenticationWhitelistIpVersion]);
				$parameters['data']['authentication_whitelist'] = array_merge($parameters['data']['authentication_whitelist'], $authenticationWhitelistIpVersionIps);
			}

			if (empty($parameters['data']['password']) === true) {
				unset($parameters['data']['password']);
			}

			$userDataUpdated = $this->update(array(
				'data' => array_intersect_key($parameters['data'], array(
					'authentication_password' => true,
					'authentication_whitelist' => true
				),
				'in' => 'users',
				'where' => array(
					'id' => 1
				)
			));
			$response['status_valid'] = ($userDataUpdated === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['message'] = 'System configured successfully.';
			return $response;
		}

		public function count($parameters) {
			$query = ' FROM ' . $parameters['in'];

			if (
				(empty($parameters['where']) === false) &&
				(is_array($parameters['where']) === true)
			) {
				$query .= ' WHERE ' . implode(' AND ', $this->_formatQuery($parameters['from'], $parameters['where']));
			}

			$count = $this->_query('SELECT COUNT(id)' . $query);
			$response = false;

			if (isset($count['COUNT(id)']) === true) {
				$response = $count['COUNT(id)'];
			}

			return $response;
		}

		public function delete($parameters) {
			$query = 'DELETE FROM ' . $parameters['from'];

			if (
				(empty($parameters['where']) === false) &&
				(is_array($parameters['where']) === true)
			) {
				$query .= ' WHERE ' . implode(' AND ', $this->_parseQueryConditions($parameters['from'], $parameters['where']));
			}

			$response = $this->_query($query);
			return $response;
		}

		public function endpoint() {
			$response = $this->_request($_POST);
			return $response;
		}

		public function fetch($parameters) {
			$query = ' FROM ' . $parameters['from'];

			if (
				(empty($parameters['item_list_name']) === false) &&
				(empty($parameters['search'][$parameters['item_list_name']]) === false)
			) {
				$parameters['where']['_search'] = $parameters['search'][$parameters['item_list_name']];
			}

			if (
				(empty($parameters['where']) === false) &&
				(is_array($parameters['where']) === true)
			) {
				$query .= ' WHERE ' . implode(' AND ', $this->_parseQueryConditions($parameters['from'], $parameters['where']));
			}

			if (empty($parameters['sort']) === false) {
				$query .= ' ORDER BY ';

				if ($parameters['sort'] === 'random') {
					$query .= 'RAND()';
				} elseif (
					(empty($parameters['sort']['field']) === false) &&
					($sortField = $parameters['sort']['field'])
				) {
					if (empty($parameters['sort']['order']) === true) {
						$parameters['sort']['order'] = 'DESC';
					}

					$query .= $sortField . ' ' . $parameters['sort']['order'] . ', id DESC';
				}
			}

			if (
				(empty($parameters['fields']) === false) &&
				(is_array($parameters['fields']) === true)
			) {
				foreach ($parameters['fields'] as $fieldKey => $fieldName) {
					if (empty($this->settings['database']['structure'][$parameters['from']][$fieldName])) {
						unset($parameters['fields'][$fieldKey]);
					}
				}
			}

			$query = 'SELECT ' . (!empty($parameters['fields']) && is_array($parameters['fields']) ? implode(',', $parameters['fields']) : '*') . $query;

			if (empty($parameters['limit']) === false) {
				$query .= ' LIMIT ' . $this->_parseQueryValue($parameters['limit']);
			}

			if (empty($parameters['offset']) === false) {
				$query .= ' OFFSET ' . $this->_parseQueryValue($parameters['offset']);
			}

			$response = $this->_query($query, $parameters);
			return $response;
		}

		public function login($parameters) {
			$response = array(
				'message' => 'Error logging in to account, please try again.',
				'status_valid' => (empty($parameters['data']['password']) === false)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$userCount = $this->count(array(
				'in' => 'users',
				'where' => array(
					'authentication_password' => $parameters['data']['password'],
					'id' => 1
				)
			));
			$response['status_valid'] = (
				(is_int($userCount) === true) &&
				($userCount === 1)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$userDataUpdated = $this->update(array(
				'data' => array(
					'authentication_expires' => date('Y-m-d H:i:s', strtotime('+1 month')),
					'authentication_username' => sha1($this->settings['keys']['start'] . '_' . $parameters['settings']['session_id'])
				),
				'in' => 'users',
				'where' => array(
					'id' => 1
				)
			));
			$response['status_valid'] = (
				($userDataUpdated !== false) &&
				(empty($userDataUpdated) === false)
			);
			return $response;
		}

		public function route($parameters) {
			$response = false;

			if (empty($parameters['route']['file']) === false) {
				$methodName = str_replace('.php', '', basename($parameters['route']['file']));

				if (method_exists($this, $methodName) === true) {
					$response = array_merge($this->$methodName($parameters), array(
						'from' => basename(dirname($parameters['route']['file'])),
						'method' => $methodName
					));

					if (empty($response['user']['endpoint']) === false) {
						$response = array_intersect_key($response, array(
							'data' => true,
							'message' => true
						));
					}
				}
			}

			return $response;
		}

		public function save($parameters) {
			$queries = array();
			$response = true;

			if (empty($parameters['data']) === false) {
				if (is_numeric(key($parameters['data'])) === false) {
					$parameters['data'] = array(
						$parameters['data']
					);
				}

				foreach (array_chunk($parameters['data'], 1000) as $rows) {
					$groupValues = array();

					foreach ($rows as $row) {
						$fields = array_keys($row);
						$values = array_map(function($value) {
							if (is_bool($value) === true) {
								$value = (integer) $value;
							}

							if (is_null($value) === true) {
								$value = '_is_null_';
							}

							return $value;
						}, array_values($row));

						if (
							(in_array('created', $fields) === false) &&
							(in_array('id', $fields) === false)
						) {
							$fields[] = 'created';
							$values[] = date('Y-m-d H:i:s', time());
						}

						if (
							(in_array('modified', $fields) === false) &&
							(
								(isset($parameters['update_modified']) === false) ||
								($parameters['update_modified'] !== false)
							)
						) {
							$fields[] = 'modified';
							$values[] = date('Y-m-d H:i:s', time());
						}

						$groupValues[implode(',', $fields)][] = $this->settings['keys']['start'] . implode($this->settings['keys']['stop'] . ',' . $this->settings['keys']['start'], $values) . $this->settings['keys']['stop'];
					}

					foreach ($groupValues as $fields => $values) {
						$updateFields = explode(',', $fields);
						array_walk($updateFields, function(&$updateFieldValue, $updateFieldKey) {
							$updateFieldValue = $updateFieldValue . '=VALUES(' . $updateFieldValue . ')';
						});
						$queries[] = 'INSERT INTO ' . $parameters['to'] . '(' . $fields . ') VALUES (' . implode('),(', $values) . ') ON DUPLICATE KEY UPDATE ' . implode(',', $updateFields);
					}
				}

				foreach ($queries as $query) {
					$connection = $this->_query($query);

					if (empty($connection) === true) {
						$response = false;
						break;
					}
				}
			}

			return $response;
		}

		public function update($parameters) {
			$response = true;

			if (empty($parameters['data']) === false) {
				$query = 'UPDATE ' . $parameters['in'] . ' SET ';

				foreach ($parameters['data'] as $updateValueKey => $updateValue) {
					$query .= $this->settings['keys']['start'] . $updateValueKey . $this->settings['keys']['stop'] .  ' = ' . $this->settings['keys']['start'] . $updateValue . $this->settings['keys']['stop'] . ','
				}

				$query = rtrim($query, ',') . ' WHERE ' . implode(' AND ', $this->_parseQueryConditions($parameters['in'], $parameters['where']));
				$response = $this->_query($query);
			}

			return $response;
		}

	}

	if (
		(empty($system->parameters) === false) &&
		(empty($extend) === true)
	) {
		$systemMethods = new SystemMethods();
		$data = $systemMethods->route($system->parameters);
	}
?>
