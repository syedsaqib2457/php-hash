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
			$response['message'] = 'Invalid node process type, please try again.';
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

		$nodeIpAddressVersions = array(
			'4',
			'6'
		);

		foreach ($nodeIpAddressVersions as $nodeIpAddressVersion) {
			if (empty($node['external_ip_address_version_' . $nodeIpAddressVersion]) === false) {
				if (empty($parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion]) === true) {
					$response['message'] = 'Node process recursive DNS destination listening IP address version ' . $nodeIpAddressVersion . ' is required, please try again.';
					return $response;
				}

				if (empty($parameters['data']['port_number_version_' . $nodeIpAddressVersion]) === true) {
					$response['message'] = 'Node process recursive DNS destination port number version ' . $nodeIpAddressVersion . ' is required, please try again.';
					return $response;
				}

				if (is_string(_validatePortNumber($parameters['data']['port_number'])) === true) {
					$response['message'] = 'Invalid node process recursive DNS destination port number version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}
			}

			if (empty($parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion]) === false) {
				$parameters['data'][$nodeIpAddressKey] = strval(_validateIpAddressVersion($parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion], $nodeIpAddressVersion));

				if (empty($parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion]) === true) {
					$response['message'] = 'Invalid node process recursive DNS destination listening IP address version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}

				$listeningIpAddressNode = _list(array(
					'columns' => array(
						'id'
					),
					'in' => $parameters['databases']['nodes'],
					'where' => array(
						'either' => array(
							array(
								'either' => array(
									'id' => $node['id'],
									'node_id' => $node['node_id']
								),
								'internal_ip_version_' . $nodeIpAddressVersion => $parameters['data']['listening_ip_address_version_' . $nodeIpAddressVersion]
							)

						)
					)
				), $response);
				$listeningIpAddressNode = current($listeningIpAddressNode);

				// todo: set listening IP address node_id
				// todo: set internal IP if external IP is set when an internal IP exists
				// todo: validate source IPs
			}
		}

		$parameters['data']['node_node_id'] = $node['node_id'];
		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'id' => true,
				'node_id' => true,
				'node_node_id' => true,
				'node_process_type' => true
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
