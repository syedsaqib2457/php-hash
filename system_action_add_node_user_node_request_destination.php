<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_process_node_user_node_request_destinations',
		'node_request_destinations',
		'node_user_node_request_destinations',
		'node_users'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_process_node_user_node_request_destinations'] = $systemDatabasesConnections['node_process_node_user_node_request_destinations'];
	$parameters['system_databases']['node_request_destinations'] = $systemDatabasesConnections['node_request_destinations'];
	$parameters['system_databases']['node_user_node_request_destinations'] = $systemDatabasesConnections['node_user_node_request_destinations'];
	$parameters['system_databases']['node_users'] = $systemDatabasesConnections['node_users'];

	function _addNodeUserNodeRequestDestination($parameters, $response) {
		if (empty($parameters['data']['node_request_destination_id']) === true) {
			$response['message'] = 'Node process node user node request destination must have a node request destination ID, please try again.';
			return $response;
		}

		if (empty($parameters['data']['node_user_id']) === true) {
			$response['message'] = 'Node process node user node request destination must have a node user ID, please try again.';
			return $response;
		}

		$nodeRequestDestination = _list(array(
			'data' => array(
				'address'
			),
			'in' => $parameters['system_databases']['node_request_destinations'],
			'where' => array(
				'id' => $parameters['data']['node_request_destination_id']
			)
		), $response);
		$nodeRequestDestination = current($nodeRequestDestination);

		if (empty($nodeRequestDestination) === true) {
			$response['message'] = 'Invalid node user node request destination node request destination ID, please try again.';
			return $response;
		}

		$nodeUserCount = _count(array(
			'in' => $parameters['system_databases']['node_users'],
			'where' => array(
				'id' => $parameters['data']['node_user_id']
			)
		), $response);

		if (($nodeUserCount === 0) === true) {
			$response['message'] = 'Invalid node user node request destination node user ID, please try again.';
			return $response;
		}

		$existingNodeUserNodeRequestDestinationCount = _count(array(
			'in' => $parameters['system_databases']['node_user_node_request_destinations'],
			'where' => array(
				'node_request_destination_id' => $parameters['data']['node_request_destination_id'],
				'node_user_id' => $parameters['data']['node_user_id']
			)
		), $response);

		if (($existingNodeUserNodeRequestDestinationCount === 1) === true) {
			$response['message'] = 'Node user node request destination already exists, please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_user_node_request_destinations']
		));
		$nodeUserNodeRequestDestination = _list(array(
			'in' => $parameters['system_databases']['node_user_node_request_destinations'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeUserNodeRequestDestination = current($nodeUserNodeRequestDestination);
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
			$nodeProcessNodeUserNodeRequestDestinations = array();

			foreach ($nodeProcessNodeUsers as $nodeProcessNodeUser) {
				$nodeProcessNodeUser['created_timestamp' = $nodeUserNodeRequestDestination['created_timestamp'];
				$nodeProcessNodeUser['modified_timestamp' = $nodeUserNodeRequestDestination['modified_timestamp'];
				$nodeProcessNodeUser['node_request_destination_address'] = $nodeUserNodeRequestDestination['node_request_destination_address'];
				$nodeProcessNodeUser['node_request_destination_id'] = $nodeUserNodeRequestDestination['node_request_destination_id'];
				$nodeProcessNodeUserNodeRequestDestinations[] = $nodeProcessNodeUser;
			}

			_save(array(
				'data' => $nodeProcessNodeUserNodeRequestDestinations,
				'in' => $parameters['system_databases']['node_process_node_user_node_request_destinations']
			));
		}

		$response['data'] = $nodeUserNodeRequestDestination;
		$response['message'] = 'Node user node request destination added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_user_node_request_destination') === true) {
		$response = _addNodeUserNodeRequestDestination($parameters, $response);
	}
?>
