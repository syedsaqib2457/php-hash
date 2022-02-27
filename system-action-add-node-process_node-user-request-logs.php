<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeProcessNodeUserRequestLogs',
		'nodes'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeProcessNodeUserRequestLogs'] = $systemDatabasesConnections['nodeProcessNodeUserRequestLogs'];
	$parameters['systemDatabases']['nodes'] = $systemDatabasesConnections['nodes'];

	function _addNodeProcessNodeUserRequestLogs($parameters, $response) {
		if (empty($_FILES['data']['tmp_name']) === true) {
			$response['message'] = 'Node process node user request logs must have a data file, please try again.';
			return $response;
		}

		if (empty($parameters['data']['nodeId']) === true) {
			$response['message'] = 'Node process node user request logs must have a node ID, please try again.';
			return $response;
		}

		if (empty($parameters['data']['nodeProcessType']) === true) {
			$response['message'] = 'Node process node user request logs must have a node process type, please try again.';
			return $response;
		}

		if (
			(($parameters['data']['nodeProcessType'] === 'httpProxy') === false) &&
			(($parameters['data']['nodeProcessType'] === 'loadBalancer') === false) &&
			(($parameters['data']['nodeProcessType'] === 'recursiveDns') === false) &&
			(($parameters['data']['nodeProcessType'] === 'socksProxy') === false)
		) {
			$response['message'] = 'Invalid node process node user request log node process type, please try again.';
			return $response;
		}

		if (empty($parameters['data']['nodeUserId']) === true) {
			$response['message'] = 'Node process node user request logs must have a node user ID, please try again.';
			return $response;
		}

		$nodeCount = _count(array(
			'in' => $parameters['systemDatabases']['nodes'],
			'where' => array(
				'authenticationToken' => $parameters['nodeAuthenticationToken'],
				'id' => $parameters['data']['node_id']
			)
		), $response);

		if (($nodeCount === 1) === false) {
			$response['message'] = 'Error counting node process node user request log node, please try again.';
			return $response;
		}

		$nodeProcessNodeUserRequestLogs = file_get_contents($_FILES['data']['tmp_name']);
		$nodeProcessNodeUserRequestLogs = explode("\n", $nodeProcessNodeUserRequestLogs);

		if (empty($nodeProcessNodeUserRequestLogs) === true) {
			$response['message'] = 'Invalid node process node user request log data, please try again.';
			return $response;
		}

		$nodeProcessNodeUserRequestLogsData = array();

		switch ($parameters['data']['nodeProcessType']) {
			case 'httpProxy':
			case 'socksProxy':
				array_pop($nodeProcessNodeUserRequestLogs);

				foreach ($nodeProcessNodeUserRequestLogs as $nodeProcessNodeUserRequestLog) {
					$nodeProcessNodeUserRequestLog = explode(' _ ', $nodeProcessNodeUserRequestLog);
					$nodeProcessNodeUserRequestLogsData[] = array(
						'bytesReceived' => $nodeProcessNodeUserRequestLog[0],
						'bytesSent' => $nodeProcessNodeUserRequestLog[1],
						'created' => $nodeProcessNodeUserRequestLog[2],
						'destinationHostnameAddress' => $nodeProcessNodeUserRequestLog[3],
						'destinationIpAddress' => $nodeProcessNodeUserRequestLog[4],
						'id' => _createUniqueId(),
						'nodeId' => $parameters['data']['nodeId'],
						'nodeNodeId' => $parameters['node']['id'],
						'nodeProcessType' => $parameters['data']['nodeProcessType'],
						'nodeUserId' => $parameters['data']['nodeUserId'],
						'processedStatus' => '0',
						'processingProcessId' => null,
						'responseCode' => $nodeProcessNodeUserRequestLog[5],
						'sourceIpAddress' => $nodeProcessNodeUserRequestLog[6]
					);
				}

				break;
			case 'loadBalancer':
				// todo: format load_balancer request logs for node_process_node_user_request_logs
				break;
			case 'recursiveDns':
				// todo: format recursive_dns request logs for node_process_node_user_request_logs
				break;
		}

		_save(array(
			'data' => $nodeProcessNodeUserRequestLogsData,
			'in' => $parameters['systemDatabases']['nodeProcessNodeUserRequestLogs']
		), $response);
		$response['message'] = 'Node process node user request logs added successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add-node-process-node-user-request-logs') === true) {
		$response = _addNodeProcessNodeUserRequestLogs($parameters, $response);
	}
?>
