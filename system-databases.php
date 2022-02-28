<?php
	// todo: create functionality to allow API access to list specific chronologically-sorted request logs in multiple databases for a custom date range

	if (empty($response) === true) {
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
					'authenticationCredentialAddress',
					'authenticationCredentialPassword',
					'id',
					'tableKey'
				),
				'in' => $existingSystemDatabases['systemDatabases'],
				'limit' => 1,
				'sort' => array(
					'createdTimestamp' => 'descending'
				),
				'where' => array(
					'tableKey' => $systemDatabase
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
					_output($parameters, $response);
				}

				$systemDatabaseParameters['where'] = array(
					'tableKey' => $systemDatabaseParts[0],
					'tag' => $systemDatabaseParts[1]
				);
			}

			$systemDatabase = _list($systemDatabaseParameters, $response);
			$systemDatabase = current($systemDatabase);

			if (empty($systemDatabase) === true) {
				$response['message'] = 'Invalid system database ' . $systemDatabase['tableKey'] . ', please try again.';
				unset($response['_connect']);
				_output($parameters, $response);
			}

			$response['_connect'][$systemDatabase['tableKey']] = array(
				'connection' => mysqli_connect($systemDatabase['authenticationCredentialAddress'], 'root', $systemDatabase['authenticationCredentialPassword'], 'firewallSecurityApi'),
				'structure'=> array(
					'tableKey' => $systemDatabaseParameters['where']['tableKey']
				)
			);

			if ($response['_connect'][$systemDatabase['tableKey']]['connection'] === false) {
				$response['message'] = 'Error connecting to ' . $systemDatabase['tableKey'] . ' system database, please try again.';
				unset($response['connect']);
				_output($parameters, $response);
			}

			$systemDatabaseColumns = _list(array(
				'data' => array(
					'key'
				),
				'in' => $existingSystemDatabases['systemDatabaseColumns'],
				'where' => array(
					'systemDatabaseId' => $systemDatabase['id']
				)
			), $response);

			if (empty($systemDatabaseColumns) === true) {
				$response['message'] = 'Error listing system database columns in ' . $systemDatabase['tableKey'] . ' system database, please try again.';
				unset($response['_connect']);
				_output($parameters, $response);
			}

			foreach ($systemDatabaseColumns as $systemDatabaseColumn) {
				$response['_connect'][$systemDatabase['tableKey']]['structure']['columnKeys'][] = [$systemDatabaseColumn['key']];
			}
		}

		return $response['_connect'];
	}

	function _count($parameters, $response) {
		$systemDatabaseCountCommand = 'SELECT COUNT(id) FROM ' . $parameters['in']['structure']['tableKey'];

		if (empty($parameters['where']) === false) {
			$systemDatabaseCountCommand .= ' WHERE ' . implode(' AND ', _parseSystemDatabaseCommandWhereConditions($parameters['where']));
		}

		$systemDatabaseCountRows = mysqli_query($parameters['in']['connection'], $systemDatabaseCountCommand);

		if ($systemDatabaseCountRows === false) {
			$response['message'] = 'Error counting data in ' . $parameters['in']['structure']['tableKey'] . ' system database, please try again.';
			_output($parameters, $response);
		}

		foreach ($systemDatabaseCountRows as $systemDatabaseCountRow) {
			$systemDatabaseCountRow = current($systemDatabaseCountRow);
			return intval($systemDatabaseCountRow);
		}
	}

	function _delete($parameters, $response) {
		$systemDatabaseDeleteCommand = 'DELETE FROM ' . $parameters['in']['structure']['tableKey'];

		if (empty($parameters['where']) === false) {
			$systemDatabaseDeleteCommand .= ' WHERE ' . implode(' AND ', _parseSystemDatabaseCommandWhereConditions($parameters['where']));
		}

		if (mysqli_query($parameters['in']['connection'], $systemDatabaseDeleteCommand) === false) {
			$response['message'] = 'Error deleting data in ' . $parameters['in']['structure']['tableKey'] . ' system database, please try again.';
			_output($parameters, $response);
		}

		return true;
	}

	function _edit($parameters, $response) {
		if (empty($parameters['data']) === false) {
			$systemDatabaseUpdateCommand = 'UPDATE ' . $parameters['in']['tableKey'] . ' SET ';

			if (isset($parameters['data']['modifiedTimestamp']) === false) {
				$parameters['data']['modifiedTimestamp'] = time();
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
				$response['message'] = 'Error editing data in ' . $parameters['in']['structure']['tableKey'] . ' system database, please try again.';
				_output($parameters, $response);
			}
		}

		return true;
	}

	function _list($parameters, $response) {
		$systemDatabaseListColumnKeys = '*';

		if (empty($parameters['data']) === false) {
			$systemDatabaseListColumnKeys = '`' . implode('`,`', $parameters['data']) . '`';
		}

		$systemDatabaseListCommand = 'SELECT ' . $systemDatabaseListColumnKeys . ' FROM ' . $parameters['in']['structure']['tableKey'];

		if (empty($parameters['where']) === false) {
			$parameters['where'] = _parseSystemDatabaseCommandWhereConditions($parameters['where']);
			$systemDatabaseListCommand .= ' WHERE ' . implode(' AND ', $parameters['where']);
		}

		if (empty($parameters['sort']) === false) {
			$systemDatabaseListCommand .= ' ORDER BY ';

			if ($parameters['sort'] === 'random') {
				$systemDatabaseListCommand .= 'RAND()';
			} else {
				foreach ($parameters['sort'] as $systemDatabaseListSortColumnKey => $systemDatabaseListSortOrder) {
					$systemDatabaseListSortOrder = str_replace('ending', '', $systemDatabaseListSortOrder);
					$systemDatabaseListCommand .= $systemDatabaseListSortColumnKey . ' ' . strtoupper($systemDatabaseListSortOrder) . ',';
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
			$response['message'] = 'Error listing data rows in ' . $parameters['in']['structure']['tableKey'] . ' system database, please try again.';
			_output($parameters, $response);
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

						$whereConditionValueConditions[] = '`' . $whereConditionValueKey . '` ' . $whereConditionValueValueCondition . " ('" . str_replace("'", "\'", $whereConditionValueValue) . "')";
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

						$whereConditionValue = str_replace("'", "\'", $whereConditionValue);
						$whereConditionValueConditions[] = '`' . $whereConditionValueKey . '` ' . $whereConditionValueCondition . " ('" . implode("','", $whereConditionValue) . "')";
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
				$systemDatabaseInsertColumnKeys = $systemDatabaseInsertColumnValues = $systemDatabaseUpdateColumnValues = '';

				foreach ($systemDatabaseColumns as $systemDatabaseColumnKey => $systemDatabaseColumnValue) {
					$systemDatabaseInsertColumnKeys .= ',' . $systemDatabaseColumnKey;
					$systemDatabaseInsertColumnValue = str_replace('\\', '\\\\', $systemDatabaseColumnValue);
					$systemDatabaseInsertColumnValue = str_replace("'", "\'", $systemDatabaseInsertColumnValue);
					$systemDatabaseInsertColumnValues .= "','" . $systemDatabaseInsertColumnValue;
					$systemDatabaseUpdateColumnValues .= "," . $systemDatabaseColumnKey . "='" . $systemDatabaseInsertColumnValue . "'";
				}

				if (empty($systemDatabaseColumns['createdTimestamp']) === true) {
					$systemDatabaseInsertColumnKeys .= ',createdTimestamp';
					$systemDatabaseInsertColumnValues .= "','" . $timestamp;
					$systemDatabaseUpdateColumnValues .= ",createdTimestamp='" . $timestamp . "'";
				}

				if (empty($systemDatabaseColumns['modifiedTimestamp']) === true) {
					$systemDatabaseInsertColumnKeys .= ',modifiedTimestamp';
					$systemDatabaseInsertColumnValues .= "','" . $timestamp;
					$systemDatabaseUpdateColumnValues .= ",modifiedTimestamp='" . $timestamp . "'";
				}

				$systemDatabaseInsertColumnKeys = substr($systemDatabaseInsertColumnKeys, 1);
				$systemDatabaseInsertColumnValues = substr($systemDatabaseInsertColumnValues, 2);
				$systemDatabaseUpdateColumnValues = ' ON DUPLICATE KEY UPDATE ' . substr($systemDatabaseUpdateColumnValues, 1);

				if (mysqli_query($parameters['in']['connection'], 'INSERT INTO ' . $parameters['in']['structure']['tableKey'] . '(' . $systemDatabaseInsertColumnKeys . ") VALUES (" . $systemDatabaseInsertColumnValues . "')" . $systemDatabaseUpdateColumnValues) === false) {
					$response['message'] = 'Error saving data in ' . $parameters['in']['structure']['tableKey'] . ' system database, please try again.';
					_output($parameters, $response);
				}
			}
		}

		return true;
	}

	$systemDatabaseConnection = mysqli_connect('localhost', 'root', 'password', 'firewallSecurityApi');
	$parameters['systemDatabases'] = array(
		'systemDatabaseColumns' => array(
			'connection' => $systemDatabaseConnection,
			'structure' => array(
				'columnKeys' => array(
					'createdTimestamp',
					'id',
					'key',
					'modifiedTimestamp',
					'systemDatabaseId'
				),
				'tableKey' => 'systemDatabaseColumns'
			)
		),
		'systemDatabases' => array(
			'connection' => $systemDatabaseConnection,
			'structure' => array(
				'columnKeys' => array(
					'authenticationCredentialAddress',
					'authenticationCredentialPassword',
					'createdTimestamp',
					'id',
					'modifiedTimestamp',
					'tableKey',
					'tag'
				),
				'tableKey' => 'systemDatabases'
			)
		)
	);

	if ($systemDatabaseConnection === false) {
		$response['message'] = 'Error connecting to system database, please try again.';
		_output($parameters, $response);
	}

	$systemDatabasesConnections = _connect(array(
		'systemRequestLogs',
		'systemSettings',
		'systemUserAuthenticationTokens',
		'systemUserAuthenticationTokenScopes',
		'systemUserAuthenticationTokenSources',
		'systemUsers'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['systemRequestLogs'] = $systemDatabasesConnections['systemRequestLogs'];
	$parameters['systemDatabases']['systemSettings'] = $systemDatabasesConnections['systemSettings'];
	$parameters['systemDatabases']['systemUserAuthenticationTokens'] = $systemDatabasesConnections['systemUserAuthenticationTokens'];
	$parameters['systemDatabases']['systemUserAuthenticationTokenScopes'] = $systemDatabasesConnections['systemUserAuthenticationTokenScopes'];
	$parameters['systemDatabases']['systemUserAuthenticationTokenSources'] = $systemDatabasesConnections['systemUserAuthenticationTokenSources'];
	$parameters['systemDatabases']['systemUsers'] = $systemDatabasesConnections['systemUsers'];
?>
