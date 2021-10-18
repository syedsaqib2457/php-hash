<?php
	// todo: database tables with more than 1 external database should only support _fetch() + _save() with no sorting

	if (empty($parameters) === true) {
		exit;
	}

	function _connect($databases) {
		$response = array();

		foreach ($databases as $databaseKey => $database) {
			$response[$databaseKey] = array(
				'settings' => array(
					'name' => $databaseKey,
					'structure' => $database['structure']
				)
			);

			foreach ($database['authentication'] as $databaseAuthenticationIndex => $databaseAuthentication) {
				$response[$databaseKey]['connections'][$databaseAuthenticationIndex] = mysqli_connect($databaseAuthentication['hostname'], 'root', $databaseAuthentication['password'], 'ghostcompute');

				if ($response[$databaseKey]['connections'][$databaseAuthenticationIndex] === false) {
					$response = array(
						'message' => 'Error connecting to ' . $databaseKey . ' database, please try again.'
					);
					return $response;
				}
			}
		}

		return $response;
	}

	function _count($parameters) {
		$response = 0;
		$command = 'SELECT COUNT(id) FROM ' . $parameters['in']['settings']['name'];

		if (empty($parameters['where']) === false) {
			$command .= ' WHERE ' . implode(' AND ', _parseCommandWhereConditions($parameters['where']));
		}

		foreach ($parameters['in']['connections'] as $connection) {
			$commandResponse = mysqli_query($connection, $command);

			if ($commandResponse === false) {
				return false;
			}

			$commandResponse = mysqli_fetch_assoc($commandResponse);

			if ($commandResponse === false) {
				return false;
			}

			$response += $commandResponse['COUNT(id)'];
		}

		return $response;
	}

	function _delete($parameters) {
		$response = true;
		$command = 'DELETE FROM ' . $parameters['in']['settings']['name'];

		if (empty($parameters['where']) === false) {
			$command .= ' WHERE ' . implode(' AND ', _parseCommandWhereConditions($parameters['where']));
		}

		foreach ($parameters['in']['connections'] as $connection) {
			$commandResponse = mysqli_query($connection, $command);

			if ($commandResponse === false) {
				$response = false;
			}
		}

		return $response;
	}

	function _list($parameters) {
		$response = array();
		$command = 'SELECT * FROM ' . $parameters['in']['settings']['name'];

		if (empty($parameters['where']) === false) {
			$command .= ' WHERE ' . implode(' AND ', _parseCommandWhereConditions($parameters['where']));
		}

		if (empty($parameters['sort']) === false) {
			$command .= ' ORDER BY ';

			if ($parameters['sort'] === 'random') {
				$command .= 'RAND()';
			} elseif (
				(empty($parameters['sort']['key']) === false) &&
				($sortKey = $parameters['sort']['key'])
			) {
				if (empty($parameters['sort']['order']) === true) {
					$parameters['sort']['order'] = 'DESC';
				}

				$command .= $sortKey . ' ' . $parameters['sort']['order'] . ', id DESC';
			}
		}

		if (empty($parameters['limit']) === false) {
			$command .= ' LIMIT ' . $parameters['limit'];
		}

		if (empty($parameters['offset']) === false) {
			$command .= ' OFFSET ' . $parameters['offset'];
		}

		foreach ($parameters['in']['connections'] as $connectionIndex => $connection) {
			$commandResponse = mysqli_query($connection, $command);

			if ($commandResponse === false) {
				return false;
			}

			$response[$connectionIndex] = mysqli_fetch_assoc($commandResponse);

			if ($response[$connectionIndex] === false) {
				return false;
			}
		}

		return $response;
	}

	function _parseCommandWhereConditions($whereConditions, $conjunction = 'AND') {
		foreach ($whereConditions as $whereConditionKey => $whereConditionValue) {
			if ($whereConditionKey === 'OR') {
				$conjunction = $whereConditionKey;
			}

			if (
				(is_array($whereConditionValue) === true) &&
				(count($whereConditionValue) !== count($whereConditionValue, COUNT_RECURSIVE))
			) {
				$whereConditions[$whereConditionKey] = '(' . implode(') ' . $conjunction . ' (', _parseCommandWhereConditions($whereConditionValue, $conjunction)) . ')';
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

					if ($conjunction === $whereConditionKey) {
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
						(strpos($whereConditionKey, ' >') !== false) ||
						(strpos($whereConditionKey, ' <') !== false)
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

				$whereConditions[$whereConditionKey] = '(' . implode(' ' . $conjunction . ' ', $whereConditionValueConditions) . ')';
			}
		}

		$response = $whereConditions;
		return $response;
	}

	function _save($parameters) {
		$response = true;

		if (empty($parameters['data']) === false) {
			if (is_numeric(key($parameters['data'])) === false) {
				$parameters['data'] = array(
					$parameters['data']
				);
			}

			$connectionIndex = 0;

			foreach ($parameters['data'] as $data) {
				$dataInsertValues = $dataKeys = $dataUpdateValues = '';
				$timestamp = date('Y-m-d H:i:s', time());

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
					$dataUpdateValues = ' ON DUPLICATE KEY UPDATE ' . substr($dataUpdateValues, 1);
				}

				$commandResponse = mysqli_query($parameters['in']['connections'][$connectionIndex], 'INSERT INTO ' . $parameters['in']['settings']['name'] . '(' . substr($dataKeys, 1) . ") VALUES (" . substr($dataInsertValues, 2) . "')" . $dataUpdateValues);

				if ($commandResponse === false) {
					$response = false;
				}

				if (empty($parameters['in']['connections'][1]) === false) {
					$connectionIndex++;

					if (empty($parameters['in']['connections'][$connectionIndex]) === true) {
						$connectionIndex = 0;
					}
				}
			}
		}

		return $response;
	}

	function _update($parameters) {
		$response = true;

		if (empty($parameters['data']) === false) {
			$command = 'UPDATE ' . $parameters['in']['settings']['name'] . ' SET ';

			foreach ($parameters['data'] as $updateValueKey => $updateValue) {
				$command .= $updateValueKey . "='" . str_replace("'", "\'", $updateValue) . "',";
			}

			$command = rtrim($command, ',') . ' WHERE ' . implode(' AND ', _parseCommandWhereConditions($parameters['where']));

			foreach ($parameters['in']['connections'] as $connection) {
				$commandResponse = mysqli_query($connection, $command);

				if ($commandResponse === false) {
					$response = false;
				}
			}
		}

		return $response;
	}

	$parameters['databases'] = array(
		'system_user_authentication_token_scopes' => $settings['databases']['system_user_authentication_token_scopes'],
		'system_user_authentication_token_sources' => $settings['databases']['system_user_authentication_token_sources'],
		'system_user_authentication_tokens' => $settings['databases']['system_user_authentication_tokens'],
		'system_users' => $settings['databases']['system_users']
	);
?>
