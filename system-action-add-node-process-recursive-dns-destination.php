<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeProcessRecursiveDnsDestinations',
		'nodeProcesses',
		'nodes'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeProcessRecursiveDnsDestinations'] = $systemDatabasesConnections['nodeProcessRecursiveDnsDestinations'];
	$parameters['systemDatabases']['nodeProcesses'] = $systemDatabasesConnections['nodeProcesses'];
	$parameters['systemDatabases']['nodes'] = $systemDatabasesConnections['nodes'];
	require_once('/var/www/firewall-security-api/system-action-validate-ip-address-type.php');
	require_once('/var/www/firewall-security-api/system-action-validate-port-number.php');

	function _addNodeProcessRecursiveDnsDestination($parameters, $response) {
		$parameters['data']['id'] = _createUniqueId();

		if (empty($parameters['data']['nodeId']) === true) {
			$response['message'] = 'Node process recursive DNS destination must have a node ID, please try again.';
			return $response;
		}

		if (empty($parameters['data']['nodeProcessType']) === true) {
			$response['message'] = 'Node process recursive DNS destination must have a node process type, please try again.';
			return $response;
		}

		if (
			(($parameters['data']['nodeProcessType'] === 'httpProxy') === false) &&
			(($parameters['data']['nodeProcessType'] === 'socksProxy') === false)
		) {
			$response['message'] = 'Invalid node process recursive DNS destination node process type, please try again.';
			return $response;
		}

		$node = _list(array(
			'data' => array(
				'externalIpAddressVersion4',
				'externalIpAddressVersion6',
				'id',
				'nodeId'
			),
			'in' => $parameters['systemDatabases']['nodes'],
			'where' => array(
				'id' => $parameters['data']['nodeId']
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node process node ID, please try again.';
			return $response;
		}

		$nodeNodeId = $node['id'];

		if (empty($node['nodeId']) === false) {
			$nodeNodeId = $node['nodeId'];
		}

		$nodeIpAddressVersionNumbers = array(
			'4',
			'6'
		);

		foreach ($nodeIpAddressVersionNumbers as $nodeIpAddressVersionNumber) {
			unset($parameters['data']['destinationIpAddressVersion' . $nodeIpAddressVersionNumber . 'NodeId']);
			unset($parameters['data']['sourceIpAddressVersion' . $nodeIpAddressVersionNumber]);

			if (empty($node['externalIpAddressVersion' . $nodeIpAddressVersionNumber]) === false) {
				if (empty($parameters['data']['destinationIpAddressVersion' . $nodeIpAddressVersionNumber]) === true) {
					$response['message'] = 'Node process recursive DNS destination must have a destination IP address version ' . $nodeIpAddressVersionNumber . ', please try again.';
					return $response;
				}

				if (empty($parameters['data']['portNumberVersion' . $nodeIpAddressVersionNumber]) === true) {
					$response['message'] = 'Node process recursive DNS destination must have a port number version ' . $nodeIpAddressVersionNumber . ', please try again.';
					return $response;
				}

				if (_validatePortNumber($parameters['data']['portNumberVersion' . $nodeIpAddressVersionNumber]) === false) {
					$response['message'] = 'Invalid node process recursive DNS destination port number version ' . $nodeIpAddressVersionNumber . ', please try again.';
					return $response;
				}
			} else {
				unset($parameters['data']['destinationIpAddressVersion' . $nodeIpAddressVersionNumber]);
				unset($parameters['data']['portNumberVersion' . $nodeIpAddressVersionNumber]);
			}

			if (empty($parameters['data']['destinationIpAddressVersion' . $nodeIpAddressVersionNumber]) === false) {
				$parameters['data']['destinationIpAddressVersion' . $nodeIpAddressVersionNumber] = _validateIpAddressVersionNumber($parameters['data']['destinationIpAddressVersion' . $nodeIpAddressVersionNumber], $nodeIpAddressVersionNumber);

				if ($parameters['data']['destinationIpAddressVersion' . $nodeIpAddressVersionNumber] === false) {
					$response['message'] = 'Invalid node process recursive DNS destination destination IP address version ' . $nodeIpAddressVersionNumber . ', please try again.';
					return $response;
				}

				$destinationIpAddressNode = _list(array(
					'data' => array(
						'externalIpAddressVersion' . $nodeIpAddressVersionNumber,
						'id',
						'internalIpAddressVersion' . $nodeIpAddressVersionNumber
					),
					'in' => $parameters['systemDatabases']['nodes'],
					'where' => array(
						'either' => array(
							array(
								array(
									'either' => array(
										array(
											'externalIpAddressVersion' . $nodeIpAddressVersionNumber => $parameters['data']['destinationIpAddressVersion' . $nodeIpAddressVersionNumber],
											'externalIpAddressVersion' . $nodeIpAddressVersionNumber . 'Type !=' => 'publicNetwork'
										),
										'internalIpAddressVersion' . $nodeIpAddressVersionNumber => $parameters['data']['destinationIpAddressVersion' . $nodeIpAddressVersionNumber]
									)
								),
								array(
									'either' => array(
										'id' => $nodeNodeId,
										'nodeId' => $nodeNodeId
									)
								)
							),
							array(
								'externalIpAddressVersion' . $nodeIpAddressVersionNumber => $parameters['data']['destinationIpAddressVersion' . $nodeIpAddressVersionNumber],
								'externalIpAddressVersion' . $nodeIpAddressVersionNumber . 'Type' => 'publicNetwork'
							)
						)
					)
				), $response);
				$destinationIpAddressNode = current($destinationIpAddressNode);

				if (empty($destinationIpAddressNode) === false) {
					$parameters['data']['destinationIpAddressVersion' . $nodeIpAddressVersionNumber . 'NodeId'] = $destinationIpAddressNode['id'];
					$portNumberNodeProcessCount = _count(array(
						'in' => $parameters['systemDatabases']['nodeProcesses'],
						'where' => array(
							'either' => array(
								'id' => $destinationIpAddressNode['id'],
								'nodeId' => $destinationIpAddressNode['id']
							),
							'portNumber' => $parameters['data']['portNumberVersion' . $nodeIpAddressVersionNumber],
							'type' => 'recursiveDns'
						)
					), $response);

					if (($portNumberNodeProcessCount === 1) === false) {
						$response['message'] = 'Node process recursive DNS destination port number version ' . $nodeIpAddressVersionNumber . ' must have a matching recursive DNS node process port number, please try again.';
						return $response;
					}
				}

				if (empty($destinationIpAddressNode['internalIpAddressVersion' . $nodeIpAddressVersionNumber]) === false) {
					$parameters['data']['destinationIpAddressVersion' . $nodeIpAddressVersionNumber] = $destinationIpAddressNode['internalIpAddressVersion' . $nodeIpAddressVersionNumber];
					$parameters['data']['sourceIpAddressVersion' . $nodeIpAddressVersionNumber] = $destinationIpAddressNode['externalIpAddressVersion' . $nodeIpAddressVersionNumber];
				}
			}
		}

		$parameters['data']['nodeNodeId'] = $node['nodeId'];
		$existingNodeProcessRecursiveDnsDestinationCount = _count(array(
			'in' => $parameters['systemDatabases']['nodeProcessRecursiveDnsDestinations'],
			'where' => array(
				'destinationIpAddressVersion4' => $parameters['data']['destinationIpAddressVersion4'],
				'destinationIpAddressVersion6' => $parameters['data']['destinationIpAddressVersion6'],
				'nodeId' => $parameters['data']['nodeId'],
				'nodeProcessType' => $parameters['data']['nodeProcessType'],
				'sourceIpAddressVersion4' => $parameters['data']['sourceIpAddressVersion4'],
				'sourceIpAddressVersion6' => $parameters['data']['sourceIpAddressVersion6']
			)
		), $response);

		if (($existingNodeProcessRecursiveDnsDestinationCount === 1) === true) {
			$response['message'] = 'Node process recursive DNS destination already exists, please try again.';
			return $response;
		}

		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['nodeProcessRecursiveDnsDestinations']
		), $response);
		$nodeProcessRecursiveDnsDestination = _list(array(
			'in' => $parameters['systemDatabases']['nodeProcessRecursiveDnsDestinations'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeProcessRecursiveDnsDestination = current($nodeProcessRecursiveDnsDestination);
		$response['data'] = $nodeProcessRecursiveDnsDestination;
		$response['message'] = 'Node process recursive DNS destination added successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'addNodeProcessRecursiveDnsDestination') === true) {
		$response = _addNodeProcessRecursiveDnsDestination($parameters, $response);
	}
?>
