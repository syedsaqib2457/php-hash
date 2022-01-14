<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_resource_usage_logs'
	), $parameters['system_databases'], $response);

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
			)
		), $response);
		$existingNodeResourceUsageLog = current($existingNodeResourceUsageLog);

		if (empty($existingNodeResourceUsageLog) === false) {
			$parameters['data']['id'] = $existingNodeResourceUsageLog['id'];

			if (empty($existingNodeResourceUsageLog['request_count']) === false) {
				// todo: increment existing values
			}
		}

		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'bytes_received' => true,
				'bytes_sent' => true,
				'cpu_capacity_cores' => true,
				'cpu_capacity_megahertz' => true,
				'cpu_percentage' => true,
				'created_timestamp' => true,
				'id' => true,
				'memory_capacity_megabytes' => true,
				'memory_percentage' => true,
				'node_id' => true,
				'request_count' => true,
				'storage_capacity_megabytes' => true,
				'storage_percentage' => true
			)),
			'in' => $parameters['system_databases']['node_resource_usage_logs']
		), $response);
		$response['message'] = 'Node resource usage log added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_resource_usage_log') === true) {
		$response = _addNodeResourceUsageLog($parameters, $response);
		_output($response);
	}
?>
