<?php
	if (empty($_SERVER['argv'][1]) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeProcessNodeUserNodeRequestLimitRules',
		'nodeProcessNodeUserRequestDestinationLogs',
		'nodeRequestLimitRules',
		'nodeUserNodeRequestLimitRules'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeProcessNodeUserNodeRequestLimitRules'] = $systemDatabasesConnections['nodeProcessNodeUserNodeRequestLimitRules'];
	$parameters['systemDatabases']['nodeProcessNodeUserRequestDestinationLogs'] = $systemDatabasesConnections['nodeProcessNodeUserRequestDestinationLogs'];
	$parameters['systemDatabases']['nodeRequestLimitRules'] = $systemDatabasesConnections['nodeRequestLimitRules'];
	$parameters['systemDatabases']['nodeUserNodeRequestLimitRules'] = $systemDatabasesConnections['nodeUserNodeRequestLimitRules'];

	function _processNodeProcessNodeUserRequestLimitRules($parameters, $response) {
		// todo: activate new request limit rules based on node_process_node_user_request_destination_logs with activated_status = '1' and expired_timestamp = 'calculated_time_from_request_limit_rule'
		// todo: deactivate request limit rules exceeding expired_timestamp value with activated_status = '0' and expired_timestamp = ''
		// todo: modify node for override processing if request limit rules are activated or deactivated
	}

	$response = _processNodeProcessNodeUserRequestLimitRules($parameters, $response);
?>
