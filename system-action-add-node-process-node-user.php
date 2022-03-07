<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeProcesses',
		'nodeProcessNodeUsers',
		'nodeUsers',
		'nodes'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeProcessNodeUsers'] = $systemDatabasesConnections['nodeProcessNodeUsers'];
	$parameters['systemDatabases']['nodeProcesses'] = $systemDatabasesConnections['nodeProcesses'];
	$parameters['systemDatabases']['nodeUsers'] = $systemDatabasesConnections['nodeUsers'];
	$parameters['systemDatabases']['nodes'] = $systemDatabasesConnections['nodes'];

	function _addNodeProcessNodeUser($parameters, $response) {
		if (empty($parameters['data']['nodeId']) === true) {
			$response['message'] = 'Node process node user must have a node ID, please try again.';
			return $response;
		}

		if (empty($parameters['data']['nodeProcessType']) === true) {
			$response['message'] = 'Node process node user must have a node process type, please try again.';
			return $response;
		}

		if (empty($parameters['data']['nodeUserId']) === true) {
			$response['message'] = 'Node process node user must have a node user ID, please try again.';
			return $response;
		}

		$node = _list(array(
			'data' => array(
				'nodeId'
			),
			'in' => $parameters['systemDatabases']['nodes'],
			'where' => array(
				'id' => $parameters['data']['nodeId']
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Error listing node process node user node, please try again.';
			return $response;
		}

		$parameters['data']['nodeNodeId'] = $parameters['data']['nodeId'];

		if (empty($node['nodeId']) === false) {
			$parameters['data']['nodeNodeId'] = $node['nodeId'];
		}

		$nodeProcessesCount = _list(array(
			'in' => $parameters['systemDatabases']['nodeProcesses'],
			'where' => array(
				'nodeId' => $parameters['data']['nodeId'],
				'type' => $parameters['data']['nodeProcessType']
			)
		), $response);
		$nodeProcessesCount = current($nodeProcessesCount);

		if (($nodeProcessesCount === 0) === true) {
			$response['message'] = 'Error counting node process node user node processes, please try again.';
			return $response;
		}

		$nodeUser = _list(array(
			'data' => array(
				'authenticationStrictOnlyAllowedStatus',
				'nodeRequestDestinationsOnlyAllowedStatus',
				'nodeRequestLogsAllowedStatus'
			),
			'in' => $parameters['systemDatabases']['nodeUsers'],
			'where' => array(
				'id' => $parameters['data']['nodeUserId']
			)
		), $response);
		$nodeUser = current($nodeUser);

		if (empty($nodeUser) === true) {
			$response['message'] = 'Error listing node process node user node user, please try again.';
			return $response;
		}

		$parameters['data']['id'] = _generateUniqueId();
		$parameters['data']['nodeUserauthenticationStrictOnlyAllowedStatus'] = $nodeUser['authenticationStrictOnlyAllowedStatus'];
		$parameters['data']['nodeUsernodeRequestDestinationsOnlyAllowedStatus'] = $nodeUser['nodeRequestDestinationsOnlyAllowedStatus'];
		$parameters['data']['nodeUsernodeRequestLogsAllowedStatus'] = $nodeUser['nodeRequestLogsAllowedStatus'];
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['nodeProcessNodeUsers']
		), $response);
		$nodeProcessNodeUser = _list(array(
			'in' => $parameters['systemDatabases']['nodeProcessNodeUsers'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeProcessNodeUser = current($nodeProcessNodeUser);
		$response['data'] = $nodeProcessNodeUser;
		$response['message'] = 'Node process node user added successfully.';
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
