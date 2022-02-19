<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_process_node_user_authentication_sources',
		'node_process_node_users',
		'node_user_authentication_sources',
		'node_users'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_process_node_user_authentication_sources'] = $systemDatabasesConnections['node_process_node_user_authentication_sources'];
	$parameters['system_databases']['node_process_node_users'] = $systemDatabasesConnections['node_process_node_users'];
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
			$response['message'] = 'Invalid node user authentication source node user ID, please try again.';
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

		if (
			(
				(($parameters['data']['ip_address_version_number'] === '4') === true) &&
				(
					(($parameters['data']['ip_address_block_length'] > '32') === true) ||
					(($parameters['data']['ip_address_block_length'] < '8') === true)
				)
			) ||
			(
				(($parameters['data']['ip_address_version_number'] === '6') === true) &&
				(
					(($parameters['data']['ip_address_block_length'] > '128') === true) ||
					(($parameters['data']['ip_address_block_length'] < '48') === true)
				)
			)
		) {
			$response['message'] = 'Invalid node user authentication source IP address block length ' . $parameters['data']['ip_address_block_length'] . ', please try again.';
			return $response;
		}

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
		$nodeProcessNodeUsers = _list(array(
			'data' => array(
				'node_id',
				'node_node_id',
				'node_process_type',
				'node_user_id'
			),
			'in' => $parameters['system_databases']['node_process_node_users'],
			'where' => array(
				'node_user_id' => $parameters['data']['node_user_id']
			)
		), $response);

		if (empty($nodeProcessNodeUsers) === false) {
			$nodeProcessNodeUserNodeAuthenticationSources = array();

			foreach ($nodeProcessNodeUsers as $nodeProcessNodeUser) {
				$nodeProcessNodeUser['created_timestamp'] = $nodeUserAuthenticationSource['created_timestamp'];
				$nodeProcessNodeUser['modified_timestamp'] = $nodeUserAuthenticationSource['modified_timestamp'];
				$nodeProcessNodeUser['node_user_authentication_source_id'] = $nodeUserAuthenticationSource['id'];
				$nodeProcessNodeUser['node_user_authentication_source_ip_address'] = $nodeUserAuthenticationSource['ip_address'];
				$nodeProcessNodeUser['node_user_authentication_source_ip_address_block_length'] = $nodeUserAuthenticationSource['ip_address_block_length'];
				$nodeProcessNodeUser['node_user_authentication_source_ip_address_version_number'] = $nodeUserAuthenticationSource['ip_address_version_number'];
				$nodeProcessNodeUserNodeAuthenticationSources[] = $nodeProcessNodeUser;
			}

			_save(array(
				'data' => $nodeProcessNodeUserNodeAuthenticationSources,
				'in' => $parameters['system_databases']['node_process_node_user_node_authentication_sources']
			));
		}

		$response['data'] = $nodeUserAuthenticationSource;
		$response['message'] = 'Node user authentication source added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_user_authentication_source') === true) {
		$response = _addNodeUserAuthenticationSource($parameters, $response);
	}
?>
