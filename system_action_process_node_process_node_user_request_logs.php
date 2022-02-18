<?php
	if (empty($_SERVER['argv'][1]) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_process_node_user_request_destination_logs',
		'node_process_node_user_request_logs',
		'node_process_node_users'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_process_node_user_request_destination_logs'] = $systemDatabasesConnections['node_process_node_user_request_destination_logs'];
	$parameters['system_databases']['node_process_node_user_request_logs'] = $systemDatabasesConnections['node_process_node_user_request_logs'];
	$parameters['system_databases']['node_process_node_users'] = $systemDatabasesConnections['node_process_node_users'];
	require_once('/var/www/nodecompute/system_action_add_node_resource_usage_log.php');
	require_once('/var/www/nodecompute/system_action_add_node_process_resource_usage_logs.php');

	function _processNodeProcessNodeUserRequestLogs($parameters, $response) {
		_edit(array(
			'data' => array(
				'processing_process_id' => $parameters['process_id']
			),
			'in' => $parameters['system_databases']['node_process_node_user_request_logs'],
			'limit' => 10000,
			'where' => array(
				'processed_status' => '0',
				'processing_process_id' => null
			)
		), $response);
		$nodeProcessNodeUserRequestDestinationLogs = array();
		$nodeProcessNodeUserRequestLogPartIndex = 0;
		$nodeProcessResourceUsageLogs = array();

		while (($nodeProcessNodeUserRequestLogPartIndex === 9) === false) {
			$nodeProcessNodeUserRequestLogData = array();
			$nodeProcessNodeUserRequestLogs = _list(array(
				'data' => array(
					'bytes_received',
					'bytes_sent',
					'destination_hostname_address',
					'id',
					'node_id',
					'node_node_id',
					'node_process_type',
					'node_user_id'
				),
				'in' => $parameters['system_databases']['node_process_node_user_request_logs'],
				'limit' => 1000,
				'where' => array(
					'processed_status' => '0',
					'processing_process_id' => $parameters['process_id']
				)
			), $response);
			$nodeRequestDestinations = array();

			foreach ($nodeProcessNodeUserRequestLogs as $nodeProcessNodeUserRequestLogsKey => $nodeProcessNodeUserRequestLog) {
				if (empty($nodeRequestDestinations[$nodeProcessNodeUserRequestLog['destination_hostname_address']]) === true) {
					$nodeRequestDestinations[$nodeProcessNodeUserRequestLog['destination_hostname_address']] = '';
					$nodeRequestDestination = _list(array(
						'data' => array(
							'id'
						),
						'in' => $parameters['system_databases']['node_request_destinations'],
						'where' => array(
							'address' => $nodeProcessNodeUserRequestLog['destination_hostname_address']
						)
					), $response);
					$nodeRequestDestination = current($nodeRequestDestination);

					if (empty($nodeRequestDestination) === false) {
						$nodeRequestDestinations[$nodeProcessNodeUserRequestLog['destination_hostname_address']] = $nodeRequestDestination['id'];
					}
				}

				$nodeProcessNodeUserRequestDestinationLogCreatedTimestamp = date('Y-m-d H:i', $nodeProcessNodeUserRequestDestinationLogCreatedTimestamp['created_timestamp']);
				$nodeProcessNodeUserRequestDestinationLogCreatedTimestamp = substr($nodeProcessNodeUserRequestDestinationLogCreatedTimestamp, 0, 13) . ':00:00';
				$nodeProcessNodeUserRequestDestinationLogCreatedTimestamp = strtotime($nodeProcessNodeUserRequestDestinationLogCreatedTimestamp);

				if (empty($nodeProcessNodeUserRequestDestinationLogs[$nodeProcessNodeUserRequestLog['node_id']][$nodeProcessNodeUserRequestLog['node_process_type']][$nodeProcessNodeUserRequestLog['node_user_id']]) === true) {
					$nodeProcessNodeUserRequestDestinationLogs[$nodeProcessNodeUserRequestLog['node_id']][$nodeProcessNodeUserRequestLog['node_process_type']][$nodeProcessNodeUserRequestLog['node_user_id']] = array(
						'created_timestamp' => $nodeProcessNodeUserRequestDestinationLogCreatedTimestamp,
						'node_id' => $nodeProcessNodeUserRequestLog['node_id'],
						'node_node_id' => $nodeProcessNodeUserRequestLog['node_node_id'],
						'node_process_type' => $nodeProcessNodeUserRequestLog['node_process_type'],
						'node_request_destination_id' => $nodeRequestDestinations[$nodeProcessNodeUserRequestLog['destination_hostname_address']],
						'node_user_id' => $nodeProcessNodeUserRequestLog['node_user_id'],
						'request_count' => 0
					);
				}

				$nodeProcessNodeUserRequestDestinationLogs[$nodeProcessNodeUserRequestLog['node_id']][$nodeProcessNodeUserRequestLog['node_process_type']][$nodeProcessNodeUserRequestLog['node_user_id']]['request_count']++;
				$nodeProcessNodeUserRequestLogs[$nodeProcessNodeUserRequestLogsKey] = array(
					'id' => $nodeProcessNodeUserRequestLog['id'],
					'node_request_destination_id' => $nodeRequestDestinations[$nodeProcessNodeUserRequestLog['destination_hostname_address']],
					'processed_status' => '1',
					'processing_process_id' => null
				);
				$nodeProcessResourceUsageLogCreatedTimestamp = date('Y-m-d H:i', $nodeProcessNodeUserRequestLog['created_timestamp']);
				$nodeProcessResourceUsageLogCreatedTimestamp = substr($nodeProcessResourceUsageLogCreatedTimestamp, 0, 15) . '0:00';
				$nodeProcessResourceUsageLogCreatedTimestamp = strtotime($nodeProcessResourceUsageLogCreatedTimestamp);

				if (empty($nodeProcessResourceUsageLogs[$nodeProcessResourceUsageLogCreatedTimestamp][$nodeProcessNodeUserRequestLog['node_node_id']][$nodeProcessNodeUserRequestLog['node_process_type']]) === true) {
					$nodeProcessResourceUsageLogs[$nodeProcessResourceUsageLogCreatedTimestamp][$nodeProcessNodeUserRequestLog['node_node_id']][$nodeProcessNodeUserRequestLog['node_process_type']] = array(
						'bytes_received' => 0,
						'bytes_sent' => 0,
						'request_count' => 0
					);
				}

				$nodeProcessResourceUsageLogs[$nodeProcessResourceUsageLogCreatedTimestamp][$nodeProcessNodeUserRequestLog['node_node_id']][$nodeProcessNodeUserRequestLog['node_process_type']]['bytes_received'] += $nodeProcessNodeUserRequestLog['bytes_received'];
				$nodeProcessResourceUsageLogs[$nodeProcessResourceUsageLogCreatedTimestamp][$nodeProcessNodeUserRequestLog['node_node_id']][$nodeProcessNodeUserRequestLog['node_process_type']]['bytes_sent'] += $nodeProcessNodeUserRequestLog['bytes_sent'];
				$nodeProcessResourceUsageLogs[$nodeProcessResourceUsageLogCreatedTimestamp][$nodeProcessNodeUserRequestLog['node_node_id']][$nodeProcessNodeUserRequestLog['node_process_type']]['request_count']++;
			}

			_save(array(
				'data' => $nodeProcessNodeUserRequestLogs,
				'in' => $parameters['system_databases']['node_process_node_user_request_logs']
			), $response);
			$nodeProcessNodeUserRequestDestinationLogsData = array();

			foreach ($nodeProcessNodeUserRequestDestinationLogs as $nodeProcessNodeUserRequestDestinationLogsNodeId => $nodeProcessNodeUserRequestDestinationLogs) {
				foreach ($nodeProcessNodeUserRequestDestinationLogs as $nodeProcessNodeUserRequestDestinationLogsNodeProcessType => $nodeProcessNodeUserRequestDestinationLogs) {
					foreach ($nodeProcessNodeUserRequestDestinationLogs as $nodeProcessNodeUserRequestDestinationLogsNodeUserId => $nodeProcessNodeUserRequestDestinationLog) {
						$existingNodeProcessNodeUserRequestDestinationLog = _list(array(
							'data' => array(
								'id',
								'request_count'
							),
							'in' => $parameters['system_databases']['node_request_destinations'],
							'where' => array(
								'created_timestamp' => $nodeProcessNodeUserRequestDestinationLog['created_timestamp'],
								'node_id' => $nodeProcessNodeUserRequestDestinationLog['node_id'],
								'node_process_type' => $nodeProcessNodeUserRequestDestinationLog['node_process_type'],
								'node_request_destination_id' => $nodeProcessNodeUserRequestDestinationLog['node_request_destination_id'],
								'node_user_id' => $nodeProcessNodeUserRequestDestinationLog['node_user_id']
							)
						), $response);
						$existingNodeProcessNodeUserRequestDestinationLog = current($existingNodeProcessNodeUserRequestDestinationLog);

						if (empty($existingNodeProcessNodeUserRequestDestinationLog['id']) === false) {
							$nodeProcessNodeUserRequestDestinationLog['id'] = $existingNodeProcessNodeUserRequestDestinationLog['id'];
							$nodeProcessNodeUserRequestDestinationLog['request_count'] += $existingNodeProcessNodeUserRequestDestinationLog['request_count'];
						}

						$nodeProcessNodeUserRequestDestinationLogsData[] = $nodeProcessNodeUserRequestDestinationLog;
					}
				}
			}

			_save(array(
				'data' => $nodeProcessNodeUserRequestDestinationLogsData,
				'in' => $parameters['system_databases']['node_process_node_user_request_destination_logs']
			), $response);
			$nodeProcessNodeUserRequestLogPartIndex++;
		}

		foreach ($nodeProcessResourceUsageLogs as $nodeProcessResourceUsageLogCreatedTimestamp => $nodeProcessResourceUsageLogs) {
			foreach ($nodeProcessResourceUsageLogs as $nodeProcessResourceUsageLogNodeId => $nodeProcessResourceUsageLogs) {
				$nodeResourceUsageLogData = array(
					'bytes_received' => 0,
					'bytes_sent' => 0,
					'created_timestamp' => $nodeProcessResourceUsageLogCreatedTimestamp,
					'node_id' => $nodeProcessResourceUsageLogNodeId,
					'request_count' => 0
				);
				$parameters['action'] = 'add_node_process_resource_usage_logs';
				$parameters['node']['id'] = $nodeProcessResourceUsageLogNodeId;

				foreach ($nodeProcessResourceUsageLogs as $nodeProcessResourceUsageLogProcessType => $nodeProcessResourceUsageLog) {
					$nodeResourceUsageLogData['bytes_received'] += $nodeProcessResourceUsageLog['bytes_received'];
					$nodeResourceUsageLogData['bytes_sent'] += $nodeProcessResourceUsageLog['bytes_sent'];
					$nodeResourceUsageLogData['request_count'] += $nodeProcessResourceUsageLog['request_count'];
					$parameters['data'][] = array(
						'bytes_received' => $nodeProcessResourceUsageLog['bytes_received'],
						'bytes_sent' => $nodeProcessResourceUsageLog['bytes_sent'],
						'created_timestamp' => $nodeProcessResourceUsageLogCreatedTimestamp,
						'node_id' => $nodeProcessResourceUsageLogNodeId,
						'node_process_type' => $nodeProcessResourceUsageLogProcessType,
						'request_count' => $nodeProcessResourceUsageLog['request_count']
					);
				}

				$response = _addNodeProcessResourceUsageLogs($parameters, $response);
				$parameters['action'] = 'add_node_resource_usage_log';
				$parameters['data'] = $nodeResourceUsageLogData;
				$response = _addNodeResourceUsageLog($parameters, $response);
			}
		}

		_edit(array(
			'data' => array(
				'processing_process_id' => null
			),
			'in' => $parameters['system_databases']['node_process_node_user_request_logs'],
			'where' => array(
				'modified_timestamp <' => strtotime('-10 minutes'),
				'processed_status' => '0'
			)
		), $response);
		$response['message'] = 'Node process node user request logs processed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	$response = _processNodeProcessNodeUserRequestLogs($parameters, $response);
?>
