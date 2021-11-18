<?php
	if (empty($_SERVER['argv'][1]) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'node_process_node_user_request_logs',
		'node_process_node_users'
	), $parameters['databases'], $response);

	function _processNodeProcessNodeUserRequestLogs($parameters, $response) {
		$nodeProcessNodeUserRequestLogData = array();
		// todo
		_save(array(
			'data' => $nodeProcessNodeUserRequestLogData,
			'in' => $parameters['databases']['node_process_node_user_request_logs']
		), $response);
		$response['message'] = 'Node process node user request logs added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	_processNodeProcessNodeUserRequestLogs($parameters, $response);
?>
