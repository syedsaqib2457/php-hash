<?php
	// todo: create functionality to allow API access to list specific chronologically-sorted request logs in multiple databases for a custom date range

	if (empty($parameters) === true) {
		exit;
	}

	function _connect($systemDatabases, $existingSystemDatabaseConnections, $response) {
		foreach ($systemDatabases as $systemDatabase) {
			if (
				(empty($existingSystemDatabaseConnections) === false) &&
				(empty($existingSystemDatabaseConnections[$systemDatabase['structure']['table_name']]) === false)
			) {
				continue;
			}

			$systemDatabaseKey = $systemDatabase;
			$systemDatabaseParameters = array(
				'columns' => array(
					'authentication_credential_hostname',
					'authentication_credential_password',
					'id'
				),
				'in' => $parameters['system_databases']['system_databases'],
				'limit' => 1,
				'sort' => array(
					'column' => 'created_timestamp',
					'order' => 'descending'
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
					$response['message'] = 'Invalid system database tag for ' . $systemDatabaseKey . ', please try again.';
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
				$response['message'] = 'Invalid system database ' . $systemDatabaseKey . ', please try again.';
				unset($response['_connect']);
				_output($response);
			}

			$response['_connect'][$systemDatabase] = array(
				'connection' => mysqli_connect($systemDatabase['authentication_credential_hostname'], 'root', $systemDatabase['authentication_credential_password'], 'ghostcompute'),
				'structure'=> array(
					'table_name' => $systemDatabaseParameters['where']['table_name']
				)
			);

			if ($response['_connect'][$systemDatabaseKey]['connection'] === false) {
				$response['message'] = 'Error connecting to ' . $systemDatabaseKey . ' system database, please try again.';
				unset($response['connect']);
				_output($response);
			}

			$systemDatabaseColumns = _list(array(
				'columns' => array(
					'name'
				),
				'in' => $parameters['system_databases']['system_database_columns'],
				'where' => array(
					'system_database_id' => $systemDatabase['id']
				)
			), $response);

			if (empty($systemDatabaseColumns) === true) {
				$response['message'] = 'Invalid system database columns in ' . $database . ' system database, please try again.';
				unset($response['_connect']);
				_output($response);
			}

			foreach ($systemDatabaseColumns as $systemDatabaseColumn) {
				$response['_connect'][$systemDatabaseKey]['structure']['column_names'][] = [$systemDatabaseColumn['name']];
			}
		}

		$response = $response['_connect'];
		return $response;
	}

	function _count($parameters, $response) {
		$command = 'SELECT COUNT (id) FROM ' . $parameters['in']['structure']['table_name'];

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

		$response = $commandResponse;
		return $response;
	}

	function _delete($parameters, $response) {
		$command = 'delete from ' . $parameters['in']['structure']['table'];

		if (empty($parameters['where']) === false) {
			$command .= ' where ' . implode(' and ', _parseCommandWhereConditions($parameters['where']));
		}

		$commandResponse = mysqli_query($parameters['in']['connection'], $command);

		if ($commandResponse === false) {
			$response['message'] = 'Error deleting data in ' . $parameters['in']['structure']['table'] . ' system database, please try again.';
			_output($response);
		}

		$response = true;
		return $response;
	}

	function _list($parameters, $response) {
		$databaseColumns = '*';

		if (empty($parameters['columns']) === false) {
			$databaseColumns = '';

			foreach ($parameters['columns'] as $databaseColumn) {
				$databaseColumns .= $databaseColumn . ',';
			}
		}

		$command = 'select ' . rtrim($databaseColumns, ',') . ' from ' . $parameters['in']['structure']['table'];

		if (empty($parameters['where']) === false) {
			$command .= ' where ' . implode(' and ', _parseCommandWhereConditions($parameters['where']));
		}

		if (empty($parameters['sort']) === false) {
			$command .= ' order by ';

			if ($parameters['sort'] === 'random') {
				$command .= 'RAND()';
			} elseif (empty($parameters['sort']['column']) === false) {
				if (empty($parameters['sort']['order']) === true) {
					$parameters['sort']['order'] = 'desc';
				} else {
					$sortOrders = array(
						'ascending' => 'asc',
						'descending' => 'desc'
					);
					$parameters['sort']['order'] = $sortOrders[$parameters['sort']['order']];
				}

				$command .= $parameters['sort']['column'] . ' ' . $parameters['sort']['order'] . ', id DESC';
			}
		}

		if (empty($parameters['limit']) === false) {
			$command .= ' limit ' . $parameters['limit'];
		}

		if (empty($parameters['offset']) === false) {
			$command .= ' offset ' . $parameters['offset'];
		}

		$commandResponse = mysqli_query($connection, $command);

		if ($commandResponse === false) {
			$response['message'] = 'Error listing data in ' . $parameters['in']['structure']['table'] . ' system database, please try again.';
			_output($response);
		}

		$commandResponse = mysqli_fetch_assoc($commandResponse);

		if ($commandResponse === false) {
			$response['message'] = 'Error listing data in ' . $parameters['in']['structure']['table'] . ' system database, please try again.';
			_output($response);
		}

		$response = $commandResponse;
		return $response;
	}

	function _parseCommandWhereConditions($parameters, $whereConditionConjunction = 'and') {
		foreach ($parameters['where'] as $whereConditionKey => $whereConditionValue) {
			if ($whereConditionKey === 'either') {
				$whereConditionConjunction = 'or';
			}

			if (
				(is_array($whereConditionValue) === true) &&
				((count($whereConditionValue) === count($whereConditionValue, true)) === false)
			) {
				$recursiveParameters = array(
					'where' => $whereConditionValue
				);
				$parameters['where'][$whereConditionKey] = '(' . implode(') ' . $conjunction . ' (', _parseCommandWhereConditions($recursiveParameters, $conjunction)) . ')';
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
						$whereConditionValueValue = 'is null';
					}

					$whereConditionValue[$whereConditionValueKey] = $whereConditionValueValue;

					if (($whereConditionKey === 'either') === true) {
						$whereConditionValueValueCondition = 'in';

						if (is_int(strpos($whereConditionValueKey, ' !=')) === true) {
							$whereConditionValueKey = substr($whereConditionValueKey, 0, strpos($whereConditionValueKey, ' '));
							$whereConditionValueValueCondition = 'not ' . $whereConditionValueValueCondition;
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
						$whereConditionValueCondition = 'in';
						$whereConditionValueKey = $whereConditionKey;

						if (is_int(strpos($whereConditionValueKey, ' !=')) === true) {
							$whereConditionValueKey = substr($whereConditionValueKey, 0, strpos($whereConditionValueKey, ' '));
							$whereConditionValueCondition = 'not ' . $whereConditionValueCondition;
						}

						$whereConditionValueConditions[] = $whereConditionValueKey . ' ' . $whereConditionValueCondition . " ('" . implode("','", str_replace("'", "\'", $whereConditionValue)) . "')";
					}
				}

				$parameters['where'][$whereConditionKey] = '(' . implode(' ' . $whereConditionConjunction . ' ', $whereConditionValueConditions) . ')';
			}
		}

		$response = $parameters['where'];
		return $response;
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
					$dataUpdateValues = ' on duplicate key update ' . substr($dataUpdateValues, 1);
				}

				$commandResponse = mysqli_query($parameters['in']['connection'], 'insert into ' . $parameters['in']['structure']['table'] . '(' . substr($dataKeys, 1) . ") values (" . substr($dataInsertValues, 2) . "')" . $dataUpdateValues);

				if ($commandResponse === false) {
					$response['message'] = 'Error saving data in ' . $parameters['in']['structure']['table'] . ' system database, please try again.';
					_output($response);
				}
			}
		}

		$response = true;
		return $response;
	}

	function _update($parameters, $response) {
		if (empty($parameters['data']) === false) {
			$command = 'update ' . $parameters['in']['table'] . ' set ';

			if (isset($parameters['data']['modified']) === false) {
				$parameters['data']['modified'] = time();
			}

			foreach ($parameters['data'] as $updateValueKey => $updateValue) {
				$command .= $updateValueKey . "='" . str_replace("'", "\'", $updateValue) . "',";
			}

			$command = rtrim($command, ',') . ' where ' . implode(' and ', _parseCommandWhereConditions($parameters['where']));
			$commandResponse = mysqli_query($parameters['in']['connection'], $command);

			if ($commandResponse === false) {
				$response['message'] = 'Error updating data in ' . $parameters['in']['structure']['table'] . ' system database, please try again.';
				_output($response);
			}
		}

		$response = true;
		return $response;
	}

	$parameters['databases'] = array(
		'system_database_columns' => array(
			'connection' => ($systemDatabaseConnection = mysqli_connect('localhost', 'root', 'password', 'ghostcompute')),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'id',
					'modified_timestamp',
					'name',
					'system_database_id'
				),
				'table' => 'system_database_columns'
			)
		),
		'system_databases' => array(
			'connection' => $systemDatabaseConnection,
			'structure' => array(
				'columns' => array(
					'authentication_credential_hostname',
					'authentication_credential_password',
					'created_timestamp',
					'id',
					'modified_timestamp',
					'name',
					'tag'
				),
				'table' => 'system_databases'
			)
		)
	);

	if ($systemDatabaseConnection === false) {
		$response['message'] = 'Error connecting to system database, please try again.';
		_output($response);
	}

	$parameters['databases'] += _connect(array(
		'system_user_authentication_token_scopes',
		'system_user_authentication_token_sources',
		'system_user_authentication_tokens',
		'system_users'
	), $parameters['databases'], $response);
?>
