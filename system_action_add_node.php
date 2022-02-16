<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_reserved_internal_sources',
		'nodes'
	), $parameters['system_databases'], $response);
	require_once('/var/www/nodecompute/system_action_add_node_reserved_internal_destination.php');
	require_once('/var/www/nodecompute/system_action_validate_ip_address_type.php');

	function _addNode($parameters, $response) {
		$parameters['data']['activated_status'] = $parameters['data']['deployed_status'] = '0';

		if (empty($parameters['data']['node_id']) === false) {
			$nodeNode = _list(array(
				'data' => array(
					'authentication_token',
					'deployed_status',
					'id',
					'node_id'
				),
				'in' => $parameters['system_databases']['nodes'],
				'where' => array(
					'id' => $parameters['data']['node_id']
				)
			), $response);
			$nodeNode = current($nodeNode);

			if (empty($nodeNode) === true) {
				$response['message'] = 'Invalid node ID, please try again.';
				return $response;
			}

			$parameters['data']['authentication_token'] = $nodeNode['authentication_token'];
			$parameters['data']['deployed_status'] = $nodeNode['deployed_status'];
			$parameters['data']['node_id'] = $nodeNode['id'];
		} else {
			$parameters['data']['authentication_token'] = _createUniqueId();
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

				if (
					(empty($parameters['data']['node_id']) === true) &&
					(($parameters['data']['external_ip_address_version_' . $nodeIpAddressVersionNumber . '_type'] === 'public_network') === false) &&
					(($parameters['endpoint_destination_ip_address_type'] === 'public_network') === true)
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
			'in' => $parameters['system_databases']['nodes'],
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
					$response['message'] = 'Node already exists with the same IP address ' . $existingNodeIpAddress . ', please try again.';
					return $response;
				}
			}
		}

		$parameters['data']['id'] = _createUniqueId();

		foreach ($nodeIpAddressVersionNumbers as $nodeIpAddressVersionNumber) {
			if (empty($parameters['data']['external_ip_address_version_' . $nodeIpAddressVersionNumber]) === false) {
				$parameters['node'] = array(
					$nodeIpAddressVersionNumber => array(
						'id' => $parameters['data']['id'],
						'node_id' => $parameters['data']['node_id']
					)
				);

				if (empty($parameters['node'][$nodeIpAddressVersionNumber]['node_id']) === true) {
					$parameters['node'][$nodeIpAddressVersionNumber]['node_id'] = $parameters['data']['id'];
				}

				_addNodeReservedInternalDestination($parameters, $response);
			}
		}

		if (empty($parameters['data']['node_id']) === true) {
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
					array(
						'ip_address' => '198.18.0.0',
						'ip_address_block_length' => '15'
					),
					array(
						'ip_address' => '198.51.100.0',
						'ip_address_block_length' => '24'
					),
					array(
						'ip_address' => '203.0.113.0',
						'ip_address_block_length' => '24'
					),
					array(
						'ip_address' => '224.0.0.0',
						'ip_address_block_length' => '4'
					),
					array(
						'ip_address' => '233.252.0.0',
						'ip_address_block_length' => '24'
					),
					array(
						'ip_address' => '240.0.0.0',
						'ip_address_block_length' => '4'
					),
					array(
						'ip_address' => '255.255.255.255',
						'ip_address_block_length' => '32'
					)
				),
				'6' => array(
					array(
						'ip_address' => '0000:0000:0000:0000:0000:0000:0000:0000',
						'ip_address_block_length' => '128'
					),
					array(
						'ip_address' => '0000:0000:0000:0000:0000:0000:0000:0001',
						'ip_address_block_length' => '128'
					),
					array(
						'ip_address' => '0000:0000:0000:0000:0000:ffff:0000:0000',
						'ip_address_block_length' => '96'
					),
					array(
						'ip_address' => '0000:0000:0000:0000:ffff:0000:0000:0000',
						'ip_address_block_length' => '96'
					),
					array(
						'ip_address' => '0064:ff9b:0000:0000:0000:0000:0000:0000',
						'ip_address_block_length' => '96'
					),
					array(
						'ip_address' => '0064:ff9b:0001:0000:0000:0000:0000:0000',
						'ip_address_block_length' => '48'
					),
					array(
						'ip_address' => '0100:0000:0000:0000:0000:0000:0000:0000',
						'ip_address_block_length' => '64'
					),
					array(
						'ip_address' => '2001:0000:0000:0000:0000:0000:0000:0000',
						'ip_address_block_length' => '32'
					),
					array(
						'ip_address' => '2001:0020:0000:0000:0000:0000:0000:0000',
						'ip_address_block_length' => '28'
					),
					array(
						'ip_address' => '2001:0db8:0000:0000:0000:0000:0000:0000',
						'ip_address_block_length' => '32'
					),
					array(
						'ip_address' => '2002:0000:0000:0000:0000:0000:0000:0000',
						'ip_address_block_length' => '16'
					),
					array(
						'ip_address' => 'fc00:0000:0000:0000:0000:0000:0000:0000',
						'ip_address_block_length' => '7'
					),
					array(
						'ip_address' => 'fe80:0000:0000:0000:0000:0000:0000:0000',
						'ip_address_block_length' => '10'
					),
					array(
						'ip_address' => 'ff00:0000:0000:0000:0000:0000:0000:0000',
						'ip_address_block_length' => '8'
					)
				)
			);

			foreach ($nodeReservedInternalSources as $nodeReservedInternalSourceIpAddressVersionNumber => $nodeReservedInternalSources) {
				foreach ($nodeReservedInternalSources as $nodeReservedInternalSource) {
					if (empty($nodeExternalIpAddresses['external_ip_address_version_' . $nodeReservedInternalSourceIpAddressVersionNumber]) === false) {
						$nodeReservedInternalSourceData[] = array(
							'id' => _createUniqueId(),
							'ip_address' => $nodeReservedInternalSource['ip_address'],
							'ip_address_block_length' => $nodeReservedInternalSource['ip_address_block_length'],
							'ip_address_version_number' => $nodeReservedInternalSourceIpAddressVersionNumber,
							'node_id' => $parameters['data']['id']
						);
					}
				}
			}

			_save(array(
				'data' => $nodeReservedInternalSourceData,
				'in' => $parameters['system_databases']['node_reserved_internal_sources']
			), $response);
		}

		$parameters['data']['processed_status'] = $parameters['data']['processing_progress_override_status'] = $parameters['data']['processing_progress_percentage'] = $parameters['data']['processing_status'] = '0';
		$parameters['data']['processing_progress_checkpoint'] = 'processing_queued';
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['nodes']
		), $response);
		$node = _list(array(
			'in' => $parameters['system_databases']['nodes'],
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
	}
?>
