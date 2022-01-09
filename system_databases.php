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

			if ((strpos($systemDatabase, '__') === false) === false) {
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
		$systemDatabaseCountCommand = 'SELECT COUNT(id) FROM ' . $parameters['in']['structure']['table_name'];

		if (empty($parameters['where']) === false) {
			$systemDatabaseCountCommand .= ' WHERE ' . implode(' AND ', _parseSystemDatabaseCommandWhereConditions($parameters['where']));
		}

		$systemDatabaseCountRows = mysqli_query($parameters['in']['connection'], $systemDatabaseCountCommand);

		if ($systemDatabaseCountRows === false) {
			$response['message'] = 'Error counting data in ' . $parameters['in']['structure']['table_name'] . ' system database, please try again.';
			_output($response);
		}

		foreach ($systemDatabaseCountRows as $systemDatabaseCountRow) {
			$systemDatabaseCountRow = current($systemDatabaseCountRow);
			return intval($systemDatabaseCountRow);
		}
	}

	function _delete($parameters, $response) {
		$systemDatabaseDeleteCommand = 'DELETE FROM ' . $parameters['in']['structure']['table_name'];

		if (empty($parameters['where']) === false) {
			$systemDatabaseDeleteCommand .= ' WHERE ' . implode(' AND ', _parseSystemDatabaseCommandWhereConditions($parameters['where']));
		}

		if (mysqli_query($parameters['in']['connection'], $systemDatabaseDeleteCommand) === false) {
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

		$systemDatabaseListCommand = 'SELECT ' . implode(',', $parameters['data']) . ' FROM ' . $parameters['in']['structure']['table_name'];

		if (empty($parameters['where']) === false) {
			$systemDatabaseListCommand .= ' WHERE ' . implode(' AND ', _parseSystemDatabaseCommandWhereConditions($parameters['where']));
		}

		if (empty($parameters['sort']) === false) {
			$systemDatabaseListCommand .= ' ORDER BY ';

			if ($parameters['sort'] === 'random') {
				$systemDatabaseListCommand .= 'RAND()';
			} else {
				foreach ($parameters['sort'] as $systemDatabaseListSortColumnName => $systemDatabaseListSortOrder) {
					$systemDatabaseListSortOrder = str_replace('ending', '', $systemDatabaseListSortOrder);
					$systemDatabaseListCommand .= $systemDatabaseListSortColumnName . ' ' . strtoupper($systemDatabaseListSortOrder) . ',';
				}

				$systemDatabaseListCommand = rtrim($systemDatabaseListCommand, ',');
			}
		}

		if (empty($parameters['limit']) === false) {
			$systemDatabaseListCommand .= ' LIMIT ' . $parameters['limit'];
		}

		if (empty($parameters['offset']) === false) {
			$systemDatabaseListCommand .= ' OFFSET ' . $parameters['offset'];
		}

		$systemDatabaseListRows = mysqli_query($parameters['in']['connection'], $systemDatabaseListCommand);

		if ($systemDatabaseListRows === false) {
			$response['message'] = 'Error listing data rows in ' . $parameters['in']['structure']['table_name'] . ' system database, please try again.';
			_output($response);
		}

		foreach ($systemDatabaseListRows as $systemDatabaseListRow) {
			$response['data'][] = $systemDatabaseListRow;
		}

		return $response['data'];
	}

	function _parseSystemDatabaseCommandWhereConditions($whereConditions, $whereConditionConjunction = 'AND') {
		foreach ($whereConditions as $whereConditionKey => $whereConditionValue) {
			if ($whereConditionKey === 'either') {
				$whereConditionConjunction = 'OR';
			}

			if (
				(is_array($whereConditionValue) === true) &&
				((count($whereConditionValue) === count($whereConditionValue, true)) === false)
			) {
				$recursiveWhereConditions = $whereConditionValue;
				$whereConditions[$whereConditionKey] = '(' . implode(') ' . $whereConditionConjunction . ' (', _parseSystemDatabaseCommandWhereConditions($recursiveWhereConditions, $whereConditionConjunction)) . ')';
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

						if ((strpos($whereConditionValueKey, ' !=') === false) === false) {
							$whereConditionValueKeyDelimiterPosition = strpos($whereConditionValueKey, ' ');
							$whereConditionValueKey = substr($whereConditionValueKey, 0, $whereConditionValueKeyDelimiterPosition);
							$whereConditionValueValueCondition = 'NOT ' . $whereConditionValueValueCondition;
						}

						$whereConditionValueConditions[] = $whereConditionValueKey . ' ' . $whereConditionValueValueCondition . " ('" . str_replace("'", "\'", $whereConditionValueValue) . "')";
					}
				}

				if (empty($whereConditionValueConditions) === true) {
					if (
						((strpos($whereConditionKey, ' >') === false) === false) ||
						((strpos($whereConditionKey, ' <') === false) === false)
					) {
						$whereConditionValue = current($whereConditionValue);
						$whereConditionValueConditions[] = $whereConditionKey . ' ' . str_replace("'", "\'", $whereConditionValue);
					} else {
						$whereConditionValueCondition = 'IN';
						$whereConditionValueKey = $whereConditionKey;

						if ((strpos($whereConditionValueKey, ' !=') === false) === false) {
							$whereConditionValueKeyDelimiterPosition = strpos($whereConditionValueKey, ' ');
							$whereConditionValueKey = substr($whereConditionValueKey, 0, $whereConditionValueKeyDelimiterPosition);
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
			$systemDatabaseRowIndex = key($parameters['data']);

			if (is_numeric($systemDatabaseRowIndex) === false) {
				$parameters['data'] = array(
					$parameters['data']
				);
			}

			$timestamp = time();

			foreach ($parameters['data'] as $systemDatabaseColumns) {
				$systemDatabaseInsertColumnNames = $systemDatabaseInsertColumnValues = $systemDatabaseUpdateColumnValues = '';

				foreach ($systemDatabaseColumns as $systemDatabaseColumnName => $systemDatabaseColumnValue) {
					$systemDatabaseInsertColumnNames .= ',' . $systemDatabaseColumnName;
					$systemDatabaseInsertColumnValue = str_replace('\\', '\\\\', $systemDatabaseColumnValue);
					$systemDatabaseInsertColumnValue = str_replace("'", "\'", $systemDatabaseInsertColumnValue);
					$systemDatabaseInsertColumnValues .= "','" . $systemDatabaseInsertColumnValue;
					$systemDatabaseUpdateColumnValues .= "," . $systemDatabaseColumnName . "='" . $systemDatabaseInsertColumnValue . "'";
				}

				if (empty($systemDatabaseColumns['created_timestamp']) === true) {
					$systemDatabaseInsertColumnNames .= ',created_timestamp';
					$systemDatabaseInsertColumnValues .= "','" . $timestamp;
					$systemDatabaseUpdateColumnValues .= ",created_timestamp='" . $timestamp . "'";
				}

				if (empty($systemDatabaseColumns['modified_timestamp']) === true) {
					$systemDatabaseInsertColumnNames .= ',modified_timestamp';
					$systemDatabaseInsertColumnValues .= "','" . $timestamp;
					$systemDatabaseUpdateColumnValues .= ",modified_timestamp='" . $timestamp . "'";
				}

				$systemDatabaseInsertColumnNames = substr($systemDatabaseInsertColumnNames, 1);
				$systemDatabaseInsertColumnValues = substr($systemDatabaseInsertColumnValues, 2);
				$systemDatabaseUpdateColumnValues = ' ON DUPLICATE KEY UPDATE ' . substr($systemDatabaseUpdateColumnValues, 1);

				if (mysqli_query($parameters['in']['connection'], 'INSERT INTO ' . $parameters['in']['structure']['table_name'] . '(' . $systemDatabaseInsertColumnNames . ") VALUES (" . $systemDatabaseInsertColumnValues . "')" . $systemDatabaseUpdateColumnValues) === false) {
					$response['message'] = 'Error saving data in ' . $parameters['in']['structure']['table_name'] . ' system database, please try again.';
					_output($response);
				}
			}
		}

		return true;
	}

	function _update($parameters, $response) {
		if (empty($parameters['data']) === false) {
			$systemDatabaseUpdateCommand = 'UPDATE ' . $parameters['in']['table_name'] . ' SET ';

			if (isset($parameters['data']['modified']) === false) {
				$parameters['data']['modified'] = time();
			}

			foreach ($parameters['data'] as $updateValueKey => $updateValue) {
				$systemDatabaseUpdateCommand .= $updateValueKey . "='" . str_replace("'", "\'", $updateValue) . "',";
			}

			$systemDatabaseUpdateCommand = rtrim($systemDatabaseUpdateCommand, ',') . ' WHERE ' . implode(' AND ', _parseSystemDatabaseCommandWhereConditions($parameters['where']));

			if (empty($parameters['limit']) === false) {
				$systemDatabaseUpdateCommand .= ' LIMIT ' . $parameters['limit'];
			}

			$systemDatabaseUpdateCommandResponse = mysqli_query($parameters['in']['connection'], $systemDatabaseUpdateCommand);

			if ($systemDatabaseUpdateCommandResponse === false) {
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
