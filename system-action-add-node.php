<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeReservedInternalSources',
		'nodes'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeReservedInternalSources'] = $systemDatabasesConnections['nodeReservedInternalSources'];
	$parameters['systemDatabases']['nodes'] = $systemDatabasesConnections['nodes'];
	require_once('/var/www/firewall-security-api/system-action-add-node-reserved-internal-destination.php');
	require_once('/var/www/firewall-security-api/system-action-validate-ip-address-type.php');

	function _addNode($parameters, $response) {
		$parameters['data']['activatedStatus'] = '0';
		$parameters['data']['deployedStatus'] = '0';

		if (empty($parameters['data']['nodeId']) === false) {
			$nodeNode = _list(array(
				'data' => array(
					'authenticationToken',
					'deployedStatus',
					'id',
					'nodeId'
				),
				'in' => $parameters['systemDatabases']['nodes'],
				'where' => array(
					'id' => $parameters['data']['nodeId']
				)
			), $response);
			$nodeNode = current($nodeNode);

			if (empty($nodeNode) === true) {
				$response['message'] = 'Invalid node ID, please try again.';
				return $response;
			}

			$parameters['data']['authenticationToken'] = $nodeNode['authenticationToken'];
			$parameters['data']['deployedStatus'] = $nodeNode['deployedStatus'];
			$parameters['data']['nodeId'] = $nodeNode['id'];
		} else {
			$parameters['data']['authenticationToken'] = _createUniqueId();
			$parameters['data']['nodeId'] = null;
		}

		$nodeExternalIpAddresses = $nodeInternalIpAddresses = array();
		$nodeIpAddressVersionNumbers = array(
			'4',
			'6'
		);

		foreach ($nodeIpAddressVersionNumbers as $nodeIpAddressVersionNumber) {
			if (empty($parameters['data']['externalIpAddressVersion' . $nodeIpAddressVersionNumber]) === false) {
				$nodeExternalIpAddresses['externalIpAddressVersion' . $nodeIpAddressVersionNumber] = _validateIpAddressVersionNumber($parameters['data']['externalIpAddressVersion' . $nodeIpAddressVersionNumber], $nodeIpAddressVersionNumber);

				if ($nodeExternalIpAddresses['externalIpAddressVersion' . $nodeIpAddressVersionNumber] === false) {
					$response['message'] = 'Invalid node external IP address version ' . $nodeIpAddressVersionNumber . ', please try again.';
					return $response;
				}

				$parameters['data']['externalIpAddressVersion' . $nodeIpAddressVersionNumber . 'Type'] = _validateIpAddressType($nodeExternalIpAddresses['externalIpAddressVersion' . $nodeIpAddressVersionNumber], $nodeIpAddressVersionNumber);

				if (
					(empty($parameters['data']['nodeId']) === true) &&
					(($parameters['data']['externalIpAddressVersion' . $nodeIpAddressVersionNumber . 'Type'] === 'publicNetwork') === false) &&
					(($parameters['endpointDestinationIpAddressType'] === 'publicNetwork') === true)
				) {
					$response['message'] = 'Node external IP address version ' . $nodeIpAddressVersionNumber . ' must have a public network IP address type, please try again.';
					return $response;
				}
			}
		}

		if (empty($nodeExternalIpAddresses) === true) {
			$response['message'] = 'Node must have an external IP address, please try again.';
			return $response;
		}

		foreach ($nodeIpAddressVersionNumbers as $nodeIpAddressVersionNumber) {
			if (empty($parameters['data']['internalIpAddressVersion' . $nodeIpAddressVersionNumber]) === false) {
				$nodeInternalIpAddresses['internalIpAddressVersion' . $nodeIpAddressVersionNumber] = _validateIpAddressVersionNumber($parameters['data']['internalIpAddressVersion' . $nodeIpAddressVersionNumber], $nodeIpAddressVersionNumber);

				if ($nodeInternalIpAddresses[$nodeIpAddressVersionNumber] === false) {
					$response['message'] = 'Invalid node internal IP address version ' . $nodeIpAddressVersionNumber . ', please try again.';
					return $response;
				}

				if (empty($nodeExternalIpAddresses['externalIpAddressVersion' . $nodeIpAddressVersionNumber]) === true) {
					$response['message'] = 'Node internal IP address version ' . $nodeIpAddressVersionNumber . '  must have a matching external IP address, please try again.';
					return $response;
				}

				$parameters['data']['internalIpAddressVersion' . $nodeIpAddressVersionNumber . 'Type'] = _validateIpAddressType($nodeInternalIpAddresses['internalIpAddressVersion' . $nodeIpAddressVersionNumber], $nodeIpAddressVersionNumber);

				if (($parameters['data']['internalIpAddressVersion' . $nodeIpAddressVersionNumber] === 'publicNetwork') === true) {
					$response['message'] = 'Node internal IP address version ' . $nodeIpAddressVersionNumber . ' must be a reserved IP address, please try again.';
					return $response;
				}
			}
		}

		$existingNodeParameters = array(
			'data' => array(
				'externalIpAddressVersion4',
				'externalIpAddressVersion6',
				'internalIpAddressVersion4',
				'internalIpAddressVersion6'
			),
			'in' => $parameters['systemDatabases']['nodes'],
			'where' => array(
				'either' => $nodeExternalIpAddresses
			)
		);
		$nodeIpAddresses = array_merge($nodeExternalIpAddresses, $nodeInternalIpAddresses);

		if (empty($parameters['data']['nodeId']) === false) {
			$existingNodeParameters['where']['either'] = array(
				$existingNodeParameters['where'],
				array(
					'either' => $nodeIpAddresses,
					'nodeId' => $parameters['data']['nodeId']
				)
			);
		}

		$existingNode = _list($existingNodeParameters, $response);
		$existingNode = current($existingNode);

		if (empty($existingNode) === false) {
			$existingNodeIpAddresses = array_filter($existingNode);

			foreach ($existingNodeIpAddresses as $existingNodeIpAddress) {
				if (in_array($existingNodeIpAddress, $nodeIpAddresses) === true) {
					$response['message'] = 'Node already exists with the same IP address ' . $existingNodeIpAddress . ', please try again.';
					return $response;
				}
			}
		}

		$parameters['data']['id'] = _createUniqueId();

		foreach ($nodeIpAddressVersionNumbers as $nodeIpAddressVersionNumber) {
			if (empty($parameters['data']['externalIpAddressVersion' . $nodeIpAddressVersionNumber]) === false) {
				$parameters['node'] = array(
					$nodeIpAddressVersionNumber => array(
						'id' => $parameters['data']['id'],
						'nodeId' => $parameters['data']['nodeId']
					)
				);

				if (empty($parameters['node'][$nodeIpAddressVersionNumber]['nodeId']) === true) {
					$parameters['node'][$nodeIpAddressVersionNumber]['nodeId'] = $parameters['data']['id'];
				}

				_addNodeReservedInternalDestination($parameters, $response);
			}
		}

		if (empty($parameters['data']['nodeId']) === true) {
			$nodeReservedInternalSourceData = array();
			$nodeReservedInternalSources = array(
				'4' => array(
					array(
						'ipAddress' => '0.0.0.0',
						'ipAddressBlockLength' => '8'
					),
					array(
						'ipAddress' => '10.0.0.0',
						'ipAddressBlockLength' => '8'
					),
					array(
						'ipAddress' => '100.64.0.0',
						'ipAddressBlockLength' => '10'
					),
					array(
						'ipAddress' => '127.0.0.0',
						'ipAddressBlockLength' => '8'
					),
					array(
						'ipAddress' => '169.254.0.0',
						'ipAddressBlockLength' => '16'
					),
					array(
						'ipAddress' => '172.16.0.0',
						'ipAddressBlockLength' => '12'
					),
					array(
						'ipAddress' => '192.0.0.0',
						'ipAddressBlockLength' => '24'
					),
					array(
						'ipAddress' => '192.0.2.0',
						'ipAddressBlockLength' => '24'
					),
					array(
						'ipAddress' => '192.88.99.0',
						'ipAddressBlockLength' => '24'
					),
					array(
						'ipAddress' => '192.168.0.0',
						'ipAddressBlockLength' => '16'
					),
					array(
						'ipAddress' => '198.18.0.0',
						'ipAddressBlockLength' => '15'
					),
					array(
						'ipAddress' => '198.51.100.0',
						'ipAddressBlockLength' => '24'
					),
					array(
						'ipAddress' => '203.0.113.0',
						'ipAddressBlockLength' => '24'
					),
					array(
						'ipAddress' => '224.0.0.0',
						'ipAddressBlockLength' => '4'
					),
					array(
						'ipAddress' => '233.252.0.0',
						'ipAddressBlockLength' => '24'
					),
					array(
						'ipAddress' => '240.0.0.0',
						'ipAddressBlockLength' => '4'
					),
					array(
						'ipAddress' => '255.255.255.255',
						'ipAddressBlockLength' => '32'
					)
				),
				'6' => array(
					array(
						'ipAddress' => '0000:0000:0000:0000:0000:0000:0000:0000',
						'ipAddressBlockLength' => '128'
					),
					array(
						'ipAddress' => '0000:0000:0000:0000:0000:0000:0000:0001',
						'ipAddressBlockLength' => '128'
					),
					array(
						'ipAddress' => '0000:0000:0000:0000:0000:ffff:0000:0000',
						'ipAddressBlockLength' => '96'
					),
					array(
						'ipAddress' => '0000:0000:0000:0000:ffff:0000:0000:0000',
						'ipAddressBlockLength' => '96'
					),
					array(
						'ipAddress' => '0064:ff9b:0000:0000:0000:0000:0000:0000',
						'ipAddressBlockLength' => '96'
					),
					array(
						'ipAddress' => '0064:ff9b:0001:0000:0000:0000:0000:0000',
						'ipAddressBlockLength' => '48'
					),
					array(
						'ipAddress' => '0100:0000:0000:0000:0000:0000:0000:0000',
						'ipAddressBlockLength' => '64'
					),
					array(
						'ipAddress' => '2001:0000:0000:0000:0000:0000:0000:0000',
						'ipAddressBlockLength' => '32'
					),
					array(
						'ipAddress' => '2001:0020:0000:0000:0000:0000:0000:0000',
						'ipAddressBlockLength' => '28'
					),
					array(
						'ipAddress' => '2001:0db8:0000:0000:0000:0000:0000:0000',
						'ipAddressBlockLength' => '32'
					),
					array(
						'ipAddress' => '2002:0000:0000:0000:0000:0000:0000:0000',
						'ipAddressBlockLength' => '16'
					),
					array(
						'ipAddress' => 'fc00:0000:0000:0000:0000:0000:0000:0000',
						'ipAddressBlockLength' => '7'
					),
					array(
						'ipAddress' => 'fe80:0000:0000:0000:0000:0000:0000:0000',
						'ipAddressBlockLength' => '10'
					),
					array(
						'ipAddress' => 'ff00:0000:0000:0000:0000:0000:0000:0000',
						'ipAddressBlockLength' => '8'
					)
				)
			);

			foreach ($nodeReservedInternalSources as $nodeReservedInternalSourceIpAddressVersionNumber => $nodeReservedInternalSources) {
				foreach ($nodeReservedInternalSources as $nodeReservedInternalSource) {
					if (empty($nodeExternalIpAddresses['externalIpAddressVersion' . $nodeReservedInternalSourceIpAddressVersionNumber]) === false) {
						$nodeReservedInternalSourceData[] = array(
							'id' => _createUniqueId(),
							'ipAddress' => $nodeReservedInternalSource['ipAddress'],
							'ipAddressBlockLength' => $nodeReservedInternalSource['ipAddressBlockLength'],
							'ipAddressVersionNumber' => $nodeReservedInternalSourceIpAddressVersionNumber,
							'nodeId' => $parameters['data']['id']
						);
					}
				}
			}

			_save(array(
				'data' => $nodeReservedInternalSourceData,
				'in' => $parameters['systemDatabases']['nodeReservedInternalSources']
			), $response);
		}

		$parameters['data']['processedStatus'] = '0';
		$parameters['data']['processingProgressCheckpoint'] = 'processingQueued';
		$parameters['data']['processingProgressOverrideStatus'] = '0';
		$parameters['data']['processingProgressPercentage'] = '0';
		$parameters['data']['processingStatus'] = '0';
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['nodes']
		), $response);
		$node = _list(array(
			'in' => $parameters['systemDatabases']['nodes'],
			'where' => $nodeIpAddresses
		), $response);
		$node = current($node);
		$response['data'] = $node;
		$response['message'] = 'Node added successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add-node') === true) {
		$response = _addNode($parameters, $response);
	}
?>
