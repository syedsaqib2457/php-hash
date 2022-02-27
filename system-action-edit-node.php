<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeReservedInternalDestinations',
		'nodes'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeReservedInternalDestinations'] = $systemDatabasesConnections['nodeReservedInternalDestinations'];
	$parameters['systemDatabases']['nodes'] = $systemDatabasesConnections['nodes'];
	require_once('/var/www/firewall-security-api/system-action-add-node-reserved-internal-destination.php');
	require_once('/var/www/firewall-security-api/system-action-validate-ip_address-type.php');

	function _editNode($parameters, $response) {
		if (empty($parameters['systemUserAuthenticationToken']) === true) {
			return $response;
		}

		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'Node must have an ID, please try again.';
			return $response;
		}

		$node = _list(array(
			'data' => array(
				'externalIpAddressVersion4',
				'externalIpAddressVersion6',
				'internalIpAddressVersion4',
				'internalIpAddressVersion6',
				'nodeId'
			),
			'in' => $parameters['systemDatabases']['nodes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node, please try again.';
			return $response;
		}

		$nodeExternalIpAddresses = $nodeInternalIpAddresses = array();
		$nodeIpAddressVersions = array(
			'4',
			'6'
		);

		foreach ($nodeIpAddressVersions as $nodeIpAddressVersion) {
			if (empty($parameters['data']['externalIpAddressVersion' . $nodeIpAddressVersion]) === false) {
				$nodeExternalIpAddresses['externalIpAddressVersion' . $nodeIpAddressVersion] = _validateIpAddressVersion($parameters['data']['externalIpAddressVersion' . $nodeIpAddressVersion], $nodeIpAddressVersion);

				if ($nodeExternalIpAddresses['externalIpAddressVersion' . $nodeIpAddressVersion] === false) {
					$response['message'] = 'Invalid node external IP address version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}

				$parameters['data']['externalIpAddressVersion' . $nodeIpAddressVersion . 'Type'] = _validateIpAddressType($nodeExternalIpAddresses['externalIpAddressVersion' . $nodeIpAddressVersion], $nodeIpAddressVersion);
			}
		}

		if (empty($nodeExternalIpAddresses) === true) {
			$response['message'] = 'Node must have an external IP address, please try again.';
			return $response;
		}

		foreach ($nodeIpAddressVersions as $nodeIpAddressVersion) {
			if (empty($parameters['data']['internalIpAddressVersion' . $nodeIpAddressVersion]) === false) {
				$nodeInternalIpAddresses['internalIpAddressVersion' . $nodeIpAddressVersion] = _validateIpAddressVersion($parameters['data']['internalIpAddressVersion' . $nodeIpAddressVersion], $nodeIpAddressVersion);

				if ($nodeInternalIpAddresses[$nodeIpAddressVersion] === false) {
					$response['message'] = 'Invalid node internal IP address version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}

				if (empty($nodeExternalIpAddresses['externalIpAddressVersion' . $nodeIpAddressVersion]) === true) {
					$response['message'] = 'Node internal IP address version ' . $nodeIpAddressVersion . '  must have a matching external IP address, please try again.';
					return $response;
				}

				$parameters['data']['internalIpAddressVersion' . $nodeIpAddressVersion . 'Type'] = _validateIpAddressType($nodeInternalIpAddresses['internalIpAddressVersion' . $nodeIpAddressVersion], $nodeIpAddressVersion);

				if (($parameters['data']['internalIpAddressVersion' . $nodeIpAddressVersion] === 'publicNetwork') === true) {
					$response['message'] = 'Node internal IP address version ' . $nodeIpAddressVersion . ' must be a reserved IP address, please try again.';
					return $response;
				}
			}
		}

		$nodeIds = array(
			$parameters['where']['id']
		);

		if (empty($node['nodeId']) === false) {
			$nodeIds[] = $node['nodeId'];
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
				'either' => $nodeExternalIpAddresses,
				'id !=' => $parameters['where']['id']
			)
		);
		$nodeIpAddresses = array_merge($nodeExternalIpAddresses, $nodeInternalIpAddresses);

		if (empty($parameters['data']['nodeId']) === false) {
			$existingNodeParameters['where']['either'] = array(
				array(
					$existingNodeParameters['where']['either']
				),
				array(
					'either' => $nodeIpAddresses,
					'nodeId' => $nodeIds
				)
			);
		}

		$existingNode = _list($existingNodeParameters, $response);
		$existingNode = current($existingNode);

		if (empty($existingNode) === false) {
			$existingNodeIpAddresses = array_filter($existingNode);

			foreach ($existingNodeIpAddresses as $existingNodeIpAddress) {
				if (in_array($existingNodeIpAddress, $nodeIpAddresses) === true) {
					$response['message'] = 'Node IP address ' . $existingNodeIpAddress . ' already exists, please try again.';
					return $response;
				}
			}

			return $response;
		}

		$existingNodeReservedInternalDestinations = _list(array(
			'data' => array(
				'id',
				'ipAddressVersion',
				'nodeId',
				'nodeNodeId'
			),
			'in' => $parameters['systemDatabases']['nodeReservedInternalDestinations'],
			'where' => array(
				'ipAddress' => $nodeIpAddresses,
				'nodeNodeId' => $nodeIds
			)
		), $response);

		foreach ($existingNodeReservedInternalDestinations as $existingNodeReservedInternalDestination) {
			$parameters['node'] = array(
				$existingNodeReservedInternalDestination['ipAddressVersion'] => array(
					'id' => $existingNodeReservedInternalDestination['nodeId'],
					'nodeId' => $existingNodeReservedInternalDestination['nodeNodeId']
				)
			);
			_addNodeReservedInternalDestination($parameters, $response);
			_delete(array(
				'in' => $parameters['systemDatabases']['nodeReservedInternalDestinations'],
				'where' => array(
					'id' => $existingNodeReservedInternalDestination['id']
				)
			), $response);
		}

		unset($parameters['data']['createdTimestamp']);
		unset($parameters['data']['modifiedTimestamp']);
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['nodes']
		), $response);
		$node = _list(array(
			'in' => $parameters['systemDatabases']['nodes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$node = current($node);
		$response['data'] = $node;
		$response['message'] = 'Node edited successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'edit-node') === true) {
		$response = _editNode($parameters, $response);
	}
?>
