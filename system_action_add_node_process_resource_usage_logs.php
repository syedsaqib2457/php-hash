<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeProcessResourceUsageLogs',
		'nodes'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeProcessResourceUsageLogs'] = $systemDatabasesConnections['nodeProcessResourceUsageLogs'];
	$parameters['systemDatabases']['nodes'] = $systemDatabasesConnections['nodes'];

	function _addNodeProcessResourceUsageLogs($parameters, $response) {
		if (empty($parameters['nodeAuthenticationToken']) === true) {
			return $response;
		}

		foreach ($parameters['data'] as $nodeProcessResourceUsageLogKey => $nodeProcessResourceUsageLog) {
			$parameters['data'][$nodeProcessResourceUsageLogKey]['id'] = _createUniqueId();
			$parameters['data'][$nodeProcessResourceUsageLogKey]['nodeId'] = $parameters['node']]['id'];
			$existingNodeProcessResourceUsageLog = _list(array(
				'data' => array(
					'bytesReceived',
					'bytesSent',
					'id',
					'requestCount'
				),
				'in' => $parameters['systemDatabases']['nodeProcessResourceUsageLogs'],
				'where' => array(
					'createdTimestamp' => $parameters['data'][$nodeProcessResourceUsageLogKey]['createdTimestamp'],
					'nodeId' => $parameters['data'][$nodeProcessResourceUsageLogKey]['nodeId']
				)
			), $response);
			$existingNodeProcessResourceUsageLog = current($existingNodeProcessResourceUsageLog);

			if (empty($existingNodeProcessResourceUsageLog) === false) {
				$parameters['data'][$nodeProcessResourceUsageLogKey]['id'] = $existingNodeProcessResourceUsageLog['id'];

				if (empty($existingNodeProcessResourceUsageLog['request_count']) === false) {
					$parameters['data'][$nodeProcessResourceUsageLogKey]['bytesReceived'] += $existingNodeProcessResourceUsageLog['bytesReceived'];
					$parameters['data'][$nodeProcessResourceUsageLogKey]['bytesSent'] += $existingNodeProcessResourceUsageLog['bytesSent'];
					$parameters['data'][$nodeProcessResourceUsageLogKey]['requestCount'] += $existingNodeProcessResourceUsageLog['requestCount'];
				}
			}
		}

		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['nodeProcessResourceUsageLogs']
		), $response);
		$response['message'] = 'Node process resource usage logs added successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add-node-process-resource-usage-logs') === true) {
		$response = _addNodeProcessResourceUsageLogs($parameters, $response);
	}
?>
