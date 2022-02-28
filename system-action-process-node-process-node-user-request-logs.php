<?php
	if (empty($_SERVER['argv'][1]) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeProcessNodeUserRequestDestinationLogs',
		'nodeProcessNodeUserRequestLogs',
		'nodeProcessNodeUsers'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeProcessNodeUserRequestDestinationLogs'] = $systemDatabasesConnections['nodeProcessNodeUserRequestDestinationLogs'];
	$parameters['systemDatabases']['nodeProcessNodeUserRequestLogs'] = $systemDatabasesConnections['nodeProcessNodeUserRequestLogs'];
	$parameters['systemDatabases']['nodeProcessNodeUsers'] = $systemDatabasesConnections['nodeProcessNodeUsers'];
	require_once('/var/www/firewall-security-api/system-action-add-node-resource-usage-log.php');
	require_once('/var/www/firewall-security-api/system-action-add-node-process-resource-usage-logs.php');

	function _processNodeProcessNodeUserRequestLogs($parameters, $response) {
		_edit(array(
			'data' => array(
				'processingProcessId' => $parameters['processId']
			),
			'in' => $parameters['systemDatabases']['nodeProcessNodeUserRequestLogs'],
			'limit' => 10000,
			'where' => array(
				'processedStatus' => '0',
				'processingProcessId' => null
			)
		), $response);
		$nodeProcessNodeUserRequestDestinationLogs = array();
		$nodeProcessNodeUserRequestLogPartIndex = 0;
		$nodeProcessResourceUsageLogs = array();

		while (($nodeProcessNodeUserRequestLogPartIndex === 9) === false) {
			$nodeProcessNodeUserRequestLogData = array();
			$nodeProcessNodeUserRequestLogs = _list(array(
				'data' => array(
					'bytesReceived',
					'bytesSent',
					'destinationHostnameAddress',
					'id',
					'nodeId',
					'nodeNodeId',
					'nodeProcessType',
					'nodeUserId'
				),
				'in' => $parameters['systemDatabases']['nodeProcessNodeUserRequestLogs'],
				'limit' => 1000,
				'where' => array(
					'processedStatus' => '0',
					'processingProcessId' => $parameters['processId']
				)
			), $response);
			$nodeRequestDestinations = array();

			foreach ($nodeProcessNodeUserRequestLogs as $nodeProcessNodeUserRequestLogsKey => $nodeProcessNodeUserRequestLog) {
				if (empty($nodeRequestDestinations[$nodeProcessNodeUserRequestLog['destinationHostnameAddress']]) === true) {
					$nodeRequestDestinations[$nodeProcessNodeUserRequestLog['destinationHostnameAddress']] = '';
					$nodeRequestDestination = _list(array(
						'data' => array(
							'id'
						),
						'in' => $parameters['systemDatabases']['nodeRequestDestinations'],
						'where' => array(
							'address' => $nodeProcessNodeUserRequestLog['destinationHostnameAddress']
						)
					), $response);
					$nodeRequestDestination = current($nodeRequestDestination);

					if (empty($nodeRequestDestination) === false) {
						$nodeRequestDestinations[$nodeProcessNodeUserRequestLog['destinationHostnameAddress']] = $nodeRequestDestination['id'];
					}
				}

				$nodeProcessNodeUserRequestDestinationLogCreatedTimestamp = date('Y-m-d H:i', $nodeProcessNodeUserRequestDestinationLogCreatedTimestamp['createdTimestamp']);
				$nodeProcessNodeUserRequestDestinationLogCreatedTimestamp = substr($nodeProcessNodeUserRequestDestinationLogCreatedTimestamp, 0, 11) . '00:00:00';
				$nodeProcessNodeUserRequestDestinationLogCreatedTimestamp = strtotime($nodeProcessNodeUserRequestDestinationLogCreatedTimestamp);

				if (empty($nodeProcessNodeUserRequestDestinationLogs[$nodeProcessNodeUserRequestLog['nodeId']][$nodeProcessNodeUserRequestLog['nodeProcessType']][$nodeProcessNodeUserRequestLog['nodeUserId']]) === true) {
					$nodeProcessNodeUserRequestDestinationLogs[$nodeProcessNodeUserRequestLog['nodeId']][$nodeProcessNodeUserRequestLog['nodeProcessType']][$nodeProcessNodeUserRequestLog['nodeUserId']] = array(
						'createdTimestamp' => $nodeProcessNodeUserRequestDestinationLogCreatedTimestamp,
						'nodeId' => $nodeProcessNodeUserRequestLog['nodeId'],
						'nodeNodeId' => $nodeProcessNodeUserRequestLog['nodeNodeId'],
						'nodeRequestDestinationId' => $nodeRequestDestinations[$nodeProcessNodeUserRequestLog['destinationHostnameAddress']],
						'nodeUserId' => $nodeProcessNodeUserRequestLog['nodeUserId'],
						'requestCount' => 0
					);
				}

				$nodeProcessNodeUserRequestDestinationLogs[$nodeProcessNodeUserRequestLog['nodeId']][$nodeProcessNodeUserRequestLog['nodeProcessType']][$nodeProcessNodeUserRequestLog['nodeUserId']]['requestCount']++;
				$nodeProcessNodeUserRequestLogs[$nodeProcessNodeUserRequestLogsKey] = array(
					'id' => $nodeProcessNodeUserRequestLog['id'],
					'nodeRequestDestinationId' => $nodeRequestDestinations[$nodeProcessNodeUserRequestLog['destinationHostnameAddress']],
					'processedStatus' => '1',
					'processingProcessId' => null
				);
				$nodeProcessResourceUsageLogCreatedTimestamp = date('Y-m-d H:i', $nodeProcessNodeUserRequestLog['createdTimestamp']);
				$nodeProcessResourceUsageLogCreatedTimestamp = substr($nodeProcessResourceUsageLogCreatedTimestamp, 0, 15) . '0:00';
				$nodeProcessResourceUsageLogCreatedTimestamp = strtotime($nodeProcessResourceUsageLogCreatedTimestamp);

				if (empty($nodeProcessResourceUsageLogs[$nodeProcessResourceUsageLogCreatedTimestamp][$nodeProcessNodeUserRequestLog['nodeNodeId']][$nodeProcessNodeUserRequestLog['nodeProcessType']]) === true) {
					$nodeProcessResourceUsageLogs[$nodeProcessResourceUsageLogCreatedTimestamp][$nodeProcessNodeUserRequestLog['nodeNodeId']][$nodeProcessNodeUserRequestLog['nodeProcessType']] = array(
						'bytesReceived' => 0,
						'bytesSent' => 0,
						'requestCount' => 0
					);
				}

				$nodeProcessResourceUsageLogs[$nodeProcessResourceUsageLogCreatedTimestamp][$nodeProcessNodeUserRequestLog['nodeNodeId']][$nodeProcessNodeUserRequestLog['nodeProcessType']]['bytesReceived'] += $nodeProcessNodeUserRequestLog['bytesReceived'];
				$nodeProcessResourceUsageLogs[$nodeProcessResourceUsageLogCreatedTimestamp][$nodeProcessNodeUserRequestLog['nodeNodeId']][$nodeProcessNodeUserRequestLog['nodeProcessType']]['bytesSent'] += $nodeProcessNodeUserRequestLog['bytesSent'];
				$nodeProcessResourceUsageLogs[$nodeProcessResourceUsageLogCreatedTimestamp][$nodeProcessNodeUserRequestLog['nodeNodeId']][$nodeProcessNodeUserRequestLog['nodeProcessType']]['requestCount']++;
			}

			_save(array(
				'data' => $nodeProcessNodeUserRequestLogs,
				'in' => $parameters['systemDatabases']['nodeProcessNodeUserRequestLogs']
			), $response);
			$nodeProcessNodeUserRequestDestinationLogsData = array();

			foreach ($nodeProcessNodeUserRequestDestinationLogs as $nodeProcessNodeUserRequestDestinationLogsNodeId => $nodeProcessNodeUserRequestDestinationLogs) {
				foreach ($nodeProcessNodeUserRequestDestinationLogs as $nodeProcessNodeUserRequestDestinationLogsNodeProcessType => $nodeProcessNodeUserRequestDestinationLogs) {
					foreach ($nodeProcessNodeUserRequestDestinationLogs as $nodeProcessNodeUserRequestDestinationLogsNodeUserId => $nodeProcessNodeUserRequestDestinationLog) {
						$existingNodeProcessNodeUserRequestDestinationLog = _list(array(
							'data' => array(
								'id',
								'requestCount'
							),
							'in' => $parameters['system_databases']['node_request_destinations'],
							'where' => array(
								'createdTimestamp' => $nodeProcessNodeUserRequestDestinationLog['createdTimestamp'],
								'nodeId' => $nodeProcessNodeUserRequestDestinationLog['nodeId'],
								'nodeRequestDestinationId' => $nodeProcessNodeUserRequestDestinationLog['nodeRequestDestinationId'],
								'nodeUserId' => $nodeProcessNodeUserRequestDestinationLog['nodeUserId']
							)
						), $response);
						$existingNodeProcessNodeUserRequestDestinationLog = current($existingNodeProcessNodeUserRequestDestinationLog);

						if (empty($existingNodeProcessNodeUserRequestDestinationLog['id']) === false) {
							$nodeProcessNodeUserRequestDestinationLog['id'] = $existingNodeProcessNodeUserRequestDestinationLog['id'];
							$nodeProcessNodeUserRequestDestinationLog['requestCount'] += $existingNodeProcessNodeUserRequestDestinationLog['requestCount'];
						}

						$nodeProcessNodeUserRequestDestinationLogsData[] = $nodeProcessNodeUserRequestDestinationLog;
					}
				}
			}

			_save(array(
				'data' => $nodeProcessNodeUserRequestDestinationLogsData,
				'in' => $parameters['systemDatabases']['nodeProcessNodeUserRequestDestinationLogs']
			), $response);
			$nodeProcessNodeUserRequestLogPartIndex++;
		}

		foreach ($nodeProcessResourceUsageLogs as $nodeProcessResourceUsageLogCreatedTimestamp => $nodeProcessResourceUsageLogs) {
			foreach ($nodeProcessResourceUsageLogs as $nodeProcessResourceUsageLogNodeId => $nodeProcessResourceUsageLogs) {
				$nodeResourceUsageLogData = array(
					'bytesReceived' => 0,
					'bytesSent' => 0,
					'createdTimestamp' => $nodeProcessResourceUsageLogCreatedTimestamp,
					'nodeId' => $nodeProcessResourceUsageLogNodeId,
					'requestCount' => 0
				);
				$parameters['action'] = 'addNodeProcessResourceUsageLogs';
				$parameters['node']['id'] = $nodeProcessResourceUsageLogNodeId;

				foreach ($nodeProcessResourceUsageLogs as $nodeProcessResourceUsageLogProcessType => $nodeProcessResourceUsageLog) {
					$nodeResourceUsageLogData['bytesReceived'] += $nodeProcessResourceUsageLog['bytesReceived'];
					$nodeResourceUsageLogData['bytesSent'] += $nodeProcessResourceUsageLog['bytesSent'];
					$nodeResourceUsageLogData['requestCount'] += $nodeProcessResourceUsageLog['requestCount'];
					$parameters['data'][] = array(
						'bytesReceived' => $nodeProcessResourceUsageLog['bytesReceived'],
						'bytesSent' => $nodeProcessResourceUsageLog['bytesSent'],
						'createdTimestamp' => $nodeProcessResourceUsageLogCreatedTimestamp,
						'nodeId' => $nodeProcessResourceUsageLogNodeId,
						'nodeProcessType' => $nodeProcessResourceUsageLogProcessType,
						'requestCount' => $nodeProcessResourceUsageLog['requestCount']
					);
				}

				$response = _addNodeProcessResourceUsageLogs($parameters, $response);
				$parameters['action'] = 'addNodeResourceUsageLog';
				$parameters['data'] = $nodeResourceUsageLogData;
				$response = _addNodeResourceUsageLog($parameters, $response);
			}
		}

		_edit(array(
			'data' => array(
				'processingProcessId' => null
			),
			'in' => $parameters['systemDatabases']['nodeProcessNodeUserRequestLogs'],
			'where' => array(
				'modifiedTimestamp <' => strtotime('-10 minutes'),
				'processedStatus' => '0'
			)
		), $response);
		$response['message'] = 'Node process node user request logs processed successfully.';
		$response['validatedStatus'] = '1';
		return $response;
	}

	$response = _processNodeProcessNodeUserRequestLogs($parameters, $response);
?>
