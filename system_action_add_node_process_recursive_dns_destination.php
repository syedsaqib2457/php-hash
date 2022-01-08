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

		$nodeIpAddressVersionNumbers = array(
			'4',
			'6'
		);

		foreach ($nodeIpAddressVersionNumbers as $nodeIpAddressVersionNumber) {
			unset($parameters['data']['destination_ip_address_version_' . $nodeIpAddressVersionNumber . '_node_id']);
			unset($parameters['data']['source_ip_address_version_' . $nodeIpAddressVersionNumber]);

			if (empty($node['external_ip_address_version_' . $nodeIpAddressVersionNumber]) === false) {
				if (empty($parameters['data']['destination_ip_address_version_' . $nodeIpAddressVersionNumber]) === true) {
					$response['message'] = 'Node process recursive DNS destination must have a destination IP address version ' . $nodeIpAddressVersionNumber . ', please try again.';
					return $response;
				}

				if (empty($parameters['data']['port_number_version_' . $nodeIpAddressVersionNumber]) === true) {
					$response['message'] = 'Node process recursive DNS destination must have a port number version ' . $nodeIpAddressVersionNumber . ', please try again.';
					return $response;
				}

				if (_validatePortNumber($parameters['data']['port_number_version_' . $nodeIpAddressVersionNumber]) === false) {
					$response['message'] = 'Invalid node process recursive DNS destination port number version ' . $nodeIpAddressVersionNumber . ', please try again.';
					return $response;
				}
			} else {
				unset($parameters['data']['destination_ip_address_version_' . $nodeIpAddressVersionNumber]);
				unset($parameters['data']['port_number_version_' . $nodeIpAddressVersionNumber]);
			}

			if (empty($parameters['data']['destination_ip_address_version_' . $nodeIpAddressVersionNumber]) === false) {
				$parameters['data'][$parameters['data']['destination_ip_address_version_' . $nodeIpAddressVersionNumber] = _validateIpAddressVersionNumber($parameters['data']['destination_ip_address_version_' . $nodeIpAddressVersionNumber], $nodeIpAddressVersionNumber);

				if ($parameters['data']['destination_ip_address_version_' . $nodeIpAddressVersionNumber] === false) {
					$response['message'] = 'Invalid node process recursive DNS destination destination IP address version ' . $nodeIpAddressVersionNumber . ', please try again.';
					return $response;
				}

				$destinationIpAddressNode = _list(array(
					'data' => array(
						'external_ip_address_version_' . $nodeIpAddressVersionNumber,
						'id',
						'internal_ip_address_version_' . $nodeIpAddressVersionNumber
					),
					'in' => $parameters['system_databases']['nodes'],
					'where' => array(
						'either' => array(
							array(
								array(
									'either' => array(
										array(
											'external_ip_address_version_' . $nodeIpAddressVersionNumber => $parameters['data']['destination_ip_address_version_' . $nodeIpAddressVersionNumber],
											'external_ip_address_version_' . $nodeIpAddressVersionNumber . '_type !=' => 'public_network'
										),
										'internal_ip_address_version_' . $nodeIpAddressVersionNumber => $parameters['data']['destination_ip_address_version_' . $nodeIpAddressVersionNumber]
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
								'external_ip_address_version_' . $nodeIpAddressVersionNumber => $parameters['data']['destination_ip_address_version_' . $nodeIpAddressVersionNumber],
								'external_ip_address_version_' . $nodeIpAddressVersionNumber . '_type' => 'public_network'
							)
						)
					)
				), $response);
				$destinationIpAddressNode = current($destinationIpAddressNode);

				if (empty($destinationIpAddressNode) === false) {
					$parameters['data']['destination_ip_address_version_' . $nodeIpAddressVersionNumber . '_node_id'] = $destinationIpAddressNode['id'];
					$portNumberNodeProcessCount = _count(array(
						'in' => $parameters['system_databases']['node_processes'],
						'where' => array(
							'either' => array(
								'id' => $destinationIpAddressNode['id'],
								'node_id' => $destinationIpAddressNode['id']
							),
							'port_number' => $parameters['data']['port_number_version_' . $nodeIpAddressVersionNumber],
							'type' => 'recursive_dns'
						)
					));

					if (($portNumberNodeProcessCount <= 0) === true) {
						$response['message'] = 'Node process recursive DNS destination port number must have a matching recursive DNS node process port number, please try again.';
						return $response;
					}
				}

				if (empty($destinationIpAddressNode['internal_ip_address_version_' . $nodeIpAddressVersionNumber]) === false) {
					$parameters['data']['destination_ip_address_version_' . $nodeIpAddressVersionNumber] = $destinationIpAddressNode['internal_ip_address_version_' . $nodeIpAddressVersionNumber];
					$parameters['data']['source_ip_address_version_' . $nodeIpAddressVersionNumber] = $destinationIpAddressNode['external_ip_address_version_' . $nodeIpAddressVersionNumber];
				}
			}
		}

		$parameters['data']['node_node_id'] = $node['node_id'];
		$existingNodeProcessRecursiveDnsDestinationCount = _count(array(
			'in' => $parameters['system_databases']['node_process_recursive_dns_destinations'],
			'where' => array_intersect_key($parameters['data'], array(
				'destination_ip_address_version_4' => true,
				'destination_ip_address_version_6' => true,
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
				'destination_ip_address_version_4' => true,
				'destination_ip_address_version_4_node_id' => true,
				'destination_ip_address_version_6' => true,
				'destination_ip_address_version_6_node_id' => true,
				'node_id' => true,
				'node_node_id' => true,
				'node_process_type' => true,
				'port_number_version_4' => true,
				'port_number_version_6' => true,
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
