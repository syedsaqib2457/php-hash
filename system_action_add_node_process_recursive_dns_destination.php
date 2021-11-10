<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'node_process_recursive_dns_destinations',
		'nodes'
	), $parameters['databases'], $response);
	require_once('/var/www/ghostcompute/system_action_validate_ip_address_type.php');
	require_once('/var/www/ghostcompute/system_action_validate_port_number.php');

	function _addNodeProcessRecursiveDnsDestination($parameters, $response) {
		$parameters['data']['id'] = random_bytes(10) . time() . random_bytes(10);

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
			'load_balancer',
			'recursive_dns',
			'socks_proxy'
		)) === false) {
			$response['message'] = 'Invalid node process recursive DNS destination node process type, please try again.';
			return $response;
		}

		$node = _list(array(
			'columns' => array(
				'id',
				'node_id'
			),
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'id' => $parameters['data']['node_id']
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node process node ID, please try again.';
			return $response;
		}

		$nodeIds = array_filter($node);
		$nodeIpAddressVersions = array(
			'4',
			'6'
		);

		foreach ($nodeIpAddressVersions as $nodeIpAddressVersion) {
			if (empty($node['external_ip_address_version_' . $nodeIpAddressVersion]) === false) {
				if (empty($parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion]) === true) {
					$response['message'] = 'Node process recursive DNS destination must have a listening IP address version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}

				if (empty($parameters['data']['port_number_version_' . $nodeIpAddressVersion]) === true) {
					$response['message'] = 'Node process recursive DNS destination must have a port number version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}

				if (_validatePortNumber($parameters['data']['port_number_version_' . $nodeIpAddressVersion]) === false) {
					$response['message'] = 'Invalid node process recursive DNS destination port number version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}
			} else {
				unset($parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion]);
				unset($parameters['data']['port_number_version_' . $nodeIpAddressVersion]);
			}

			if (empty($parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion]) === false) {
				$parameters['data'][$parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion] = strval(_validateIpAddressVersion($parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion], $nodeIpAddressVersion));

				if ($parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion] === false) {
					$response['message'] = 'Invalid node process recursive DNS destination listening IP address version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}

				$listeningIpAddressNode = _list(array(
					'columns' => array(
						'external_ip_address_version_' . $nodeIpAddressVersion,
						'id',
						'internal_ip_address_version_' . $nodeIpAddressVersion
					),
					'in' => $parameters['databases']['nodes'],
					'where' => array(
						'either' => array(
							array(
								'either' => array(
									array(
										'external_ip_address_version_' . $nodeIpAddressVersion => $parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion],
										'external_ip_address_version_' . $nodeIpAddressVersion . '_type !=' => 'public_network'
									),
									'internal_ip_address_version_' . $nodeIpAddressVersion => $parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion]
								),
								'node_id' => $nodeIds
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
					$parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion . '_node_id'] = $listeningIpAddressNode['id'];
				}

				if (empty($listeningIpAddressNode['internal_ip_address_version_' . $nodeIpAddressVersion]) === false) {
					$parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion] = $listeningIpAddressNode['internal_ip_address_version_' . $nodeIpAddressVersion'];
					$parameters['data']['source_ip_address_version_' . $nodeIpAddressVersion] = $listeningIpAddressNode['external_ip_address_version_' . $nodeIpAddressVersion'];
				}
			} else {
				unset($parameters['data']['source_ip_address_version_' . $nodeIpAddressVersion]);
			}
		}

		$parameters['data']['node_node_id'] = $node['node_id'];
		$existingNodeProcessRecursiveDnsDestinationCount = _count(array(
			'in' => $parameters['databases']['node_process_recursive_dns_destinations'],
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
				'node_id' => true,
				'node_node_id' => true,
				'node_process_type' => true,
				'source_ip_address_version_4' => true,
				'source_ip_address_version_6' => true
			)),
			'in' => $parameters['databases']['node_process_recursive_dns_destinations']
		), $response);
		$nodeProcessRecursiveDnsDestination = _list(array(
			'in' => $parameters['databases']['node_process_recursive_dns_destinations'],
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
