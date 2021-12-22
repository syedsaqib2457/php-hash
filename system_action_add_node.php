<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_reserved_internal_sources',
		'nodes'
	), $parameters['system_databases'], $response);
	require_once('/var/www/ghostcompute/system_action_add_node_reserved_internal_destination.php');
	require_once('/var/www/ghostcompute/system_action_validate_ip_address_type.php');

	function _addNode($parameters, $response) {
		$parameters['data']['activated_status'] = $parameters['data']['deployed_status'] = '0';

		if (empty($parameters['data']['node_id']) === false) {
			$nodeNode = _list(array(
				'data' => array(
					'deployed_status',
					'id',
					'node_id'
				),
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

			$parameters['data']['deployed_status'] = $nodeNode['deployed_status'];
			$parameters['data']['id'] = $nodeNode['id'];

			if (empty($nodeNode['node_id']) === false) {
				$parameters['data']['node_id'] = $nodeNode['node_id'];
			}
		} else {
			$parameters['data']['authentication_token'] = substr(time() . str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz01234567890123456789', 10)), 0, rand(90, 100));
			$parameters['data']['node_id'] = null;
		}

		$nodeExternalIpAddresses = $nodeInternalIpAddresses = array();
		$nodeIpAddressVersionNumbers = array(
			'4',
			'6'
		);

		foreach ($nodeIpAddressVersionNumbers as $nodeIpAddressVersionNumber) {
			if (empty($parameters['data']['external_ip_address_version_' . $nodeIpAddressVersionNumber]) === false) {
				$nodeExternalIpAddresses['external_ip_address_version_' . $nodeIpAddressVersionNumber] = _validateIpAddressVersionNumber($parameters['data']['external_ip_address_version_' . $nodeIpAddressVersionNumber], $nodeIpAddressVersionNumber);

				if ($nodeExternalIpAddresses['external_ip_address_version_' . $nodeIpAddressVersionNumber] === false) {
					$response['message'] = 'Invalid node external IP address version ' . $nodeIpAddressVersionNumber . ', please try again.';
					return $response;
				}

				$parameters['data']['external_ip_address_version_' . $nodeIpAddressVersionNumber . '_type'] = _validateIpAddressType($nodeExternalIpAddresses['external_ip_address_version_' . $nodeIpAddressVersionNumber], $nodeIpAddressVersionNumber);
			}
		}

		if (empty($nodeExternalIpAddresses) === true) {
			$response['message'] = 'Node must have an external IP address, please try again.';
			return $response;
		}

		foreach ($nodeIpAddressVersionNumbers as $nodeIpAddressVersionNumber) {
			if (empty($parameters['data']['internal_ip_address_version_' . $nodeIpAddressVersionNumber]) === false) {
				$nodeInternalIpAddresses['internal_ip_address_version_' . $nodeIpAddressVersionNumber] = _validateIpAddressVersionNumber($parameters['data']['internal_ip_address_version_' . $nodeIpAddressVersionNumber], $nodeIpAddressVersionNumber);

				if ($nodeInternalIpAddresses[$nodeIpAddressVersionNumber] === false) {
					$response['message'] = 'Invalid node internal IP address version ' . $nodeIpAddressVersionNumber . ', please try again.';
					return $response;
				}

				if (empty($nodeExternalIpAddresses['external_ip_address_version_' . $nodeIpAddressVersionNumber]) === true) {
					$response['message'] = 'Node internal IP address version ' . $nodeIpAddressVersionNumber . '  must have a matching external IP address, please try again.';
					return $response;
				}

				$parameters['data']['internal_ip_address_version_' . $nodeIpAddressVersionNumber . '_type'] = _validateIpAddressType($nodeInternalIpAddresses['internal_ip_address_version_' . $nodeIpAddressVersionNumber], $nodeIpAddressVersionNumber);

				if (($parameters['data']['internal_ip_address_version_' . $nodeIpAddressVersionNumber] === 'public_network') === true) {
					$response['message'] = 'Node internal IP address version ' . $nodeIpAddressVersionNumber . ' must be a reserved IP address, please try again.';
					return $response;
				}
			}
		}

		$existingNodeParameters = array(
			'data' => array(
				'external_ip_address_version_4',
				'external_ip_address_version_6',
				'internal_ip_address_version_4',
				'internal_ip_address_version_6'
			),
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
			$existingNodeIpAddresses = array_filter($existingNode);

			foreach ($existingNodeIpAddresses as $existingNodeIpAddress) {
				if (in_array($existingNodeIpAddress, $nodeIpAddresses) === true) {
					$response['message'] = 'Node IP address ' . $existingNodeIpAddress . ' already exists, please try again.';
					break;
				}
			}

			return $response;
		}

		$parameters['data']['id'] = random_bytes(10) . time() . random_bytes(10);

		foreach ($nodeIpAddressVersionNumbers as $nodeIpAddressVersionNumber) {
			if (empty($parameters['data']['external_ip_address_version_' . $nodeIpAddressVersionNumber]) === false) {
				$parameters['node'] = array(
					$nodeIpAddressVersionNumber => array(
						'id' => $parameters['data']['id'],
						'node_id' => $parameters['data']['node_id']
					)
				);
				_addNodeReservedInternalDestination($parameters, $response);
			}
		}

		$nodeReservedInternalSourceData = array();
		$nodeReservedInternalSources = array(
			'4' => array(
				array(
					'ip_address' => '0.0.0.0',
					'ip_address_block_length' => '8'
				),
				array(
					'ip_address' => '10.0.0.0',
					'ip_address_block_length' => '8'
				),
				array(
					'ip_address' => '100.64.0.0',
					'ip_address_block_length' => '10'
				),
				array(
					'ip_address' => '127.0.0.0',
					'ip_address_block_length' => '8'
				),
				array(
					'ip_address' => '169.254.0.0',
					'ip_address_block_length' => '16'
				),
				array(
					'ip_address' => '172.16.0.0',
					'ip_address_block_length' => '12'
				),
				array(
					'ip_address' => '192.0.0.0',
					'ip_address_block_length' => '24'
				),
				array(
					'ip_address' => '192.0.2.0',
					'ip_address_block_length' => '24'
				),
				array(
					'ip_address' => '192.88.99.0',
					'ip_address_block_length' => '24'
				),
				array(
					'ip_address' => '192.168.0.0',
					'ip_address_block_length' => '16'
				),
				// todo: add default node reserved internal destinations (link-local IPs can be deleted for GCP nodes after adding)
			)
		);

		foreach ($nodeReservedInternalSources as $nodeReservedInternalSourceIpAddressVersionNumber => $nodeReservedInternalSource) {
			$nodeReservedInternalSourceData[] = array(
				'id' => random_bytes(10) . time() . random_bytes(10),
				'ip_address' => $nodeReservedInternalSource['ip_address'],
				'ip_address_block_length' => $nodeReservedInternalSource['ip_address_block_length'],
				'ip_address_version_number' => $nodeReservedInternalSourceIpAddressVersionNumber,
				'node_id' => $parameters['data']['id']
			);
		}

		_save(array(
			'data' => $nodeReservedInternalSourceData,
			'in' => $parameters['databases']['node_reserved_internal_sources']
		), $response);
		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'activated_status' => true,
				'authentication_token' => true,
				'deployed_status' => true,
				'external_ip_address_version_4' => true,
				'external_ip_address_version_4_type' => true,
				'external_ip_address_version_6' => true,
				'external_ip_address_version_6_type' => true,
				'id' => true,
				'internal_ip_address_version_4' => true,
				'internal_ip_address_version_4_type' => true,
				'internal_ip_address_version_6' => true,
				'internal_ip_address_version_6_type' => true,
				'node_id' => true
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
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node') === true) {
		$response = _addNode($parameters, $response);
		_output($response);
	}
?>
