<?php
	if (empty($_SERVER['argv'][1]) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_process_node_user_node_request_limit_rules',
		'node_process_node_user_request_destination_logs',
		'node_request_limit_rules',
		'node_user_node_request_limit_rules'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_process_node_user_node_request_limit_rules'] = $systemDatabasesConnections['node_process_node_user_node_request_limit_rules'];
	$parameters['system_databases']['node_process_node_user_request_destination_logs'] = $systemDatabasesConnections['node_process_node_user_request_destination_logs'];
	$parameters['system_databases']['node_request_limit_rules'] = $systemDatabasesConnections['node_request_limit_rules'];
	$parameters['system_databases']['node_user_node_request_limit_rules'] = $systemDatabasesConnections['node_user_node_request_limit_rules'];

	function _processNodeProcessNodeUserRequestLimitRules($parameters, $response) {
		// todo: add new request limit rules based on node_process_node_user_request_destination_logs
		// todo: delete request limit rules exceeding expired_timestamp value
	}

	$response = _processNodeProcessNodeUserRequestLimitRules($parameters, $response);
?>
