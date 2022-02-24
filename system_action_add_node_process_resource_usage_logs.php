<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_process_resource_usage_logs',
		'nodes'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_process_resource_usage_logs'] = $systemDatabasesConnections['node_process_resource_usage_logs'];
	$parameters['system_databases']['nodes'] = $systemDatabasesConnections['nodes'];

	function _addNodeProcessResourceUsageLogs($parameters, $response) {
		if (empty($parameters['node_authentication_token']) === true) {
			return $response;
		}

		foreach ($parameters['data'] as $nodeProcessResourceUsageLogKey => $nodeProcessResourceUsageLog) {
			$parameters['data'][$nodeProcessResourceUsageLogKey]['id'] = _createUniqueId();
			$parameters['data'][$nodeProcessResourceUsageLogKey]['node_id'] = $parameters['node']]['id'];
			$existingNodeProcessResourceUsageLog = _list(array(
				'data' => array(
					'bytes_received',
					'bytes_sent',
					'id',
					'request_count'
				),
				'in' => $parameters['system_databases']['node_process_resource_usage_logs'],
				'where' => array(
					'created_timestamp' => $parameters['data'][$nodeProcessResourceUsageLogKey]['created_timestamp'],
					'node_id' => $parameters['data'][$nodeProcessResourceUsageLogKey]['node_id']
				)
			), $response);
			$existingNodeProcessResourceUsageLog = current($existingNodeProcessResourceUsageLog);

			if (empty($existingNodeProcessResourceUsageLog) === false) {
				$parameters['data'][$nodeProcessResourceUsageLogKey]['id'] = $existingNodeProcessResourceUsageLog['id'];

				if (empty($existingNodeProcessResourceUsageLog['request_count']) === false) {
					$parameters['data'][$nodeProcessResourceUsageLogKey]['bytes_received'] += $existingNodeProcessResourceUsageLog['bytes_received'];
					$parameters['data'][$nodeProcessResourceUsageLogKey]['bytes_sent'] += $existingNodeProcessResourceUsageLog['bytes_sent'];
					$parameters['data'][$nodeProcessResourceUsageLogKey]['request_count'] += $existingNodeProcessResourceUsageLog['request_count'];
				}
			}
		}

		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_process_resource_usage_logs']
		), $response);
		$response['message'] = 'Node process resource usage logs added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_process_resource_usage_logs') === true) {
		$response = _addNodeProcessResourceUsageLogs($parameters, $response);
	}
?>
