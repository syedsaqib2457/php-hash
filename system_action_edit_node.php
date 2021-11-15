<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'nodes'
	), $parameters['databases'], $response);
	require_once('/var/www/ghostcompute/system_action_add_node_reserved_internal_destination.php');
	require_once('/var/www/ghostcompute/system_action_validate_ip_address_type.php');

	function _editNode($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'Node must have an ID, please try again.';
			return $response;
		}

		$node = _list(array(
			'columns' => array(
				'external_ip_address_version_4',
				'external_ip_address_version_6',
				'internal_ip_address_version_4',
				'internal_ip_address_version_6',
				'node_id'
			),
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node ID, please try again.';
			return $response;
		}

		$nodeExternalIpAddresses = $nodeInternalIpAddresses = array();
		$nodeIpAddressVersions = array(
			'4',
			'6'
		);

		foreach ($nodeIpAddressVersions as $nodeIpAddressVersion) {
			if (empty($parameters['data']['external_ip_address_version_' . $nodeIpAddressVersion]) === false) {
				$nodeExternalIpAddresses['external_ip_address_version_' . $nodeIpAddressVersion] = _validateIpAddressVersion($parameters['data']['external_ip_address_version_' . $nodeIpAddressVersion], $nodeIpAddressVersion);

				if ($nodeExternalIpAddresses['external_ip_address_version_' . $nodeIpAddressVersion] === false) {
					$response['message'] = 'Invalid node external IP address version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}

				$parameters['data']['external_ip_address_version_' . $nodeIpAddressVersion . '_type'] = _validateIpAddressType($nodeExternalIpAddresses['external_ip_address_version_' . $nodeIpAddressVersion], $nodeIpAddressVersion);
			}
		}

		if (empty($nodeExternalIpAddresses) === true) {
			$response['message'] = 'Node must have an external IP address, please try again.';
			return $response;
		}

		foreach ($nodeIpAddressVersions as $nodeIpAddressVersion) {
			if (empty($parameters['data']['internal_ip_address_version_' . $nodeIpAddressVersion]) === false) {
				$nodeInternalIpAddresses['internal_ip_address_version_' . $nodeIpAddressVersion] = _validateIpAddressVersion($parameters['data']['internal_ip_address_version_' . $nodeIpAddressVersion], $nodeIpAddressVersion);

				if ($nodeInternalIpAddresses[$nodeIpAddressVersion] === false) {
					$response['message'] = 'Invalid node internal IP address version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}

				if (empty($nodeExternalIpAddresses['external_ip_address_version_' . $nodeIpAddressVersion]) === true) {
					$response['message'] = 'Node internal IP address version ' . $nodeIpAddressVersion . '  must have a matching external IP address, please try again.';
					return $response;
				}

				$parameters['data']['internal_ip_address_version_' . $nodeIpAddressVersion . '_type'] = _validateIpAddressType($nodeInternalIpAddresses['internal_ip_address_version_' . $nodeIpAddressVersion], $nodeIpAddressVersion);

				if (($parameters['data']['internal_ip_address_version_' . $nodeIpAddressVersion] === 'public_network') === true) {
					$response['message'] = 'Node internal IP address version ' . $nodeIpAddressVersion . ' must be a reserved IP address, please try again.';
					return $response;
				}
			}
		}

		$existingNodeParameters = array(
			'columns' => array(
				'external_ip_address_version_4',
				'external_ip_address_version_6',
				'internal_ip_address_version_4',
				'internal_ip_address_version_6'
			),
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'either' => $nodeExternalIpAddresses,
				'id !=' => $parameters['where']['id']
			)
		);
		$nodeIpAddresses = array_merge($nodeExternalIpAddresses, $nodeInternalIpAddresses);

		if (empty($parameters['data']['node_id']) === false) {
			$existingNodeParameters['where']['either'] = array(
				array(
					$existingNodeParameters['where']['either'],
				),
				array(
					'either' => $nodeIpAddresses,
					'node_id' => $parameters['data']['node_id']
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
					break;
				}
			}

			return $response;
		}

		// todo: overwrite assigned internal reserved IPs with edited node IPs

		foreach ($nodeIpAddressVersions as $nodeIpAddressVersion) {
			if (empty($parameters['data']['external_ip_address_version_' . $nodeIpAddressVersion]) === false) {
				$parameters['node'] = array(
					$nodeIpAddressVersion => array(
						'id' => $parameters['data']['id'],
						'node_id' => $parameters['data']['node_id']
					)
				);
				_addNodeReservedInternalDestination($parameters, $response);
			}
		}

		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'external_ip_address_version_4' => true,
				'external_ip_address_version_4_type' => true,
				'external_ip_address_version_6' => true,
				'external_ip_address_version_6_type' => true,
				'id' => true,
				'internal_ip_address_version_4' => true,
				'internal_ip_address_version_4_type' => true,
				'internal_ip_address_version_6' => true,
				'internal_ip_address_version_6_type' => true
			)),
			'in' => $parameters['databases']['nodes']
		), $response);
		$node = _list(array(
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$node = current($node);
		$response['data'] = $node;
		$response['message'] = 'Node edited successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'edit_node') === true) {
		$response = _editNode($parameters, $response);
		_output($response);
	}
?>
