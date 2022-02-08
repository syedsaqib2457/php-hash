<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_reserved_internal_destinations',
		'nodes'
	), $parameters['system_databases'], $response);
	require_once('/var/www/nodecompute/system_action_add_node_reserved_internal_destination.php');
	require_once('/var/www/nodecompute/system_action_validate_ip_address_type.php');

	function _editNode($parameters, $response) {
		if (empty($parameters['system_user_authentication_token']) === false) {
			return $response
		}

		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'Node must have an ID, please try again.';
			return $response;
		}

		$node = _list(array(
			'data' => array(
				'external_ip_address_version_4',
				'external_ip_address_version_6',
				'internal_ip_address_version_4',
				'internal_ip_address_version_6',
				'node_id'
			),
			'in' => $parameters['system_databases']['nodes'],
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

		$nodeIds = array(
			$parameters['where']['id']
		);

		if (empty($node['node_id']) === false) {
			$nodeIds[] = $node['node_id'];
		}

		$existingNodeParameters = array(
			'data' => array(
				'external_ip_address_version_4',
				'external_ip_address_version_6',
				'internal_ip_address_version_4',
				'internal_ip_address_version_6'
			),
			'in' => $parameters['system_databases']['nodes'],
			'where' => array(
				'either' => $nodeExternalIpAddresses,
				'id !=' => $parameters['where']['id']
			)
		);
		$nodeIpAddresses = array_merge($nodeExternalIpAddresses, $nodeInternalIpAddresses);

		if (empty($parameters['data']['node_id']) === false) {
			$existingNodeParameters['where']['either'] = array(
				array(
					$existingNodeParameters['where']['either']
				),
				array(
					'either' => $nodeIpAddresses,
					'node_id' => $nodeIds
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

		$existingNodeReservedInternalDestinations = _list(array(
			'data' => array(
				'id',
				'ip_address_version',
				'node_id',
				'node_node_id'
			),
			'in' => $parameters['system_databases']['node_reserved_internal_destinations'],
			'where' => array(
				'ip_address' => $nodeIpAddresses,
				'node_node_id' => $nodeIds
			)
		), $response);

		foreach ($existingNodeReservedInternalDestinations as $existingNodeReservedInternalDestination) {
			$parameters['node'] = array(
				$existingNodeReservedInternalDestination['ip_address_version'] => array(
					'id' => $existingNodeReservedInternalDestination['node_id'],
					'node_id' => $existingNodeReservedInternalDestination['node_node_id']
				)
			);
			_addNodeReservedInternalDestination($parameters, $response);
			_delete(array(
				'in' => $parameters['system_databases']['node_reserved_internal_destinations'],
				'where' => array(
					'id' => $existingNodeReservedInternalDestination['id']
				)
			), $response);
		}

		unset($parameters['data']['created_timestamp']);
		unset($parameters['data']['modified_timestamp']);
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['nodes']
		), $response);
		$node = _list(array(
			'in' => $parameters['system_databases']['nodes'],
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
	}
?>
