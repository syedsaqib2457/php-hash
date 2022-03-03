<?php
	// todo: create functionality to allow API access to list specific chronologically-sorted request logs in multiple databases for a custom date range

	if (empty($response) === true) {
		exit;
	}

	function _connect($systemDatabaseTableKeys, $existingSystemDatabases, $response) {
		foreach ($systemDatabaseTableKeys as $systemDatabaseTableKey) {
			if (
				(empty($existingSystemDatabases) === false) &&
				(empty($existingSystemDatabases[$systemDatabaseTableKey]) === false)
			) {
				$response['_connect'][$systemDatabaseTableKey] = $existingSystemDatabases[$systemDatabaseTableKey];
				continue;
			}

			$systemDatabase = _list(array(
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
					'tableKey' => $systemDatabaseTableKey
				)
			), $response);
			$systemDatabase = current($systemDatabase);

			if (empty($systemDatabase) === true) {
				$response['message'] = 'Invalid system database ' . $systemDatabase['tableKey'] . ', please try again.';
				unset($response['_connect']);
				_output($parameters, $response);
			}

			$response['_connect'][$systemDatabase['tableKey']] = array(
				'connection' => mysqli_connect($systemDatabase['authenticationCredentialAddress'], 'root', $systemDatabase['authenticationCredentialPassword'], 'firewallSecurityApi'),
				'structure'=> array(
					'tableKey' => $systemDatabaseTableKey
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
			$parameters['where'] = _processSystemDatabaseCommandWhereConditions($parameters['where']);

			if ($parameters['where'] === false) {
				$response['message'] = 'Error processing where conditions in ' . $parameters['in']['structure']['tableKey'] . ' system database, please try again.';
				_output($parameters, $response);
			}

			$systemDatabaseCountCommand .= ' WHERE ' . implode(' AND ', $parameters['where']);
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
			$parameters['where'] = _processSystemDatabaseCommandWhereConditions($parameters['where']);

			if ($parameters['where'] === false) {
				$response['message'] = 'Error processing where conditions in ' . $parameters['in']['structure']['tableKey'] . ' system database, please try again.';
				_output($parameters, $response);
			}

			$systemDatabaseDeleteCommand .= ' WHERE ' . implode(' AND ', $parameters['where']);
		}

		if (mysqli_query($parameters['in']['connection'], $systemDatabaseDeleteCommand) === false) {
			$response['message'] = 'Error deleting data in ' . $parameters['in']['structure']['tableKey'] . ' system database, please try again.';
			_output($parameters, $response);
		}

		return true;
	}

	function _edit($parameters, $response) {
		if (empty($parameters['data']) === false) {
			$systemDatabaseEditCommand = 'UPDATE ' . $parameters['in']['structure']['tableKey'] . ' SET ';

			if (isset($parameters['data']['modifiedTimestamp']) === false) {
				$parameters['data']['modifiedTimestamp'] = time();
			}

			foreach ($parameters['data'] as $editDataKey => $editDataValue) {
				if ((strpos($editDataKey, '`') === false) === false) {
					$response['message'] = 'Error processing data in ' . $parameters['in']['structure']['tableKey'] . ' system database, please try again.';
					_output($parameters, $response);
				}

				if ((strpos($editDataValue, "'") === false) === false) {
					$editDataValue = str_replace("'", "\'", $editDataValue);
				} elseif (is_bool($editDataValue) === true) {
					$editDataValue = intval($editDataValue);
				} elseif ((strlen($editDataValue) === 0) === true) {
					$editDataValue = '';
				}

				$systemDatabaseEditCommand .= '`' . $editDataKey . "`='" . $editDataValue . "',";
			}

			$parameters['where'] = _processSystemDatabaseCommandWhereConditions($parameters['where']);

			if ($parameters['where'] === false) {
				$response['message'] = 'Error processing where conditions in ' . $parameters['in']['structure']['tableKey'] . ' system database, please try again.';
				_output($parameters, $response);
			}

			$systemDatabaseEditCommand = rtrim($systemDatabaseEditCommand, ',') . ' WHERE ' . implode(' AND ', $parameters['where']);

			if (
				(isset($parameters['limit']) === true) &&
				(is_numeric($parameters['limit']) === true)
			) {
				$systemDatabaseEditCommand .= ' LIMIT ' . intval($parameters['limit']);
			}

			$systemDatabaseEditCommandResponse = mysqli_query($parameters['in']['connection'], $systemDatabaseEditCommand);

			if ($systemDatabaseEditCommandResponse === false) {
				$response['message'] = 'Error editing data in ' . $parameters['in']['structure']['tableKey'] . ' system database, please try again.';
				_output($parameters, $response);
			}
		}

		return true;
	}

	function _list($parameters, $response) {
		$systemDatabaseListColumnKeys = '*';

		if (empty($parameters['data']) === false) {
			$systemDatabaseListColumnKeys = '';

			foreach ($parameters['data'] as $listDataValue) {
				if (
					((strlen($listDataValue) === 0) === true) ||
					((strpos($listDataValue, '`') === false) === false)
				) {
					$response['message'] = 'Error processing data in ' . $parameters['in']['structure']['tableKey'] . ' system database, please try again.';
					_output($parameters, $response);
				}

				if (is_bool($listDataValue) === true) {
					$listDataValue = intval($listDataValue);
				}

				$systemDatabaseListColumnKeys .= ',`' . $listDataValue . '`';
			}

			$systemDatabaseListColumnKeys = substr($systemDatabaseListColumnKeys, 1);
		}

		$systemDatabaseListCommand = 'SELECT ' . $systemDatabaseListColumnKeys . ' FROM ' . $parameters['in']['structure']['tableKey'];

		if (empty($parameters['where']) === false) {
			$parameters['where'] = _processSystemDatabaseCommandWhereConditions($parameters['where']);

			if ($parameters['where'] === false) {
				$response['message'] = 'Error processing where conditions in ' . $parameters['in']['structure']['tableKey'] . ' system database, please try again.';
				_output($parameters, $response);
			}

			$systemDatabaseListCommand .= ' WHERE ' . implode(' AND ', $parameters['where']);
		}

		if (empty($parameters['sort']) === false) {
			$systemDatabaseListCommand .= ' ORDER BY ';

			if ($parameters['sort'] === 'random') {
				$systemDatabaseListCommand .= 'RAND()';
			} else {
				foreach ($parameters['sort'] as $systemDatabaseListSortColumnKey => $systemDatabaseListSortOrder) {
					if (
						((strpos($systemDatabaseListSortColumnKey, '`') === false) === false) ||
						(
							(($systemDatabaseListSortOrder === 'ascending') === false) &&
							(($systemDatabaseListSortOrder === 'descending') === false)
						)
					) {
						$response['message'] = 'Error processing sort conditions in ' . $parameters['in']['structure']['tableKey'] . ' system database, please try again.';
						_output($parameters, $response);
					}

					$systemDatabaseListSortOrder = substr($systemDatabaseListSortOrder, 0, -6);
					$systemDatabaseListCommand .= '`' . $systemDatabaseListSortColumnKey . '` ' . strtoupper($systemDatabaseListSortOrder) . ',';
				}

				$systemDatabaseListCommand = substr($systemDatabaseListCommand, 0, -1);
			}
		}

		if (
			(isset($parameters['limit']) === true) &&
			(is_numeric($parameters['limit']) === true)
		) {
			$systemDatabaseListCommand .= ' LIMIT ' . intval($parameters['limit']);
		}

		if (
			(isset($parameters['offset']) === true) &&
			(is_numeric($parameters['offset']) === true)
		) {
			$systemDatabaseListCommand .= ' OFFSET ' . intval($parameters['offset']);
		}

		$systemDatabaseListRows = mysqli_query($parameters['in']['connection'], $systemDatabaseListCommand);

		if ($systemDatabaseListRows === false) {
			$response['message'] = 'Error listing data rows in ' . $parameters['in']['structure']['tableKey'] . ' system database, please try again.';
			_output($parameters, $response);
		}

		$response['data'] = array();

		foreach ($systemDatabaseListRows as $systemDatabaseListRow) {
			$response['data'][] = $systemDatabaseListRow;
		}

		return $response['data'];
	}

	function _processSystemDatabaseCommandWhereConditions($whereConditions) {
		foreach ($whereConditions as $whereConditionKey => $whereConditionValue) {
			if ((strpos($whereConditionKey, '`') === false) === false) {
				return false;
			}

			$whereConditionConjunction = 'AND';

			if ($whereConditionKey === 'either') {
				$whereConditionConjunction = 'OR';
			}

			if (
				(is_numeric($whereConditionKey) === true) ||
				(
					(is_array($whereConditionValue) === true) &&
					((count($whereConditionValue) === count($whereConditionValue, true)) === false)
				)
			) {
				$recursiveWhereConditions = $whereConditionValue;
				$whereConditions[$whereConditionKey] = _processSystemDatabaseCommandWhereConditions($recursiveWhereConditions);
				$whereConditions[$whereConditionKey] = '(' . implode(' ' . $whereConditionConjunction . ' ', $whereConditions[$whereConditionKey]) . ')';
			} else {
				if (is_array($whereConditionValue) === false) {
					$whereConditionValue = array(
						$whereConditionValue
					);
				}

				$whereConditionValueConditions = array();

				foreach ($whereConditionValue as $whereConditionValueKey => $whereConditionValueValue) {
					if ((strpos($whereConditionValueKey, '`') === false) === false) {
						return false;
					}

					if ((strpos($whereConditionValueValue, "'") === false) === false) {
						$whereConditionValueValue = str_replace("'", "\'", $whereConditionValueValue);
					} elseif (is_bool($whereConditionValueValue) === true) {
						$whereConditionValueValue = intval($whereConditionValueValue);
					} elseif ((strlen($whereConditionValueValue) === 0) === true) {
						$whereConditionValueValue = '';
					}

					$whereConditionValue[$whereConditionValueKey] = $whereConditionValueValue;

					if (($whereConditionKey === 'either') === true) {
						if (
							((strpos($whereConditionValueKey, ' greaterThan') === false) === false) ||
							((strpos($whereConditionValueKey, ' lessThan') === false) === false)
						) {
							$whereConditionValueComparisons = array(
								'greaterThan' => '>',
								'greaterThanOrEqualTo' => '>=',
								'lessThan' => '<',
								'lessThanOrEqualTo' => '<='
							);
							$whereConditionKeyDelimiterPosition = strpos($whereConditionValueKey, ' ');
							$whereConditionValueCondition = substr($whereConditionValueKey, ($whereConditionKeyDelimiterPosition + 1));
							$whereConditionValueKeyDelimiterPosition = strpos($whereConditionValueKey, ' ');
							$whereConditionValueKey = '`' . substr($whereConditionValueKey, 0, $whereConditionValueKeyDelimiterPosition) . '`';

							if (empty($whereConditionValueComparisons[$whereConditionValueCondition]) === true) {
								return false;
							}

							$whereConditionValueKey .= ' ' . $whereConditionValueComparisons[$whereConditionValueCondition];
							$whereConditionValueConditions[] = $whereConditionValueKey . " '" . $whereConditionValueValue . "'";
						} else {
							if ((strpos($whereConditionValueKey, ' like') === false) === false) {
								$whereConditionValueWildcards = array(
									'prefix' => '%',
									'suffix' => '%'
								);
								$whereConditionValueKeyDelimiterPosition = strrpos($whereConditionValueKey, ' ');
								$whereConditionValuePrefix = substr($whereConditionValueKey, ($whereConditionValueKeyDelimiterPosition + 1));

								if (empty($whereConditionValueWildcards[$whereConditionValuePrefix]) === false) {
									$whereConditionValueKey = substr($whereConditionValueKey, 0, $whereConditionValueKeyDelimiterPosition);
									$whereConditionValueWildcards[$whereConditionValuePrefix] = '';
								}

								$whereConditionValueKeyDelimiterPosition = strpos($whereConditionValueKey, ' ');
								$whereConditionValueCondition = substr($whereConditionValueKey, ($whereConditionValueKeyDelimiterPosition + 1));

								if (
									(($whereConditionValueCondition === 'like') === false) &&
									(($whereConditionValueCondition === 'not like') === false)
								) {
									return false;
								}

								$whereConditionValueCondition = strtoupper($whereConditionValueCondition);
								$whereConditionValueKey = substr($whereConditionValueKey, 0, $whereConditionValueKeyDelimiterPosition);
								$whereConditionValueValue = $whereConditionValueWildcards['prefix'] . str_replace('%', '\%', $whereConditionValueValue) . $whereConditionValueWildcards['suffix'];
							} else {
								$whereConditionValueValueCondition = 'IN';

								if ((strpos($whereConditionValueKey, ' not') === false) === false) {
									$whereConditionValueKeyDelimiterPosition = strpos($whereConditionValueKey, ' ');
									$whereConditionValueKey = substr($whereConditionValueKey, 0, $whereConditionValueKeyDelimiterPosition);
									$whereConditionValueValueCondition = 'NOT ' . $whereConditionValueValueCondition;
								}

								$whereConditionValueValue = "('" . $whereConditionValueValue . "')";
							}

							$whereConditionValueConditions[] = '`' . $whereConditionValueKey . '` ' . $whereConditionValueValueCondition . ' ' . $whereConditionValueValue;
						}
					}
				}

				if (empty($whereConditionValueConditions) === true) {
					$whereConditionValueKey = $whereConditionKey;

					if (
						((strpos($whereConditionValueKey, ' greaterThan') === false) === false) ||
						((strpos($whereConditionValueKey, ' lessThan') === false) === false)
					) {
						$whereConditionValueComparisons = array(
							'greaterThan' => '>',
							'greaterThanOrEqualTo' => '>=',
							'lessThan' => '<',
							'lessThanOrEqualTo' => '<='
						);
						$whereConditionKeyDelimiterPosition = strpos($whereConditionValueKey, ' ');
						$whereConditionValueCondition = substr($whereConditionValueKey, ($whereConditionKeyDelimiterPosition + 1));
						$whereConditionValueKeyDelimiterPosition = strpos($whereConditionValueKey, ' ');
						$whereConditionValueKey = '`' . substr($whereConditionValueKey, 0, $whereConditionValueKeyDelimiterPosition) . '`';

						if (empty($whereConditionValueComparisons[$whereConditionValueCondition]) === true) {
							return false;
						}

						$whereConditionValueKey .= ' ' . $whereConditionValueComparisons[$whereConditionValueCondition];
						$whereConditionValueConditions[] = $whereConditionValueKey . " '" . current($whereConditionValue) . "'";
					} else {
						if ((strpos($whereConditionValueKey, ' like') === false) === false) {
							$whereConditionValueWildcards = array(
								'prefix' => '%',
								'suffix' => '%'
							);
							$whereConditionValueKeyDelimiterPosition = strrpos($whereConditionValueKey, ' ');
							$whereConditionValuePrefix = substr($whereConditionValueKey, ($whereConditionValueKeyDelimiterPosition + 1));

							if (empty($whereConditionValueWildcards[$whereConditionValuePrefix]) === false) {
								$whereConditionValueKey = substr($whereConditionValueKey, 0, $whereConditionValueKeyDelimiterPosition);
								$whereConditionValueWildcards[$whereConditionValuePrefix] = '';
							}

							$whereConditionValueKeyDelimiterPosition = strpos($whereConditionValueKey, ' ');
							$whereConditionValueCondition = substr($whereConditionValueKey, ($whereConditionValueKeyDelimiterPosition + 1));

							if (
								(($whereConditionValueCondition === 'like') === false) &&
								(($whereConditionValueCondition === 'not like') === false)
							) {
								return false;
							}

							$whereConditionValueCondition = strtoupper($whereConditionValueCondition);
							$whereConditionValueKey = substr($whereConditionValueKey, 0, $whereConditionValueKeyDelimiterPosition);
							$whereConditionValueValueConditions = '(';

							foreach ($whereConditionValue as $whereConditionValueValue) {
								if ((strpos($whereConditionValueValue, '%') === false) === false) {
									$whereConditionValueValue = str_replace('%', '\%', $whereConditionValueValue);
								}

								$whereConditionValueValueConditions .= '`' . $whereConditionValueKey . '` ' . $whereConditionValueCondition . " '" . $whereConditionValueWildcards['prefix'] . $whereConditionValueValue . $whereConditionValueWildcards['suffix'] . "' OR";
							}

							$whereConditionValueConditions[] = substr($whereConditionValueValueConditions, 0, '-3') . ')';
						} else {
							$whereConditionValueCondition = 'IN';
							$whereConditionValueKey = $whereConditionKey;

							if ((strpos($whereConditionValueKey, ' not') === false) === false) {
								$whereConditionValueKeyDelimiterPosition = strpos($whereConditionValueKey, ' ');
								$whereConditionValueKey = substr($whereConditionValueKey, 0, $whereConditionValueKeyDelimiterPosition);
								$whereConditionValueCondition = 'NOT ' . $whereConditionValueCondition;
							}

							$whereConditionValueConditions[] = '`' . $whereConditionValueKey . '` ' . $whereConditionValueCondition . " ('" . implode("','", $whereConditionValue) . "')";
						}
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
				$systemDatabaseInsertColumnKeys = '';
				$systemDatabaseInsertColumnValues = '';
				$systemDatabaseUpdateColumnValues = '';

				foreach ($systemDatabaseColumns as $systemDatabaseColumnKey => $systemDatabaseColumnValue) {
					if ((strpos($systemDatabaseColumnKey, '`') === false) === false) {
						continue;
					}

					$systemDatabaseInsertColumnKeys .= ',`' . $systemDatabaseColumnKey . '`';
					$systemDatabaseInsertColumnValue = '';

					if ((strlen($systemDatabaseColumnValue) === 0) === false) {
						$systemDatabaseInsertColumnValue = $systemDatabaseColumnValue;

						if ((strpos($systemDatabaseInsertColumnValue, '\\') === false) === false) {
							$systemDatabaseInsertColumnValue = str_replace('\\', '\\\\', $systemDatabaseInsertColumnValue);
						}

						if ((strpos($systemDatabaseInsertColumnValue, "'") === false) === false) {
							$systemDatabaseInsertColumnValue = str_replace("'", "\'", $systemDatabaseInsertColumnValue);
						}
					}

					$systemDatabaseInsertColumnValues .= ",'" . $systemDatabaseInsertColumnValue . "'";
					$systemDatabaseUpdateColumnValues .= ',`' . $systemDatabaseColumnKey . "`='" . $systemDatabaseInsertColumnValue . "'";
				}

				if (empty($systemDatabaseColumns['createdTimestamp']) === true) {
					$systemDatabaseInsertColumnKeys .= ',`createdTimestamp`';
					$systemDatabaseInsertColumnValues .= ",'" . $timestamp . "'";
					$systemDatabaseUpdateColumnValues .= ",`createdTimestamp`='" . $timestamp . "'";
				}

				if (empty($systemDatabaseColumns['modifiedTimestamp']) === true) {
					$systemDatabaseInsertColumnKeys .= ',`modifiedTimestamp`';
					$systemDatabaseInsertColumnValues .= ",'" . $timestamp . "'";
					$systemDatabaseUpdateColumnValues .= ",`modifiedTimestamp`='" . $timestamp . "'";
				}

				$systemDatabaseInsertColumnKeys = substr($systemDatabaseInsertColumnKeys, 1);
				$systemDatabaseInsertColumnValues = substr($systemDatabaseInsertColumnValues, 1);
				$systemDatabaseUpdateColumnValues = 'ON DUPLICATE KEY UPDATE ' . substr($systemDatabaseUpdateColumnValues, 1);

				if (mysqli_query($parameters['in']['connection'], 'INSERT INTO ' . $parameters['in']['structure']['tableKey'] . '(' . $systemDatabaseInsertColumnKeys . ') VALUES (' . $systemDatabaseInsertColumnValues . ') ' . $systemDatabaseUpdateColumnValues) === false) {
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
