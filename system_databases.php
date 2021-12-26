<?php
	// todo: create functionality to allow API access to list specific chronologically-sorted request logs in multiple databases for a custom date range

	if (empty($parameters) === true) {
		exit;
	}

	function _connect($systemDatabases, $existingSystemDatabases, $response) {
		foreach ($systemDatabases as $systemDatabase) {
			if (
				(empty($existingSystemDatabases) === false) &&
				(empty($existingSystemDatabases[$systemDatabase]) === false)
			) {
				continue;
			}

			$systemDatabaseParameters = array(
				'data' => array(
					'authentication_credential_hostname',
					'authentication_credential_password',
					'id',
					'table_name'
				),
				'in' => $existingSystemDatabases['system_databases'],
				'limit' => 1,
				'sort' => array(
					'created_timestamp' => 'descending'
				),
				'where' => array(
					'table_name' => $systemDatabase
				)
			);

			if (is_int(strpos($systemDatabase, '__')) === true) {
				$systemDatabaseParts = explode('__', $systemDatabase);

				if (
					(isset($systemDatabaseParts[1]) === false) ||
					(isset($systemDatabaseParts[2]) === true)
				) {
					$response['message'] = 'Invalid system database tag for ' . $systemDatabase . ', please try again.';
					unset($response['_connect']);
					_output($response);
				}

				$systemDatabaseParameters['where'] = array(
					'table_name' => $systemDatabaseParts[0],
					'tag' => $systemDatabaseParts[1]
				);
			}

			$systemDatabase = _list($systemDatabaseParameters, $response);
			$systemDatabase = current($systemDatabase);

			if (empty($systemDatabase) === true) {
				$response['message'] = 'Invalid system database ' . $systemDatabase['table_name'] . ', please try again.';
				unset($response['_connect']);
				_output($response);
			}

			$response['_connect'][$systemDatabase['table_name']] = array(
				'connection' => mysqli_connect($systemDatabase['authentication_credential_hostname'], 'root', $systemDatabase['authentication_credential_password'], 'ghostcompute'),
				'structure'=> array(
					'table_name' => $systemDatabaseParameters['where']['table_name']
				)
			);

			if ($response['_connect'][$systemDatabase['table_name']]['connection'] === false) {
				$response['message'] = 'Error connecting to ' . $systemDatabase['table_name'] . ' system database, please try again.';
				unset($response['connect']);
				_output($response);
			}

			$systemDatabaseColumns = _list(array(
				'data' => array(
					'name'
				),
				'in' => $existingSystemDatabases['system_database_columns'],
				'where' => array(
					'system_database_id' => $systemDatabase['id']
				)
			), $response);

			if (empty($systemDatabaseColumns) === true) {
				$response['message'] = 'Invalid system database columns in ' . $systemDatabase['table_name'] . ' system database, please try again.';
				unset($response['_connect']);
				_output($response);
			}

			foreach ($systemDatabaseColumns as $systemDatabaseColumn) {
				$response['_connect'][$systemDatabase['table_name']]['structure']['column_names'][] = [$systemDatabaseColumn['name']];
			}
		}

		return $response['_connect'];
	}

	function _count($parameters, $response) {
		$command = 'SELECT COUNT(id) FROM ' . $parameters['in']['structure']['table_name'];

		if (empty($parameters['where']) === false) {
			$command .= ' WHERE ' . implode(' AND ', _parseCommandWhereConditions($parameters['where']));
		}

		$commandResponse = mysqli_query($parameters['in']['connection'], $command);

		if ($commandResponse === false) {
			$response['message'] = 'Error counting data in ' . $parameters['in']['structure']['table_name'] . ' system database, please try again.';
			_output($response);
		}

		$commandResponse = mysqli_fetch_assoc($commandResponse);

		if ($commandResponse === false) {
			$response['message'] = 'Error counting data in ' . $parameters['in']['structure']['table_name'] . ' system database, please try again.';
			_output($response);
		}

		$commandResponse = current($commandResponse);

		if (is_int($commandResponse) === false) {
			$response['message'] = 'Error counting data in ' . $parameters['in']['structure']['table_name'] . ' system database, please try again.';
			_output($response);
		}

		return $commandResponse;
	}

	function _delete($parameters, $response) {
		$command = 'DELETE FROM ' . $parameters['in']['structure']['table_name'];

		if (empty($parameters['where']) === false) {
			$command .= ' WHERE ' . implode(' AND ', _parseCommandWhereConditions($parameters['where']));
		}

		$commandResponse = mysqli_query($parameters['in']['connection'], $command);

		if ($commandResponse === false) {
			$response['message'] = 'Error deleting data in ' . $parameters['in']['structure']['table_name'] . ' system database, please try again.';
			_output($response);
		}

		return true;
	}

	function _list($parameters, $response) {
		if (empty($parameters['data']) === true) {
			$parameters['data'] = array(
				'*'
			);
		}

		$command = 'SELECT ' . implode(',', $parameters['data']) . ' FROM ' . $parameters['in']['structure']['table_name'];

		if (empty($parameters['where']) === false) {
			$command .= ' WHERE ' . implode(' AND ', _parseCommandWhereConditions($parameters['where']));
		}

		if (empty($parameters['sort']) === false) {
			$command .= ' ORDER BY ';

			if ($parameters['sort'] === 'random') {
				$command .= 'RAND()';
			} else {
				foreach ($parameters['sort'] as $sortColumnName => $sortOrder) {
					$command .= $sortColumnName . ' ' . strtoupper(str_replace('ending', '', $sortOrder)) . ',';
				}

				$command = rtrim($command, ',');
			}
		}

		if (empty($parameters['limit']) === false) {
			$command .= ' LIMIT ' . $parameters['limit'];
		}

		if (empty($parameters['offset']) === false) {
			$command .= ' OFFSET ' . $parameters['offset'];
		}

		$commandResponse = mysqli_query($parameters['in']['connection'], $command);

		if ($commandResponse === false) {
			$response['message'] = 'Error listing data in ' . $parameters['in']['structure']['table_name'] . ' system database, please try again.';
			_output($response);
		}

		$commandResponse = mysqli_fetch_assoc($commandResponse);

		if ($commandResponse === false) {
			$response['message'] = 'Error listing data in ' . $parameters['in']['structure']['table_name'] . ' system database, please try again.';
			_output($response);
		}

		return $commandResponse;
	}

	function _parseCommandWhereConditions($whereConditions, $whereConditionConjunction = 'AND') {
		foreach ($whereConditions as $whereConditionKey => $whereConditionValue) {
			if ($whereConditionKey === 'either') {
				$whereConditionConjunction = 'OR';
			}

			if (
				(is_array($whereConditionValue) === true) &&
				((count($whereConditionValue) === count($whereConditionValue, true)) === false)
			) {
				$recursiveWhereConditions = $whereConditionValue;
				$whereConditions[$whereConditionKey] = '(' . implode(') ' . $conjunction . ' (', _parseCommandWhereConditions($recursiveWhereConditions, $conjunction)) . ')';
			} else {
				if (is_array($whereConditionValue) === false) {
					$whereConditionValue = array(
						$whereConditionValue
					);
				}

				$whereConditionValueConditions = array();

				foreach ($whereConditionValue as $whereConditionValueKey => $whereConditionValueValue) {
					if (is_bool($whereConditionValueValue) === true) {
						$whereConditionValueValue = intval($whereConditionValueValue);
					}

					if (is_null($whereConditionValueValue) === true) {
						$whereConditionValueValue = 'IS NULL';
					}

					$whereConditionValue[$whereConditionValueKey] = $whereConditionValueValue;

					if (($whereConditionKey === 'either') === true) {
						$whereConditionValueValueCondition = 'IN';

						if (is_int(strpos($whereConditionValueKey, ' !=')) === true) {
							$whereConditionValueKey = substr($whereConditionValueKey, 0, strpos($whereConditionValueKey, ' '));
							$whereConditionValueValueCondition = 'NOT ' . $whereConditionValueValueCondition;
						}

						$whereConditionValueConditions[] = $whereConditionValueKey . ' ' . $whereConditionValueValueCondition . " ('" . str_replace("'", "\'", $whereConditionValueValue) . "')";
					}
				}

				if (empty($whereConditionValueConditions) === true) {
					if (
						(is_int(strpos($whereConditionKey, ' >')) === true) ||
						(is_int(strpos($whereConditionKey, ' <')) === true)
					) {
						$whereConditionValueConditions[] = $whereConditionKey . ' ' . str_replace("'", "\'", current($whereConditionValue));
					} else {
						$whereConditionValueCondition = 'IN';
						$whereConditionValueKey = $whereConditionKey;

						if (is_int(strpos($whereConditionValueKey, ' !=')) === true) {
							$whereConditionValueKey = substr($whereConditionValueKey, 0, strpos($whereConditionValueKey, ' '));
							$whereConditionValueCondition = 'NOT ' . $whereConditionValueCondition;
						}

						$whereConditionValueConditions[] = $whereConditionValueKey . ' ' . $whereConditionValueCondition . " ('" . implode("','", str_replace("'", "\'", $whereConditionValue)) . "')";
					}
				}

				$whereConditions[$whereConditionKey] = '(' . implode(' ' . $whereConditionConjunction . ' ', $whereConditionValueConditions) . ')';
			}
		}

		return $whereConditions;
	}

	function _save($parameters, $response) {
		if (empty($parameters['data']) === false) {
			if (is_numeric(key($parameters['data'])) === false) {
				$parameters['data'] = array(
					$parameters['data']
				);
			}

			$timestamp = time();

			foreach ($parameters['data'] as $data) {
				$dataInsertValues = $dataKeys = $dataUpdateValues = '';

				foreach ($data as $dataKey => $dataValue) {
					$dataInsertValue = str_replace("'", "\'", str_replace('\\', '\\\\', $dataValue));
					$dataInsertValues .= "','" . $dataInsertValue;
					$dataKeys .= ',' . $dataKey;
					$dataUpdateValues .= "," . $dataKey . "='" . $dataInsertValue . "'";
				}

				if (empty($data['modified_timestamp']) === true) {
					$dataInsertValues .= "','" . $timestamp;
					$dataKeys .= ',modified_timestamp';
					$dataUpdateValues .= ",modified_timestamp='" . $timestamp . "'";
				}

				if (empty($data['id']) === true) {
					$dataInsertValues .= "','" . $timestamp;
					$dataKeys .= ',created_timestamp';
					$dataUpdateValues = '';
				} else {
					$dataUpdateValues = ' ON DUPLICATE KEY UPDATE ' . substr($dataUpdateValues, 1);
				}

				$commandResponse = mysqli_query($parameters['in']['connection'], 'INSERT INTO ' . $parameters['in']['structure']['table_name'] . '(' . substr($dataKeys, 1) . ") values (" . substr($dataInsertValues, 2) . "')" . $dataUpdateValues);

				if ($commandResponse === false) {
					$response['message'] = 'Error saving data in ' . $parameters['in']['structure']['table_name'] . ' system database, please try again.';
					_output($response);
				}
			}
		}

		return true;
	}

	function _update($parameters, $response) {
		if (empty($parameters['data']) === false) {
			$command = 'UPDATE ' . $parameters['in']['table_name'] . ' SET ';

			if (isset($parameters['data']['modified']) === false) {
				$parameters['data']['modified'] = time();
			}

			foreach ($parameters['data'] as $updateValueKey => $updateValue) {
				$command .= $updateValueKey . "='" . str_replace("'", "\'", $updateValue) . "',";
			}

			$command = rtrim($command, ',') . ' WHERE ' . implode(' AND ', _parseCommandWhereConditions($parameters['where']));
			$commandResponse = mysqli_query($parameters['in']['connection'], $command);

			if ($commandResponse === false) {
				$response['message'] = 'Error updating data in ' . $parameters['in']['structure']['table_name'] . ' system database, please try again.';
				_output($response);
			}
		}

		return true;
	}

	$systemDatabaseConnection = mysqli_connect('localhost', 'root', 'password', 'ghostcompute');
	$parameters['system_databases'] = array(
		'system_database_columns' => array(
			'connection' => $systemDatabaseConnection,
			'structure' => array(
				'column_names' => array(
					'created_timestamp',
					'id',
					'modified_timestamp',
					'name',
					'system_database_id'
				),
				'table_name' => 'system_database_columns'
			)
		),
		'system_databases' => array(
			'connection' => $systemDatabaseConnection,
			'structure' => array(
				'column_names' => array(
					'authentication_credential_hostname',
					'authentication_credential_password',
					'created_timestamp',
					'id',
					'modified_timestamp',
					'table_name',
					'tag'
				),
				'table_name' => 'system_databases'
			)
		)
	);

	if ($systemDatabaseConnection === false) {
		$response['message'] = 'Error connecting to system database, please try again.';
		_output($response);
	}

	$parameters['system_databases'] += _connect(array(
		'system_settings',
		'system_user_authentication_token_scopes',
		'system_user_authentication_token_sources',
		'system_user_authentication_tokens',
		'system_users'
	), $parameters['system_databases'], $response);
?>
