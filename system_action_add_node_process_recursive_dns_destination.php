<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_recursive_dns_destinations',
		'nodes'
	), $parameters['system_databases'], $response);
	require_once('/var/www/ghostcompute/system_action_validate_ip_address_type.php');
	require_once('/var/www/ghostcompute/system_action_validate_port_number.php');

	function _addNodeProcessRecursiveDnsDestination($parameters, $response) {
		// todo: add correct logic for setting node recursive DNS destinations
		$parameters['data']['id'] = _createUniqueId();

		if (empty($parameters['data']['node_id']) === true) {
			$response['message'] = 'Node process recursive DNS destination must have a node ID, please try again.';
			return $response;
		}

		if (empty($parameters['data']['node_process_type']) === true) {
			$response['message'] = 'Node process recursive DNS destination must have a node process type, please try again.';
			return $response;
		}

		if (in_array(strval($parameters['data']['node_process_type']), array(
			'http_proxy',
			'recursive_dns', // todo: review node processing code to confirm recursive_dns node recursive DNS destinations are required
			'socks_proxy'
		)) === false) {
			$response['message'] = 'Invalid node process recursive DNS destination node process type, please try again.';
			return $response;
		}

		$node = _list(array(
			'data' => array(
				'external_ip_address_version_4',
				'external_ip_address_version_6',
				'id',
				'node_id'
			),
			'in' => $parameters['system_databases']['nodes'],
			'where' => array(
				'id' => $parameters['data']['node_id']
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node process node ID, please try again.';
			return $response;
		}


		$nodeNodeId = $node['id'];

		if (empty($node['node_id']) === false) {
			$nodeNodeId = $node['node_id'];
		}

		$nodeIpAddressVersions = array(
			'4',
			'6'
		);

		foreach ($nodeIpAddressVersions as $nodeIpAddressVersion) {
			unset($parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion . '_node_id']);
			unset($parameters['data']['source_ip_address_version_' . $nodeIpAddressVersion]);

			if (empty($node['external_ip_address_version_' . $nodeIpAddressVersion]) === false) {
				if (empty($parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion]) === true) {
					$response['message'] = 'Node process recursive DNS destination must have a listening IP address version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}

				if (empty($parameters['data']['listening_port_number_version_' . $nodeIpAddressVersion]) === true) {
					$response['message'] = 'Node process recursive DNS destination must have a listening port number version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}

				if (_validatePortNumber($parameters['data']['listening_port_number_version_' . $nodeIpAddressVersion]) === false) {
					$response['message'] = 'Invalid node process recursive DNS destination listening port number version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}
			} else {
				unset($parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion]);
				unset($parameters['data']['listening_port_number_version_' . $nodeIpAddressVersion]);
			}

			if (empty($parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion]) === false) {
				$parameters['data'][$parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion] = strval(_validateIpAddressVersion($parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion], $nodeIpAddressVersion));

				if ($parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion] === false) {
					$response['message'] = 'Invalid node process recursive DNS destination listening IP address version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}

				$listeningIpAddressNode = _list(array(
					'data' => array(
						'external_ip_address_version_' . $nodeIpAddressVersion,
						'id',
						'internal_ip_address_version_' . $nodeIpAddressVersion
					),
					'in' => $parameters['system_databases']['nodes'],
					'where' => array(
						'either' => array(
							array(
								array(
									'either' => array(
										array(
											'external_ip_address_version_' . $nodeIpAddressVersion => $parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion],
											'external_ip_address_version_' . $nodeIpAddressVersion . '_type !=' => 'public_network'
										),
										'internal_ip_address_version_' . $nodeIpAddressVersion => $parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion]
									)
								),
								array(
									'either' => array(
										'id' => $nodeNodeId,
										'node_id' => $nodeNodeId
									)
								)
							),
							array(
								'external_ip_address_version_' . $nodeIpAddressVersion => $parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion],
								'external_ip_address_version_' . $nodeIpAddressVersion . '_type' => 'public_network'
							)
						)
					)
				), $response);
				$listeningIpAddressNode = current($listeningIpAddressNode);

				if (empty($listeningIpAddressNode) === false) {
					$parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion] = $listeningIpAddressNode['external_ip_address_version_' . $nodeIpAddressVersion];
					$parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion . '_node_id'] = $listeningIpAddressNode['id'];
				}

				if (empty($listeningIpAddressNode['internal_ip_address_version_' . $nodeIpAddressVersion]) === false) {
					$parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion] = $listeningIpAddressNode['internal_ip_address_version_' . $nodeIpAddressVersion];
					$parameters['data']['source_ip_address_version_' . $nodeIpAddressVersion] = $listeningIpAddressNode['external_ip_address_version_' . $nodeIpAddressVersion];
				}
			}
		}

		$parameters['data']['node_node_id'] = $node['node_id'];
		$existingNodeProcessRecursiveDnsDestinationCount = _count(array(
			'in' => $parameters['system_databases']['node_process_recursive_dns_destinations'],
			'where' => array_intersect_key($parameters['data'], array(
				'listening_ip_address_version_4' => true,
				'listening_ip_address_version_6' => true,
				'node_id' => true,
				'node_process_type' => true,
				'source_ip_address_version_4' => true,
				'source_ip_address_version_6' => true
			))
		), $response);

		if (($existingNodeProcessRecursiveDnsDestinationCount > 0) === true) {
			$response['message'] = 'Node process recursive DNS destination already exists, please try again.';
			return $response;
		}

		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'id' => true,
				'listening_ip_address_version_4' => true,
				'listening_ip_address_version_4_node_id' => true,
				'listening_ip_address_version_6' => true,
				'listening_ip_address_version_6_node_id' => true,
				'listening_port_number_version_4' => true,
				'listening_port_number_version_6' => true,
				'node_id' => true,
				'node_node_id' => true,
				'node_process_type' => true,
				'source_ip_address_version_4' => true,
				'source_ip_address_version_6' => true
			)),
			'in' => $parameters['system_databases']['node_process_recursive_dns_destinations']
		), $response);
		$nodeProcessRecursiveDnsDestination = _list(array(
			'in' => $parameters['system_databases']['node_process_recursive_dns_destinations'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeProcessRecursiveDnsDestination = current($nodeProcessRecursiveDnsDestination);
		$response['data'] = $nodeProcessRecursiveDnsDestination;
		$response['message'] = 'Node process recursive DNS destination added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_process_recursive_dns_destination') === true) {
		$response = _addNodeProcessRecursiveDnsDestination($parameters, $response);
		_output($response);
	}
?>
