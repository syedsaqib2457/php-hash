<?php
	if (empty($_SERVER['argv'][0]) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'node_process_node_user_request_logs',
		'node_process_node_users'
	), $parameters['databases'], $response);

	function _processNodeProcessNodeUserRequestLogs($parameters, $response) {
		// todo
	}

	_processNodeProcessNodeUserRequestLogs($parameters, $response);
?>
