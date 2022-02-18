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

		$parameters['data']['ip_address_block_length'] = '32';
		$parameters['data']['ip_address_version_number'] = '4';

		if ((strpos($parameters['data']['ip_address'], ':') === false) === false) {
			$parameters['data']['ip_address_block_length'] = '128';
			$parameters['data']['ip_address_version_number'] = '6';
		}

		if ((strpos($parameters['data']['ip_address'], '/') === false) === false) {
			$parameters['data']['ip_address'] = explode('/', $parameters['data']['ip_address']);
			$parameters['data']['ip_address_block_length'] = next($parameters['data']['ip_address']);
			$parameters['data']['ip_address'] = prev($parameters['data']['ip_address']);
		}

		$parameters['data']['ip_address'] = _validateIpAddressVersionNumber($parameters['data']['ip_address'], $parameters['data']['ip_address_version_number']);

		if ($parameters['data']['ip_address'] === false) {
			$response['message'] = 'Invalid node user authentication source IP address, please try again.';
			return $response;
		}

		// todo

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_user_authentication_sources']
		), $response);
		$nodeUserAuthenticationSource = _list(array(
			'in' => $parameters['system_databases']['node_user_authentication_sources'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeUserAuthenticationSource = current($nodeUserAuthenticationSource);
		$response['data'] = $nodeUserAuthenticationSource;
		$response['message'] = 'Node user authentication source added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_user_authentication_source') === true) {
		$response = _addNodeUserAuthenticationSource($parameters, $response);
	}
?>
