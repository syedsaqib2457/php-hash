<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeProcessNodeUserNodeRequestLimitRules',
		'nodeRequestDestinations',
		'nodeRequestLimitRules',
		'nodeUserNodeRequestDestinations',
		'nodeUserNodeRequestLimitRules',
		'nodeUsers'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeProcessNodeUserNodeRequestLimitRules'] = $systemDatabasesConnections['nodeProcessNodeUserNodeRequestLimitRules'];
	$parameters['systemDatabases']['nodeRequestDestinations'] = $systemDatabasesConnections['nodeRequestDestinations'];
	$parameters['systemDatabases']['nodeRequestLimitRules'] = $systemDatabasesConnections['nodeRequestLimitRules'];
	$parameters['systemDatabases']['nodeUserNodeRequestDestinations'] = $systemDatabasesConnections['nodeUserNodeRequestDestinations'];
	$parameters['systemDatabases']['nodeUserNodeRequestLimitRules'] = $systemDatabasesConnections['nodeUserNodeRequestLimitRules'];
	$parameters['systemDatabases']['nodeUsers'] = $systemDatabasesConnections['nodeUsers'];

	function _addNodeUserNodeRequestLimitRule($parameters, $response) {
		if (empty($parameters['data']['nodeRequestDestinationId']) === true) {
			$response['message'] = 'Node process node user node request limit rule must have a node request destination ID, please try again.';
			return $response;
		}

		if (empty($parameters['data']['nodeRequestLimitRuleId']) === true) {
			$response['message'] = 'Node process node user node request limit rule must have a node request limit rule ID, please try again.';
			return $response;
		}

		if (empty($parameters['data']['nodeUserId']) === true) {
			$response['message'] = 'Node process node user node request limit rule must have a node user ID, please try again.';
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
			$response['message'] = 'Invalid node user node request limit rule node request destination ID, please try again.';
			return $response;
		}

		$nodeRequestLimitRuleCount = _count(array(
			'in' => $parameters['systemDatabases']['nodeRequestLimitRules'],
			'where' => array(
				'id' => $parameters['data']['nodeRequestLimitRuleId']
			)
		), $response);

		if (($nodeRequestLimitRuleCount === 0) === true) {
			$response['message'] = 'Invalid node user node request limit rule node request limit rule ID, please try again.';
			return $response;
		}

		$nodeUserCount = _count(array(
			'in' => $parameters['systemDatabases']['nodeUsers'],
			'where' => array(
				'id' => $parameters['data']['nodeUserId']
			)
		), $response);

		if (($nodeUserCount === 0) === true) {
			$response['message'] = 'Invalid node user node request limit rule node user ID, please try again.';
			return $response;
		}

		$existingNodeUserNodeRequestLimitRuleCount = _count(array(
			'in' => $parameters['systemDatabases']['nodeUserNodeRequestLimitRules'],
			'where' => array(
				'nodeRequestDestinationId' => $parameters['data']['nodeRequestDestinationId'],
				'nodeRequestLimitRuleId' => $parameters['data']['nodeRequestLimitRuleId'],
				'nodeUserId' => $parameters['data']['nodeUserId']
			)
		), $response);

		if (($existingNodeUserNodeRequestLimitRuleCount === 1) === true) {
			$response['message'] = 'Node user node request limit rule already exists, please try again.';
			return $response;
		}

		$nodeUserNodeRequestDestinationCount = _count(array(
			'in' => $parameters['systemDatabases']['nodeUserNodeRequestDestinations'],
			'where' => array(
				'nodeRequestDestinationId' => $parameters['data']['nodeRequestDestinationId'],
				'nodeUserId' => $parameters['data']['nodeUserId']
			)
		), $response);

		if (($nodeUserNodeRequestDestinationCount === 0) === true) {
			_save(array(
				'data' => array(
					'id' => _createUniqueId(),
					'nodeRequestDestinationAddress' => $nodeRequestDestination['address'],
					'nodeRequestDestinationId' => $parameters['data']['nodeRequestDestinationId'],
					'nodeUserId' => $parameters['data']['nodeUserId']
				),
				'in' => $parameters['systemDatabases']['nodeUserNodeRequestDestinations']
			));
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['nodeUserNodeRequestLimitRules']
		));
		$nodeUserNodeRequestLimitRule = _list(array(
			'in' => $parameters['systemDatabases']['nodeUserNodeRequestLimitRules'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeUserNodeRequestLimitRule = current($nodeUserNodeRequestLimitRule);
		$nodeProcessNodeUsers = _list(array(
			'data' => array(
				'nodeId',
				'nodeNodeId',
				'nodeProcessType',
				'nodeUserId'
			),
			'in' => $parameters['system_databases']['nodeProcessNodeUsers'],
			'where' => array(
				'nodeUserId' => $parameters['data']['nodeUserId']
			)
		), $response);

		if (empty($nodeProcessNodeUsers) === false) {
			$nodeProcessNodeUserNodeRequestLimitRules = array();

			foreach ($nodeProcessNodeUsers as $nodeProcessNodeUser) {
				$nodeProcessNodeUser['activatedStatus'] = $nodeUserNodeRequestLimitRule['activatedStatus'];
				$nodeProcessNodeUser['createdTimestamp'] = $nodeUserNodeRequestLimitRule['createdTimestamp'];
				$nodeProcessNodeUser['expiredTimestamp'] = $nodeUserNodeRequestLimitRule['expiredTimestamp'];
				$nodeProcessNodeUser['modifiedTimestamp'] = $nodeUserNodeRequestLimitRule['modifiedTimestamp'];
				$nodeProcessNodeUser['nodeRequestDestinationId'] = $nodeUserNodeRequestLimitRule['nodeRequestDestinationId'];
				$nodeProcessNodeUser['nodeRequestLimitRuleId'] = $nodeUserNodeRequestLimitRule['nodeRequestLimitRuleId'];
				$nodeProcessNodeUserNodeRequestLimitRules[] = $nodeProcessNodeUser;
			}

			_save(array(
				'data' => $nodeProcessNodeUserNodeRequestLimitRules,
				'in' => $parameters['systemDatabases']['nodeProcessNodeUserNodeRequestLimitRules']
			));
		}

		$response['data'] = $nodeUserNodeRequestLimitRule;
		$response['message'] = 'Node user node request limit rule added successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'addNodeUserNodeRequestLimitRule') === true) {
		$response = _addNodeUserNodeRequestLimitRule($parameters, $response);
	}
?>
