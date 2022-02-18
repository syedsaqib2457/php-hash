<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_user_authentication_sources',
		'node_users'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_user_authentication_sources'] = $systemDatabasesConnections['node_user_authentication_sources'];
	$parameters['system_databases']['node_users'] = $systemDatabasesConnections['node_users'];

	function _addNodeUserAuthenticationSource($parameters, $response) {
		if (empty($parameters['data']['node_user_id']) === true) {
			$response['message'] = 'Node user authentication source must have a node user ID, please try again.';
			return $response;
		}

		$nodeUser = _list(array(
			'data' => array(
				'id'
			),
			'in' => $parameters['system_databases']['node_users'],
			'where' => array(
				'id' => $parameters['data']['node_user_id']
			)
		), $response);
		$nodeUser = current($nodeUser);

		if (empty($nodeUser) === true) {
			$response['message'] = 'Invalid node user ID, please try again.';
			return $response;
		}

		// todo
	}

	if (($parameters['action'] === 'add_node_user_authentication_source') === true) {
		$response = _addNodeUserAuthenticationSource($parameters, $response);
	}
?>
