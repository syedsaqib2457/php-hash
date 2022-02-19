<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_process_node_user_node_request_destinations',
		'node_process_node_user_node_request_limit_rules',
		'node_processes',
		'node_request_destinations',
		'node_request_limit_rules',
		'node_user_node_request_destinations',
		'node_user_node_request_limit_rules',
		'node_users',
		'nodes'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_process_node_user_node_request_destinations'] = $systemDatabasesConnections['node_process_node_user_node_request_destinations'];
	$parameters['system_databases']['node_process_node_user_node_request_limit_rules'] = $systemDatabasesConnections['node_process_node_user_node_request_limit_rules'];
	$parameters['system_databases']['node_processes'] = $systemDatabasesConnections['node_processes'];
	$parameters['system_databases']['node_request_destinations'] = $systemDatabasesConnections['node_request_destinations'];
	$parameters['system_databases']['node_request_limit_rules'] = $systemDatabasesConnections['node_request_limit_rules'];
	$parameters['system_databases']['node_user_node_request_destinations'] = $systemDatabasesConnections['node_user_node_request_destinations'];
	$parameters['system_databases']['node_user_node_request_limit_rules'] = $systemDatabasesConnections['node_user_node_request_limit_rules'];
	$parameters['system_databases']['node_users'] = $systemDatabasesConnections['node_users'];
	$parameters['system_databases']['nodes'] = $systemDatabasesConnections['nodes'];

	function _addNodeUserNodeRequestLimitRule($parameters, $response) {
		if (empty($parameters['data']['node_request_destination_id']) === true) {
			$response['message'] = 'Node process node user node request limit rule must have a node request destination ID, please try again.';
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

		// todo: validate existing nodeUserNodeRequestLimitRule
		// todo: validate node_request_destination_address with node_request_destination_id
		// todo: validate node_request_limit_rule_id
		// todo: save in node_user_node_request_limit_rules
		// todo: get node_process_node_user node_id + node_node_id + node_process_type
		// todo: save in node_process_node_user_node_request_destinations
		// todo: save in node_process_node_user_node_request_limit_rules based on node_id + node_node_id + node_process_type in node_process_node_users
			// node_process_node_user_node_request_limit_rules is only for fast querying + unsetting node_user request destination IDs in process_node_processes with node_id based on node_user_request_limit_rules 
	}

	if (($parameters['action'] === 'add_node_user_node_request_limit_rule') === true) {
		$response = _addNodeUserNodeRequestLimitRule($parameters, $response);
	}
?>
