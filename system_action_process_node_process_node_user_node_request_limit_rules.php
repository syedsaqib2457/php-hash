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
		// todo: activate new request limit rules based on node_process_node_user_request_destination_logs with activated_status = '1' and expired_timestamp = 'calculated_time_from_request_limit_rule'
		// todo: deactivate request limit rules exceeding expired_timestamp value with activated_status = '0' and expired_timestamp = ''
		// todo: modify node for override processing if request limit rules are activated or deactivated
	}

	$response = _processNodeProcessNodeUserRequestLimitRules($parameters, $response);
?>
