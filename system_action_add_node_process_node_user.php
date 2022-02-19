<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_process_node_users',
		'node_processes',
		'node_users'
		'nodes'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_process_node_users'] = $systemDatabasesConnections['node_process_node_users'];
	$parameters['system_databases']['node_processes'] = $systemDatabasesConnections['node_processes'];
	$parameters['system_databases']['node_users'] = $systemDatabasesConnections['node_users'];
	$parameters['system_databases']['nodes'] = $systemDatabasesConnections['nodes'];

	function _addNodeProcessNodeUser($parameters, $response) {
		if (empty($parameters['data']['node_id']) === true) {
			$response['message'] = 'Node process node user must have a node ID, please try again.';
			return $response;
		}

		if (empty($parameters['data']['node_process_type']) === true) {
			$response['message'] = 'Node process node user must have a node process type, please try again.';
			return $response;
		}

		if (empty($parameters['data']['node_user_id']) === true) {
			$response['message'] = 'Node process node user must have a node user ID, please try again.';
			return $response;
		}

		$node = _list(array(
			'data' => array(
				'node_id'
			),
			'in' => $parameters['system_databases']['nodes'],
			'where' => array(
				'id' => $parameters['data']['node_id']
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node process node user node ID, please try again.';
			return $response;
		}

		$parameters['data']['node_node_id'] = $parameters['data']['node_id'];

		if (empty($node['node_id']) === false) {
			$parameters['data']['node_node_id'] = $node['node_id'];
		}

		$nodeProcessesCount = _list(array(
			'in' => $parameters['system_databases']['node_processes'],
			'where' => array(
				'node_id' => $parameters['data']['node_id'],
				'type' => $parameters['data']['node_process_type']
			)
		), $response);
		$nodeProcessesCount = current($nodeProcessesCount);

		if (($nodeProcessesCount === 0) === true) {
			$response['message'] = 'Invalid node process node user node process type, please try again.';
			return $response;
		}

		$nodeUser = _list(array(
			'data' => array(
				'authentication_strict_only_allowed_status',
				'node_request_destinations_only_allowed_status',
				'node_request_logs_allowed_status'
			),
			'in' => $parameters['system_databases']['node_users'],
			'where' => array(
				'id' => $parameters['data']['node_user_id']
			)
		), $response);
		$nodeUser = current($nodeUser);

		if (empty($nodeUser) === true) {
			$response['message'] = 'Invalid node process node user node user ID, please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		$parameters['data']['node_user_authentication_strict_only_allowed_status'] = $nodeUser['authentication_strict_only_allowed_status'];
		$parameters['data']['node_user_node_request_destinations_only_allowed_status'] = $nodeUser['node_request_destinations_only_allowed_status'];
		$parameters['data']['node_user_node_request_logs_allowed_status'] = $nodeUser['node_request_logs_allowed_status'];
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_process_node_users']
		), $response);
		$nodeProcessNodeUser = _list(array(
			'in' => $parameters['system_databases']['node_process_node_users'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeProcessNodeUser = current($nodeProcessNodeUser);
		$response['data'] = $nodeProcessNodeUser;
		$response['message'] = 'Node process node user added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_process_node_user') === true) {
		$response = _addNodeProcessNodeUser($parameters, $response);
	}
?>
