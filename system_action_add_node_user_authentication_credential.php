<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_user_authentication_credentials',
		'node_users'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_user_authentication_credentials'] = $systemDatabasesConnections['node_user_authentication_credentials'];
	$parameters['system_databases']['node_users'] = $systemDatabasesConnections['node_users'];

	function _addNodeUserAuthenticationCredential($parameters, $response) {
		if (empty($parameters['data']['node_user_id']) === true) {
			$response['message'] = 'Node user authentication credential must have a node user ID, please try again.';
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

		if (
			(empty($parameters['data']['password']) === true) ||
			(is_string($parameters['data']['password']) === false)
		) {
			$response['message'] = 'Invalid node user authentication credential password, please try again.';
			return $response;
		}

		if (
			(empty($parameters['data']['username']) === true) ||
			(is_string($parameters['data']['username']) === false)
		) {
			$response['message'] = 'Invalid node user authentication credential username, please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_user_authentication_credentials']
		), $response);
		$nodeUserAuthenticationCredential = _list(array(
			'in' => $parameters['system_databases']['node_user_authentication_credentials'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeUserAuthenticationCredential = current($nodeUserAuthenticationCredential);
		$response['data'] = $nodeUserAuthenticationCredential;
		$response['message'] = 'Node user authentication credential added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_user_authentication_credential') === true) {
		$response = _addNodeUserAuthenticationCredential($parameters, $response);
	}
?>
