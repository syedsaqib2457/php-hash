<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeProcesses',
		'nodeProcessForwardingDestinations',
		'nodeProcessNodeUserAuthenticationCredentials',
		'nodeProcessNodeUserAuthenticationSources',
		'nodeProcessNodeUserNodeRequestDestinationLogs',
		'nodeProcessNodeUserNodeRequestDestinations',
		'nodeProcessNodeUserNodeRequestLimitRules',
		'nodeProcessNodeUserRequestLogs',
		'nodeProcessNodeUserResourceUsageLogs',
		'nodeProcessNodeUsers',
		'nodeProcessRecursiveDnsDestinations',
		'nodeProcessResourceUsageLogs'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['nodeProcesses'] = $systemDatabasesConnections['nodeProcesses'];
	$parameters['system_databases']['nodeProcessForwardingDestinations'] = $systemDatabasesConnections['nodeProcessForwardingDestinations'];
	$parameters['system_databases']['nodeProcessNodeUserAuthenticationCredentials'] = $systemDatabasesConnections['nodeProcessNodeUserAuthenticationCredentials'];
	$parameters['system_databases']['nodeProcessNodeUserAuthenticationSources'] = $systemDatabasesConnections['nodeProcessNodeUserAuthenticationSources'];
	$parameters['system_databases']['nodeProcessNodeUserNodeRequestDestinationLogs'] = $systemDatabasesConnections['nodeProcessNodeUserNodeRequestDestinationLogs'];
	$parameters['system_databases']['nodeProcessNodeUserNodeRequestDestinations'] = $systemDatabasesConnections['nodeProcessNodeUserNodeRequestDestinations'];
	$parameters['system_databases']['nodeProcessNodeUserNodeRequestLimitRules'] = $systemDatabasesConnections['nodeProcessNodeUserNodeRequestLimitRules'];
	$parameters['system_databases']['nodeProcessNodeUserRequestLogs'] = $systemDatabasesConnections['nodeProcessNodeUserRequestLogs'];
	$parameters['system_databases']['nodeProcessNodeUserResourceUsageLogs'] = $systemDatabasesConnections['nodeProcessNodeUserResourceUsageLogs'];
	$parameters['system_databases']['nodeProcessNodeUsers'] = $systemDatabasesConnections['nodeProcessNodeUsers'];
	$parameters['system_databases']['nodeProcessRecursiveDnsDestinations'] = $systemDatabasesConnections['nodeProcessRecursiveDnsDestinations'];
	$parameters['system_databases']['nodeProcessResourceUsageLogs'] = $systemDatabasesConnections['nodeProcessResourceUsageLogs'];

	function _deleteNodeProcess($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'Node process must have an ID, please try again.';
			return $response;
		}

		$nodeProcesses = _list(array(
			'data' => array(
				'nodeId',
				'type'
			),
			'in' => $parameters['systemDatabases']['nodeProcesses'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);

		if (empty($nodeProcesses) === true) {
			$response['message'] = 'Error listing node processes, please try again.';
			return $response;
		}

		$systemDatabaseTableKeys = array(
			'nodeProcessForwardingDestinations',
			'nodeProcessNodeUserAuthenticationCredentials',
			'nodeProcessNodeUserAuthenticationSources',
			'nodeProcessNodeUserNodeRequestDestinationLogs',
			'nodeProcessNodeUserNodeRequestDestinations',
			'nodeProcessNodeUserNodeRequestLimitRules',
			'nodeProcessNodeUserRequestLogs',
			'nodeProcessNodeUserResourceUsageLogs',
			'nodeProcessNodeUsers',
			'nodeProcessRecursiveDnsDestinations',
			'nodeProcessResourceUsageLogs'
		);

		foreach ($nodeProcesses as $nodeProcess) {
			foreach ($systemDatabaseTableKeys as $systemDatabaseTableKey) {
				_delete(array(
					'in' => $parameters['systemDatabases'][$systemDatabaseTableKey],
					'where' => array(
						'nodeId' => $nodeProcess['nodeId'],
						'type' => $nodeProcess['type']
					)
				), $response);
			}
		}

		_delete(array(
			'in' => $parameters['systemDatabases']['nodeProcesses'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$response['message'] = 'Node process deleted successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'deleteNodeProcess') === true) {
		$response = _deleteNodeProcess($parameters, $response);
	}
?>
