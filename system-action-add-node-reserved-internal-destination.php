<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeReservedInternalDestinations'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeReservedInternalDestinations'] = $systemDatabasesConnections['nodeReservedInternalDestinations'];

	function _addNodeReservedInternalDestination($parameters, $response) {
		$nodeIpAddressVersionNumber = key($parameters['node']);
		$existingNodeReservedInternalDestination = _list(array(
			'data' => array(
				'id',
				'ipAddress',
				'ipAddressVersionNumber',
				'nodeNodeId'
			),
			'in' => $parameters['systemDatabases']['nodeReservedInternalDestinations'],
			'limit' => 1,
			'sort' => array(
				'ipAddress' => 'ascending'
			),
			'where' => array(
				'either' => array(
					'nodeId' => $parameters['node'][$nodeIpAddressVersionNumber],
					'nodeNodeId' => $parameters['node'][$nodeIpAddressVersionNumber]
				),
				'ipAddressVersionNumber' => $nodeIpAddressVersionNumber,
				'processedStatus' => '0'
			)
		), $response);
		$existingNodeReservedInternalDestination = current($existingNodeReservedInternalDestination);

		if (empty($existingNodeReservedInternalDestination) === true) {
			$existingNodeReservedInternalDestination = array(
				'id' => _generateUniqueId(),
				'ipAddressVersionNumber' => $nodeIpAddressVersionNumber,
				'nodeId' => $parameters['node'][$nodeIpAddressVersionNumber]['id'],
				'nodeNodeId' => $parameters['node'][$nodeIpAddressVersionNumber]['nodeId'],
				'processedStatus' => '0'
			);

			switch ($nodeIpAddressVersionNumber) {
				case '4':
					$existingNodeReservedInternalDestination['ipAddress'] = '10.0.0.0';
					break;
				case '6':
					$existingNodeReservedInternalDestination['ipAddress'] = 'fc10:0000:0000:0000:0000:0000:0000:0000';
					break;
			}

			$nodeReservedInternalDestinationIpAddress = $existingNodeReservedInternalDestination['ipAddress'];

			while (($existingNodeReservedInternalDestination['processedStatus'] === '0') === true) {
				switch ($nodeIpAddressVersionNumber) {
					case '4':
						$nodeReservedInternalDestinationIpAddress = ip2long($nodeReservedInternalDestinationIpAddress);
						$nodeReservedInternalDestinationIpAddress = long2ip($nodeReservedInternalDestinationIpAddress + 1);
						break;
					case '6':
						$nodeReservedInternalDestinationIpAddressBlock = substr($nodeReservedInternalDestinationIpAddress, -29);
						$nodeReservedInternalDestinationIpAddressBlockInteger = str_replace(':', '', $nodeReservedInternalDestinationIpAddressBlock);
						$nodeReservedInternalDestinationIpAddressBlockInteger = intval($nodeReservedInternalDestinationIpAddressBlockInteger);
						$nodeReservedInternalDestinationIpAddressBlockIntegerIncrement = ($nodeReservedInternalDestinationIpAddressBlockInteger + 1);
						$nodeReservedInternalDestinationIpAddress = sprintf('%024u', $nodeReservedInternalDestinationIpAddressBlockIntegerIncrement);
						$nodeReservedInternalDestinationIpAddress = str_split($nodeReservedInternalDestinationIpAddress, 4);
						$nodeReservedInternalDestinationIpAddress = 'fc10:0000:' . implode(':', $nodeReservedInternalDestinationIpAddress, 4);
						break;
				}

				if ((_validateIpAddressType($nodeReservedInternalDestinationIpAddress, $nodeIpAddressVersionNumber) === 'publicNetwork') === true) {
					continue;
				}

				$existingNodeCount = _count(array(
					'in' => $parameters['systemDatabases']['nodes'],
					'where' => array(
						'either' => array(
							array(
								'either' => array(
									'id' => $parameters['node'][$nodeIpAddressVersionNumber],
									'nodeId' => $parameters['node'][$nodeIpAddressVersionNumber]
								),
								'internalIpAddressVersion' . $nodeIpAddressVersionNumber => $nodeReservedInternalDestinationIpAddress
							),
							array(
								'externalIpAddressVersion' . $nodeIpAddressVersionNumber => $nodeReservedInternalDestinationIpAddress,
								'externalIpAddressVersion' . $nodeIpAddressVersionNumber . 'Type not' => 'publicNetwork'
							)
						)
					)
				), $response);

				if (($existingNodeCount === 0) === true) {
					$existingNodeReservedInternalDestination['ipAddress'] = $nodeReservedInternalDestinationIpAddress;
					$existingNodeReservedInternalDestination['processedStatus'] = '1';
				}
			}
		} else {
			$existingNodeReservedInternalDestination['nodeId'] = $parameters['node'][$nodeIpAddressVersionNumber]['id'];
			$existingNodeReservedInternalDestination['processedStatus'] = '1';
		}

		$existingNodeReservedInternalDestinationsData = array(
			$existingNodeReservedInternalDestination,
			$existingNodeReservedInternalDestination
		);
		$existingNodeReservedInternalDestinationsData[1]['id'] = _generateUniqueId();
		$existingNodeReservedInternalDestinationsData[1]['nodeId'] = '';
		$existingNodeReservedInternalDestinationsData[1]['processedStatus'] = '0';
		$nodeReservedInternalDestinationIpAddress = $existingNodeReservedInternalDestination['ipAddress'];

		while (($existingNodeReservedInternalDestinationsData[0]['ipAddress'] === $existingNodeReservedInternalDestinationsData[1]['ipAddress']) === true) {
			switch ($nodeIpAddressVersionNumber) {
				case '4':
					$nodeReservedInternalDestinationIpAddress = ip2long($nodeReservedInternalDestinationIpAddress);
					$nodeReservedInternalDestinationIpAddress = long2ip($nodeReservedInternalDestinationIpAddress + 1);
					break;
				case '6':
					$nodeReservedInternalDestinationIpAddressBlock = substr($nodeReservedInternalDestinationIpAddress, -29);
					$nodeReservedInternalDestinationIpAddressBlockInteger = str_replace(':', '', $nodeReservedInternalDestinationIpAddressBlock);
					$nodeReservedInternalDestinationIpAddressBlockInteger = intval($nodeReservedInternalDestinationIpAddressBlockInteger);
					$nodeReservedInternalDestinationIpAddressBlockIntegerIncrement = ($nodeReservedInternalDestinationIpAddressBlockInteger + 1);
					$nodeReservedInternalDestinationIpAddress = sprintf('%024u', $nodeReservedInternalDestinationIpAddressBlockIntegerIncrement);
					$nodeReservedInternalDestinationIpAddress = str_split($nodeReservedInternalDestinationIpAddress, 4);
					$nodeReservedInternalDestinationIpAddress = 'fc10:0000:' . implode(':', $nodeReservedInternalDestinationIpAddress);
					break;
			}

			if ((_validateIpAddressType($nodeReservedInternalDestinationIpAddress, $nodeIpAddressVersionNumber) === 'publicNetwork') === true) {
				continue;
			}

			$existingNodeCount = _count(array(
				'in' => $parameters['systemDatabases']['nodes'],
				'where' => array(
					'either' => array(
						array(
							'either' => array(
								'id' => $parameters['node'][$nodeIpAddressVersionNumber],
								'nodeId' => $parameters['node'][$nodeIpAddressVersionNumber]
							),
							'internalIpAddressVersion' . $nodeIpAddressVersionNumber => $nodeReservedInternalDestinationIpAddress
						),
						array(
							'externalIpAddressVersion' . $nodeIpAddressVersionNumber => $nodeReservedInternalDestinationIpAddress,
							'externalIpAddressVersion' . $nodeIpAddressVersionNumber . 'Type not' => 'publicNetwork'
						)
					)
				)
			), $response);

			if (($existingNodeCount === 0) === true) {
				$existingNodeReservedInternalDestinationsData[1]['ipAddress'] = $nodeReservedInternalDestinationIpAddress;
			}
		}

		_save(array(
			'data' => $existingNodeReservedInternalDestinationsData,
			'in' => $parameters['systemDatabases']['nodeReservedInternalDestinations']
		), $response);
		$response = $nodeReservedInternalDestinationIpAddress;
		return $response;
	}
?>
