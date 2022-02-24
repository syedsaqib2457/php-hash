<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_resource_usage_logs'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_resource_usage_logs'] = $systemDatabasesConnections['node_resource_usage_logs'];

	function _addNodeResourceUsageLog($parameters, $response) {
		$parameters['data']['id'] = _createUniqueId();
		$parameters['data']['node_id'] = $parameters['node']]['id'];
		$existingNodeResourceUsageLog = _list(array(
			'data' => array(
				'bytes_received',
				'bytes_sent',
				'id',
				'request_count'
			),
			'in' => $parameters['system_databases']['node_resource_usage_logs'],
			'where' => array(
				'created_timestamp' => $parameters['data']['created_timestamp'],
				'node_id' => $parameters['data']['node_id']
			)
		), $response);
		$existingNodeResourceUsageLog = current($existingNodeResourceUsageLog);

		if (empty($existingNodeResourceUsageLog) === false) {
			$parameters['data']['id'] = $existingNodeResourceUsageLog['id'];

			if (empty($existingNodeResourceUsageLog['request_count']) === false) {
				$parameters['data']['bytes_received'] += $existingNodeResourceUsageLog['bytes_received'];
				$parameters['data']['bytes_sent'] += $existingNodeResourceUsageLog['bytes_sent'];
				$parameters['data']['request_count'] += $existingNodeResourceUsageLog['request_count'];
			}
		}

		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_resource_usage_logs']
		), $response);
		$response['message'] = 'Node resource usage log added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_resource_usage_log') === true) {
		$response = _addNodeResourceUsageLog($parameters, $response);
	}
?>
