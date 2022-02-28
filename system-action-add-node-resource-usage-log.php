<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeResourceUsageLogs'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeResourceUsageLogs'] = $systemDatabasesConnections['nodeResourceUsageLogs'];

	function _addNodeResourceUsageLog($parameters, $response) {
		$parameters['data']['id'] = _createUniqueId();
		$parameters['data']['nodeId'] = $parameters['node']]['id'];
		$existingNodeResourceUsageLog = _list(array(
			'data' => array(
				'bytesReceived',
				'bytesSent',
				'id',
				'requestCount'
			),
			'in' => $parameters['systemDatabases']['nodeResourceUsageLogs'],
			'where' => array(
				'createdTimestamp' => $parameters['data']['createdTimestamp'],
				'nodeId' => $parameters['data']['nodeId']
			)
		), $response);
		$existingNodeResourceUsageLog = current($existingNodeResourceUsageLog);

		if (empty($existingNodeResourceUsageLog) === false) {
			$parameters['data']['id'] = $existingNodeResourceUsageLog['id'];

			if (empty($existingNodeResourceUsageLog['requestCount']) === false) {
				$parameters['data']['bytesReceived'] += $existingNodeResourceUsageLog['bytesReceived'];
				$parameters['data']['bytesSent'] += $existingNodeResourceUsageLog['bytesSent'];
				$parameters['data']['requestCount'] += $existingNodeResourceUsageLog['requestCount'];
			}
		}

		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['nodeResourceUsageLogs']
		), $response);
		$response['message'] = 'Node resource usage log added successfully.';
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
