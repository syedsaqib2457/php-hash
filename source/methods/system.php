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
			// todo: validate ipv6 private ip ranges
			$response = 'public';
			$ipInteger = ip2long($ip);

			foreach ($this->privateIpRangeIntegers as $privateIpRangeIntegerStart => $privateIpRangeIntegerEnd) {
				if (
					$ipInteger >= $privateIpRangeIntegerStart &&
					$ipInteger <= $privateIpRangeIntegerEnd
				) {
					$response = 'private';
				}
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

			$requestLogData = array(
				$requestLogData
			);
			$this->save(array(
				'data' => $requestLogData,
				'to' => 'request_logs'
			));
			return;
		}

		protected function _parseFormDataItem($formDataItemKey, $formDataItemValue) {
			$parsedFormDataItem = array(
				$formDataItemKey => $formDataItemValue
			);

			if (
				!empty($formDataItemKey) &&
				($openingBracketPosition = stripos($formDataItemKey, '[')) !== false &&
				($closingBracketPosition = strrpos($formDataItemKey, ']')) !== false &&
				$closingBracketPosition === (strlen($formDataItemKey) - 1)
			) {
				$parsedFormDataItemKey = substr_replace(substr($formDataItemKey, $openingBracketPosition), '', -1);
				$parsedFormDataItemKey = substr($parsedFormDataItemKey, 1);
				$parsedFormDataItem = array(
					substr_replace($formDataItemKey, '', $openingBracketPosition) => $this->_parseFormDataItem($parsedFormDataItemKey, $formDataItemValue)
				);
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
				if (($position = strpos($queryPart, $this->keys['stop'])) !== false) {
					$queryPart = str_replace($this->keys['stop'], '?', $queryPart);
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

				if (
					is_string($parameterValue) &&
					($jsonString = trim($parameterValue)) &&
					($jsonStringLength = strlen($jsonString)) &&
					(
						(
							stripos($jsonString, '{') === 0 &&
							(strrpos($jsonString, '}') + 1) === $jsonStringLength
						) ||
						(
							stripos($jsonString, '[{') === 0 &&
							(strrpos($jsonString, '}]') + 2) === $jsonStringLength
						)
					) &&
					($decodedJsonString = json_decode($jsonString, true)) &&
					is_array($decodedJsonString)
				) {
					$parameterValue = $decodedJsonString;
				}

				$parameters[$parameterKey] = $parameterValue;

				if (is_array($parameterValue)) {
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

					if (empty($exceptionKeyValues[$parameterKey])) {
						$parameterKeyCharacters[0] = strtolower($parameterKeyCharacters[0]);
					}

					$parameterKey = implode('', array_map(function($parameterKeyCharacter, $parameterKeyKey) {
						if (ctype_upper($parameterKeyCharacter)) {
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

				if (is_array($parameterValue)) {
					$parameters[$parameterKey] = $this->_parseParametersToSnakeCase($parameterValue);
				}
			}

			$response = $parameters;
			return $response;
		}

		protected function _parseParameters($parameters, $caseType) {
			$response = array();
			$parseMethod = '_parseParametersTo' . ucwords($caseType) . 'Case';

			if (method_exists($this, $parseMethod)) {
				foreach ($parameters as $parameterKey => $parameterValue) {
					$parameter = array(
						$parameterKey => $parameterValue
					);
					$parsedParameter = $this->$parseMethod($parameter);
					unset($parameters[$parameterKey]);
					$parameters = array_merge_recursive($parameters, $parsedParameter);
				}
			}

			$response = $parameters;
			return $response;
		}

		protected function _parseQueryConditions($from, $where = array(), $conjunction = 'OR') {
			$operators = array('>', '>=', '<', '<=', '=', '!=', 'LIKE');

			foreach ($where as $key => $value) {
				$conjunction = !empty($key) && in_array($key, array('AND', 'OR')) ? $key : $conjunction;
				$validQuery = true;

				if (
					is_array($value) &&
					count($value) != count($value, COUNT_RECURSIVE)
				) {
					$where[$key] = '(' . implode(') ' . $conjunction . ' (', $this->_parseQueryConditions($from, $value, $conjunction)) . ')';
				} else {
					if (!is_array($value)) {
						$value = array(
							$value
						);
					}

					array_walk($value, function(&$fieldValue, $fieldKey) use ($key, $operators) {
						$key = (strlen($fieldKey) > 1 && is_string($fieldKey) ? $fieldKey : $key);
						$fieldValue = (is_null($fieldValue) ? $key . ' IS NULL' : trim(in_array(trim(substr($key, strpos($key, ' '))), $operators) ? $key : $key . ' =') . ' ' . $this->_parseQueryValue($fieldValue));
					});
					$keyParts = explode(' ', $key);

					if (
						!in_array($key, array('AND', 'OR')) &&
						empty($this->settings['database']['schema'][$from][$keyParts[0]])
					) {
						unset($where[$key]);
						$validQuery = false;
					}

					if ($validQuery === true) {
						$where[$key] = implode(' ' . (strpos($key, '!=') !== false ? 'AND' : $conjunction) . ' ', $value);
					}
				}
			}

			$response = $where;
			return $response;
		}

		protected function _parseQueryValue($value) {
			$response = $this->keys['start'] . (is_bool($value) ? (integer) $value : $value) . $this->keys['stop'];
			return $response;
		}

		protected function _query($query, $parameters = array()) {
			$database = new PDO('mysql:host=' . $this->settings['database']['hostname'] . '; dbname=' . $this->settings['database']['name'] . ';', $this->settings['database']['username'], $this->settings['database']['password']);
			$database->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
			$database->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$parameterized = $this->_parseParameterizedQuery($query);

			if (empty($parameterized['parameterizedQuery'])) {
				return false;
			}

			$connection = $database->prepare($parameterized['parameterizedQuery']);
			$data = array();

			if (
				empty($connection) ||
				!is_object($connection)
			) {
				return false;
			}

			$findDataRowPartSize = 100000;
			$queryParts = array_fill(0, max(1, ($hasResults ? ceil($parameters['limit'] / $findDataRowPartSize) : 1)), true);

			foreach ($queryParts as $queryPartKey => $queryPartValue) {
				if (empty($parameters['limit']) === false) {
					end($parameterized['parameterizedValues']);
					$offset = $parameterized['parameterizedValues'][key($parameterized['parameterizedValues'])] = $parameters['offset'] + ($queryPartKey * $findDataRowPartSize);
					$limit = prev($parameterized['parameterizedValues']);

					if ($parameters['limit'] > $findDataRowPartSize) {
						if ($parameters['limit'] < (($queryPartKey + 1) * $limit)) {
							$limit = $parameters['limit'] + $parameters['offset'] - $offset;
						} else {
							$limit = $findDataRowPartSize;
						}
					}

					$parameterized['parameterizedValues'][key($parameterized['parameterizedValues'])] = $limit;
				}

				if (
					!empty($parameterized['parameterizedValues']) &&
					is_array($parameterized['parameterizedValues'])
				) {
					foreach ($parameterized['parameterizedValues'] as $parameterizedValueKey => $parameterizedValue) {
						if ($parameterizedValue === $this->keys['salt'] . 'is_null' . $this->keys['salt']) {
							$parameterized['parameterizedValues'][$parameterizedValueKey] = null;
						}
					}
				}

				$execute = $connection->execute($parameterized['parameterizedValues']);
				$data[] = $connection->fetchAll(PDO::FETCH_ASSOC);
			}

			$response = empty($data[0]) === false ? call_user_func_array('array_merge', $data) : $execute;

			if (
				is_array($response) === true &
				empty($response[1]) === true
			) {
				$response = current($response);
			}

			$connection->closeCursor();
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

			if (!is_array($ips)) {
				$ips = explode("\n", $ips);
			}

			$ips = array_values(array_filter($ips));

			foreach ($ips as $ip) {
				$ipVersion = 4;
				$validatedIp = false;

				if (
					empty($ip) ||
					!($ip = trim($ip, '.')) ||
					(
						strpos($ip, ':') !== false &&
						strpos($ip, ':::') === false &&
						($ipVersion = 6) &&
						($validatedIp = $this->_validateIp($ip, $ipVersion, $allowSubnets, $allowSubnetParts)) === false
					) ||
					(
						empty($validatedIp) &&
						($validatedIp = $this->_validateIp($ip, $ipVersion, $allowSubnets, $allowSubnetParts)) === false
					)
				) {
					continue;
				}

				$validatedIps[$ipVersion][$validatedIp] = $validatedIp;
			}

			$response = $validatedIps;
			return $response;
		}

		protected function _validateIp($ip, $ipVersion, $allowSubnets = false, $allowSubnetParts = false) {
			$response = false;

			switch ($ipVersion) {
				case 4:
					$ipSubnetParts = explode('.', $ip);

					if (
						count($ipSubnetParts) === 4 ||
						$allowSubnetParts === true
					) {
						foreach ($ipSubnetParts as $ipSubnetPartKey => $ipSubnetPart) {
							if (
								!is_numeric($ipSubnetPart) ||
								strlen(intval($ipSubnetPart)) >= 4 ||
								$ipSubnetPart > 255 ||
								$ipSubnetPart < 0
							) {
								if (
									$allowSubnets === true &&
									$ipSubnetPart === end($ipSubnetParts) &&
									substr_count($ipSubnetPart, '/') === 1 &&
									($ipSubnetMaskParts = explode('/', $ipSubnetPart)) &&
									is_numeric($ipSubnetMaskParts[0]) &&
									($ipSubnetPart = (integer) $ipSubnetMaskParts[0]) !== false &&
									strlen($ipSubnetPart) >= 1 &&
									strlen($ipSubnetPart) <= 3 &&
									$ipSubnetPart <= 255 &&
									$ipSubnetPart >= 0 &&
									is_numeric($ipSubnetMaskParts[1]) &&
									$ipSubnetMaskParts[1] <= 30 &&
									$ipSubnetMaskParts[1] >= 8
								) {
									$ipSubnetPart .= '/' . $ipSubnetMaskParts[1];
								} else {
									return false;
								}
							} else {
								$ipSubnetPart = (integer) $ipSubnetPart;
							}

							$ipSubnetParts[$ipSubnetPartKey] = $ipSubnetPart;
						}

						$response = implode('.', $ipSubnetParts);
					}

					break;
				case 6:
					if (strpos($ip, '::') !== false) {
						$ip = str_replace('::', str_repeat(':0', 7 - (substr_count($ip, ':') - 1)) . ':', $ip);

						if ($ip[0] === ':') {
							$ip = '0' . $ip;
						}
					}

					$ipSubnetParts = explode(':', $ip);
					$validCharacters = '0123456789ABCDEF';

					if (count($ipSubnetParts) === 8) {
						foreach ($ipSubnetParts as $ipSubnetPart) {
							if (strlen($ipSubnetPart) > 4) {
								return false;
							}

							if (!is_numeric($ipSubnetPart)) {
								foreach (range(0, strlen($ipSubnetPart) - 1) as $ipSubnetPartIndex) {
									if (strpos($validCharacters, $ipSubnetPart[$ipSubnetPartIndex]) === false) {
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
				is_numeric($port) &&
				(
					$port >= 1 &&
					$port <= 65535
				)
			) {
				$response = (integer) trim($port);
			}

			return $response;
		}

		protected function _verifyKeys() {
			$response = false;

			if (
				!empty($this->keys['salt']) &&
				!empty($this->keys['start']) &&
				!empty($this->keys['stop'])
			) {
				$string = sha1($this->keys['salt'] . $this->keys['start'] . $this->keys['stop']);
				$keys = $this->fetch(array(
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
						'id' => 'keys'
					)
				));

				if (!empty($keys['count'])) {
					$response = true;
				}

				if (
					empty($keys['count']) ||
					(
						!empty($keys['count']) &&
						$keys['data'][0]['value'] !== $string
					)
				) {
					$response = false;
					$user = $this->fetch(array(
						'fields' => array(
							'id',
							'password',
						),
						'from' => 'users'
					));

					if (!empty($user['count'])) {
						$userData = array(
							array(
								'id' => $user['data'][0]['id'],
								'password' => ''
							)
						);
						$this->save(array(
							'data' => $userData,
							'to' => 'users'
						));
					}

					$this->delete(array(
						'from' => 'tokens'
					));
					$settingData = array(
						array(
							'id' => 'keys',
							'value' => $string
						)
					);
					$this->save(array(
						'data' => $settingData,
						'to' => 'settings'
					));
				}
			}

			return $response;
		}

		public function configure($parameters = array()) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error configuring system, please try again.')
				)
			);

			if (
				isset($parameters['data']['account_password']) &&
				isset($parameters['data']['account_whitelisted_ips'])
			) {
				$whitelistedIps = array();

				if (!empty($parameters['data']['account_whitelisted_ips'])) {
					foreach ($this->_validateIps($parameters['data']['account_whitelisted_ips']) as $validatedWhitelistedIpVersionIps) {
						$whitelistedIps += $validatedWhitelistedIpVersionIps;
					}
				}

				$parameters['data'] = array(
					'id' => 1,
					'password' => $parameters['data']['account_password'],
					'whitelisted_ips' => ($whitelistedIps = implode("\n", $whitelistedIps))
				);

				if (empty($parameters['data']['password'])) {
					unset($parameters['data']['password']);
				}

				if ($this->save(array(
					'data' => array(
						$parameters['data']
					),
					'to' => 'users'
				))) {
					$response = array(
						'data' => array(
							'whitelisted_ips' => $whitelistedIps
						),
						'message' => array(
							'status' => 'success',
							'text' => 'System configured successfully.'
						)
					);
				}
			}

			return $response;
		}

		public function count($parameters) {
			$query = ' FROM ' . $parameters['in'];

			if (
				empty($parameters['where']) === false &&
				is_array($parameters['where']) === true
			) {
				$query .= ' WHERE ' . implode(' AND ', $this->_formatQuery($parameters['from'], $parameters['where']));
			}

			$count = $this->_query('SELECT COUNT(id)' . $query);
			$response = isset($count[0]['COUNT(id)']) === true ? $count[0]['COUNT(id)'] : false;
			return $response;
		}

		public function delete($parameters) {
			$query = 'DELETE FROM ' . $parameters['from'];

			if (
				!empty($parameters['where']) &&
				is_array($parameters['where'])
			) {
				$query .= ' WHERE ' . implode(' AND ', $this->_parseQueryConditions($parameters['from'], $parameters['where']));
			}

			$response = $this->_query($query);
			return $response;
		}

		public function endpoint() {
			return $this->_request($_POST);
		}

		public function fetch($parameters) {
			$query = ' FROM ' . $parameters['from'];

			if (
				!empty($parameters['item_list_name']) &&
				!empty($parameters['search'][$parameters['item_list_name']])
			) {
				$parameters['where']['_search'] = $parameters['search'][$parameters['item_list_name']];
			}

			if (
				!empty($parameters['where']) &&
				is_array($parameters['where'])
			) {
				$query .= ' WHERE ' . implode(' AND ', $this->_parseQueryConditions($parameters['from'], $parameters['where']));
			}

			if (!empty($parameters['sort'])) {
				$query .= ' ORDER BY ';

				if ($parameters['sort'] === 'random') {
					$query .= 'RAND()';
				} elseif (
					!empty($parameters['sort']['field']) &&
					($sortField = $parameters['sort']['field'])
				) {
					$query .= $sortField . ' ' . (!empty($parameters['sort']['order']) ? $parameters['sort']['order'] : 'DESC') . ', ' . implode(' DESC, ', array_diff(array('modified', 'created', 'id'), array($sortField))) . ' DESC';
				}
			}

			if (
				!empty($parameters['fields']) &&
				is_array($parameters['fields'])
			) {
				foreach ($parameters['fields'] as $fieldKey => $fieldName) {
					if (empty($this->settings['database']['schema'][$parameters['from']][$fieldName])) {
						unset($parameters['fields'][$fieldKey]);
					}
				}
			}

			$parameters = array_merge($parameters, array(
				'limit' => empty($parameters['limit']) === false ? $parameters['limit'] : 10000,
				'offset' => empty($parameters['offset']) === false ? $parameters['offset'] : 0
			));
			$query = 'SELECT ' . (!empty($parameters['fields']) && is_array($parameters['fields']) ? implode(',', $parameters['fields']) : '*') . $query;
			$query .= ' LIMIT ' . $this->_parseQueryValue($parameters['limit']) . ' OFFSET ' . $this->_parseQueryValue($parameters['offset']);
			$data = $this->_query($query, $parameters);
			$response = $data;

			if (
				$data !== false &&
				empty($data) === true
			) {
				$response['message'] = array(
					'status' => 'error',
					'text' => 'No ' . str_replace('_', ' ', $parameters['from']) . ' found.'
				);
			}

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

			if (
				!empty($parameters['route']['file']) &&
				($action = str_replace('.php', '', basename($parameters['route']['file']))) &&
				method_exists($this, $action)
			) {
				$response = array_merge($this->$action($parameters), array(
					'action' => $action,
					'from' => basename(dirname($parameters['route']['file']))
				));

				if (!empty($response['user']['endpoint'])) {
					$response = array_intersect_key($response, array(
						'data' => true,
						'message' => true
					));
				}
			}

			return $response;
		}

		public function save($parameters) {
			$queries = array();
			$response = true;

			if (!empty($parameters['data'])) {
				foreach (array_chunk($parameters['data'], 1000) as $rows) {
					$groupValues = array();

					foreach ($rows as $row) {
						$fields = array_keys($row);
						$values = array_map(function($value) {
							if (is_bool($value)) {
								$value = (integer) $value;
							}

							if (is_null($value)) {
								$value = $this->keys['salt'] . 'is_null' . $this->keys['salt'];
							}

							return $value;
						}, array_values($row));

						if (
							!in_array('created', $fields) &&
							!in_array('id', $fields)
						) {
							$fields[] = 'created';
							$values[] = date('Y-m-d H:i:s', time());
						}

						if (
							!in_array('modified', $fields) &&
							(
								!isset($parameters['update_modified']) ||
								$parameters['update_modified'] !== false
							)
						) {
							$fields[] = 'modified';
							$values[] = date('Y-m-d H:i:s', time());
						}

						$groupValues[implode(',', $fields)][] = $this->keys['start'] . implode($this->keys['stop'] . ',' . $this->keys['start'], $values) . $this->keys['stop'];
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

					if (empty($connection)) {
						$response = false;
					}
				}
			}

			return $response;
		}

		public function settings($parameters = array()) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error saving settings, please try again.')
				)
			);

			if (
				isset($parameters['data']['account_password']) &&
				isset($parameters['data']['account_whitelisted_ips'])
			) {
				$whitelistedIps = array();

				if (!empty($parameters['data']['account_whitelisted_ips'])) {
					foreach ($this->_validateIps($parameters['data']['account_whitelisted_ips']) as $validatedWhitelistedIpVersionIps) {
						$whitelistedIps += $validatedWhitelistedIpVersionIps;
					}
				}

				$parameters['data'] = array(
					'id' => 1,
					'password' => $parameters['data']['account_password'],
					'whitelisted_ips' => ($whitelistedIps = implode("\n", $whitelistedIps))
				);

				if (empty($parameters['data']['password'])) {
					unset($parameters['data']['password']);
				}

				if ($this->save(array(
					'data' => array(
						$parameters['data']
					),
					'to' => 'users'
				))) {
					$response = array(
						'data' => array(
							'whitelisted_ips' => $whitelistedIps
						),
						'message' => array(
							'status' => 'success',
							'text' => 'Settings saved successfully.'
						)
					);
				}
			}

			return $response;
		}

		public function update($parameters) {
			$response = true;

			if (empty($parameters['data']) === false) {
				$query = 'UPDATE ' . $parameters['in'] . ' SET ';

				foreach ($parameters['data'] as $updateValueKey => $updateValue) {
					$query .= $this->keys['start'] . $updateValueKey . $this->keys['stop'] .  ' = ' . $this->keys['start'] . $updateValue . $this->keys['stop'] . ','
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
