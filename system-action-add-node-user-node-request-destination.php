<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeProcessNodeUserNodeRequestDestinations',
		'nodeRequestDestinations',
		'nodeUserNodeRequestDestinations',
		'nodeUsers'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeProcessNodeUserNodeRequestDestinations'] = $systemDatabasesConnections['nodeProcessNodeUserNodeRequestDestinations'];
	$parameters['systemDatabases']['nodeRequestDestinations'] = $systemDatabasesConnections['nodeRequestDestinations'];
	$parameters['systemDatabases']['nodeUserNodeRequestDestinations'] = $systemDatabasesConnections['nodeUserNodeRequestDestinations'];
	$parameters['systemDatabases']['nodeUsers'] = $systemDatabasesConnections['nodeUsers'];

	function _addNodeUserNodeRequestDestination($parameters, $response) {
		if (empty($parameters['data']['nodeRequestDestinationId']) === true) {
			$response['message'] = 'Node process node user node request destination must have a node request destination ID, please try again.';
			return $response;
		}

		if (empty($parameters['data']['nodeUserId']) === true) {
			$response['message'] = 'Node process node user node request destination must have a node user ID, please try again.';
			return $response;
		}

		$nodeRequestDestination = _list(array(
			'data' => array(
				'address'
			),
			'in' => $parameters['systemDatabases']['nodeRequestDestinations'],
			'where' => array(
				'id' => $parameters['data']['nodeRequestDestinationId']
			)
		), $response);
		$nodeRequestDestination = current($nodeRequestDestination);

		if (empty($nodeRequestDestination) === true) {
			$response['message'] = 'Invalid node user node request destination node request destination ID, please try again.';
			return $response;
		}

		$nodeUserCount = _count(array(
			'in' => $parameters['systemDatabases']['nodeUsers'],
			'where' => array(
				'id' => $parameters['data']['nodeUserId']
			)
		), $response);

		if (($nodeUserCount === 0) === true) {
			$response['message'] = 'Invalid node user node request destination node user ID, please try again.';
			return $response;
		}

		$existingNodeUserNodeRequestDestinationCount = _count(array(
			'in' => $parameters['systemDatabases']['nodeUserNodeRequestDestinations'],
			'where' => array(
				'nodeRequestDestinationId' => $parameters['data']['nodeRequestDestinationId'],
				'nodeUserId' => $parameters['data']['nodeUserId']
			)
		), $response);

		if (($existingNodeUserNodeRequestDestinationCount === 1) === true) {
			$response['message'] = 'Node user node request destination already exists, please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['nodeUserNodeRequestDestinations']
		));
		$nodeUserNodeRequestDestination = _list(array(
			'in' => $parameters['systemDatabases']['nodeUserNodeRequestDestinations'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeUserNodeRequestDestination = current($nodeUserNodeRequestDestination);
		$nodeProcessNodeUsers = _list(array(
			'data' => array(
				'nodeId',
				'nodeNodeId',
				'nodeProcessType',
				'nodeUserId'
			),
			'in' => $parameters['systemDatabases']['nodeProcessNodeUsers'],
			'where' => array(
				'nodeUserId' => $parameters['data']['nodeUserId']
			)
		), $response);

		if (empty($nodeProcessNodeUsers) === false) {
			$nodeProcessNodeUserNodeRequestDestinations = array();

			foreach ($nodeProcessNodeUsers as $nodeProcessNodeUser) {
				$nodeProcessNodeUser['createdTimestamp' = $nodeUserNodeRequestDestination['createdTimestamp'];
				$nodeProcessNodeUser['modifiedTimestamp' = $nodeUserNodeRequestDestination['modifiedTimestamp'];
				$nodeProcessNodeUser['nodeRequestDestinationAddress'] = $nodeUserNodeRequestDestination['nodeRequestDestinationAddress'];
				$nodeProcessNodeUser['nodeRequestDestinationId'] = $nodeUserNodeRequestDestination['nodeRequestDestinationId'];
				$nodeProcessNodeUserNodeRequestDestinations[] = $nodeProcessNodeUser;
			}

			_save(array(
				'data' => $nodeProcessNodeUserNodeRequestDestinations,
				'in' => $parameters['systemDatabases']['nodeProcessNodeUserNodeRequestDestinations']
			));
		}

		$response['data'] = $nodeUserNodeRequestDestination;
		$response['message'] = 'Node user node request destination added successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add-node-user-node-request-destination') === true) {
		$response = _addNodeUserNodeRequestDestination($parameters, $response);
	}
?>
