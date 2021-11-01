<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		$databases['node_process_forwarding_destinations'],
		$databases['node_process_node_user_authentication_credentials'],
		$databases['node_process_node_user_authentication_sources'],
		$databases['node_process_node_user_node_request_destinations'],
		$databases['node_process_node_user_node_request_limit_rules'],
		$databases['node_process_node_users'],
		$databases['node_process_recursive_dns_destinations'],
		$databases['node_processes'],
		$databases['node_reserved_internal_destinations'],
		$databases['nodes']
	), $parameters['databases'], $response);

	function _processNode($parameters, $response) {
		// todo: verify no reserved internal ip duplicates before each process reconfig
		$response['data'] = array(
			'node_ip_address_versions' => array(
				'32' => '4',
				'128' => '6'
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
			'version' => '1' // todo: add system version nber from file
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
			$response['message'] = 'Invalid node authentication token or ID, please try again.';
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
					'either' => array(
						'id' => $nodeIds,
						'node_id' => $nodeIds
					)
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
					'status_processed' => '0'
				)
			), $response);

			if (($nodeCount > 0) === false) {
				$response['message'] = 'Node is already processed, please try again.';
				return $response;
			}

			$nodeProcesses = _list(array(
				'columns' => array(
					'id',
					'node_id',
					'port_number',
					'type'
				),
				'in' => $parameters['databases']['node_processes'],
				'where' => array(
					'either' => array(
						'node_id' => $nodeIds,
						'node_node_id' => $nodeIds
					)
				)
			), $response);
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
					'either' => array(
						'node_id' => $nodeIds,
						'node_node_id' => $nodeIds
					)
				)
			), $response);
			$nodeProcessNodeUserAuthenticationCredentials = _list(array(
				'columns' => array(
					'node_user_authentication_credential_password',
					'node_user_authentication_credential_username',
					'node_user_id'
				),
				'in' => $parameters['databases']['node_process_node_user_authentication_credentials'],
				'where' => array(
					'either' => array(
						'node_id' => $nodeIds,
						'node_node_id' => $nodeIds
					)
				)
			), $response);
			$nodeProcessNodeUserAuthenticationSources = _list(array(
				'columns' => array(
					'node_user_authentication_source_ip_address',
					'node_user_authentication_source_ip_address_block_length',
					'node_user_id'
				),
				'in' => $parameters['databases']['node_process_node_user_authentication_sources'],
				'where' => array(
					'either' => array(
						'node_id' => $nodeIds,
						'node_node_id' => $nodeIds
					)
				)
			), $response);
			$nodeProcessNodeUserNodeRequestDestinations = _list(array(
				'columns' => array(
					'node_user_id',
					'node_user_node_request_destination_address',
					'node_user_node_request_destination_id'
				), 'in' => $parameters['databases']['node_process_node_user_node_request_destinations'],
				'where' => array(
					'either' => array(
						'node_id' => $nodeIds,
						'node_node_id' => $nodeIds
					)
				)
			), $response);
			$nodeProcessNodeUserNodeRequestLimitRules = _list(array(
				'columns' => array(
					'node_user_id',
					'node_user_node_request_destination_id'
				),
				'in' => $parameters['databases']['node_process_node_user_node_request_limit_rules'],
				'where' => array(
					'either' => array(
						'node_id' => $nodeIds,
						'node_node_id' => $nodeIds
					)
				)
			), $response);
			$nodeProcessNodeUsers = _list(array(
				'columns' => array(
					'node_id',
					'node_process_type',
					'node_user_id',
					'node_user_status_node_request_destinations_only_allowed',
					'node_user_status_node_request_logs_allowed',
					'node_user_status_strict_authentication_required'
				),
				'in' => $parameters['databases']['node_process_node_users'], 
				'where' => array(
					'either' => array(
						'node_id' => $nodeIds,
						'node_node_id' => $nodeIds
					)
				)
			), $response);
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
					'either' => array(
						'node_id' => $nodeIds,
						'node_node_id' => $nodeIds
					)
				)
			), $response);
			$nodeReservedInternalDestinations = _list(array(
				'columns' => array(
					'ip_address',
					'ip_address_version'
				),
				'in' => $parameters['databases']['node_reserved_internal_destinations'],
				'where' => array(
					'either' => array(
						'node_id' => $nodeIds,
						'node_node_id' => $nodeIds
					),
					'status_assigned' => '1'
				)
			), $response);
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
			), $response);

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

			if (empty($nodeProcessNodeUsers) === false) {
				foreach ($nodeProcessNodeUsers as $nodeProcessNodeUser) {
					$response['data']['node_process_node_users'][$nodeProcessNodeUser['node_process_type']][$nodeProcessNodeUser['node_id']][$nodeProcessNodeUser['node_user_id']] = $nodeProcessNodeUser['node_user_id'];
					$response['data']['node_users'][$nodeProcessNodeUser['node_user_id']] = array(
						'status_node_request_destinations_only_allowed' => $nodeProcessNodeUser['node_user_status_node_request_destinations_only_allowed'], 
						'status_node_request_logs_allowed' => $nodeProcessNodeUser['node_user_status_node_request_logs_allowed'], 
						'status_strict_authentication_required' => $nodeProcessNodeUser['node_user_status_strict_authentication_required']
					);
				}

				foreach ($nodeProcessNodeUserAuthenticationCredentials as $nodeProcessNodeUserAuthenticationCredential) {
					$response['data']['node_users'][$nodeProcessNodeUserAuthenticationCredential['node_user_id']]['node_user_authentication_credentials'] = array(
						'password' => $nodeProcessNodeUserAuthenticationCredential['node_user_authentication_credential_password'],
						'username' => $nodeProcessNodeUserAuthenticationCredential['node_user_authentication_credential_username']
					);
				}

				foreach ($nodeProcessNodeUserAuthenticationSources as $nodeProcessNodeUserAuthenticationSource) {
					$response['data']['node_users'][$nodeProcessNodeUserAuthenticationSource['node_user_id']]['node_user_authentication_sources'] = array(
						'ip_address' => $nodeProcessNodeUserAuthenticationSource['node_user_authentication_source_ip_address'],
						'ip_address_block_length' => $nodeProcessNodeUserAuthenticationSource['node_user_authentication_source_ip_address_block_length']
					);
				}

				if (empty($nodeProcessNodeUserNodeRequestDestinations) === false) {
					foreach ($nodeProcessNodeUserNodeRequestDestinations as $nodeProcessNodeUserNodeRequestDestination) {
						$response['data']['node_request_destinations'][$nodeProcessNodeUserNodeRequestDestination['node_request_destination_id']] = $nodeProcessNodeUserNodeRequestDestination['node_request_destination_address'];
						$response['data']['node_users'][$nodeProcessNodeUserNodeRequestDestination['node_user_id']]['node_request_destination_ids'][$nodeProcessNodeUserNodeRequestDestination['node_request_destination_id']] = $nodeProcessNodeUserNodeRequestDestination['node_request_destination_id'];
					}
				}

				if (empty($nodeProcessNodeUserNodeRequestLimitRules) === false) {
					foreach ($nodeProcessNodeUserNodeRequestLimitRules as $nodeProcessNodeUserNodeRequestLimitRule) {
						if (empty($nodeProcessNodeUserNodeRequestLimitRule['node_request_destination_id']) === false) {
							if (empty($response['data']['node_users'][$nodeProcessNodeUserNodeRequestLimitRule['node_user_id']]['status_node_request_destinations_only_allowed']) === false) {
								if (empty($response['data']['node_users'][$nodeProcessNodeUserNodeRequestLimitRule['node_user_id']]['node_request_destination_ids']) === false) {
									unset($response['data']['node_users'][$nodeProcessNodeUserNodeRequestLimitRule['node_user_id']]['node_request_destination_ids'][$nodeProcessNodeUserNodeRequestLimitRule['node_request_destination_id']]);
								} else {
									unset($response['data']['node_users'][$nodeProcessNodeUserNodeRequestLimitRule['node_user_id']]);
								}
							}
						}
					}
				}
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

	if (($parameters['action'] === 'process_node') === true) {
		$response = _processNode($parameters, $response);
		_output($response);
	}
?>
