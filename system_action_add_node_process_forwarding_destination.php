<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_forwarding_destinations',
		'nodes'
	), $parameters['system_databases'], $response);
	require_once('/var/www/ghostcompute/system_action_validate_hostname.php');
	require_once('/var/www/ghostcompute/system_action_validate_port_number.php');

	function _addNodeProcessForwardingDestination($parameters, $response) {
		if (empty($parameters['data']['node_id']) === true) {
			$response['message'] = 'Node process forwarding destination must have a node ID, please try again.';
			return $response;
		}

		if (
			(empty($parameters['data']['node_process_type']) === true) ||
			(is_string($parameters['data']['node_process_type']) === false)
		) {
			$response['message'] = 'Node process forwarding destination must have a node process type, please try again.';
			return $response;
		}

		if (in_array($parameters['data']['node_process_type'], array(
			'http_proxy',
			'socks_proxy'
		)) === false) {
			$response['message'] = 'Invalid node process forwarding destination node process type, please try again.';
			return $response;
		}

		$node = _list(array(
			'data' => array(
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

		$nodeIpAddressVersions = array(
			'4',
			'6'
		);

		foreach ($nodeIpAddressVersions as $nodeIpAddressVersion) {
			if (empty($parameters['data']['hostname_version_' . $nodeIpAddressVersion]) === false) {
				$parameters['data']['hostname_version_' . $nodeIpAddressVersion] = _validateHostname($parameters['data']['hostname_version_' . $nodeIpAddressVersion], true);

				if ($parameters['data']['hostname_version_' . $nodeIpAddressVersion] === false) {
					$response['message'] = 'Invalid node process forwarding destination hostname version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}

				if (empty($parameters['data']['port_number_version_' . $nodeIpAddressVersion]) === true) {
					$response['message'] = 'Node process forwarding destination must have a port number version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}

				if (_validatePortNumber($parameters['data']['port_number_version_' . $nodeIpAddressVersion]) === false) {
					$response['message'] = 'Invalid node process forwarding destination port number version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}
			} else {
				unset($parameters['data']['hostname_version_' . $nodeIpAddressVersion]);
				unset($parameters['data']['port_number_version_' . $nodeIpAddressVersion]);
			}
		}

		$parameters['data']['node_node_id'] = $node['node_id'];
		$existingNodeProcessForwardingDestinationCount = _count(array(
			'in' => $parameters['system_databases']['node_process_recursive_dns_destinations'],
			'where' => array_intersect_key($parameters['data'], array(
				'node_id' => true,
				'node_process_type' => true
			))
		), $response);

		if (($existingNodeProcessForwardingDestinationCount > 0) === true) {
			$response['message'] = 'Node process forwarding destination already exists, please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'hostname_version_4' => true,
				'hostname_version_6' => true,
				'id' => true,
				'node_id' => true,
				'node_node_id' => true,
				'node_process_type' => true,
				'port_number_version_4' => true,
				'port_number_version_6' => true
			)),
			'in' => $parameters['system_databases']['node_process_forwarding_destinations']
		), $response);
		$nodeProcessForwardingDestination = _list(array(
			'in' => $parameters['system_databases']['node_process_forwarding_destinations'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeProcessForwardingDestination = current($nodeProcessForwardingDestination);
		$response['data'] = $nodeProcessForwardingDestination;
		$response['message'] = 'Node process forwarding destination added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_process_forwarding_destination') === true) {
		$response = _addNodeProcessForwardingDestination($parameters, $response);
		_output($response);
	}
?>
