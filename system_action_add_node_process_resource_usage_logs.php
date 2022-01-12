<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_resource_usage_logs',
		'nodes'
	), $parameters['system_databases'], $response);

	function _addNodeProcessResourceUsageLogs($parameters, $response) {
		$nodeProcessResourceUsageLogData = array();
		// todo
		_save(array(
			'data' => $nodeProcessResourceUsageLogData,
			'in' => $parameters['system_databases']['node_process_resource_usage_logs']
		), $response);
		$response['message'] = 'Node process resource usage logs added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_process_resource_usage_logs') === true) {
		$response = _addNodeProcessResourceUsageLogs($parameters, $response);
		_output($response);
	}
?>
