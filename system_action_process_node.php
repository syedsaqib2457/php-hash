<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		$databases['node_process_forwarding_destinations'],
		$databases['node_process_node_users'],
		$databases['node_process_recursive_dns_destinations'],
		$databases['node_processes'],
		$databases['node_reserved_internal_destinations'],
		$databases['node_user_authentication_credentials'],
		$databases['node_user_authentication_sources'],
		$databases['node_user_node_request_destinations'],
		$databases['node_user_node_request_limit_rules'],
		$databases['node_users'],
		$databases['nodes']
	), $parameters['databases'], $response);

	function _processNode($parameters, $response) {
		// todo: verify no reserved internal ip duplicates before each process reconfig
		$response['data'] = array(
			'node_ip_address_versions' => array(
				32 => 4,
				128 => 6
			),
			'node_process_types' => array(
				'http_proxy',
				'load_balancer',
				'recursive_dns',
				'socks_proxy'
			),
			'proxy_node_process_types' => array(
				'proxy' => 'http_proxy',
				'socks' => 'socks_proxy'
			),
			'reserved_network' => array(), // todo: add reserved network IP data from validation file
			'version' => 1 // todo: add system version nber from file
		);

		if (empty($parameters['where']['authentication_token']) === true) {
			$response['message'] = 'Node authentication token is required, please try again.';
			return $response;
		}

		$node = _list(array(
			'columns' => array(
				'id',
				'node_id'
			),
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'authentication_token' => $parameters['where']['authentication_token']
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node authentication token or ID, please try again';
			// todo: log as unauthorized request request
			return $response;
		}

		$nodeIds = array_filter($node);

		if (
			(isset($parameters['data']['processing_progress_checkpoint']) === true) &&
			(isset($parameters['data']['processing_progress_percentage']) === true) &&
			(isset($parameters['data']['status_processed']) === true) &&
			(isset($parameters['data']['status_processing']) === true)
		) {
			_update(array(
				'data' => array(
					'processing_progress_checkpoint' => $parameters['data']['processing_progress_checkpoint'],
					'processing_progress_percentage' => $parameters['data']['processing_progress_percentage'],
					'status_processed' => boolval($parameters['data']['status_processed']),
					'status_processing' => boolval($parameters['data']['status_processing'])
				),
				'in' => $parameters['databases']['nodes'],
				'where' => array(
					'id' => $nodeIds,
					'node_id' => $nodeIds
				)
			), $response);
		} else {
			$nodeCount = _count(array(
				'in' => $parameters['databases']['nodes'],
				'where' => array(
					'either' => array(
						'id' => $nodeIds,
						'node_id' => $nodeIds
					),
					'status_processed' => false
				)
			), $response);

			if (($nodeCount > 0) === false) {
				$response['message'] = 'Node is already processed, please try again.';
				return $response;
			}

			$nodeProcesses = _list(array(
				'in' => $parameters['databases']['node_processes'],
				'where' => array(
					'node_node_id' => $nodeIds
				)
			));
			$nodeProcessForwardingDestinations = _list(array(
				'columns' => array(
					'address_version_4',
					'address_version_6',
					'id',
					'node_process_type',
					'port_number_version_4',
					'port_number_version_6'
				),
				'in' => $parameters['databases']['node_process_forwarding_destinations'],
				'where' => array(
					'node_node_id' => $nodeIds
				)
			));
			$nodeProcessNodeUsers = _list(array(
				'in' => $parameters['databases']['node_process_node_users'], 
				'where' => array(
					'node_node_id' => $nodeIds
				)
			));
			$nodeProcessRecursiveDnsDestinations = _list(array(
				'columns' => array(
					'listening_ip_address_version_4',
					'listening_ip_address_version_4_node_id',
					'listening_ip_address_version_6',
					'listening_ip_address_version_6_node_id',
					'listening_port_number_version_4',
					'listening_port_number_version_6',
					'node_id',
					'node_process_type',
					'source_ip_address_version_4',
					'source_ip_address_version_6'
				),
				'in' => $parameters['databases']['node_process_recursive_dns_destinations'],
				'where' => array(
					'node_node_id' => $nodeIds
				)
			));
			$nodeReservedInternalDestinations = _list(array(
				'in' => $parameters['databases']['node_reserved_internal_destinations'],
				'where' => array(
					'node_node_id' => $nodeIds,
					'status_assigned' => true
				)
			));
			$nodes = _list(array(
				'columns' => array(
					'external_ip_address_version_4',
					'external_ip_address_version_6',
					'id',
					'internal_ip_address_version_4',
					'internal_ip_address_version_6',
					'status_activated'
				),
				'in' => $parameters['databases']['nodes'],
				'where' => array(
					'either' => array(
						'id' => $nodeIds,
						'node_id' => $nodeIds
					)
				)
			));

			foreach ($nodes as $node) {
				$response['data']['nodes'][$node['id']] = $node;
				unset($response['data']['nodes'][$node['id']]['id']);

				foreach ($response['data']['node_ip_address_versions'] as $nodeIpAddressVersion) {
					$nodeIpAddresses = array(
						$node['external_ip_address_version_' . $nodeIpAddressVersion],
						$node['internal_ip_address_version_' . $nodeIpAddressVersion]
					);

					foreach (array_filter($nodeIpAddresses) as $nodeIpAddress) {
						$response['data']['node_ip_addresses'][$nodeIpAddressVersion][$nodeIpAddress] = $nodeIpAddress;
					}
				}
			}

			foreach (array_values($response['data']['node_ip_address_versions']) as $nodeIpAddressVersionKey => $nodeIpAddressVersion) {
				if (empty($response['data']['node_ip_addresses'][$nodeIpAddressVersion]) === true) {
					unset($response['data']['node_ip_address_versions'][(128 / 4) + (96 * $nodeIpAddressVersionKey)]);
				}
			}

			$nodeProcessPartKey = 0;

			foreach ($nodeProcesses as $nodeProcess) {
				$response['data']['node_processes'][$nodeProcess['type']][$nodeProcessPartKey][$nodeProcess['node_id']][$nodeProcess['id']] = $nodeProcess['port_number'];
				$nodeProcessPartKey = abs($nodeProcessPartKey + -1);
			}

			foreach ($nodeProcessForwardingDestinations as $nodeProcessForwardingDestination) {
				$response['data']['node_process_forwarding_destinations'][$nodeProcessForwardingDestination['node_process_type']][$nodeProcessForwardingDestination['node_id']] = $nodeProcessForwardingDestination;
				unset($response['data']['node_process_forwarding_destinations'][$nodeProcessForwardingDestination['node_process_type']][$nodeProcessForwardingDestination['node_id']]['node_id']);
			}

			foreach ($nodeProcessRecursiveDnsDestinations as $nodeProcessRecursiveDnsDestination) {
				$response['data']['node_process_recursive_dns_destinations'][$nodeProcessRecursiveDnsDestination['node_process_type']][$nodeProcessRecursiveDnsDestination['node_id']] = $nodeProcessRecursiveDnsDestination;
				unset($response['data']['node_process_recursive_dns_destinations'][$nodeProcessRecursiveDnsDestination['node_process_type']][$nodeProcessRecursiveDnsDestination['node_id']]['node_id']);

				foreach ($response['data']['node_ip_address_versions'] as $nodeIpAddressVersion) {
					if (empty($nodeProcessRecursiveDnsDestination['source_ip_address_version_' . $nodeIpAddressVersion]) === false) {
						$response['data']['node_ip_addresses'][$nodeIpAddressVersion][$nodeProcessRecursiveDnsDestination['listening_ip_address_version_' . $nodeIpAddressVersion]] = $nodeProcessRecursiveDnsDestination['listening_ip_address_version_' . $nodeIpAddressVersion];

						if (empty($response['data']['nodes'][$nodeProcessRecursiveDnsDestination['listening_ip_address_version_' . $nodeIpAddressVersion . '_node_id']]['internal_ip_address_version_' . $nodeIpAddressVersion]) === false) {
							$response['data']['node_process_recursive_dns_destinations'][$nodeProcessRecursiveDnsDestination['node_process_type']][$nodeProcessRecursiveDnsDestination['node_id']]['source_ip_address_version_' . $nodeIpAddressVersion] = $response['data']['nodes'][$nodeProcessRecursiveDnsDestination['node_id']]['internal_ip_address_version_' . $nodeIpAddressVersion];
						}
					}

					unset($response['data']['node_process_recursive_dns_destinations'][$nodeProcessRecursiveDnsDestination['node_process_type']][$nodeProcessRecursiveDnsDestination['node_id']]['listening_ip_address_version_' . $nodeIpAddressVersion . '_node_id']);
				}
			}

			if (empty($nodeProcessNodeUsers) === false) {
				$nodeProcessNodeUserIds = array();

				foreach ($nodeProcessNodeUsers as $nodeProcessNodeUser) {
					$response['data']['node_process_node_users'][$nodeProcessNodeUser['node_process_type']][$nodeProcessNodeUser['node_id']][$nodeProcessNodeUser['node_user_id']] = $nodeProcessNodeUser['node_user_id'];
					$nodeProcessNodeUserIds[$nodeProcessNodeUser['node_user_id']] = $nodeProcessNodeUser['node_user_id'];
				}

				$nodeUsers = _list(array(
					'in' => $parameters['databases']['node_users'],
					'where' => array(
						'id' => $nodeProcessNodeUserIds
					)
				));

				foreach ($nodeUsers as $nodeUser) {
					$response['data']['node_users'][$nodeUser['id']] = array(
						'status_node_request_destinations_only_allowed' => $nodeUser['status_node_request_destinations_only_allowed'],
						'status_node_request_logs_allowed' => $nodeUser['status_node_request_logs_allowed'],
						'status_strict_authentication_required' => $nodeUser['status_strict_authentication_required']
					);
					$nodeUserAuthenticationCredentials = _list(array(
						'columns' => array(
							'password',
							'username'
						),
						'in' => $parameters['databases']['node_user_authentication_credentials'],
						'where' => array(
							'node_user_id' => $nodeUser['id']
						)
					));
					$nodeUserAuthenticationSources = _list(array(
						'columns' => array(
							'ip_address',
							'ip_address_block_length'
						),
						'in' => $parameters['databases']['node_user_authentication_sources'],
						'where' => array(
							'node_user_id' => $nodeUser['id']
						)
					));
					$nodeUserNodeRequestDestinations = _list(array(
						'columns' => array(
							'node_request_destination_address',
							'node_request_destination_id'
						),
						'in' => $parameters['databases']['node_user_node_request_destinations'],
						'where' => array(
							'node_user_id' => $nodeUser['id']
						)
					));
					$nodeUserNodeRequestLimitRules = _list(array(
						'columns' => array(
							'node_request_destination_id'
						),
						'in' => $parameters['databases']['node_user_node_request_limit_rules'],
						'where' => array(
							'node_user_id' => $nodeUser['id']
						)
					));
					$response['data']['node_users'][$nodeUser['id']]['node_user_authentication_credentials'] = $nodeUserAuthenticationCredentials;
					$response['data']['node_users'][$nodeUser['id']]['node_user_authentication_sources'] = $nodeUserAuthenticationSources;

					if (empty($nodeUserNodeRequestDestinations) === false) {
						foreach ($nodeUserNodeRequestDestinations as $nodeUserNodeRequestDestination) {
							$response['data']['node_request_destinations'][$nodeUserNodeRequestDestination['node_request_destination_id']] = $nodeUserNodeRequestDestination['node_request_destination_address'];
							$response['data']['node_users'][$nodeUser['id']]['request_destination_ids'][$nodeUserNodeRequestDestination['node_request_destination_id']] = $nodeUserNodeRequestDestination['node_request_destination_id'];
						}
					}

					if (empty($nodeUserNodeRequestLimitRules) === false) {
						foreach ($nodeUserNodeRequestLimitRules as $nodeUserNodeRequestLimitRule) {
							if (empty($nodeUserNodeRequestLimitRule['node_request_destination_id']) === false) {
								if (empty($response['data']['node_users'][$nodeUserNodeRequestLimitRule['node_user_id']]['status_node_request_destinations_only_allowed']) === false) {
									if (empty($response['data']['node_users'][$nodeUser['id']]['node_request_destination_ids']) === false) {
										unset($response['data']['node_users'][$nodeUser['id']]['node_request_destination_ids'][$nodeUserNodeRequestLimitRule['node_request_destination_id']]);
									} else {
										unset($response['data']['node_users'][$nodeUser['id']]);
									}
								} else {
									$response['data']['node_users'][$nodeUser['id']]['node_request_destination_ids'][$nodeUserNodeRequestLimitRule['node_request_destination_id']] = $nodeUserNodeRequestLimitRule['node_request_destination_id'];
								}
							} else {
								unset($response['data']['node_users'][$nodeUser['id']]);
							}
						}
					}
				}
			}

			foreach ($nodeReservedInternalDestinations as $nodeReservedInternalDestination) {
				$response['data']['node_ip_addresses'][$nodeReservedInternalDestination['ip_address_version']][$nodeReservedInternalDestination['ip_address']] = $response['data']['node_reserved_internal_destination_ip_addresses'][$nodeReservedInternalDestination['ip_address_version']][$nodeReservedInternalDestination['ip_address']] = $nodeReservedInternalDestination['ip_address'];
				$response['data']['node_reserved_internal_destinations'][$nodeReservedInternalDestination['node_id']][$nodeReservedInternalDestination['ip_address_version']] = array(
					'ip_address' => $nodeReservedInternalDestination['ip_address'],
					'ip_address_version' => $nodeReservedInternalDestination['ip_address_version']
				);
			}
		}

		$response['message'] = 'Nodes processed successfully.';
		return $response;
	}

	if ($parameters['action'] === 'process_node') {
		$response = _processNode($parameters, $response);
		_output($response);
	}
?>
