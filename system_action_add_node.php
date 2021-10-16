<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'node_processes' => $settings['databases']['node_processes'],
		'node_recursive_dns_destinations' => $settings['databases']['node_recursive_dns_destinations'],
		'node_reserved_internal_destinations' => $settings['databases']['node_reserved_internal_destinations'],
		'nodes' => $settings['databases']['nodes']
	));

	if ($parameters['databases'] === false) {
		$response['message'] = 'Error connecting to database, please try again.';
		echo json_encode($response);
		exit;
	}

	// todo: include _detectIpType
	// todo: include _sanitizeIps
	require_once('/var/www/ghostcompute/system_action_add_node_reserved_internal_destination.php');

	function _addNode($parameters) {
		$response = array(
			'message' => 'Error adding node, please try again.',
			'status_valid' => false
		);
		$parameters['data']['status_processed'] = true;

		if (empty($parameters['data']['node_id']) === false) {
			$nodeNode = _fetch(array(
				'fields' => array(
					'id',
					'node_id',
					'status_active',
					'status_deployed'
				),
				'from' => $parameters['databases']['nodes'],
				'where' => array(
					'OR' => array(
						'external_ip_version_4' => ($nodeNodeId = $parameters['data']['node_id']),
						'external_ip_version_6' => $nodeNodeId,
						'id' => $nodeNodeId,
						'internal_ip_version_4' => $nodeNodeId,
						'internal_ip_version_6' => $nodeNodeId
					)
				)
			));
			$response['status_valid'] = ($node !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (empty($nodeNode) === false);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node ID, please try again.';
				return $response;
			}

			$parameters['data']['status_active'] = $nodeNode['status_active'];
			$parameters['data']['status_deployed'] = $nodeNode['status_deployed'];

			if (empty($nodeNode['node_id']) === false) {
				$nodeIdType = 'ID';

				if (is_numeric($parameters['data']['node_id']) === false) {
					$nodeIdType = 'IP';
				}

				$response['message'] = 'Node ' . $nodeIdType . ' ' . $parameters['data']['node_id'] . ' already belongs to node ID ' . $nodeNode['node_id'] . ', please try again.';
				return $response;
			}

			$nodeNodeId = $parameters['data']['node_id'] = $nodeNode['id'];
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

		$response['status_valid'] = (empty($nodeExternalIps) === false);

		if ($response['status_valid'] === false) {
			return $response;
		}

		$response['status_valid'] = ($nodeIpVersionExternalIps === _sanitizeIps($nodeExternalIps));

		if ($response['status_valid'] === false) {
			$response['message'] = 'Invalid node external IPs, please try again.';
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

		$response['status_valid'] = (count($nodeExternalIpTypes) === 1);

		if ($response['status_valid'] === false) {
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

		$response['status_valid'] = (
			(empty($nodeInternalIps) === true) ||
			($nodeIpVersionInternalIps === $this->_sanitizeIps($nodeInternalIps))
		);

		if ($response['status_valid'] === false) {
			$response['message'] = 'Invalid node internal IPs, please try again.';
			return $response;
		}

		foreach ($nodeIpVersionInternalIps as $nodeIpVersion => $nodeIpVersionInternalIp) {
			$response['status_valid'] = ($this->_detectIpType(current($nodeIpVersionInternalIp), $nodeIpVersion) === 'reserved');

			if ($response['status_valid'] === false) {
				$response['message'] = 'Node internal IPs must be private, please try again.';
				return $response;
			}

			$response['status_valid'] = (empty($nodeIpVersionExternalIps[$nodeIpVersion]) === true);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Node internal IPs must have a matching external IP, please try again.';
				return $response;
			}
		}

		$existingNodeParameters = array(
			'fields' => array(
				'external_ip_version_4',
				'external_ip_version_6',
				'internal_ip_version_4',
				'internal_ip_version_6'
			),
			'from' => $parameters['databases']['nodes'],
			'where' => array(
				'OR' => $nodeExternalIps
			)
		);
		$nodeIps = array_merge($nodeExternalIps, $nodeInternalIps);

		if (empty($nodeNodeId) === false) {
			$existingNodeParameters['where']['OR'] = array(
				$existingNodeParameters['where'],
				array(
					'node_id' => $nodeNodeId,
					'OR' => $nodeIps
				)
			);
		}

		$existingNode = _fetch($existingNodeParameters);
		$response['status_valid'] = ($existingNode !== false);

		if ($response['status_valid'] === false) {
			return $response;
		}

		$response['status_valid'] = (empty($existingNode) === true);

		if ($response['status_valid'] === false) {
			foreach ($existingNode as $existingNodeIp) {
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

		$nodesSaved = _save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'external_ip_version_4' => true,
				'external_ip_version_4_type' => true,
				'external_ip_version_6' => true,
				'external_ip_version_6_type' => true,
				'internal_ip_version_4' => true,
				'internal_ip_version_6' => true,
				'node_id' => true,
				'status_active' => true,
				'status_deployed' => true,
				'status_processed' => true,
				'token' => true
			)),
			'to' => $parameters['databases']['nodes']
		));
		$response['status_valid'] = ($nodesSaved === true);

		if ($response['status_valid'] === false) {
			return $response;
		}

		$parameters['node'] = _fetch(array(
			'fields' => array(
				'id',
				'node_id'
			),
			'from' => $parameters['databases']['nodes'],
			'where' => $nodeIps
		));
		$response['status_valid'] = (
			($parameters['node'] !== false) &&
			(empty($parameters['node']['id']) === false)
		);

		if ($response['status_valid'] === false) {
			$this->delete(array(
				'from' => $parameters['databases']['nodes'],
				'where' => $nodeIps
			));
			return $response;
		}

		$nodeId = $parameters['node']['id'];
		$nodeProcessData = $nodeProcessPortData = $nodeRecursiveDnsDestinationData = array();

		foreach ($settings['node_process_type_default_port_numbers'] as $nodeProcessType => $nodeProcessTypeDefaultPortNumber) {
			foreach (range(0, 9) as $nodeProcessPortNumberIndex) {
				$nodeProcessData[] = array(
					'node_id' => $nodeId,
					'port_number' => ($nodeProcessTypeDefaultPortNumber + $nodeProcessPortNumberIndex),
					'type' => $nodeProcessType
				);
			}

			foreach ($nodeIpVersions as $nodeIpVersion) {
				if (empty($nodeIpVersionExternalIps[$nodeIpVersion]) === false) {
					$nodeRecursiveDnsDestinationData[$nodeProcessType]['listening_ip_version_' . $nodeIpVersion . '_node_id'] = $nodeRecursiveDnsDestinationData[$nodeProcessType]['node_id'] = $nodeId;
					$nodeRecursiveDnsDestinationData[$nodeProcessType]['listening_port_number_version_' . $nodeIpVersion] = $settings['node_process_type_default_port_numbers']['recursive_dns'];
					$nodeRecursiveDnsDestinationData[$nodeProcessType]['source_ip_version_' . $nodeIpVersion] = $nodeIpVersionExternalIps[$nodeIpVersion];
					$parameters['node']['ip_address_version'] = $nodeIpVersion;
					$addNodeReservedInternalDestinationResponse = _addNodeReservedInternalDestination($parameters);

					if ($addNodeReservedInternalDestinationResponse['status_valid'] === false) {
						// todo: remove node data with $nodeId + _removeNode() if reserved internal ip assignment fails
						return $addNodeReservedInternalDestinationResponse;
					}

					$nodeRecursiveDnsDestinationData[$nodeProcessType]['listening_ip_version_' . $nodeIpVersion] = $addNodeReservedInternalDestinationResponse['data']['node_reserved_internal_destination_ip_address'];
				}
			}

			$nodeRecursiveDnsDestinationData[$nodeProcessType]['node_id'] = $nodeId;
			$nodeRecursiveDnsDestinationData[$nodeProcessType]['node_process_type'] = $nodeProcessType;
		}

		if (empty($nodeNodeId) === false) {
			$nodeIds = array(
				$nodeId,
				$nodeNodeId
			);
			$existingNodeReservedInternalDestinations = _fetch(array(
				'fields' => array(
					'id',
					'ip_address',
					'ip_address_version',
					'node_id',
					'node_node_id'
				),
				'from' => $parameters['databases']['node_reserved_internal_destinations'],
				'where' => array(
					'ip_address' => $nodeIps,
					'OR' => array(
						array(
							'OR' => array(
								'node_id' => $nodeIds,
								'node_node_id' => $nodeIds
							)
						),
						array(
							'node_node_external_ip_address_type' => 'reserved'
						)
					)
				)
			));
			$response['status_valid'] = ($existingNodeReservedInternalDestinations !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			if (empty($existingNodeReservedInternalDestinations) === false) {
				foreach ($existingNodeReservedInternalDestinations as $existingNodeReservedInternalDestination) {
					$parameters['node'] = array(
						'id' => $existingNodeReservedInternalDestination['node_id'],
						'ip_address_version' => $existingNodeReservedInternalDestination['ip_address_version'],
						'node_id' => $existingNodeReservedInternalDestination['node_node_id']
					);
					$addNodeeservedInternalDestinationResponse = _addNodeReservedInternalDestination($parameters);

					if ($addNodeReservedInternalDestinationResponse['status_valid'] === false) {
						// todo: remove node data with $nodeId + _removeNode() if reserved internal ip assignment fails
						return $addNodeReservedInternalDestinationResponse;
					}

					$nodeReservedInternalDestinationsDeleted = _delete(array(
						'from' => $parameters['databases']['node_reserved_internal_destinations'],
						'where' => array(
							'id' => $existingNodeReservedInternalDestination['id']
						)
					));
					$response['status_valid'] = ($nodeReservedInternalDestinationsDeleted !== false);

					if ($response['status_valid'] === false) {
						// todo: remove node data with $nodeId + _removeNode() if reserved internal ip assignment fails
						return $response;
					}
				}
			}
		}

		$nodeProcessesSaved = _save(array(
			'data' => $nodeProcessData,
			'to' => $parameters['databases']['node_processes']
		));
		$nodeRecursiveDnsDestinationsSaved = _save(array(
			'data' => $nodeRecursiveDnsDestinationData,
			'to' => $parameters['databases']['node_recursive_dns_destinations']
		));
		$nodesUpdated = _update(array(
			'data' => array(
				'status_processed' => false
			),
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'id' => $nodeId
			)
		));
		$response['status_valid'] = (
			($nodeProcessesSaved !== false) &&
			($nodeRecursiveDnsDestinationsSaved !== false) &&
			($nodesUpdated !== false)
		);

		if ($response['status_valid'] === false) {
			// todo: use $nodeId + $this->remove() instead of repeating $this->delete()
			_delete(array(
				'from' => $parameters['databases']['node_processes'],
				'where' => array(
					'node_id' => $nodeId
				)
			));
			_delete(array(
				'from' => $parameters['databases']['node_recursive_dns_destinations'],
				'where' => array(
					'node_id' => $nodeId
				)
			));
			_delete(array(
				'from' => $parameters['databases']['node_reserved_internal_destinations'],
				'where' => array(
					'node_id' => $nodeId
				)
			));
			_delete(array(
				'from' => $parameters['databases']['nodes'],
				'where' => array(
					'id' => $nodeId
				)
			));
			return $response;
		}

		$response['message'] = 'Node added successfully.';
		return $response;
	}

	if ($parameters['action'] === 'add_node') {
		$response = _addNode($parameters);
		echo json_encode($response);
		exit;
	}
?>
