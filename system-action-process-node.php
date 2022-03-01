<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeProcessForwardingDestinations',
		'nodeProcessNodeUserAuthenticationCredentials',
		'nodeProcessNodeUserAuthenticationSources',
		'nodeProcessNodeUserNodeRequestDestinations',
		'nodeProcessNodeUserNodeRequestLimitRules',
		'nodeProcessNodeUsers',
		'nodeProcessRecursiveDnsDestinations',
		'nodeProcesses',
		'nodeReservedInternalDestinations',
		'nodeReservedInternalSources',
		'nodes'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeProcessForwardingDestinations'] = $systemDatabasesConnections['nodeProcessForwardingDestinations'];
	$parameters['systemDatabases']['nodeProcessNodeUserAuthenticationCredentials'] = $systemDatabasesConnections['nodeProcessNodeUserAuthenticationCredentials'];
	$parameters['systemDatabases']['nodeProcessNodeUserAuthenticationSources'] = $systemDatabasesConnections['nodeProcessNodeUserAuthenticationSources'];
	$parameters['systemDatabases']['nodeProcessNodeUserNodeRequestDestinations'] = $systemDatabasesConnections['nodeProcessNodeUserNodeRequestDestinations'];
	$parameters['systemDatabases']['nodeProcessNodeUserNodeRequestLimitRules'] = $systemDatabasesConnections['nodeProcessNodeUserNodeRequestLimitRules'];
	$parameters['systemDatabases']['nodeProcessNodeUsers'] = $systemDatabasesConnections['nodeProcessNodeUsers'];
	$parameters['systemDatabases']['nodeProcessRecursiveDnsDestinations'] = $systemDatabasesConnections['nodeProcessRecursiveDnsDestinations'];
	$parameters['systemDatabases']['nodeProcesses'] = $systemDatabasesConnections['nodeProcesses'];
	$parameters['systemDatabases']['nodeReservedInternalDestinations'] = $systemDatabasesConnections['nodeReservedInternalDestinations'];
	$parameters['systemDatabases']['nodeReservedInternalSources'] = $systemDatabasesConnections['nodeReservedInternalSources'];
	$parameters['systemDatabases']['nodes'] = $systemDatabasesConnections['nodes'];

	function _processNode($parameters, $response) {
		$response['data'] = array(
			'nodeIpAddressVersionNumbers' => array(
				'32' => '4',
				'128' => '6'
			),
			'nodeProcessTypes' => array(
				'httpProxy',
				'loadBalancer',
				'recursiveDns',
				'socksProxy'
			),
			'proxyNodeProcessTypes' => array(
				'proxy' => 'httpProxy',
				'socks' => 'socksProxy'
			)
		);

		if (empty($parameters['nodeAuthenticationToken']) === false) {
			$node = _list(array(
				'data' => array(
					'id',
					'nodeId',
					'processingProgressOverrideStatus'
				),
				'in' => $parameters['systemDatabases']['nodes'],
				'where' => array(
					'authenticationToken' => $parameters['nodeAuthenticationToken']
				)
			), $response);
			$node = current($node);
		}

		if (empty($node) === true) {
			$response['message'] = 'Invalid node authentication token, please try again.';

			if (empty($parameters['where']['id']) === false) {
				_edit(array(
					'data' => array(
						'processedStatus' => '0',
						'processingProgressCheckpoint' => 'processingQueued',
						'processingProgressOverrideStatus' => '1',
						'processingProgressPercentage' => '0',
						'processingStatus' => '0'
					),
					'in' => $parameters['systemDatabases']['nodes'],
					'where' => array(
						'either' => array(
							'id' => $parameters['where']['id'],
							'nodeId' => $parameters['where']['id']
						)
					)
				), $response);
				$response['message'] = 'Nodes processed successfully.';
				$response['validatedStatus'] = '1';
			}

			return $response;
		}

		$nodeNodeId = $node['id'];

		if (empty($node['nodeId']) === false) {
			$nodeNodeId = $node['nodeId'];
		}

		if (
			(isset($parameters['data']['processingProgressCheckpoint']) === true) &&
			(isset($parameters['data']['processingProgressPercentage']) === true) &&
			(isset($parameters['data']['processingStatus']) === true)
		) {
			$parameters['data']['processingProgressOverrideStatus'] = '0';

			if (
				(($node['processingProgressOverrideStatus'] === '0') === true) ||
				(
					(($node['processingProgressOverrideStatus'] === '1') === true) &&
					(($parameters['data']['processingProgressCheckpoint'] === 'listingNodeParameters') === true)
				)
			) {
				$response['data']['processingProgressOverrideStatus'] = $node['processingProgressOverrideStatus'];
			}

			_edit(array(
				'data' => array(
					'processedStatus' => $parameters['data']['processedStatus'],
					'processingProgressCheckpoint' => $parameters['data']['processingProgressCheckpoint'],
					'processingProgressOverrideStatus' => $parameters['data']['processingProgressOverrideStatus'],
					'processingProgressPercentage' => $parameters['data']['processingProgressPercentage'],
					'processingStatus' => $parameters['data']['processingStatus']
				),
				'in' => $parameters['systemDatabases']['nodes'],
				'where' => array(
					'either' => array(
						'id' => $nodeNodeId,
						'nodeId' => $nodeNodeId
					)
				)
			), $response);
		} else {
			$nodeCount = _count(array(
				'in' => $parameters['systemDatabases']['nodes'],
				'where' => array(
					'either' => array(
						'id' => $nodeNodeId,
						'nodeId' => $nodeNodeId
					),
					'processedStatus' => '0'
				)
			), $response);

			if (($nodeCount === 0) === true) {
				$response['message'] = 'Node is already processed, please try again.';
				$response['validatedStatus'] = '1';
				return $response;
			}

			$nodeProcesses = _list(array(
				'data' => array(
					'id',
					'nodeId',
					'portNumber',
					'type'
				),
				'in' => $parameters['systemDatabases']['nodeProcesses'],
				'where' => array(
					'nodeNodeId' => $nodeNodeId
				)
			), $response);
			$nodeProcessForwardingDestinations = _list(array(
				'data' => array(
					'addressVersion4',
					'addressVersion6',
					'id',
					'nodeProcessType',
					'portNumberVersion4',
					'portNumberVersion6'
				),
				'in' => $parameters['systemDatabases']['nodeProcessForwardingDestinations'],
				'where' => array(
					'nodeNodeId' => $nodeNodeId
				)
			), $response);
			$nodeProcessNodeUserAuthenticationCredentials = _list(array(
				'data' => array(
					'nodeUserAuthenticationCredentialPassword',
					'nodeUserAuthenticationCredentialUsername',
					'nodeUserId'
				),
				'in' => $parameters['systemDatabases']['nodeProcessNodeUserAuthenticationCredentials'],
				'where' => array(
					'nodeNodeId' => $nodeNodeId
				)
			), $response);
			$nodeProcessNodeUserAuthenticationSources = _list(array(
				'data' => array(
					'nodeUserAuthenticationSourceIpAddress',
					'nodeUserAuthenticationSourceIpAddressBlockLength',
					'nodeUserId'
				),
				'in' => $parameters['systemDatabases']['nodeProcessNodeUserAuthenticationSources'],
				'where' => array(
					'nodeNodeId' => $nodeNodeId
				)
			), $response);
			$nodeProcessNodeUserNodeRequestDestinations = _list(array(
				'data' => array(
					'nodeRequestDestinationAddress',
					'nodeRequestDestinationId',
					'nodeUserId'
				),
				'in' => $parameters['systemDatabases']['nodeProcessNodeUserNodeRequestDestinations'],
				'where' => array(
					'nodeNodeId' => $nodeNodeId
				)
			), $response);
			$nodeProcessNodeUserNodeRequestLimitRules = _list(array(
				'data' => array(
					'nodeId',
					'nodeProcessType',
					'nodeRequestDestinationId',
					'nodeUserId'
				),
				'in' => $parameters['systemDatabases']['nodeProcessNodeUserNodeRequestLimitRules'],
				'where' => array(
					'activatedStatus' => '1',
					'nodeNodeId' => $nodeNodeId
				)
			), $response);
			$nodeProcessNodeUsers = _list(array(
				'data' => array(
					'nodeId',
					'nodeProcessType',
					'nodeUserAuthenticationStrictOnlyAllowedStatus',
					'nodeUserId',
					'nodeUserNodeRequestDestinationsOnlyAllowedStatus',
					'nodeUserNodeRequestLogsAllowedStatus'
				),
				'in' => $parameters['systemDatabases']['nodeProcessNodeUsers'],
				'where' => array(
					'nodeNodeId' => $nodeNodeId
				)
			), $response);
			$nodeProcessRecursiveDnsDestinations = _list(array(
				'data' => array(
					'destinationIpAddressVersion4',
					'destinationIpAddressVersion4NodeId',
					'destinationIpAddressVersion6',
					'destinationIpAddressVersion6NodeId',
					'nodeId',
					'nodeProcessType',
					'portNumberVersion4',
					'portNumberVersion6',
					'sourceIpAddressVersion4',
					'sourceIpAddressVersion6'
				),
				'in' => $parameters['systemDatabases']['nodeProcessRecursiveDnsDestinations'],
				'where' => array(
					'nodeNodeId' => $nodeNodeId
				)
			), $response);
			$nodeReservedInternalDestinations = _list(array(
				'data' => array(
					'ipAddress',
					'ipAddressVersionNumber'
				),
				'in' => $parameters['system_databases']['nodeReservedInternalDestinations'],
				'where' => array(
					'assignedStatus' => '1',
					'nodeNodeId' => $nodeNodeId
				)
			), $response);
			$nodeReservedInternalSources = _list(array(
				'data' => array(
					'ipAddress',
					'ipAddressBlockLength',
					'ipAddressVersionNumber'
				),
				'in' => $parameters['systemDatabases']['nodeReservedInternalSources'],
				'where' => array(
					'nodeId' => $nodeNodeId
				)
			), $response);
			$nodes = _list(array(
				'data' => array(
					'activatedStatus',
					'externalIpAddressVersion4',
					'externalIpAddressVersion6',
					'id',
					'internalIpAddressVersion4',
					'internalIpAddressVersion6'
				),
				'in' => $parameters['systemDatabases']['nodes'],
				'where' => array(
					'either' => array(
						'id' => $nodeNodeId,
						'nodeId' => $nodeNodeId
					)
				)
			), $response);

			$nodeProcessPartKeys = array();

			foreach ($parameters['data']['nodeProcessTypes'] as $nodeProcessType) {
				$nodeProcessPartKeys[$nodeProcessType] = 0;
			}

			foreach ($nodeProcesses as $nodeProcess) {
				$response['data']['nodeProcesses'][$nodeProcess['type']][$nodeProcessPartKeys[$nodeProcess['type']]][$nodeProcess['nodeId']][$nodeProcess['id']] = $nodeProcess['portNumber'];
				$nodeProcessPartKeys[$nodeProcess['type']] = abs($nodeProcessPartKeys[$nodeProcess['type']] + -1);
			}

			foreach ($nodeProcessForwardingDestinations as $nodeProcessForwardingDestination) {
				$response['data']['nodeProcessForwardingDestinations'][$nodeProcessForwardingDestination['nodeProcessType']][$nodeProcessForwardingDestination['nodeId']] = $nodeProcessForwardingDestination;
				unset($response['data']['nodeProcessForwardingDestinations'][$nodeProcessForwardingDestination['nodeProcessType']][$nodeProcessForwardingDestination['nodeId']]['nodeId']);
			}

			if (empty($nodeProcessNodeUsers) === false) {
				foreach ($nodeProcessNodeUsers as $nodeProcessNodeUser) {
					$response['data']['nodeProcessNodeUsers'][$nodeProcessNodeUser['nodeProcessType']][$nodeProcessNodeUser['nodeId']][$nodeProcessNodeUser['nodeUserId']] = $nodeProcessNodeUser['nodeUserId'];
					$response['data']['nodeUsers'][$nodeProcessNodeUser['nodeUserId']] = array(
						'authenticationStrictOnlyAllowedStatus' => $nodeProcessNodeUser['nodeUserAuthenticationStrictOnlyAllowedStatus'],
						'nodeRequestDestinationsOnlyAllowedStatus' => $nodeProcessNodeUser['nodeUserNodeRequestDestinationsOnlyAllowedStatus'],
						'nodeRequestLogsAllowedStatus' => $nodeProcessNodeUser['nodeUserNodeRequestLogsAllowedStatus']
					);
				}

				foreach ($nodeProcessNodeUserAuthenticationCredentials as $nodeProcessNodeUserAuthenticationCredential) {
					$response['data']['nodeUsers'][$nodeProcessNodeUserAuthenticationCredential['nodeUserId']]['nodeUserAuthenticationCredentials'] = array(
						'password' => $nodeProcessNodeUserAuthenticationCredential['nodeUserAuthenticationCredentialPassword'],
						'username' => $nodeProcessNodeUserAuthenticationCredential['nodeUserAuthenticationCredentialUsername']
					);
				}

				foreach ($nodeProcessNodeUserAuthenticationSources as $nodeProcessNodeUserAuthenticationSource) {
					$response['data']['nodeUsers'][$nodeProcessNodeUserAuthenticationSource['nodeUserId']]['nodeUserAuthenticationSources'][] = $nodeProcessNodeUserAuthenticationSource['nodeUserAuthenticationSourceIpAddress'] . '/' . $nodeProcessNodeUserAuthenticationSource['nodeUserAuthenticationSourceIpAddressBlockLength'];
				}

				if (empty($nodeProcessNodeUserNodeRequestDestinations) === false) {
					foreach ($nodeProcessNodeUserNodeRequestDestinations as $nodeProcessNodeUserNodeRequestDestination) {
						$response['data']['nodeRequestDestinations'][$nodeProcessNodeUserNodeRequestDestination['nodeRequestDestinationId']] = $nodeProcessNodeUserNodeRequestDestination['nodeRequestDestinationAddress'];
						$response['data']['nodeUsers'][$nodeProcessNodeUserNodeRequestDestination['nodeUserId']]['nodeRequestDestinationIds'][$nodeProcessNodeUserNodeRequestDestination['nodeRequestDestinationId']] = $nodeProcessNodeUserNodeRequestDestination['nodeRequestDestinationId'];
					}
				}

				if (empty($nodeProcessNodeUserNodeRequestLimitRules) === false) {
					foreach ($nodeProcessNodeUserNodeRequestLimitRules as $nodeProcessNodeUserNodeRequestLimitRule) {
						if (empty($response['data']['nodeProcessNodeUsers'][$nodeProcessNodeUserNodeRequestLimitRule['nodeUserId']]['nodeRequestDestinationIds']) === true) {
							unset($response['data']['nodeProcessNodeUsers'][$nodeProcessNodeUserNodeRequestLimitRule['nodeProcessType']][$nodeProcessNodeUserNodeRequestLimitRule['nodeId']][$nodeProcessNodeUserNodeRequestLimitRule['nodeUserId']]);
						} else {
							unset($response['data']['nodeUsers'][$nodeProcessNodeUserNodeRequestLimitRule['nodeUserId']]['nodeRequestDestinationIds'][$nodeProcessNodeUserNodeRequestLimitRule['nodeRequestDestinationId']]);
						}
					}
				}
			}

			foreach ($nodes as $node) {
				$response['data']['nodes'][$node['id']] = $node;
				unset($response['data']['nodes'][$node['id']]['id']);

				foreach ($response['data']['nodeIpAddressVersionNumbers'] as $nodeIpAddressVersionNumber) {
					$nodeIpAddresses = array(
						$node['externalIpAddressVersion' . $nodeIpAddressVersionNumber],
						$node['internalIpAddressVersion' . $nodeIpAddressVersionNumber]
					);

					foreach (array_filter($nodeIpAddresses) as $nodeIpAddress) {
						$response['data']['nodeIpAddresses'][$nodeIpAddressVersionNumber][$nodeIpAddress] = $nodeIpAddress;
					}
				}
			}

			foreach (array_values($response['data']['nodeIpAddressVersions']) as $nodeIpAddressVersionNumberKey => $nodeIpAddressVersionNumber) {
				if (empty($response['data']['nodeIpAddresses'][$nodeIpAddressVersionNumber]) === true) {
					unset($response['data']['nodeIpAddressVersionNumbers'][(128 / 4) + (96 * $nodeIpAddressVersionNumberKey)]);
				}
			}

			foreach ($nodeProcessRecursiveDnsDestinations as $nodeProcessRecursiveDnsDestination) {
				$response['data']['nodeProcessRecursiveDnsDestinations'][$nodeProcessRecursiveDnsDestination['nodeProcessType']][$nodeProcessRecursiveDnsDestination['nodeId']] = $nodeProcessRecursiveDnsDestination;
				unset($response['data']['nodeProcessRecursiveDnsDestinations'][$nodeProcessRecursiveDnsDestination['nodeProcessType']][$nodeProcessRecursiveDnsDestination['nodeId']]['nodeId']);

				foreach ($response['data']['nodeIpAddressVersionNumbers'] as $nodeIpAddressVersionNumber) {
					if (empty($nodeProcessRecursiveDnsDestination['sourceIpAddressVersion' . $nodeIpAddressVersionNumber]) === false) {
						$response['data']['nodeIpAddresses'][$nodeIpAddressVersionNumber][$nodeProcessRecursiveDnsDestination['listeningIpAddressVersion' . $nodeIpAddressVersionNumber]] = $nodeProcessRecursiveDnsDestination['listeningIpAddressVersion' . $nodeIpAddressVersionNumber];

						if (empty($response['data']['nodes'][$nodeProcessRecursiveDnsDestination['listeningIpAddressVersion' . $nodeIpAddressVersionNumber . 'NodeId']]['internalIpAddressVersion' . $nodeIpAddressVersionNumber]) === false) {
							$response['data']['nodeProcessRecursiveDnsDestinations'][$nodeProcessRecursiveDnsDestination['node_process_type']][$nodeProcessRecursiveDnsDestination['node_id']]['sourceIpAddressVersion' . $nodeIpAddressVersionNumber] = $response['data']['nodes'][$nodeProcessRecursiveDnsDestination['nodeId']]['internalIpAddressVersion' . $nodeIpAddressVersionNumber];
						}
					}

					unset($response['data']['nodeProcessRecursiveDnsDestinations'][$nodeProcessRecursiveDnsDestination['nodeProcessType']][$nodeProcessRecursiveDnsDestination['nodeId']]['listeningIpAddressVersion' . $nodeIpAddressVersionNumber . 'NodeId']);
				}
			}

			foreach ($nodeReservedInternalDestinations as $nodeReservedInternalDestination) {
				$response['data']['nodeIpAddresses'][$nodeReservedInternalDestination['ipAddressVersionNumber']][$nodeReservedInternalDestination['ipAddress']] = $response['data']['nodeReservedInternalDestinationIpAddresses'][$nodeReservedInternalDestination['ipAddressVersionNumber']][$nodeReservedInternalDestination['ipAddress']] = $nodeReservedInternalDestination['ipAddress'];
				$response['data']['nodeReservedInternalDestinations'][$nodeReservedInternalDestination['nodeId']][$nodeReservedInternalDestination['ipAddressVersionNumber']] = array(
					'ipAddress' => $nodeReservedInternalDestination['ipAddress'],
					'ipAddressVersionNumber' => $nodeReservedInternalDestination['ipAddressVersionNumber']
				);
			}

			foreach ($nodeReservedInternalSources as $nodeReservedInternalSource) {
				$response['data']['nodeReservedInternalSources'][$nodeReservedInternalSource['ipAddressVersionNumber']][] = $nodeReservedInternalDestination['ipAddress'] . '/' . $nodeReservedInternalDestination['ipAddressBlockLength'];
			}
		}

		$response['message'] = 'Nodes processed successfully.';
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
