<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		$databases['nodes']
	), $parameters['databases'], $response);
	require_once('/var/www/ghostcompute/system_action_validate_ip_address_types.php');

	function _addNode($parameters, $response) {
		$parameters['data']['status_active'] = $parameters['data']['status_deployed'] = false;

		if (empty($parameters['data']['node_id']) === false) {
			$nodeNode = _list(array(
				'in' => $parameters['databases']['nodes'],
				'where' => array(
					'id' => $parameters['data']['node_id']
				)
			), $response);
			$nodeNode = current($nodeNode);

			if (empty($nodeNode) === true) {
				$response['message'] = 'Invalid node ID, please try again.';
				return $response;
			}

			$parameters['data']['status_deployed'] = $nodeNode['status_deployed'];

			if (empty($nodeNode['node_id']) === false) {
				$$parameters['data']['node_id'] = $nodeNode['node_id'];
			}
		}

		$nodeExternalIpAddresses = $nodeInternalIpAddresses = array();
		$nodeIpAddressVersions = array(
			4,
			6
		);

		foreach ($nodeIpAddressVersions as $nodeIpAddressVersion) {
			$nodeExternalIpAddressKey = 'external_ip_address_version_' . $nodeIpAddressVersion;

			if (empty($parameters['data'][$nodeExternalIpAddressKey]) === false) {
				$nodeExternalIpAddresses[$nodeExternalIpAddressKey] = _validateIpAddressVersion($parameters['data'][$nodeExternalIpAddressKey], $nodeIpAddressVersion);

				if ($nodeExternalIpAddresses[$nodeExternalIpAddressKey] === false) {
					$response['message'] = 'Invalid node external IP address version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}

				$parameters['data']['external_ip_address_version_' . $nodeIpAddressVersion . '_type'] = _validateIpAddressType($nodeExternalIpAddresses[$nodeExternalIpAddressKey], $nodeIpAddressVersion);
			}
		}

		if (empty($nodeExternalIpAddresses) === true) {
			$response['message'] = 'Node must have an external IP address, please try again.';
			return $response;
		}

		foreach ($nodeIpAddressVersions as $nodeIpAddressVersion) {
			$nodeInternalIpAddressKey = 'internal_ip_address_version_' . $nodeIpAddressVersion;

			if (empty($parameters['data'][$nodeInternalIpAddressKey]) === false) {
				$nodeInternalIpAddresses[$nodeInternalIpAddressKey] = _validateIpAddressVersion($parameters['data'][$nodeInternalIpAddressKey], $nodeIpAddressVersion);

				if ($nodeInternalIpAddresses[$nodeIpAddressVersion] === false) {
					$response['message'] = 'Invalid node internal IP address version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}

				if (empty($nodeExternalIpAddresses['external_ip_address_version_' . $nodeIpAddressVersion]) === true) {
					$response['message'] = 'Node internal IP address version ' . $nodeIpAddressVersion . '  must have a matching external IP address, please try again.';
					return $response;
				}

				$parameters['data']['internal_ip_address_version_' . $nodeIpAddressVersion . '_type'] = _validateIpAddressType($nodeInternalIpAddresses[$nodeIpAddressVersion], $nodeIpAddressVersion);

				if (($parameters['data']['internal_ip_address_version_' . $nodeIpAddressVersion] === 'public_network') === true) {
					$response['message'] = 'Node internal IP address version ' . $nodeIpAddressVersion . ' must be a reserved IP address, please try again.';
					return $response;
				}
			}
		}

		$existingNodeParameters = array(
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'either' => $nodeExternalIpAddresses
			)
		);
		$nodeIpAddresses = array_merge($nodeExternalIpAddresses, $nodeInternalIpAddresses);

		if (empty($parameters['data']['node_id']) === false) {
			$existingNodeParameters['where']['either'] = array(
				$existingNodeParameters['where'],
				array(
					'either' => $nodeIpAddresses,
					'node_id' => $parameters['data']['node_id']
				)
			);
		}

		$existingNode = _list($existingNodeParameters, $response);
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

		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'authentication_token' => true,
				'external_ip_address_version_4' => true,
				'external_ip_address_version_4_type' => true,
				'external_ip_address_version_6' => true,
				'external_ip_address_version_6_type' => true,
				'internal_ip_address_version_4' => true,
				'internal_ip_address_version_4_type' => true,
				'internal_ip_address_version_6' => true,
				'internal_ip_address_version_6_type' => true,
				'node_id' => true,
				'status_active' => true,
				'status_deployed' => true
			)),
			'in' => $parameters['databases']['nodes']
		), $response);
		$node = _list(array(
			'in' => $parameters['databases']['nodes'],
			'where' => $nodeIpAddresses
		), $response);
		$node = current($node);
		$response['data'] = $node;
		$response['message'] = 'Node added successfully.';
		$response['status_valid'] = true;
		return $response;
	}

	if ($parameters['action'] === 'add_node') {
		$response = _addNode($parameters, $response);
		_output($response);
	}
?>
