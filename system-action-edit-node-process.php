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
	$parameters['systemDatabases']['nodes'] = $systemDatabasesConnections['nodes'];
	require_once('/var/www/firewall-security-api/system-action-validate-port-number.php');

	function _editNodeProcess($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'Node process must have an ID, please try again.';
			return $response;
		}

		if (empty($parameters['where']['type']) === true) {
			$response['message'] = 'Node process must have a type, please try again.';
			return $response;
		}

		if (
			(empty($parameters['data']['portNumber']) === false) &&
			(_validatePortNumber($parameters['data']['portNumber']) === false)
		) {
			$response['message'] = 'Invalid node process port number, please try again.';
			return $response;
		}

		if (
			(($parameters['data']['type'] === 'httpProxy') === false) &&
			(($parameters['data']['type'] === 'loadBalancer') === false) &&
			(($parameters['data']['type'] === 'recursiveDns') === false) &&
			(($parameters['data']['type'] === 'socksProxy') === false)
		) {
			$response['message'] = 'Invalid node process type, please try again.';
			return $response;
		}

		$node = _list(array(
			'data' => array(
				'externalIpAddressVersion4',
				'externalIpAddressVersion4',
				'id',
				'internalIpAddressVersion6',
				'internalIpAddressVersion6',
				'nodeId',
				'processedStatus'
			),
			'in' => $parameters['systemDatabases']['nodes'],
			'where' => array(
				'id' => $parameters['data']['nodeId']
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Error listing node process node, please try again.';
			return $response;
		}

		if (($node['processedStatus'] === '1') === false) {
			$response['message'] = 'Node process node must be processed, please try again.';
			return $response;
		}

		$parameters['data']['nodeNodeId'] = $node['id'];

		if (empty($node['nodeId']) === false) {
			$parameters['data']['nodeNodeId'] = $node['nodeId'];
		}

		$nodeProcess = _list(array(
			'data' => array(
				'nodeId',
				'nodeNodeId',
				'portNumber',
				'type'
			),
			'in' => $parameters['systemDatabases']['nodeProcesses'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$nodeProcess = current($nodeProcess);

		if (empty($nodeProcess) === true) {
			$response['message'] = 'Error listing node process, please try again.';
			return $response;
		}

		$existingNodeProcessCountParameters = array(
			'in' => $parameters['systemDatabases']['nodeProcesses'],
			'where' => array(
				'id !=' => $parameters['where']['id'],
				'nodeId' => $nodeProcess['nodeId']
			)
		);

		if (empty($parameters['data']['portNumber']) === false) {
			$existingNodeProcessCountParameters['where']['portNumber'] = $parameters['data']['portNumber'];
		}

		$existingNodeProcessCount = _count($existingNodeProcessCountParameters, $response);

		if (($existingNodeProcessCount === 0) === false) {
			$response['message'] = 'Node process already exists on the same node, please try again.';
			return $response;
		}

		if (empty($parameters['data']['nodeId']) === true) {
			$parameters['data']['nodeId'] = $nodeProcess['nodeId'];
		}

		if (empty($parameters['data']['portNumber']) === true) {
			$parameters['data']['portNumber'] = $nodeProcess['portNumber'];
		}

		if (empty($parameters['data']['type']) === true) {
			$parameters['data']['type'] = $nodeProcess['type'];
		}

		$systemDatabaseNames = array(
			'nodeProcessNodeUserAuthenticationCredentials',
			'nodeProcessNodeUserAuthenticationSources',
			'nodeProcessNodeUserNodeRequestDestinations',
			'nodeProcessNodeUserNodeRequestLimitRules',
			'nodeProcessNodeUsers'
		);

		foreach ($systemDatabaseNames as $systemDatabaseName) {
			_edit(array(
				'data' => array(
					'nodeId' => $parameters['data']['nodeId'],
					'nodeNodeId' => $parameters['data']['nodeNodeId'],
					'nodeProcessType' => $parameters['data']['type']
				),
				'in' => $parameters['systemDatabases'][$systemDatabaseName],
				'where' => array(
					'nodeId' => $nodeProcess['nodeId'],
					'nodeProcessType' => $nodeProcess['type']
				)
			), $response);
		}

		$nodeProcessForwardingDestinations = _list(array(
			'data' => array(
				'addressVersion4',
				'addressVersion4NodeId',
				'addressVersion6',
				'addressVersion6NodeId',
				'id',
				'nodeId',
				'nodeNodeId',
				'portNumberVersion4',
				'portNumberVersion6'
			),
			'in' => $parameters['systemDatabases']['nodeProcessForwardingDestinations'],
			'where' => array(
				'either' => array(
					array(
						'addressVersion4NodeId' => $nodeProcess['nodeId'],
						'portNumberVersion4' => $nodeProcess['portNumber']
					),
					array(
						'addressVersion6NodeId' => $nodeProcess['nodeId'],
						'portNumberVersion6' => $nodeProcess['portNumber']
					),
					array(
						'nodeId' => $nodeProcess['nodeId'],
						'nodeProcessType' => $nodeProcess['nodeProcessType']
					)
				)
			)
		), $response);

		if (empty($nodeProcessForwardingDestinations) === false) {
			$ipAddressVersionNumbers = array(
				4,
				6
			);

			foreach ($nodeProcessForwardingDestinations as $nodeProcessForwardingDestinationsKey => $nodeProcessForwardingDestination) {
				foreach ($ipAddressVersionNumbers as $ipAddressVersionNumber) {
					if (($nodeProcess['nodeId'] === $nodeProcessForwardingDestination['addressVersion' . $ipAddressVersionNumber . 'NodeId']) === true) {
						$nodeProcessForwardingDestinations[$nodeProcessForwardingDestinationsKey]['addressVersion' . $ipAddressVersionNumber] = $node['externalIpAddressVersion' . $ipAddressVersionNumber];
						$nodeProcessForwardingDestinations[$nodeProcessForwardingDestinationsKey]['addressVersion' . $ipAddressVersionNumber . 'NodeId'] = $parameters['data']['nodeId'];

						if (
							(empty($node['internalIpAddressVersion' . $ipAddressVersionNumber]) === false) &&
							(($nodeProcess['nodeNodeId'] === $nodeProcessForwardingDestination['nodeNodeId']) === true)
						) {
							$nodeProcessForwardingDestinations[$nodeProcessForwardingDestinationsKey]['addressVersion' . $ipAddressVersionNumber] = $node['internalIpAddressVersion' . $ipAddressVersionNumber];
						}

						if (empty($nodeProcessForwardingDestinations[$nodeProcessForwardingDestinationsKey]['addressVersion' . $ipAddressVersionNumber]) === true) {
							$nodeProcessForwardingDestinations[$nodeProcessForwardingDestinationsKey]['addressVersion' . $ipAddressVersionNumber] = '';
							$nodeProcessForwardingDestinations[$nodeProcessForwardingDestinationsKey]['addressVersion' . $ipAddressVersionNumber . 'NodeId'] = '';
							$nodeProcessForwardingDestinations[$nodeProcessForwardingDestinationsKey]['portNumberVersion' . $ipAddressVersionNumber] = '';
						}

						if (($nodeProcess['portNumber'] === $nodeProcessForwardingDestination['portNumberVersion' . $ipAddressVersionNumber]) === true) {
							$nodeProcessForwardingDestinations[$nodeProcessForwardingDestinationsKey]['portNumberVersion' . $ipAddressVersionNumber] = $parameters['data']['portNumberVersion' . $ipAddressVersionNumber];
						}
					}
				}

				if (($nodeProcess['nodeId'] === $nodeProcessForwardingDestination['nodeId']) === true) {
					$nodeProcessForwardingDestinations[$nodeProcessForwardingDestinationsKey]['nodeId'] = $parameters['data']['nodeId'];
					$nodeProcessForwardingDestinations[$nodeProcessForwardingDestinationsKey]['nodeNodeId'] = $parameters['data']['nodeNodeId'];
					$nodeProcessForwardingDestinations[$nodeProcessForwardingDestinationsKey]['nodeProcessType'] = $parameters['data']['nodeProcessType'];
				}

				if (
					(empty($nodeProcessForwardingDestinations[$nodeProcessForwardingDestinationsKey]['addressVersion4']) === true) &&
					(empty($nodeProcessForwardingDestinations[$nodeProcessForwardingDestinationsKey]['addressVersion6']) === true)
				) {
					$nodeProcessForwardingDestinations[$nodeProcessForwardingDestinationsKey]['nodeNodeId'] = '';
				}
			}
		}

		_save(array(
			'data' => $nodeProcessForwardingDestinations,
			'in' => $parameters['systemDatabases']['nodeProcessForwardingDestinations']
		), $response);
		_delete(array(
			'in' => $parameters['systemDatabases']['nodeProcessForwardingDestinations'],
			'where' => array(
				'nodeNodeId' => ''
			)
		), $response);
		$nodeProcessRecursiveDnsDestinations = _list(array(
			'data' => array(
				'destinationIpAddressVersion4',
				'destinationIpAddressVersion4NodeId',
				'destinationIpAddressVersion6',
				'destinationIpAddressVersion6NodeId',
				'id',
				'nodeId',
				'nodeNodeId',
				'portNumberVersion4',
				'portNumberVersion6'
			),
			'in' => $parameters['systemDatabases']['nodeProcessRecursiveDnsDestinations'],
			'where' => array(
				'either' => array(
					array(
						'destinationIpAddressVersion4NodeId' => $nodeProcess['nodeId'],
						'portNumberVersion4' => $nodeProcess['portNumber']
					),
					array(
						'destinationIpAddressVersion6NodeId' => $nodeProcess['nodeId'],
						'portNumberVersion6' => $nodeProcess['portNumber']
					),
					array(
						'nodeId' => $nodeProcess['nodeId'],
						'nodeProcessType' => $nodeProcess['nodeProcessType']
					)
				)
			)
		), $response);

		if (empty($nodeProcessRecursiveDnsDestinations) === false) {
			$ipAddressVersionNumbers = array(
				4,
				6
			);

			foreach ($nodeProcessRecursiveDnsDestinations as $nodeProcessRecursiveDnsDestinationsKey => $nodeProcessRecursiveDnsDestination) {
				foreach ($ipAddressVersionNumbers as $ipAddressVersionNumber) {
					if (($nodeProcess['nodeId'] === $nodeProcessRecursiveDnsDestination['destinationIpAddressVersion' . $ipAddressVersionNumber . 'NodeId']) === true) {
						$nodeProcessRecursiveDnsDestinations[$nodeProcessRecursiveDnsDestinationsKey]['destinationIpAddressVersion' . $ipAddressVersionNumber] = $node['externalIpAddressVersion' . $ipAddressVersionNumber];
						$nodeProcessRecursiveDnsDestinations[$nodeProcessRecursiveDnsDestinationsKey]['destinationIpAddressVersion' . $ipAddressVersionNumber . 'NodeId'] = $parameters['data']['nodeId'];
						$nodeProcessRecursiveDnsDestinations[$nodeProcessRecursiveDnsDestinationsKey]['sourceIpAddressVersion' . $ipAddressVersionNumber] = '';

						if (
							(empty($node['internalIpAddressVersion' . $ipAddressVersionNumber]) === false) &&
							(($nodeProcess['nodeNodeId'] === $nodeProcessRecursiveDnsDestination['nodeNodeId']) === true)
						) {
							$nodeProcessRecursiveDnsDestinations[$nodeProcessRecursiveDnsDestinationsKey]['destinationIpAddressVersion' . $ipAddressVersionNumber] = $node['internalIpAddressVersion' . $ipAddressVersionNumber];
							$nodeProcessRecursiveDnsDestinations[$nodeProcessRecursiveDnsDestinationsKey]['sourceIpAddressVersion' . $ipAddressVersionNumber] = $node['externalIpAddressVersion' . $ipAddressVersionNumber];
						}

						if (empty($nodeProcessRecursiveDnsDestinations[$nodeProcessRecursiveDnsDestinationsKey]['destinationIpAddressVersion' . $ipAddressVersionNumber]) === true) {
							$nodeProcessRecursiveDnsDestinations[$nodeProcessRecursiveDnsDestinationsKey]['destinationIpAddressVersion' . $ipAddressVersionNumber] = '';
							$nodeProcessRecursiveDnsDestinations[$nodeProcessRecursiveDnsDestinationsKey]['destinationIpAddressVersion' . $ipAddressVersionNumber . 'NodeId'] = '';
							$nodeProcessRecursiveDnsDestinations[$nodeProcessRecursiveDnsDestinationsKey]['portNumberVersion' . $ipAddressVersionNumber] = '';
						}

						if (($nodeProcess['port_number'] === $nodeProcessRecursiveDnsDestination['portNumberVersion' . $ipAddressVersionNumber]) === true) {
							$nodeProcessRecursiveDnsDestinations[$nodeProcessRecursiveDnsDestinationsKey]['portNumberVersion' . $ipAddressVersionNumber] = $parameters['data']['portNumberVersion' . $ipAddressVersionNumber];
						}
					}
				}

				if (($nodeProcess['nodeId'] === $nodeProcessRecursiveDnsDestination['node_id']) === true) {
					$nodeProcessRecursiveDnsDestinations[$nodeProcessRecursiveDnsDestinationsKey]['nodeId'] = $parameters['data']['nodeId'];
					$nodeProcessRecursiveDnsDestinations[$nodeProcessRecursiveDnsDestinationsKey]['nodeNodeId'] = $parameters['data']['nodeNodeId'];
					$nodeProcessRecursiveDnsDestinations[$nodeProcessRecursiveDnsDestinationsKey]['nodeProcessType'] = $parameters['data']['nodeProcessType'];
				}

				if (
					(empty($nodeProcessRecursiveDnsDestinations[$nodeProcessRecursiveDnsDestinationsKey]['destinationIpAddressVersion4']) === true) &&
					(empty($nodeProcessRecursiveDnsDestinations[$nodeProcessRecursiveDnsDestinationsKey]['destinationIpAddressVersion6']) === true)
				) {
					$nodeProcessRecursiveDnsDestinations[$nodeProcessRecursiveDnsDestinationsKey]['nodeNodeId'] = '';
				}
			}
		}

		_save(array(
			'data' => $nodeProcessRecursiveDnsDestinations,
			'in' => $parameters['systemDatabases']['nodeProcessRecursiveDnsDestinations']
		), $response);
		_delete(array(
			'in' => $parameters['systemDatabases']['nodeProcessRecursiveDnsDestinations'],
			'where' => array(
				'nodeNodeId' => ''
			)
		), $response);
		_edit(array(
			'data' => array(
				'portNumber' => $parameters['data']['portNumber'],
				'type' => $parameters['data']['type']
			),
			'in' => $parameters['systemDatabases']['nodeProcesses'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$nodeProcess = _list(array(
			'in' => $parameters['systemDatabases']['nodeProcesses'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$nodeProcess = current($nodeProcess);
		$response['data'] = $nodeProcess;
		$response['message'] = 'Node process edited successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'edit-node-process') === true) {
		$response = _editNodeProcess($parameters, $response);
	}
?>
