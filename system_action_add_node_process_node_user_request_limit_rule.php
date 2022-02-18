<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_process_node_user_node_request_limit_rules',
		'node_processes',
		'node_request_destinations',
		'node_request_limit_rules',
		'node_user_node_request_destinations',
		'node_user_node_request_limit_rules',
		'node_users',
		'nodes'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_process_node_user_node_request_limit_rules'] = $systemDatabasesConnections['node_process_node_user_node_request_limit_rules'];
	$parameters['system_databases']['node_processes'] = $systemDatabasesConnections['node_processes'];
	$parameters['system_databases']['node_request_destinations'] = $systemDatabasesConnections['node_request_destinations'];
	$parameters['system_databases']['node_request_limit_rules'] = $systemDatabasesConnections['node_request_limit_rules'];
	$parameters['system_databases']['node_user_node_request_destinations'] = $systemDatabasesConnections['node_user_node_request_destinations'];
	$parameters['system_databases']['node_user_node_request_limit_rules'] = $systemDatabasesConnections['node_user_node_request_limit_rules'];
	$parameters['system_databases']['node_users'] = $systemDatabasesConnections['node_users'];
	$parameters['system_databases']['nodes'] = $systemDatabasesConnections['nodes'];

	function _addNodeProcessNodeUserNodeRequestLimitRule($parameters, $response) {
		if (empty($parameters['data']['node_id']) === true) {
			$response['message'] = 'Node process node user node request limit rule must have a node ID, please try again.';
			return $response;
		}

		if (empty($parameters['data']['node_process_type']) === true) {
			$response['message'] = 'Node process node user node request limit rule must have a node process type, please try again.';
			return $response;
		}

		if (empty($parameters['data']['node_request_limit_rule_id']) === true) {
			$response['message'] = 'Node process node user node request limit rule must have a node request limit rule ID, please try again.';
			return $response;
		}

		if (empty($parameters['data']['node_user_id']) === true) {
			$response['message'] = 'Node process node user node request limit rule must have a node user ID, please try again.';
			return $response;
		}

		// todo
	}

	if (($parameters['action'] === 'add_node_process_node_user_node_request_limit_rule') === true) {
		$response = _addNodeProcessNodeUserNodeRequestLimitRule($parameters, $response);
	}
?>
