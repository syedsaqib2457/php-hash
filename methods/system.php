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
						'authentication_username' => sha1($this->settings['keys']['start'] . '_' . $parameters['settings']['session_id']),
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
				$authenticationToken = false;

				if (empty($parameters['where']['token']) !== false) {
					$authenticationToken = $parameters['where']['token'];
				}

				$response = $this->_authenticateEndpoint($response, $authenticationToken);
			}

			return $response;
		}

		protected function _authenticateEndpoint($parameters, $authenticationToken) {
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

			if (empty($authenticationToken) === false) {
				$node = $this->fetch(array(
					'fields' => array(
						'id'
					),
					'from' => 'nodes',
					'where' => array(
						'token' => $authenticationToken
					)
				));
				$response['status_valid'] = ($node !== false);

				if ($response['status_valid'] === false) {
					return $response;
				}

				$response['status_valid'] = (empty($node) === false);

				if ($response['status_valid'] === false) {
					$this->_logUnauthorizedRequest();
					return $response;
				}

				$response['user']['node_id'] = $node['id'];
			}

			if ($response['status_valid'] === false) {
				// todo: add each whitelisted username + IP to database for performance
				$response['status_valid'] = (in_array($_SERVER['REMOTE_ADDR'], explode("\n", $parameters['user']['authentication_whitelist'])) === true);

				if ($response['status_valid'] === false) {
					$this->_logUnauthorizedRequest();
					return $response;
				}
			}

			$response['user']['endpoint'] = true;
			return $response;
		}

		protected function _detectIpType($ip, $ipVersion) {
			$response = 'public';

			switch ($ipVersion) {
				case 4:
					$ipInteger = ip2long($ip);

					foreach ($this->settings['reserved_network']['ip_ranges'][4] as $reservedNetworkIpRangeIntegerStart => $reservedNetworkIpRangeIntegerEnd) {
						if (
							($ipInteger >= $reservedNetworkIpRangeIntegerStart) &&
							($ipInteger <= $reservedNetworkIpRangeIntegerEnd)
						) {
							$response = 'reserved';
						}
					}

					break;
				case 6:
					$ipParts = explode(':', $ip);

					foreach ($ipParts as $ipPartKey => $ipPart) {
						$ipParts[$ipPartKey] = str_pad($ipPart, 4, '0', STR_PAD_LEFT);
					}

					$ipRanges = array(
						implode(':', $ipParts)
					);

					if (count($ipParts) === 7) {
						array_pop($ipParts);
						$ipRanges[] = implode(':', $ipParts) . ':y';
						// todo: add correct IP type detection for IPv4-mapped addresses
					} else {
						$ipRangeVariables = str_repeat(':x', 4);
						$ipParts = array_slice($ipParts, 0, count($ipParts) - 4);
						$ipRanges[] = implode(':', $ipParts) . $ipRangeVariables;
						$ipRangeVariables .= str_repeat(':x', 2);
						$ipParts = array_slice($ipParts, 0, count($ipParts) - 2);
						$ipRanges = array_merge($ipRanges, array(
							implode(':', $ipParts) . $ipRangeVariables,
							$ipParts[0] . ':' . substr($ipParts[1], 0, 3) . 'x' . $ipRangeVariables
						));
						$ipRangeVariables .= ':x';
						$ipRanges = array_merge($ipRanges, array(
							$ipParts[0] . $ipRangeVariables,
							substr($ipParts[0], 0, 2) . 'x' . $ipRangeVariables
						));

						if (array_intersect($ipRanges, $this->settings['reserved_network']['ip_ranges'][6]) !== array()) {
							$response = 'reserved';
						}
					}

					break;
			}

			return $response;
		}

		protected function _logUnauthorizedRequest() {
			$systemRequestLogs = $this->fetch(array(
				'fields' => array(
					'id',
					'request_attempts'
				),
				'from' => 'system_request_logs',
				'where' => array(
					'source_ip' => $_SERVER['REMOTE_ADDR'],
					'status_authorized' => false
				)
			));
			$systemRequestLogData = array(
				'source_ip' => $_SERVER['REMOTE_ADDR'],
				'request_attempts' => 1
			);

			if (
				($systemRequestLogs !== false) &&
				(empty($systemRequestLogs) === false)
			) {
				$systemRequestLogs['request_attempts']++;
				$systemRequestLogData = $systemRequestLogs;
			}

			$this->save(array(
				'data' => array(
					$systemRequestLogData
				),
				'to' => 'system_request_logs'
			));
			return;
		}

		protected function _parseFormDataItem($formDataItemKey, $formDataItemValue) {
			$parsedFormDataItem = array(
				$formDataItemKey => $formDataItemValue
			);

			if (empty($formDataItemKey) === false) {
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

						$where[$key] = implode(' ' . $conjunction . ' ', $value);
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
				$this->_logUnauthorizedRequest();
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
				$this->_logUnauthorizedRequest();
			} else {
				$response = $this->_authenticate($parameters);
				$parameters['user'] = $response['user'];

				if (
					(empty($parameters['user']) === true) &&
					($parameters['method'] !== 'login')
				) {
					if ($response['status_valid'] === false) {
						$this->_logUnauthorizedRequest();
					}
				} else {
					$methodName = $parameters['method'];
					$response = $this->$methodName($parameters);

					if (
						($response['status_valid'] === false) &&
						($parameters['method'] === 'login')
					) {
						$this->_logUnauthorizedRequest();
					} else {
						$response = array_merge($response, array(
							'user' => $parameters['user']
						));
						$this->delete(array(
							'from' => 'system_request_logs',
							'where' => array(
								'source_ip' => $_SERVER['REMOTE_ADDR'],
								'status_authorized' => false
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

		protected function _sanitizeIps($ips = array(), $allowRanges = false) {
			$validatedIps = array();

			if (is_array($ips) === false) {
				$ips = explode("\n", $ips);
			}

			$ips = array_values(array_filter($ips));

			foreach ($ips as $ip) {
				$ipVersion = 4;
				$validatedIp = false;

				if (empty($ip) === false) {
					if (
						(strpos($ip, ':') !== false) &&
						(strpos($ip, ':::') === false)
					) {
						$ipVersion = 6;
					}

					if (empty($validatedIps[$ipVersion][$ip]) === true) {
						$validatedIp = $this->_validateIp($ip, $ipVersion, $allowRanges);

						if ($validatedIp === false) {
							continue;
						}

						$validatedIps[$ipVersion][$validatedIp] = $validatedIp;
					}
				}
			}

			$response = $validatedIps;
			return $response;
		}

		protected function _validateHostname($hostname) {
			$response = false;

			if (
				(strpos($hostname, '://') === false) &&
				(filter_var('http://' . $hostname, FILTER_SANITIZE_URL) === filter_var('http://' . $hostname, FILTER_VALIDATE_URL))
			) {
				$response = $hostname;
			}

			return $response;
		}

		protected function _validateIp($ip, $ipVersion, $allowRanges = false) {
			$response = false;

			switch ($ipVersion) {
				case 4:
					$ipParts = explode('.', $ip);

					if (count($ipParts) === 4) {
						$ip = '';

						foreach ($ipParts as $ipPartKey => $ipPart) {
							if (
								(is_numeric($ipPart) === false) ||
								(strlen(intval($ipPart)) >= 4) ||
								($ipPart > 255) ||
								($ipPart < 0)
							) {
								if (
									($allowRanges === false) ||
									($ipPart !== end($ipParts)) ||
									(substr_count($ipPart, '/') !== 1)
								) {
									return false;
								}

								$ipBlockParts = explode('/', $ipPart);

								if (
									(is_numeric($ipBlockParts[0]) === false) ||
									(strlen(intval($ipBlockParts[0])) > 3) ||
									($ipBlockParts[0] > 255) ||
									($ipBlockParts[0] < 0) ||
									(is_numeric($ipBlockParts[1]) === false) ||
									($ipBlockParts[1] > 30) ||
									($ipBlockParts[1] < 8)
								) {
									return false;
								}

								$ip .= '.' . intval($ipBlockParts[0]) . '/' . intval($ipBlockParts[1]);
							} else {
								if ($ipPartKey !== 0) {
									$ip .= '.';
								}

								$ip .= intval($ipPart);
							}
						}

						$response = $ip;
					}

					break;
				case 6:
					$validIpPartLetters = 'ABCDEF';

					if (strpos($ip, '::') !== false) {
						$ipDelimiterCount = substr_count($ip, ':') - 2;

						if (strpos($ip, '.') !== false) {
							$ipDelimiterCount = 1;
						}

						if (
							(empty($ip[2]) === true) ||
							($ip[2] === '/')
						) {
							$ipDelimiterCount = -1;
						}

						$ip = trim(str_replace('::', str_repeat(':0000', 7 - $ipDelimiterCount) . ':', $ip), ':');

						if (strpos($ip, ':/') !== false) {
							$ip = str_replace(':/', '/', $ip);
						}
					}

					$ipParts = explode(':', strtoupper($ip));
					$mappedIpVersion4 = false;

					if (count($ipParts) === 7) {
						$mappedIpVersion4 = validateIp(end($ipParts), 4);
					}

					if (
						(count($ipParts) === 8) ||
						($mappedIpVersion4 !== false)
					) {
						$ip = '';

						foreach ($ipParts as $ipPartKey => $ipPart) {
							if (
								($mappedIpVersion4 === false) &&
								(ctype_alnum($ipPart) === false)
							) {
								if (empty($ipParts[($ipPartKey + 1)]) === false) {
									return false;
								}

								$ipBlockParts = explode('/', $ipPart);

								if (
									($allowRanges === false) ||
									(isset($ipBlockParts[1]) === false) ||
									(isset($ipBlockParts[2]) === true) ||
									(is_numeric($ipBlockParts[1]) === false) ||
									($ipBlockParts[1] > 128) ||
									($ipBlockParts[1] < 0)
								) {
									return false;
								}

								$ipPart = current($ipBlockParts);
							}

							if (
								($ipPart !== $mappedIpVersion4) &&
								(isset($ipPart[4]) === true)
							) {
								return false;
							}

							if (
								($mappedIpVersion4 === false) &&
								(is_numeric($ipPart) === false)
							) {
								if (ctype_alnum($ipPart) === false) {
									return false;
								}

								foreach (range(0, (strlen($ipPart) - 1)) as $ipPartCharacterIndex) {
									if (
										(is_numeric($ipPart[$ipPartCharacterIndex]) === false) &&
										(strpos($validIpPartLetters, $ipPart[$ipPartCharacterIndex]) === false)
									) {
										return false;
									}
								}
							}

							if ($ipPartKey !== 0) {
								$ip .= ':';
							}

							$ip .= str_pad($ipPart, 4, '0', STR_PAD_LEFT);

							if (isset($ipBlockParts[1]) === true) {
								$ip .= '/' . $ipBlockParts[1];
							}
						}

						$response = $ip;
					}

					break;
			}

			return $response;
		}

		protected function _validatePortNumber($portNumber) {
			$response = false;

			if (
				(is_numeric($portNumber) === true) &&
				(
					($portNumber >= 1) &&
					($portNumber <= 65535)
				)
			) {
				$response = intval(trim($portNumber));
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

			$usersUpdated = $this->update(array(
				'data' => array_intersect_key($parameters['data'], array(
					'authentication_password' => true,
					'authentication_whitelist' => true
				)),
				'in' => 'users',
				'where' => array(
					'id' => 1
				)
			));
			$response['status_valid'] = ($usersUpdated === true);

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

			$usersUpdated = $this->update(array(
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
				($usersUpdated !== false) &&
				(empty($usersUpdated) === false)
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
					$query .= $this->settings['keys']['start'] . $updateValueKey . $this->settings['keys']['stop'] .  ' = ' . $this->settings['keys']['start'] . $updateValue . $this->settings['keys']['stop'] . ',';
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
