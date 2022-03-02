<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeProcesses',
		'nodeProcessForwardingDestinations',
		'nodeProcessNodeUserAuthenticationCredentials',
		'nodeProcessNodeUserAuthenticationSources',
		'nodeProcessNodeUserNodeRequestDestinations',
		'nodeProcessNodeUserNodeRequestLimitRules',
		'nodeProcessNodeUsers',
		'nodeProcessRecursiveDnsDestinations',
		'nodeReservedInternalDestinations',
		'nodes'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeProcesses'] = $systemDatabasesConnections['nodeProcesses'];
	$parameters['systemDatabases']['nodeProcessForwardingDestinations'] = $systemDatabasesConnections['nodeProcessForwardingDestinations'];
	$parameters['systemDatabases']['nodeProcessNodeUserAuthenticationCredentials'] = $systemDatabasesConnections['nodeProcessNodeUserAuthenticationCredentials'];
	$parameters['systemDatabases']['nodeProcessNodeUserAuthenticationSources'] = $systemDatabasesConnections['nodeProcessNodeUserAuthenticationSources'];
	$parameters['systemDatabases']['nodeProcessNodeUserNodeRequestDestinations'] = $systemDatabasesConnections['nodeProcessNodeUserNodeRequestDestinations'];
	$parameters['systemDatabases']['nodeProcessNodeUserNodeRequestLimitRules'] = $systemDatabasesConnections['nodeProcessNodeUserNodeRequestLimitRules'];
	$parameters['systemDatabases']['nodeProcessNodeUsers'] = $systemDatabasesConnections['nodeProcessNodeUsers'];
	$parameters['systemDatabases']['nodeProcessRecursiveDnsDestinations'] = $systemDatabasesConnections['nodeProcessRecursiveDnsDestinations'];
	$parameters['systemDatabases']['nodeReservedInternalDestinations'] = $systemDatabasesConnections['nodeReservedInternalDestinations'];
	$parameters['systemDatabases']['nodes'] = $systemDatabasesConnections['nodes'];

	function _deleteNode($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'Node must have an ID, please try again.';
			return $response;
		}

		_delete(array(
			'in' => $parameters['systemDatabases']['nodes'],
			'where' => array(
				'either' => array(
					'id' => $parameters['where']['id'],
					'nodeId' => $parameters['where']['id']
				)
			)
		), $response);
		_edit(array(
			'data' => array(
				'processedStatus' => '0'
			),
			'in' => $parameters['systemDatabases']['nodeReservedInternalDestinations'],
			'where' => array(
				'either' => array(
					'nodeId' => $parameters['where']['id'],
					'nodeNodeId' => $parameters['where']['id']
				)
			)
		), $response);
		$systemDatabaseTableKeys = array(
			'nodeProcesses',
			'nodeProcessForwardingDestinations',
			'nodeProcessNodeUserAuthenticationCredentials',
			'nodeProcessNodeUserAuthenticationSources',
			'nodeProcessNodeUserNodeRequestDestinations',
			'nodeProcessNodeUserNodeRequestLimitRules',
			'nodeProcessNodeUsers',
			'nodeProcessRecursiveDnsDestinations'
		);
		$nodeCount = _count(array(
			'in' => $parameters['systemDatabases']['nodes'],
			'where' => array(
				'either' => array(
					'id' => $parameters['where']['id'],
					'nodeId' => $parameters['where']['id']
				)
			)
		), $response);

		if (($nodeCount === 0) === true) {
			$systemDatabaseTableKeys[] = 'nodeReservedInternalDestinations';
		}

		foreach ($systemDatabaseTableKeys as $systemDatabaseTableKey) {
			_delete(array(
				'in' => $parameters['systemDatabases'][$systemDatabaseTableKey],
				'where' => array(
					'either' => array(
						'nodeId' => $parameters['where']['id'],
						'nodeNodeId' => $parameters['where']['id']
					)
				)
			), $response);
		}

		$response['message'] = 'Node deleted successfully.';
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
