<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += array(
		'node_processes' => $settings['databases']['node_processes'],
		'node_recursive_dns_destinations' => $settings['databases']['node_recursive_dns_destinations'],
		'node_reserved_internal_destinations' => $settings['databases']['node_reserved_internal_destinations'],
		'nodes' => $settings['databases']['nodes']
	);
	$parameters['databases'] = _connect($parameters['databases']);

	if (
		(empty($parameters['databases']['message']) === false) &&
		(is_string($parameters['databases']['message']) === true)
	) {
		$response['message'] = $parameters['databases']['message'];
		_output($response);
	}

	// todo: include _detectIpType
	// todo: include _sanitizeIps
	require_once('/var/www/ghostcompute/system_action_add_node_reserved_internal_destination.php');

	function _addNode($parameters, $response) {
		if (empty($parameters['data']['node_id']) === false) {
			$nodeNode = _list(array(
				'in' => $parameters['databases']['nodes'],
				'where' => array(
					'id' => $parameters['data']['node_id']
				)
			));

			if ($nodeNode === false) {
				$response['message'] = 'Error listing data in nodes database, please try again.';
				return $response;
			}

			$nodeNode = current($nodeNode);

			if (empty($nodeNode) === true) {
				$response['message'] = 'Invalid node ID, please try again.';
				return $response;
			}

			$parameters['data']['status_active'] = $nodeNode['status_active'];
			$parameters['data']['status_deployed'] = $nodeNode['status_deployed'];

			if (empty($nodeNode['node_id']) === false) {
				$$parameters['data']['node_id'] = $nodeNode['node_id'];
			}
		}

		$nodeExternalIps = $nodeIpVersionExternalIps = array();
		$nodeIpVersions = array(
			4,
			6
		);

		foreach ($nodeIpVersions as $nodeIpVersion) {
			$nodeExternalIpKey = 'external_ip_version_' . $nodeIpVersion;

			if (empty($parameters['data'][$nodeExternalIpKey]) === false) {
				$nodeExternalIps[$nodeExternalIpKey] = $nodeIpVersionExternalIps[$nodeIpVersion][$parameters['data'][$nodeExternalIpKey]] = $parameters['data'][$nodeExternalIpKey];
			}
		}

		if (empty($nodeExternalIps) === true) {
			$response['message'] = 'Node must have an external IP address, please try again.';
			return $response;
		}

		if (($nodeIpVersionExternalIps === _sanitizeIps($nodeExternalIps)) === false) {
			$response['message'] = 'Invalid node external IP addresses, please try again.';
			return $response;
		}

		$nodeExternalIpTypes = array();

		foreach ($nodeIpVersionExternalIps as $nodeIpVersion => $nodeIpVersionExternalIp) {
			$parameters['data']['external_ip_version_' . $nodeIpVersion . '_type'] = _detectIpType(current($nodeIpVersionExternalIp), $nodeIpVersion);
			$nodeExternalIpTypes[$parameters['data']['external_ip_version_' . $nodeIpVersion . '_type']] = true;

			if (empty($nodeExternalIpTypes['reserved']) === false) {
				unset($parameters['data']['internal_ip_version_' . $nodeIpVersion]);
			}
		}

		if ((count($nodeExternalIpTypes) === 2) === true) {
			$response['message'] = 'Node external IPs must be either private or public, please try again.';
			return $response;
		}

		$nodeInternalIps = $nodeIpVersionInternalIps = array();

		foreach ($nodeIpVersions as $nodeIpVersion) {
			$nodeInternalIpKey = 'internal_ip_version_' . $nodeIpVersion;

			if (empty($parameters['data'][$nodeInternalIpKey]) === false) {
				$nodeInternalIps[$nodeInternalIpKey] = $nodeIpVersionInternalIps[$nodeIpVersion][$parameters['data'][$serverNodeInternalIpKey]] = $parameters['data'][$serverNodeInternalIpKey];
			}
		}

		if (
			(empty($nodeInternalIps) === false) &&
			(($nodeIpVersionInternalIps === $this->_sanitizeIps($nodeInternalIps)) === false)
		) {
			$response['message'] = 'Invalid node internal IPs, please try again.';
			return $response;
		}

		foreach ($nodeIpVersionInternalIps as $nodeIpVersion => $nodeIpVersionInternalIp) {
			if ((_detectIpType(current($nodeIpVersionInternalIp), $nodeIpVersion) === 'public') === true) {
				$response['message'] = 'Node internal IPs must be private, please try again.';
				return $response;
			}

			if (empty($nodeIpVersionExternalIps[$nodeIpVersion]) === true) {
				$response['message'] = 'Node internal IPs must have a matching external IP, please try again.';
				return $response;
			}
		}

		$existingNodeParameters = array(
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'OR' => $nodeExternalIps
			)
		);
		$nodeIps = array_merge($nodeExternalIps, $nodeInternalIps);

		if (empty($parameters['data']['node_id']) === false) {
			$existingNodeParameters['where']['OR'] = array(
				$existingNodeParameters['where'],
				array(
					'node_id' => $parameters['data']['node_id'],
					'OR' => $nodeIps
				)
			);
		}

		$existingNode = _list($existingNodeParameters);

		if ($existingNode === false) {
			$response['message'] = 'Error listing data in nodes database, please try again.';
			return $response;
		}

		$existingNode = current($existingNode);

		if (empty($existingNode) === false) {
			$existingNodeIps = array_intersect_key($existingNode, array(
				'external_ip_version_4' => true,
				'external_ip_version_6' => true,
				'internal_ip_version_4' => true,
				'internal_ip_version_6' => true
			));

			foreach ($existingNodeIps as $existingNodeIp) {
				if (in_array($existingNodeIp, $nodeIps) === true) {
					$response['message'] = 'Node IP ' . $existingNodeIp . ' already in use, please try again.';
					break;
				}
			}

			return $response;
		}

		if (empty($parameters['data']['node_id']) === true) {
			$parameters['data']['authentication_token'] = substr(time() . str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz01234567890123456789', 10)), 0, rand(90, 100));
		}

		$nodeDataSaved = _save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'authentication_token' => true,
				'external_ip_version_4' => true,
				'external_ip_version_4_type' => true,
				'external_ip_version_6' => true,
				'external_ip_version_6_type' => true,
				'internal_ip_version_4' => true,
				'internal_ip_version_6' => true,
				'node_id' => true,
				'status_active' => true,
				'status_deployed' => true
			)),
			'in' => $parameters['databases']['nodes']
		));

		if ($nodeDataSaved === false) {
			$response['message'] = 'Error saving data in nodes database, please try again.';
			return $response;
		}

		$response['message'] = 'Node added successfully.';
		$node = _list(array(
			'in' => $parameters['databases']['nodes'],
			'where' => $nodeIps
		));

		if (empty($node) === true) {
			_delete(array(
				'in' => $parameters['databases']['nodes'],
				'where' => $nodeIps
			));
			$response['message'] = 'Error listing data in nodes database, please try again.';
			return $response;
		}

		$response['data'] = $node;
		$response['status_valid'] = true;
		return $response;
	}

	if ($parameters['action'] === 'add_node') {
		$response = _addNode($parameters, $response);
		_output($response);
	}
?>
