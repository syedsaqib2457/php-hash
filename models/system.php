<?php
	class SystemModel extends Configuration {

		protected function _authenticate($parameters) {
			$response = false;

			if (
				!empty($parameters['settings']['session_id']) &&
				$this->_verifyKeys()
			) {
				$token = $this->fetch(array(
					'fields' => array(
						'foreign_key',
						'foreign_value',
						'id',
						'string'
					),
					'from' => 'tokens',
					'sort' => array(
						'field' => 'created',
						'order' => 'DESC'
					),
					'where' => array(
						'string' => $this->_createTokenString(array(
							'session_id' => $parameters['settings']['session_id']
						))
					)
				));

				if (!empty($token['count'])) {
					$response = true;
				}
			} else {
				$authenticateEndpoint = $this->_authenticateEndpoint($parameters);

				if ($authenticateEndpoint['message']['status'] === 'success') {
					$response = $authenticateEndpoint['data'];
				}
			}

			if ($response === true) {
				$user = $this->fetch(array(
					'from' => 'users'
				));

				if (!empty($user['count'])) {
					$response = $user['data'][0];
				}
			}

			return $response;
		}

		protected function _authenticateEndpoint($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error authenticating your endpoint request, please try again.')
				)
			);
			$user = $this->fetch(array(
				'from' => 'users'
			));

			if (!empty($user['count'])) {
				$response['message'] = array(
					'status' => 'error',
					'text' => 'IP ' . ($clientIp = $this->settings['client_ip']) . ' must be whitelisted in your account, please try again.'
				);
				$whitelistedIps = explode("\n", $user['data'][0]['whitelisted_ips']);

				if (in_array($clientIp, $whitelistedIps)) {
					$response['message']['status'] = 'success';
				}

				if ($response['message']['status'] === 'error') {
					$serverIp = $this->fetch(array(
						'from' => 'servers',
						'fields' => array(
							'ip'
						),
						'where' => array(
							'ip' => $clientIp
						)
					));

					if (!empty($serverIp['count'])) {
						$response['message']['status'] = 'success';
					}
				}

				if ($response['message']['status'] === 'success') {
					$response = array(
						'data' => array_merge($user['data'][0], array(
							'endpoint' => true
						)),
						'message' => array(
							'status' => 'success',
							'text' => 'Endpoint authenticated successfully.'
						)
					);
				}
			}

			return $response;
		}

		protected function _call($parameters = array()) {
			$response = false;
			$methodFromParts = explode('_', $parameters['method_from']);
			$modelName = implode('', array_map(function($methodFrom) {
				return ucwords($methodFrom);
			}, $methodFromParts)) . 'Model';
			$modelPath = $this->settings['base_path'] . '/models/' . $parameters['method_from'] . '.php';

			if (
				!class_exists($modelName) &&
				file_exists($modelPath)
			) {
				$configuration = new Configuration();
				require_once($modelPath);
			}

			if (empty($this->$modelName)) {
				$this->$modelName = new $modelName();
			}

			if (
				!empty($parameters['method_name']) &&
				method_exists($this->$modelName, $parameters['method_name'])
			) {
				$methodName = $parameters['method_name'];
				$methodParameters = !empty($parameters['method_parameters']) ? $parameters['method_parameters'] : array();
				$response = call_user_func_array(array($this->$modelName, $methodName), $methodParameters);
			}

			return $response;
		}

		protected function _createTokenString($parameters) {
			$response = array(
				$this->keys['start']
			);
			$tokenStringParameters = array(
				'fields' => array(
					'id'
				),
				'from' => $parameters['parameters_to_encode']['from'],
				'limit' => 1,
				'sort' => array(
					'field' => 'modified',
					'order' => 'DESC'
				)
			);

			if (!empty($parameters['parameters_to_encode']['sort'])) {
				$tokenStringParameters['sort'] = $parameters['parameters_to_encode']['sort'];
			}

			if (!empty($parameters['parameters_to_encode']['where'])) {
				$tokenStringParameters['where'] = $parameters['parameters_to_encode']['where'];
				$data = $this->fetch($tokenStringParameters);
				$response[] = $data['count'] . $data['data'][0];
			}

			if (!empty($parameters['session_id'])) {
				$response[] = $parameters['session_id'];
			}

			if (!empty($parameters['salt'])) {
				$response[] = $parameters['salt'];
			}

			$response = sha1(json_encode(implode('_', $response)));
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

		protected function _generateRandomAuthentication($authentication = array()) {
			$response = array();
			$letters = 'abcdefghijklmnopqrstuvwxyz';
			$numbers = '0123456789012345678901234567890123456789';
			$characters = str_shuffle(str_repeat($letters . $numbers, count($authentication)));
			$authenticationIndex = 0;

			foreach ($authentication as $authenticationKey => $authenticationValue) {
				$response[$authenticationKey] = (empty($authenticationValue) ? $letters[rand(0, 25)] : '') . substr((!empty($authenticationValue) ? $authenticationValue : '') . substr($characters, 14 * $authenticationIndex, 14), 0, 14);
				$authenticationIndex++;
			}

			return $response;
		}

		protected function _getToken($parameters) {
			$tokenParameters = array(
				'fields' => array(
					'encoded_parameters',
					'id'
				),
				'from' => 'tokens',
				'limit' => 1,
				'where' => array(
					'string' => $this->_createTokenString($parameters)
				)
			);

			if (
				!empty($parameters['foreign_key']) &&
				!empty($parameters['foreign_value'])
			) {
				$tokenParameters['where'] += array(
					'foreign_key' => $parameters['foreign_key'],
					'foreign_value' => $parameters['foreign_value']
				);
			}

			$token = $this->fetch($tokenParameters);

			if (!empty($token['count'])) {
				$tokenParameters['where']['id'] = $token['data'][0]['id'];
			}

			if (
				!empty($parameters['expiration_minutes']) &&
				is_numeric($parameters['expiration_minutes'])
			) {
				$tokenParameters['where']['expiration'] = date('Y-m-d H:i:s', strtotime('+' . $parameters['expiration_minutes'] . ' minutes'));
			}

			$encodedParameters = json_encode($parameters['parameters_to_encode']);
			$tokenData = array(
				$tokenParameters['where']
			);

			if (
				empty($token['data'][0]['encoded_parameters']) ||
				(
					!empty($encodedParameters) &&
					$encodedParameters !== $token['data'][0]['encoded_parameters']
				)
			) {
				$tokenData[0]['encoded_parameters'] = $encodedParameters;
			}

			$this->save(array(
				'data' => $tokenData,
				'to' => 'tokens'
			));
			$tokenParameters['fields'] = array(
				'created',
				'encoded_parameters',
				'expiration',
				'foreign_key',
				'foreign_value',
				'id',
				'string'
			);
			$response = $this->fetch($tokenParameters);

			if (!empty($response['data'][0]['encoded_parameters'])) {
				$response['data'][0]['parameters'] = json_decode($response['data'][0]['encoded_parameters'], true);
				unset($response['data'][0]['encoded_parameters']);
			}

			return !empty($response['data'][0]) ? $response['data'][0] : array();
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

		protected function _processAction($parameters) {
			$actionsProcessing = $response = array();
			$decodeItemList = (
				$parameters['action'] !== 'fetch' &&
				empty($parameters['encode_item_list'])
			);
			$itemListName = $parameters['item_list_name'] = !empty($parameters['item_list_name']) ? $parameters['item_list_name'] : $parameters['from'];
			$processAction = false;
			$tokenParameters = array(
				'parameters_to_encode' => array_intersect_key($parameters, array(
					'from' => true,
					'sort' => true
				))
			);
			$validTokens = true;
			$clearItems = !empty($parameters['item_list_name']) ? array(
				$parameters['item_list_name'] => array(
					'count' => 0,
					'data' => array(),
					'from' => $parameters['from'],
					'name' => $parameters['item_list_name']
				)
			) : array();
			$response['items'] = $parameters['items'] = isset($parameters['items']) ? $parameters['items'] : $clearItems;

			if (
				!empty($parameters['data']) &&
				is_array($parameters['data'])
			) {
				$parameters['data'] = $this->_parseFormDataItems($parameters['data']);
			}

			if ($validTokens === false) {
				$parameters['action'] = 'fetch';
				$response['items'] = $clearItems;

				if (!empty($parameters['items'][$itemListName]['data'])) {
					$response['message'] = array(
						'status' => 'error',
						'text' => 'Your selected items were modified by another process, please try again.'
					);
				}
			} else {
				$actionsProcessing = $this->fetch(array(
					'fields' => array(
						'encoded_parameters',
						'id',
						'progress'
					),
					'from' => 'actions',
					'where' => array(
						'processed' => false
					)
				));

				if (
					$decodeItemList &&
					empty($actionsProcessing['count']) &&
					!empty($parameters['items'][$itemListName]['data'])
				) {
					foreach ($parameters['items'] as $itemListKey => $itemList) {
						if (
							!empty($itemList['data'][1]) &&
							empty($parameters[$itemListKey]['enable_background_action_processing'])
						) {
							$decodeItemList = false;
							$parameters['items'][$itemListKey]['count'] = 10001;
						}
					}

					if ($decodeItemList) {
						$items = $this->_decodeItems($parameters);
						$itemKeyLineCount = count($parameters['items'][$itemListName]['data']);
						$parametersToEncode = array_intersect_key($parameters, array(
							'action' => true,
							'data' => true,
							'from' => true,
							'item_list_name' => true,
							'limit' => true,
							'offset' => true,
							'search' => true,
							'sort' => true,
							'where' => true
						));
						$parametersToEncode['item_count'] = $items[$itemListName]['count'];
						$actionData = array(
							array(
								'chunks' => $itemKeyLineCount,
								'encoded_items_to_process' => json_encode($items[$itemListName]['data']),
								'encoded_parameters' => json_encode($parametersToEncode),
								'processed' => false,
								'progress' => 0
							)
						);

						if (
							$decodeItemList &&
							$itemKeyLineCount === 1
						) {
							$processAction = true;

							if (is_string($parameters['items'][$itemListName]['data'][0])) {
								$parameters['items'] = $this->_decodeItems($parameters, true);
							}

							$actionData[0] = array_merge($actionData[0], array(
								'processed' => true,
								'progress' => 100
							));
						}
					}

					if ($this->save(array(
						'data' => $actionData,
						'to' => 'actions'
					))) {
						$response['processing'] = $actionData[0];

						if ($itemKeyLineCount > 1) {
							$parameters['action'] = 'fetch';
							$response['message'] = array(
								'status' => 'success',
								'text' => 'Action processing successfully.'
							);
						}
					} else {
						$response['message'] = array(
							'status' => 'error',
							'text' => 'Error processing action, please try again.'
						);
					}
				}

				if (!isset($response['processing'])) {
					$actionData = $this->fetch(array(
						'fields' => array(
							'chunks',
							'encoded_items_processed',
							'encoded_items_to_process',
							'encoded_parameters',
							'id',
							'processed',
							'progress'
						),
						'from' => 'actions',
						'limit' => 1,
						'where' => array(
							'processed' => false
						)
					));
					$response['processing'] = !empty($actionData['data'][0]) ? $actionData['data'][0] : false;
				}
			}

			if (!empty($parameters['redirect'])) {
				$response['redirect'] = $parameters['redirect'];
			}

			if (!empty($parameters['user']['endpoint'])) {
				if ($parameters['action'] === 'fetch') {
					$parameters['limit'] = 10000;
					$parameters['offset'] = 0;

					if (
						!empty($parameters['results_page']) &&
						is_numeric($parameters['results_page'])
					) {
						$parameters['offset'] = max(0, $parameters['results_page'] - 1);
					}

					if (
						!empty($parameters['results_per_page']) &&
						is_numeric($parameters['results_per_page'])
					) {
						$parameters['limit'] = min(10000, max(1, $parameters['results_per_page']));
					}
				} elseif (!empty($parameters['where'])) {
					$endpointItemIds = $this->fetch(array(
						'fields' => array(
							'id'
						),
						'from' => $parameters['from'],
						'limit' => 10000,
						'where' => $parameters['where']
					));

					if (!empty($endpointItemIds['count'])) {
						$parameters = array_merge($parameters, array(
							'items' => array(
								'list_endpoint_items' => array_intersect_key($endpointItemIds, array(
									'count' => true,
									'data' => true
								))
							),
							'item_list_name' => 'list_endpoint_items'
						));
					}
				}

				if (!empty($actionsProcessing['count'])) {
					$actionsProcessingParameters = json_decode($actionsProcessing['data'][0]['encoded_parameters'], true);
					return array(
						'message' => array(
							'status' => 'error',
							'text' => 'Action to ' . $actionsProcessingParameters['action'] . ' ' . $actionsProcessingParameters['item_count'] . ' selected ' . $actionsProcessingParameters['from'] . ' is currently processing at ' . $actionsProcessing['data'][0]['progress'] . '%, please wait and try again.'
						)
					);
				}
			}

			$action = $parameters['action'];
			$responseMessage = $response['message'];
			$response = array_merge($response, $this->$action($parameters, true));

			if (!empty($responseMessage)) {
				$response['message'] = $responseMessage;
			}

			if (
				$decodeItemList &&
				!empty($response['items'][$parameters['item_list_name']]['data'])
			) {
				$parameters['processing'] = $response['processing'];
				$response = array_merge($response, $this->_encodeItems($parameters));
			}

			if (!empty($parameters['items'])) {
				foreach ($parameters['items'] as $itemListName => $itemList) {
					if (!empty($itemList['token'])) {
						$response['items'][$itemListName]['token'] = $this->_getToken($this->_parseItemListTokenParameters($itemList['token']));
					}
				}
			}

			if (!empty($response['selected_items'])) {
				$response['items'] = array_merge($response['items'], $response['selected_items']);
			}

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
				'message' => array(
					'status' => 'error',
					'text' => 'Request parameters are required, please try again.'
				)
			);
			$clientIp = $this->settings['client_ip'];
			$validRequest = false;

			if (
				!empty($_POST['json']) &&
				is_string($_POST['json'])
			) {
				$response['message']['text'] = 'No results found, please try again.';
				$parameters = $this->_parseParameters(json_decode($_POST['json'], true), 'snake');

				if (empty($parameters['from'])) {
					$parameters['from'] = str_replace('-', '_', !empty($parameters['url']) ? basename($parameters['url']) : basename($_SERVER['REQUEST_URI']));
				}

				if (
					(
						empty($parameters['from']) ||
						(
							$parameters['from'] !== 'main' &&
							empty($this->settings['database']['schema'][$parameters['from']])
						)
					) ||
					(
						($parameters['action'] = !empty($parameters['action']) ? $parameters['action'] : 'fetch') &&
						!method_exists($this, $parameters['action'])
					) ||
					(
						isset($parameters['limit']) &&
						!is_int($parameters['limit'])
					) ||
					(
						isset($parameters['offset']) &&
						!is_int($parameters['offset'])
					) ||
					(
						(
							!empty($parameters['sort']['field']) &&
							empty($this->settings['database']['schema'][$parameters['from']][$parameters['sort']['field']])
						) ||
						(
							!empty($parameters['sort']['order']) &&
							!in_array(strtoupper($parameters['sort']['order']), array('ASC', 'DESC'))
						)
					) ||
					(
						!empty($parameters['where']) &&
						!is_array($parameters['where'])
					)
				) {
					$response['message']['text'] = 'Invalid request parameters, please try again.';
				} else {
					$response = array(
						'message' => array(
							'status' => 'error',
							'text' => 'Authentication required, please try again.'
						),
						'redirect' => '/servers?#login',
						'user' => false
					);
					$parameters = array_merge($parameters, array(
						'redirect' => '',
						'user' => $this->_authenticate($parameters)
					));

					if (
						($publicRequest = in_array($parameters['action'], $this->publicPermissions[$parameters['from']])) ||
						!empty($parameters['user']['id'])
					) {
						$queryResponse = $this->_processAction($parameters);
						$validRequest = $publicRequest && $queryResponse['message']['status'] === 'error' ? false : true;

						if (!empty($queryResponse)) {
							$response = array_merge($queryResponse, array(
								'user' => $parameters['user']
							));
						}
					}
				}
			}

			if (!empty($parameters['camel_case_response_keys'])) {
				$response = $this->_parseParameters($response, 'camel');
			}

			if ($validRequest === true) {
				$this->delete(array(
					'from' => 'public_request_limitations',
					'where' => array(
						'client_ip' => $clientIp
					)
				));
			} else {
				$publicRequestLimitationData = array();
				$publicRequestLimitations = $this->fetch(array(
					'fields' => array(
						'id',
						'request_attempts'
					),
					'from' => 'public_request_limitations',
					'limit' => 1,
					'where' => array(
						'client_ip' => $clientIp
					)
				));

				if (!empty($publicRequestLimitations['count'])) {
					$publicRequestLimitationData = $publicRequestLimitations['data'];
				} else {
					$publicRequestLimitationData = array(
						array(
							'client_ip' => $clientIp,
							'request_attempts' => 0
						)
					);
				}

				$publicRequestLimitationData[0]['request_attempts']++;
				$this->save(array(
					'data' => $publicRequestLimitationData,
					'to' => 'public_request_limitations'
				));
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
				'message' => array(
					'status' => 'error',
					'text' => 'Error logging in to account, please make sure cookies are enabled and try again.'
				)
			);
			$user = $this->fetch(array(
				'fields' => array(
					'id'
				),
				'from' => 'users',
				'where' => array(
					'password' => $parameters['data']['password']
				)
			));

			if (
				!empty($user['count']) &&
				$this->_getToken(array(
					'session_id' => $parameters['settings']['session_id']
				))
			) {
				$response['message']['status'] = 'success';
				$response['redirect'] = '/servers';
			}

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

		public function shellProcessRequestLogs() {
			// todo: limit prefixes instead of addresses for ipv6
			$response = array(
				'message' => 'Error processing request logs, please try again.',
				'status_valid' => false
			);
			$requestLogsToProcess = $this->fetch(array(
				'fields' => array(
					'id',
					'source_ip'
				),
				'from' => 'request_logs',
				'where' => array(
					'node_user_id' => null,
					'response_code >=' => 10
				)
			));

			if (empty($requestLogsToProcess) === false) {
				$requestLogsPath = $this->settings['base_path'] . '/request_logs/';

				if (is_dir($requestLogsPath) === false) {
					mkdir($requestLogsPath, 0755);
				}

				foreach ($requestLogsToProcess as $requestLogToProcess) {
					$requestLogToProcessPath = $requestLogsPath . implode('/', explode('.', $requestLogToProcessPath['source_ip'])) . '/';

					if (is_dir($requestLogToProcessPath) === false) {
						mkdir($requestLogToProcessPath, 0755, true);
					}

					$requestLogToProcessFile = $requestLogToProcessPath . '.';

					if (filemtime($requestLogToProcessFile) < strtotime('-1 hour')) {
						rmdir($requestLogToProcessPath);
					}
				}

				$this->delete(array(
					'from' => 'request_logs',
					'where' => array(
						'modified <' => date('Y-m-d H:i:s', strtotime('-1 hour')),
						'node_user_id' => null
					)
				));
				$response = array(
					'message' => array(
						'status' => 'success',
						'text' => 'Request logs processed successfully.'
					)
				);
			}

			return $response;
		}

		public function update($parameters) {
			$response = true;

			if (!empty($parameters['data'])) {
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
		!empty($configuration->parameters) &&
		empty($extend)
	) {
		$systemModel = new SystemModel();
		$data = $systemModel->route($configuration->parameters);
	}
?>
