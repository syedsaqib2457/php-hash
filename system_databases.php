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

	$databases = array(
		'node_process_blockchain_mining_resource_usage_rules' => array(),
		'node_process_forwarding_destinations' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'address_version_4',
					'address_version_6',
					'created_timestamp',
					'id',
					'modified_timestamp',
					'node_id',
					'node_node_id',
					'node_process_type',
					'port_number_version_4',
					'port_number_version_6'
				),
				'table' => 'node_process_forwarding_destinations'
			)
		),
		'node_process_node_user_authentication_credentials' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'id',
					'modified_timestamp',
					'node_id',
					'node_node_id',
					'node_process_type',
					'node_user_authentication_credential_id',
					'node_user_authentication_credential_password',
					'node_user_authentication_credential_username',
					'node_user_id'
				)
			),
			'table' => 'node_process_node_user_authentication_credentials'
		),
		'node_process_node_user_authentication_sources' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'id',
					'modified_timestamp',
					'node_id',
					'node_node_id',
					'node_process_type',
					'node_user_authentication_source_ip_address',
					'node_user_authentication_source_ip_address_block_length',
					'node_user_authentication_source_ip_address_version',
					'node_user_id'
				),
				'table' => 'node_process_node_user_authentication_sources'
			)
		),
		'node_process_node_user_request_destination_logs' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'id',
					'modified_timestamp',
					'node_id',
					'node_node_id',
					'node_process_type',
					'node_request_destination_id',
					'node_user_id',
					'request_count'
				),
				'name' => 'node_process_node_user_request_destination_logs'
			)
		),
		'node_process_node_user_node_request_destinations' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'id',
					'modified_timestamp',
					'node_id',
					'node_node_id',
					'node_process_type',
					'node_request_destination_address',
					'node_request_destination_id',
					'node_user_id'
				),
				'table' => 'node_process_node_user_node_request_destinations'
			)
		),
		'node_process_node_user_node_request_limit_rules' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'expiration_timestamp',
					'id',
					'modified_timestamp',
					'node_id',
					'node_node_id',
					'node_process_type',
					'node_request_destination_id',
					'node_request_limit_rule_id',
					'node_user_id'
				),
				'table' => 'node_process_node_user_node_request_limit_rules'
			)
		),
		'node_process_node_user_request_logs' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'bytes_received',
					'bytes_sent',
					'created_timestamp',
					'destination_ip_address',
					'destination_url',
					'id',
					'modified_timestamp',
					'node_id',
					'node_node_id',
					'node_process_type',
					'node_request_destination_id',
					'node_user_id',
					'response_code',
					'source_ip_address',
					'status_processed',
					'status_processing'
				),
				'table' => 'node_process_node_user_request_logs'
			)
		),
		'node_process_node_user_resource_usage_logs' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'bytes_received',
					'bytes_sent',
					'created_timestamp',
					'id',
					'modified_timestamp',
					'node_id',
					'node_node_id',
					'node_process_type',
					'node_user_id',
					'request_count'
				),
				'table' => 'node_process_node_user_resource_usage_logs'
			)
		),
		'node_process_node_users' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'id',
					'modified_timestamp',
					'node_id',
					'node_node_id',
					'node_process_type',
					'node_user_id',
					'node_user_status_node_request_destinations_only_allowed',
					'node_user_status_node_request_logs_allowed',
					'node_user_status_strict_authentication_required'
				),
				'table' => 'node_process_node_users'
			)
		),
		'node_process_recursive_dns_destinations' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'id',
					'listening_ip_address_version_4',
					'listening_ip_address_version_4_node_id',
					'listening_ip_address_version_6',
					'listening_ip_address_version_6_node_id',
					'listening_port_number_version_4',
					'listening_port_number_version_6',
					'modified_timestamp',
					'node_id',
					'node_node_id',
					'node_process_type',
					'source_ip_address_version_4',
					'source_ip_address_version_6'
				),
				'table' => 'node_process_recursive_dns_destinations'
			)
		),
		'node_process_resource_usage_logs' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'bytes_received',
					'bytes_sent',
					'cpu_percentage',
					'created_timestamp',
					'id',
					'memory_percentage',
					'modified_timestamp',
					'node_id',
					'node_node_id',
					'node_process_type',
					'request_count'
				),
				'table' => 'node_process_resource_usage_logs'
			)
		),
		'node_processes' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'id',
					'modified_timestamp',
					'node_id',
					'node_node_id',
					'port_number',
					'type'
				),
				'table' => 'node_processes'
			)
		),
		'node_request_destinations' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'address',
					'created_timestamp',
					'id',
					'modified_timestamp'
				),
				'table' => 'node_request_destinations'
			)
		),
		'node_request_limit_rules' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'id',
					'interval_minutes',
					'modified_timestamp',
					'request_count',
					'request_count_interval_minutes'
				),
				'table' => 'node_request_limit_rules'
			)
		),
		'node_reserved_internal_destinations' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'id',
					'ip_address',
					'ip_address_version',
					'modified_timestamp',
					'node_id',
					'node_node_id',
					'node_node_external_ip_address_type',
					'status_added',
					'status_processed'
				),
				'table' => 'node_reserved_internal_destinations'
			)
		),
		'node_resource_usage_logs' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'bytes_received',
					'bytes_sent',
					'cpu_capacity_megahertz',
					'cpu_core_count',
					'cpu_percentage',
					'created_timestamp',
					'id',
					'memory_capacity_megabytes',
					'memory_percentage',
					'modified_timestamp',
					'node_id',
					'request_count',
					'storage_capacity_megabytes',
					'storage_percentage'
				),
				'table' => 'node_resource_usage_logs'
			)
		),
		'node_user_authentication_credentials' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'id',
					'modified_timestamp',
					'node_user_id',
					'password',
					'username'
				)
			),
			'table' => 'node_user_authentication_credentials'
		),
		'node_user_authentication_sources' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'id',
					'ip_address',
					'ip_address_block_length',
					'ip_address_version',
					'modified_timestamp',
					'node_user_id'
				),
				'table' => 'node_user_authentication_sources'
			)
		),
		'node_user_node_request_destinations' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'id',
					'modified_timestamp',
					'node_request_destination_address',
					'node_request_destination_id',
					'node_user_id'
				),
				'table' => 'node_user_node_request_destinations'
			)
		),
		'node_user_node_request_limit_rules' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'expiration_date',
					'id',
					'modified_timestamp',
					'node_request_destination_id',
					'node_request_limit_rule_id',
					'node_user_id'
				),
				'table' => 'node_user_node_request_limit_rules'
			)
		),
		'node_users' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'id',
					'modified_timestamp',
					'status_node_request_destinations_only_allowed',
					'status_node_request_logs_allowed',
					'status_strict_authentication_required',
					'tag'
				),
				'table' => 'node_users'
			)
		),
		'nodes' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'authentication_token',
					'cpu_capacity_megahertz',
					'cpu_core_count',
					'created_timestamp',
					'external_ip_address_version_4',
					'external_ip_address_version_4_type',
					'external_ip_address_version_6',
					'external_ip_address_version_6_type',
					'id',
					'internal_ip_address_version_4',
					'internal_ip_address_version_4_type',
					'internal_ip_address_version_6',
					'internal_ip_address_version_6_type',
					'memory_capacity_megabytes',
					'modified_timestamp',
					'node_id',
					'processing_progress_checkpoint',
					'processing_progress_percentage',
					'status_activated',
					'status_deployed',
					'status_processed',
					'status_processing',
					'storage_capacity_megabytes'
				),
				'table' => 'nodes'
			)
		),
		'system_database_columns' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'created_timestamp',
				'id',
				'maximum_length',
				'modified_timestamp',
				'name',
				'system_database_id'
			)
		),
		'system_databases' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'authentication_credential_hostname',
				'authentication_credential_password',
				'created_timestamp',
				'id',
				'modified_timestamp',
				'name'
			)
		),
		'system_resource_usage_logs' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'bytes_received',
					'bytes_sent',
					'cpu_capacity_megahertz',
					'cpu_core_count',
					'cpu_percentage',
					'created_timestamp',
					'destination_ip_address',
					'id',
					'memory_capacity_megabytes',
					'memory_percentage',
					'modified_timestamp',
					'storage_capacity_megabytes',
					'storage_percentage'
				),
				'table' => 'system_resource_usage_logs'
			)
		),
		'system_user_authentication_token_scopes' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'id',
					'modified_timestamp',
					'system_action',
					'system_user_authentication_token_id',
					'system_user_id'
				),
				'table' => 'system_user_authentication_token_scopes'
			)
		),
		'system_user_authentication_token_sources' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'id',
					'ip_address_range_start',
					'ip_address_range_stop',
					'ip_address_version',
					'modified_timestamp',
					'system_user_authentication_token_id',
					'system_user_id'
				),
				'table' => 'system_user_authentication_token_sources'
			)
		),
		'system_user_authentication_tokens' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'id',
					'modified_timestamp',
					'string',
					'system_user_id'
				),
				'table' => 'system_user_authentication_tokens'
			)
		),
		'system_user_request_logs' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'bytes_received',
					'bytes_sent',
					'created_timestamp',
					'id',
					'modified_timestamp',
					'node_id',
					'response_code',
					'source_ip_address',
					'status_authorized',
					'status_successful',
					'system_action',
					'system_user_authentication_token_id',
					'system_user_id'
				),
				'table' => 'system_user_request_logs'
			)
		),
		'system_users' => array(
			'authentication' => array(
				'hostname' => 'localhost',
				'password' => 'password'
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp',
					'id',
					'modified_timestamp',
					'system_user_id'
				),
				'table' => 'system_users'
			)
		)
	);

	function _connect($databases, $existingDatabases, $response) {
		// todo: list database column data from system_database_columns table

		foreach ($databases as $database) {
			if (
				(empty($existingDatabases) === false) &&
				(empty($existingDatabases[$database['structure']['table']]) === false)
			) {
				continue;
			}

			$response['_connect'][$database['structure']['table']] = $database['structure'];

			foreach ($database['authentication'] as $databaseAuthenticationIndex => $databaseAuthentication) {
				$response['_connect'][$database['structure']['table']]['connections'][$databaseAuthenticationIndex] = mysqli_connect($databaseAuthentication['hostname'], 'root', $databaseAuthentication['password'], 'ghostcompute');

				if ($response['_connect'][$database['structure']['table']]['connections'][$databaseAuthenticationIndex] === false) {
					$response['message'] = 'Error connecting to ' . $database['structure']['table'] . ' database, please try again.';
					unset($response['_connect']);
					_output($response);
				}
			}
		}

		$response = $response['_connect'];
		return $response;
	}

	function _count($parameters, $response) {
		$response['_count'] = 0;
		$command = 'select count(id) from ' . $parameters['in']['table'];

		if (empty($parameters['where']) === false) {
			$command .= ' where ' . implode(' and ', _parseCommandWhereConditions($parameters['where']));
		}

		foreach ($parameters['in']['connections'] as $connection) {
			$commandResponse = mysqli_query($connection, $command);

			if ($commandResponse === false) {
				$response['message'] = 'Error counting data in ' . $parameters['in']['table'] . ' database, please try again.';
				unset($response['_count']);
				_output($response);
			}

			$commandResponse = mysqli_fetch_assoc($commandResponse);

			if ($commandResponse === false) {
				$response['message'] = 'Error counting data in ' . $parameters['in']['table'] . ' database, please try again.';
				unset($response['_count']);
				_output($response);
			}

			$commandResponse = current($commandResponse);

			if (is_int($commandResponse) === false) {
				$response['message'] = 'Error counting data in ' . $parameters['in']['table'] . ' database, please try again.';
				unset($response['_count']);
				_output($response);
			}

			$response['_count'] += $commandResponse;
		}

		$response = $response['_count'];
		return $response;
	}

	function _delete($parameters, $response) {
		$command = 'delete from ' . $parameters['in']['table'];

		if (empty($parameters['where']) === false) {
			$command .= ' where ' . implode(' and ', _parseCommandWhereConditions($parameters['where']));
		}

		foreach ($parameters['in']['connections'] as $connection) {
			$commandResponse = mysqli_query($connection, $command);

			if ($commandResponse === false) {
				$response['message'] = 'Error deleting data in ' . $parameters['in']['table'] . ' database, please try again.';
				_output($response);
			}
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

	$parameters['databases'] = _connect(array(
		$databases['system_user_authentication_token_scopes'],
		$databases['system_user_authentication_token_sources'],
		$databases['system_user_authentication_tokens'],
		$databases['system_users']
	), false, $response);
?>
