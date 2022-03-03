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

		$node = _list(array(
			'data' => array(
				'id',
				'nodeId'
			),
			'in' => $parameters['systemDatabases']['nodes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Error listing node, please try again.';
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
		$systemDatabaseTablesKeys = array(
			'nodeProcesses',
			'nodeProcessForwardingDestinations',
			'nodeProcessNodeUserAuthenticationCredentials',
			'nodeProcessNodeUserAuthenticationSources',
			'nodeProcessNodeUserNodeRequestDestinations',
			'nodeProcessNodeUserNodeRequestLimitRules',
			'nodeProcessNodeUsers',
			'nodeProcessRecursiveDnsDestinations'
		);

		if (empty($node['nodeId']) === true) {
			$systemDatabaseTablesKeys[] = 'nodeReservedInternalDestinations';
		} else {
			_edit(array(
				'data' => array(
					'nodeId' => '',
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
		}

		foreach ($systemDatabaseTablesKeys as $systemDatabaseTablesKey) {
			_delete(array(
				'in' => $parameters['systemDatabases'][$systemDatabaseTablesKey],
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
