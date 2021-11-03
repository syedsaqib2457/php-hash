<?php
	// todo: add all database structures to system_databases database during installation
		// all columns are a string varchar for simplicity with dynamically-adjusting maximum length for memory optimization
		// all columns have no default value
		// localhost database is required for storing database authentication credentials + structures
		// set request log databases to use current timestamp in database name for easy deployment of additional dedicated storage instances instead of bucket storage
			// create functionality to allow API access to list specific chronologically-sorted request logs in multiple databases for a custom date range

	if (empty($parameters) === true) {
		exit;
	}

	function _connect($databases, $existingDatabaseConnections, $response) {
		foreach ($databases as $database) {
			if (
				(empty($existingDatabaseConnections) === false) &&
				(empty($existingDatabaseConnections[$database['structure']['table']]) === false)
			) {
				continue;
			}

			$systemDatabase = _list(array(
				'columns' => array(
					'authentication_credential_hostname',
					'authentication_credential_password',
					'id'
				),
				'in' => $parameters['databases']['system_databases'],
				'where' => array(
					'name' => $database
				)
			));
			$systemDatabase = current($systemDatabase);

			if (empty($systemDatabase) === true) {
				$response['message'] = 'Invalid system database name ' . $database . ', please try again.';
				unset($response['_connect']);
				_output($response);
			}

			$response['_connect'][$database] = array(
				'connection' => mysqli_connect($systemDatabase['authentication_credential_hostname'], 'root', $systemDatabase['authentication_credential_password'], 'ghostcompute'),
				'structure'=> array(
					'table' => $database
				)
			);

			if ($response['_connect'][$database]['connection'] === false) {
				$response['message'] = 'Error connecting to ' . $database . ' system database, please try again.';
				unset($response['connect']);
				_output($response);
			}

			$systemDatabaseColumns = _list(array(
				'columns' => array(
					'name'
				),
				'in' => $parameters['databases']['system_database_columns'],
				'where' => array(
					'system_database_id' => $systemDatabase['id']
				)
			));

			if (empty($systemDatabaseColumns) === true) {
				$response['message'] = 'Invalid system database columns for ' . $database . ' system database, please try again.';
				unset($response['_connect']);
				_output($response);
			}

			foreach ($systemDatabaseColumns as $systemDatabaseColumn) {
				$response['_connect'][$database]['structure']['columns'][$systemDatabaseColumn['name']] = '';
			}
		}

		$response = $response['_connect'];
		return $response;
	}

	function _count($parameters, $response) {
		$command = 'select count(id) from ' . $parameters['in']['structure']['table'];

		if (empty($parameters['where']) === false) {
			$command .= ' where ' . implode(' and ', _parseCommandWhereConditions($parameters['where']));
		}

		$commandResponse = mysqli_query($parameters['in']['connection'], $command);

		if ($commandResponse === false) {
			$response['message'] = 'Error counting data in ' . $parameters['in']['structure']['table'] . ' system database, please try again.';
			_output($response);
		}

		$commandResponse = mysqli_fetch_assoc($commandResponse);

		if ($commandResponse === false) {
			$response['message'] = 'Error counting data in ' . $parameters['in']['structure']['table'] . ' system database, please try again.';
			_output($response);
		}

		$commandResponse = current($commandResponse);

		if (is_int($commandResponse) === false) {
			$response['message'] = 'Error counting data in ' . $parameters['in']['structure']['table'] . ' system database, please try again.';
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
		$response['_list'] = array();
		$databaseColumns = '*';

		if (empty($parameters['columns']) === false) {
			$databaseColumns = '';

			foreach ($parameters['columns'] as $databaseColumn) {
				$databaseColumns .= $databaseColumn . ',';
			}
		}

		$command = 'select ' . rtrim($databaseColumns, ',') . ' from ' . $parameters['in']['table'];

		if (empty($parameters['where']) === false) {
			$command .= ' where ' . implode(' and ', _parseCommandWhereConditions($parameters['where']));
		}

		if (empty($parameters['sort']) === false) {
			$command .= ' order by ';

			if ($parameters['sort'] === 'random') {
				$command .= 'rand()';
			} elseif (empty($parameters['sort']['key']) === false) {
				if (empty($parameters['sort']['order']) === true) {
					$parameters['sort']['order'] = 'desc';
				} else {
					$sortOrders = array(
						'ascending' => 'asc',
						'descending' => 'desc'
					);
					$parameters['sort']['order'] = $sortOrders[$parameters['sort']['order']];
				}

				$command .= $parameters['sort']['key'] . ' ' . $parameters['sort']['order'] . ', id DESC';
			}
		}

		if (empty($parameters['limit']) === false) {
			$command .= ' limit ' . $parameters['limit'];
		}

		if (empty($parameters['offset']) === false) {
			$command .= ' offset ' . $parameters['offset'];
		}

		foreach ($parameters['in']['connections'] as $connectionIndex => $connection) {
			$commandResponse = mysqli_query($connection, $command);

			if ($commandResponse === false) {
				$response['message'] = 'Error listing data in ' . $parameters['in']['table'] . ' database, please try again.';
				unset($response['_list']);
				_output($response);
			}

			$response['_list'][$connectionIndex] = mysqli_fetch_assoc($commandResponse);

			if ($response['_list'][$connectionIndex] === false) {
				$response['message'] = 'Error listing data in ' . $parameters['in']['table'] . ' database, please try again.';
				unset($response['_list']);
				_output($response);
			}
		}

		$response = $response['_list'];
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
				if (
					(($whereConditionConjunction === 'either') === false) &&
					(isset($parameters['in']['settings']['structure'][substr($whereConditionKey, 0, strpos($whereConditionKey, ' '))]) === false)
				) {
					unset($parameters['where'][$whereConditionKey]);
					continue;
				}

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
		// todo: group 10 commands per mysqli command
		// todo: verify + update column length for each value before saving data

		if (empty($parameters['data']) === false) {
			if (is_numeric(key($parameters['data'])) === false) {
				$parameters['data'] = array(
					$parameters['data']
				);
			}

			$connectionIndex = 0;
			$timestamp = time();

			foreach ($parameters['data'] as $data) {
				$dataInsertValues = $dataKeys = $dataUpdateValues = '';

				foreach ($data as $dataKey => $dataValue) {
					$dataInsertValue = str_replace("'", "\'", str_replace('\\', '\\\\', $dataValue));
					$dataInsertValues .= "','" . $dataInsertValue;
					$dataKeys .= ',' . $dataKey;
					$dataUpdateValues .= "," . $dataKey . "='" . $dataInsertValue . "'";
				}

				if (empty($data['modified_date']) === true) {
					$dataInsertValues .= "','" . $timestamp;
					$dataKeys .= ',modified_date';
					$dataUpdateValues .= ",modified_date='" . $timestamp . "'";
				}

				if (empty($data['id']) === true) {
					$dataInsertValues .= "','" . $timestamp;
					$dataKeys .= ',created_date';
					$dataUpdateValues = '';
				} else {
					$dataUpdateValues = ' on duplicate key update ' . substr($dataUpdateValues, 1);
				}

				$commandResponse = mysqli_query($parameters['in']['connections'][$connectionIndex], 'insert into ' . $parameters['in']['table'] . '(' . substr($dataKeys, 1) . ") values (" . substr($dataInsertValues, 2) . "')" . $dataUpdateValues);

				if ($commandResponse === false) {
					$response['message'] = 'Error saving data in ' . $parameters['in']['table'] . ' database, please try again.';
					_output($response);
				}

				if (empty($parameters['in']['connections'][1]) === false) {
					$connectionIndex++;

					if (empty($parameters['in']['connections'][$connectionIndex]) === true) {
						$connectionIndex = 0;
					}
				}
			}
		}

		$response = true;
		return $response;
	}

	function _update($parameters, $response) {
		// todo: verify + update column length for each value before updating data

		if (empty($parameters['data']) === false) {
			$command = 'update ' . $parameters['in']['table'] . ' set ';

			if (isset($parameters['data']['modified']) === false) {
				$parameters['data']['modified'] = time();
			}

			foreach ($parameters['data'] as $updateValueKey => $updateValue) {
				$command .= $updateValueKey . "='" . str_replace("'", "\'", $updateValue) . "',";
			}

			$command = rtrim($command, ',') . ' where ' . implode(' and ', _parseCommandWhereConditions($parameters['where']));

			foreach ($parameters['in']['connections'] as $connection) {
				$commandResponse = mysqli_query($connection, $command);

				if ($commandResponse === false) {
					$response['message'] = 'Error updating data in ' . $parameters['in']['table'] . ' database, please try again.';
					_output($response);
				}
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
					'created_timestamp' => '',
					'id' => '',
					'modified_timestamp' => '',
					'name' => '',
					'system_database_id' => ''
				),
				'table' => 'system_database_columns'
			)
		),
		'system_databases' => array(
			'connection' => $systemDatabaseConnection,
			'structure' => array(
				'columns' => array(
					'authentication_credential_hostname' => '',
					'authentication_credential_password' => '',
					'created_timestamp' => '',
					'id' => '',
					'modified_timestamp' => '',
					'name' => '',
					'tag' => ''
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
