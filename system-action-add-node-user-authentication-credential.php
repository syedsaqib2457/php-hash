<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeProcessNodeUserAuthenticationCredentials',
		'nodeProcessNodeUsers',
		'nodeUserAuthenticationCredentials',
		'nodeUsers'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeProcessNodeUserAuthenticationCredentials'] = $systemDatabasesConnections['nodeProcessNodeUserAuthenticationCredentials'];
	$parameters['systemDatabases']['nodeProcessNodeUsers'] = $systemDatabasesConnections['nodeProcessNodeUsers'];
	$parameters['systemDatabases']['nodeUserAuthenticationCredentials'] = $systemDatabasesConnections['nodeUserAuthenticationCredentials'];
	$parameters['systemDatabases']['nodeUsers'] = $systemDatabasesConnections['nodeUsers'];

	function _addNodeUserAuthenticationCredential($parameters, $response) {
		if (empty($parameters['data']['nodeUserId']) === true) {
			$response['message'] = 'Node user authentication credential must have a node user ID, please try again.';
			return $response;
		}

		$nodeUser = _list(array(
			'data' => array(
				'id'
			),
			'in' => $parameters['systemDatabases']['nodeUsers'],
			'where' => array(
				'id' => $parameters['data']['nodeUserId']
			)
		), $response);
		$nodeUser = current($nodeUser);

		if (empty($nodeUser) === true) {
			$response['message'] = 'Error listing node user authentication credential node user, please try again.';
			return $response;
		}

		if (empty($parameters['data']['password']) === true) {
			$response['message'] = 'Invalid node user authentication credential password, please try again.';
			return $response;
		}

		if (empty($parameters['data']['username']) === true) {
			$response['message'] = 'Invalid node user authentication credential username, please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['nodeUserAuthenticationCredentials']
		), $response);
		$nodeUserAuthenticationCredential = _list(array(
			'in' => $parameters['systemDatabases']['nodeUserAuthenticationCredentials'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeUserAuthenticationCredential = current($nodeUserAuthenticationCredential);
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
			$nodeProcessNodeUserNodeAuthenticationCredentials = array();

			foreach ($nodeProcessNodeUsers as $nodeProcessNodeUser) {
				$nodeProcessNodeUser['createdTimestamp' = $nodeUserAuthenticationCredential['createdTimestamp'];
				$nodeProcessNodeUser['modifiedTimestamp' = $nodeUserAuthenticationCredential['modifiedTimestamp'];
				$nodeProcessNodeUser['nodeUserAuthenticationCredentialId'] = $nodeUserAuthenticationCredential['id'];
				$nodeProcessNodeUser['nodeUserAuthenticationCredentialPassword'] = $nodeUserAuthenticationCredential['password'];
				$nodeProcessNodeUser['nodeUserAuthenticationCredentialUsername'] = $nodeUserAuthenticationCredential['username'];
				$nodeProcessNodeUserNodeAuthenticationCredentials[] = $nodeProcessNodeUser;
			}

			_save(array(
				'data' => $nodeProcessNodeUserNodeAuthenticationCredentials,
				'in' => $parameters['systemDatabases']['nodeProcessNodeUserAuthenticationCredentials']
			));
		}

		$response['data'] = $nodeUserAuthenticationCredential;
		$response['message'] = 'Node user authentication credential added successfully.';
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
