<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'node_process_node_user_request_logs'
	), $parameters['databases'], $response);

	function _addNodeProcessNodeUserRequestLogs($parameters, $response) {
		if (empty($_FILES['data']['tmp_name']) === true) {
			$response['message'] = 'Node process node user request logs must have a data file, please try again.';
			return $response;	
		}

		$nodeProcessNodeUserRequestLogs = explode("\n", file_get_contents($_FILES['data']['tmp_name']));

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
						'destination_hostname' => '$nodeProcessNodeUserRequestLog[3]',
						'destination_ip_address' => $nodeProcessNodeUserRequestLog[4],
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
			'data' => ,
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
