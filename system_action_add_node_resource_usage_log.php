<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_resource_usage_logs'
	), $parameters['system_databases'], $response);

	function _addNodeResourceUsageLog($parameters, $response) {
		$parameters['data']['node_id'] = $parameters['node']]['id'];
		$nodeResourceUsageLogData = array();
		// todo: validate required usage log parameters with either request_count logs or cpu/memory/storage logs
		// todo: update request_count parameters when processing request logs using this method
		// todo: validate 10 minute timestamp doesn't exist for resource usage log when updating cpu/memory/storage
		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'cpu_capacity_cores' => true,
				'cpu_capacity_megahertz' => true,
				'cpu_percentage' => true,
				'created_timestamp' => true,
				'id' => true,
				'memory_capacity_megabytes' => true,
				'memory_percentage' => true,
				'node_id' => true,
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
