<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_process_node_user_node_request_destinations',
		'node_users',
		'nodes'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_process_node_user_node_request_destinations'] = $systemDatabasesConnections['node_process_node_user_node_request_destinations'];
	$parameters['system_databases']['node_users'] = $systemDatabasesConnections['node_users'];

	function _addNodeProcessNodeUserNodeRequestDestination($parameters, $response) {
		if (empty($parameters['data']['node_id']) === true) {
			$response['message'] = 'Node process node user node request destination must have a node ID, please try again.';
			return $response;
		}

		if (empty($parameters['data']['node_process_type']) === true) {
			$response['message'] = 'Node process node user node request destination must have a node process type, please try again.';
			return $response;
		}

		if (empty($parameters['data']['node_request_destination_id']) === true) {
			$response['message'] = 'Node process node user node request destination must have a node request destination ID, please try again.';
			return $response;
		}

		if (empty($parameters['data']['node_user_id']) === true) {
			$response['message'] = 'Node process node user node request destination must have a node user ID, please try again.';
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
			$response['message'] = 'Invalid node process node user node request destination node ID, please try again.';
			return $response;
		}

		$parameters['data']['node_node_id'] = $parameters['data']['node_id'];

		if (empty($node['node_id']) === false) {
			$parameters['data']['node_node_id'] = $node['node_id'];
		}

		// todo: validate node_process_type
		// todo: validate node_request_destination_id
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
			$response['message'] = 'Invalid node process node user node request destination node user ID, please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_process_node_user_node_request_destinations']
		), $response);
		$nodeProcessNodeUserNodeRequestDestination = _list(array(
			'in' => $parameters['system_databases']['node_process_node_user_node_request_destinations'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeProcessNodeUserNodeRequestDestination = current($nodeProcessNodeUserNodeRequestDestination);
		$response['data'] = $nodeProcessNodeUserNodeRequestDestination;
		$response['message'] = 'Node process node user node request destination added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_process_node_user_node_request_destination') === true) {
		$response = _addNodeProcessNodeUserNodeRequestDestination($parameters, $response);
	}
?>
