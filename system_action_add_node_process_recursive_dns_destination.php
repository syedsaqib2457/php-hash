<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'node_process_recursive_dns_destinations'
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
				'external_ip_address_version_4',
				'external_ip_address_version_4_type',
				'external_ip_address_version_6',
				'external_ip_address_version_6_type',
				'internal_ip_address_version_4',
				'internal_ip_address_version_4_type',
				'internal_ip_address_version_6',
				'internal_ip_address_version_6_type',
				'node_id',
				'node_node_id'
			),
			'in' => $parameters['databases']['node_processes'],
			'where' => array(
				'node_id' => $parameters['data']['node_id']
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node process node ID, please try again.';
			return $response;
		}

		$nodeIpAddressReachTypes = array(
			'external',
			'internal'
		);
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

				// todo: validate port
			}

			foreach ($nodeIpAddressReachTypes as $nodeIpAddressReachType) {
				$nodeIpAddressKey = $nodeIpAddressReachType . '_listening_ip_address_version_' . $nodeIpAddressVersion;

				if (empty($parameters['data'][$nodeIpAddressKey]) === false) {
					$parameters['data'][$nodeIpAddressKey] = strval(_validateIpAddressVersion($parameters['data'][$nodeIpAddressKey], $nodeIpAddressVersion));

					if (empty($parameters['data'][$nodeIpAddressKey]) === true) {
						$response['message'] = 'Invalid node process recursive DNS destination listening IP address version ' . $nodeIpAddressVersion . ', please try again.';
						return $response;
					}

					// todo: set listening IP address node_id
					// todo: set internal IP if external IP is set when an internal IP exists
				}

				// todo: validate source IPs
			}
		}

		$parameters['data']['node_node_id'] = $node['node_node_id'];
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
