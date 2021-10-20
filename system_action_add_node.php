<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		$databases['nodes']
	), $response);
	require_once('/var/www/ghostcompute/system_action_validate_ip_address_types.php');
	require_once('/var/www/ghostcompute/system_action_validate_ip_address_versions.php');

	function _addNode($parameters, $response) {
		if (empty($parameters['data']['node_id']) === false) {
			$nodeNode = _list(array(
				'in' => $parameters['databases']['nodes'],
				'where' => array(
					'id' => $parameters['data']['node_id']
				)
			));

			if ($nodeNode === false) {
				$response['message'] = 'Error listing data in nodes database, please try again.';
				return $response;
			}

			$nodeNode = current($nodeNode);

			if (empty($nodeNode) === true) {
				$response['message'] = 'Invalid node ID, please try again.';
				return $response;
			}

			$parameters['data']['status_active'] = $nodeNode['status_active'];
			$parameters['data']['status_deployed'] = $nodeNode['status_deployed'];

			if (empty($nodeNode['node_id']) === false) {
				$$parameters['data']['node_id'] = $nodeNode['node_id'];
			}
		}

		$nodeExternalIpAddresses = $nodeIpAddressVersionExternalIpAddresses = array();
		$nodeIpAddressVersions = array(
			4,
			6
		);

		foreach ($nodeIpAddressVersions as $nodeIpAddressVersion) {
			$nodeExternalIpAddressKey = 'external_ip_address_version_' . $nodeIpAddressVersion;

			if (empty($parameters['data'][$nodeExternalIpAddressKey]) === false) {
				$nodeExternalIpAddresses[$nodeExternalIpAddressKey] = $nodeIpAddressVersionExternalIpAddresses[$nodeIpAddressVersion][$parameters['data'][$nodeExternalIpAddressKey]] = $parameters['data'][$nodeExternalIpAddressKey];
			}
		}

		if (empty($nodeExternalIpAddresses) === true) {
			$response['message'] = 'Node must have an external IP address, please try again.';
			return $response;
		}

		if (($nodeIpAddressVersionExternalIpAddresses === _validateIpAddressVersions($nodeExternalIpAddresses)) === false) {
			$response['message'] = 'Invalid node external IP addresses, please try again.';
			return $response;
		}

		foreach ($nodeIpAddressVersionExternalIpAddresss as $nodeIpAddressVersion => $nodeIpAddressVersionExternalIpAddress) {
			$externalIpAddressType = _validateIpAddressTypes(current($nodeIpAddressVersionExternalIpAddress), $nodeIpAddressVersion);
			$externalIpAddressType = current($externalIpAddressType);
			$parameters['data']['external_ip_address_version_' . $nodeIpAddressVersion . '_type'] = 'public|reserved';
			$parameters['data']['external_ip_address_version_' . $nodeIpAddressVersion . '_usage'] = 'public_network|private_network|etc';
		}

		$nodeInternalIpAddresses = $nodeIpAddressVersionInternalIpAddresses = array();

		foreach ($nodeIpAddressVersions as $nodeIpAddressVersion) {
			$nodeInternalIpAddressKey = 'internal_ip_address_version_' . $nodeIpAddressVersion;

			if (empty($parameters['data'][$nodeInternalIpAddressKey]) === false) {
				$nodeInternalIpAddresses[$nodeInternalIpAddressKey] = $nodeIpAddressVersionInternalIpAddresses[$nodeIpVersion][$parameters['data'][$nodeInternalIpAddressKey]] = $parameters['data'][$nodeInternalIpAddressKey];
			}
		}

		if (
			(empty($nodeInternalIpAddresses) === false) &&
			(($nodeIpAddressVersionInternalIpAddresses === _validateIpAddressVersions($nodeInternalIpAddresses)) === false)
		) {
			$response['message'] = 'Invalid node internal IP addresses, please try again.';
			return $response;
		}

		foreach ($nodeIpAddressVersionInternalIpAddresses as $nodeIpAddressVersion => $nodeIpAddressVersionInternalIpAddress) {
			if ((_validateIpAddressTypes(current($nodeIpAddressVersionInternalIpAddress), $nodeIpAddressVersion) === 'public') === true) {
				$response['message'] = 'Node internal IPs must have a reserved IP address type, please try again.';
				return $response;
			}

			if (empty($nodeIpAddressVersionExternalIpAddresses[$nodeIpAddressVersion]) === true) {
				$response['message'] = 'Node internal IP addresses must have a matching external IP address, please try again.';
				return $response;
			}
		}

		$existingNodeParameters = array(
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'OR' => $nodeExternalIpAddresses
			)
		);
		$nodeIpAddresses = array_merge($nodeExternalIpAddresses, $nodeInternalIpAddresses);

		if (empty($parameters['data']['node_id']) === false) {
			$existingNodeParameters['where']['OR'] = array(
				$existingNodeParameters['where'],
				array(
					'node_id' => $parameters['data']['node_id'],
					'OR' => $nodeIpAddresses
				)
			);
		}

		$existingNode = _list($existingNodeParameters);

		if ($existingNode === false) {
			$response['message'] = 'Error listing data in nodes database, please try again.';
			return $response;
		}

		$existingNode = current($existingNode);

		if (empty($existingNode) === false) {
			$existingNodeIpAddresses = array_intersect_key($existingNode, array(
				'external_ip_address_version_4' => true,
				'external_ip_address_version_6' => true,
				'internal_ip_address_version_4' => true,
				'internal_ip_address_version_6' => true
			));

			foreach ($existingNodeIpAddresses as $existingNodeIpAddress) {
				if (in_array($existingNodeIpAddress, $nodeIpAddresses) === true) {
					$response['message'] = 'Node IP address ' . $existingNodeIpAddress . ' already in use, please try again.';
					break;
				}
			}

			return $response;
		}

		if (empty($parameters['data']['node_id']) === true) {
			$parameters['data']['authentication_token'] = substr(time() . str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz01234567890123456789', 10)), 0, rand(90, 100));
		}

		$nodeDataSaved = _save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'authentication_token' => true,
				'external_ip_address_version_4' => true,
				'external_ip_address_version_4_type' => true,
				'external_ip_address_version_4_usage' => true,
				'external_ip_address_version_6' => true,
				'external_ip_address_version_6_type' => true,
				'external_ip_address_version_6_usage' => true,
				'internal_ip_address_version_4' => true,
				'internal_ip_address_version_4_usage' => true,
				'internal_ip_address_version_6' => true,
				'internal_ip_address_version_6_usage' => true,
				'node_id' => true,
				'status_active' => true,
				'status_deployed' => true
			)),
			'in' => $parameters['databases']['nodes']
		));

		if ($nodeDataSaved === false) {
			$response['message'] = 'Error saving data in nodes database, please try again.';
			return $response;
		}

		$response['message'] = 'Node added successfully.';
		$node = _list(array(
			'in' => $parameters['databases']['nodes'],
			'where' => $nodeIps
		));

		if (empty($node) === true) {
			_delete(array(
				'in' => $parameters['databases']['nodes'],
				'where' => $nodeIpAddresses
			));
			$response['message'] = 'Error listing data in nodes database, please try again.';
			return $response;
		}

		$response['data'] = $node;
		$response['status_valid'] = true;
		return $response;
	}

	if ($parameters['action'] === 'add_node') {
		$response = _addNode($parameters, $response);
		_output($response);
	}
?>
