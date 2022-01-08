<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_node_user_request_logs',
		'node_process_node_users'
	), $parameters['system_databases'], $response);

	function _addNodeProcessNodeUserRequestLogs($parameters, $response) {
		if (empty($_FILES['data']['tmp_name']) === true) {
			$response['message'] = 'Node process node user request logs must have a data file, please try again.';
			return $response;	
		}

		if (
			(empty($parameters['data']['node_process_type']) === true) ||
			(is_string($parameters['data']['node_process_type']) === false)
		) {
			$response['message'] = 'Node process node user request logs must have a node process type, please try again.';
			return $response;
		}

		if (in_array($parameters['data']['node_process_type'], array(
			'http_proxy',
			'load_balancer',
			'recursive_dns',
			'socks_proxy'
		)) === false) {
			$response['message'] = 'Invalid node process node user request log node process type, please try again.';
			return $response;
		}

		if (empty($parameters['data']['node_user_id']) === true) {
			$response['message'] = 'Node process node user request logs must have a node user ID, please try again.';
			return $response;
		}

		$nodeNodeId = $parameters['node']['id'];

		if (empty($parameters['node']['node_id']) === false) {
			$nodeNodeId = $parameters['node']['node_id'];
		}

		$nodeProcessNodeUserCount = _count(array(
			'in' => $parameters['databases']['node_process_node_users'],
			'where' => array(
				'either' => array(
					'node_id' => $nodeNodeId,
					'node_node_id' => $nodeNodeId
				),
				'node_process_type' => $parameters['data']['node_process_type']
			)
		), $response);

		if (($nodeProcessNodeUserCount < 1) === true) {
			$response['message'] = 'Invalid node process node user request log node process node user, please try again.';
			return $response;
		}

		$nodeProcessNodeUserRequestLogs = file_get_contents($_FILES['data']['tmp_name']);
		$nodeProcessNodeUserRequestLogs = explode("\n", $nodeProcessNodeUserRequestLogs);

		if (empty($nodeProcessNodeUserRequestLogs) === true) {
			$response['message'] = 'Invalid node process node user request log data, please try again.';
			return $response;	
		}

		$nodeProcessNodeUserRequestLogData = array();

		switch ($parameters['data']['node_process_type']) {
			case 'http_proxy':
			case 'socks_proxy':
				array_pop($nodeProcessNodeUserRequestLogs);

				foreach ($nodeProcessNodeUserRequestLogs as $nodeProcessNodeUserRequestLog) {
					$nodeProcessNodeUserRequestLog = explode(' _ ', $nodeProcessNodeUserRequestLog);
					$nodeProcessNodeUserRequestLogData[] = array(
						'bytes_received' => $nodeProcessNodeUserRequestLog[0],
						'bytes_sent' => $nodeProcessNodeUserRequestLog[1],
						'created' => $nodeProcessNodeUserRequestLog[2],
						'destination_hostname' => $nodeProcessNodeUserRequestLog[3],
						'destination_ip_address' => $nodeProcessNodeUserRequestLog[4],
						'id' => _createUniqueId(),
						'node_id' => $parameters['data']['node_id'],
						'node_process_type' => $parameters['data']['node_process_type'],
						'node_user_id' => $parameters['data']['node_user_id'],
						'response_code' => $nodeProcessNodeUserRequestLog[5],
						'source_ip_address' => $nodeProcessNodeUserRequestLog[6]
					);
				}

				break;
			case 'load_balancer':
				// todo: format load_balancer request logs for node_process_node_user_request_logs
				break;
			case 'recursive_dns':
				// todo: format recursive_dns request logs for node_process_node_user_request_logs
				break;
		}

		_save(array(
			'data' => $nodeProcessNodeUserRequestLogData,
			'in' => $parameters['databases']['node_process_node_user_request_logs']
		), $response);
		$response['message'] = 'Node process node user request logs added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_process_node_user_request_logs') === true) {
		$response = _addNodeProcessNodeUserRequestLogs($parameters, $response);
		_output($response);
	}
?>
