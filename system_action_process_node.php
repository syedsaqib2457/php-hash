<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_forwarding_destinations',
		'node_process_node_user_authentication_credentials',
		'node_process_node_user_authentication_sources',
		'node_process_node_user_node_request_destinations',
		'node_process_node_user_node_request_limit_rules',
		'node_process_node_users',
		'node_process_recursive_dns_destinations',
		'node_processes',
		'node_reserved_internal_destinations',
		'node_reserved_internal_sources',
		'nodes'
	), $parameters['system_databases'], $response);

	function _processNode($parameters, $response) {
		$response['data'] = array(
			'cryptocurrency_node_process_types' => array(
				'bitcoin_cryptocurrency',
				'monero_cryptocurrency'
			),
			'node_ip_address_version_numbers' => array(
				'32' => '4',
				'128' => '6'
			),
			'node_process_types' => array(
				'bitcoin_cryptocurrency',
				'http_proxy',
				'load_balancer',
				'monero_cryptocurrency',
				'recursive_dns',
				'socks_proxy'
			),
			'proxy_node_process_types' => array(
				'proxy' => 'http_proxy',
				'socks' => 'socks_proxy'
			)
		);

		if (empty($parameters['node_authentication_token']) === true) {
			$response['message'] = 'Node authentication token is required, please try again.';
			return $response;
		}

		$node = _list(array(
			'data' => array(
				'id',
				'node_id'
			),
			'in' => $parameters['system_databases']['nodes'],
			'where' => array(
				'authentication_token' => $parameters['node_authentication_token']
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node authentication token or ID, please try again.';
			// todo: log as unauthorized request request
			return $response;
		}

		$nodeNodeId = $node['id'];

		if (empty($node['node_id']) === false) {
			$nodeNodeId = $node['node_id'];
		}

		if (
			(isset($parameters['data']['processed_status']) === true) &&
			(isset($parameters['data']['processing_status']) === true) &&
			(isset($parameters['data']['processing_progress_checkpoint']) === true) &&
			(isset($parameters['data']['processing_progress_percentage']) === true)
		) {
			_update(array(
				'data' => array(
					'processed_status' => $parameters['data']['processed_status'],
					'processing_status' => $parameters['data']['processing_status'],
					'processing_progress_checkpoint' => $parameters['data']['processing_progress_checkpoint'],
					'processing_progress_percentage' => $parameters['data']['processing_progress_percentage']
				),
				'in' => $parameters['system_databases']['nodes'],
				'where' => array(
					'either' => array(
						'id' => $nodeNodeId,
						'node_id' => $nodeNodeId
					)
				)
			), $response);
		} else {
			$nodeCount = _count(array(
				'in' => $parameters['system_databases']['nodes'],
				'where' => array(
					'either' => array(
						'id' => $nodeNodeId,
						'node_id' => $nodeNodeId
					),
					'processed_status' => '0'
				)
			), $response);

			if (($nodeCount > 0) === false) {
				$response['message'] = 'Node is already processed, please try again.';
				$response['valid_status'] = '1';
				return $response;
			}

			$nodeProcesses = _list(array(
				'data' => array(
					'id',
					'node_id',
					'port_number',
					'type'
				),
				'in' => $parameters['system_databases']['node_processes'],
				'where' => array(
					'either' => array(
						'node_id' => $nodeNodeId,
						'node_node_id' => $nodeNodeId
					)
				)
			), $response);
			$nodeProcessCryptocurrencyDestinations = _list(array(
				'data' => array(
					'ip_address',
					'ip_address_version_number',
					'node_process_type'
				),
				'in' => $parameters['system_databases']['node_process_cryptocurrency_destinations'],
				'where' => array(
					'either' => array(
						'node_id' => $nodeNodeId,
						'node_node_id' => $nodeNodeId
					)
				)
			), $response);
			$nodeProcessForwardingDestinations = _list(array(
				'data' => array(
					'address_version_4',
					'address_version_6',
					'id',
					'node_process_type',
					'port_number_version_4',
					'port_number_version_6'
				),
				'in' => $parameters['system_databases']['node_process_forwarding_destinations'],
				'where' => array(
					'either' => array(
						'node_id' => $nodeNodeId,
						'node_node_id' => $nodeNodeId
					)
				)
			), $response);
			$nodeProcessNodeUserAuthenticationCredentials = _list(array(
				'data' => array(
					'node_user_authentication_credential_password',
					'node_user_authentication_credential_username',
					'node_user_id'
				),
				'in' => $parameters['system_databases']['node_process_node_user_authentication_credentials'],
				'where' => array(
					'either' => array(
						'node_id' => $nodeNodeId,
						'node_node_id' => $nodeNodeId
					)
				)
			), $response);
			$nodeProcessNodeUserAuthenticationSources = _list(array(
				'data' => array(
					'node_id',
					'node_user_authentication_source_ip_address',
					'node_user_authentication_source_ip_address_block_length',
					'node_user_id'
				),
				'in' => $parameters['system_databases']['node_process_node_user_authentication_sources'],
				'where' => array(
					'either' => array(
						'node_id' => $nodeNodeId,
						'node_node_id' => $nodeNodeId
					)
				)
			), $response);
			$nodeProcessNodeUserNodeRequestDestinations = _list(array(
				'data' => array(
					'node_user_id',
					'node_user_node_request_destination_address',
					'node_user_node_request_destination_id'
				),
				'in' => $parameters['system_databases']['node_process_node_user_node_request_destinations'],
				'where' => array(
					'either' => array(
						'node_id' => $nodeNodeId,
						'node_node_id' => $nodeNodeId
					)
				)
			), $response);
			$nodeProcessNodeUserNodeRequestLimitRules = _list(array(
				'data' => array(
					'node_user_id',
					'node_user_node_request_destination_id'
				),
				'in' => $parameters['system_databases']['node_process_node_user_node_request_limit_rules'],
				'where' => array(
					'either' => array(
						'node_id' => $nodeNodeId,
						'node_node_id' => $nodeNodeId
					)
				)
			), $response);
			$nodeProcessNodeUsers = _list(array(
				'data' => array(
					'node_id',
					'node_process_type',
					'node_user_authentication_strict_only_allowed_status',
					'node_user_id',
					'node_user_node_request_destinations_only_allowed_status',
					'node_user_node_request_logs_allowed_status'
				),
				'in' => $parameters['system_databases']['node_process_node_users'], 
				'where' => array(
					'either' => array(
						'node_id' => $nodeNodeId,
						'node_node_id' => $nodeNodeId
					)
				)
			), $response);
			$nodeProcessRecursiveDnsDestinations = _list(array(
				'data' => array(
					'listening_ip_address_version_4',
					'listening_ip_address_version_4_node_id',
					'listening_ip_address_version_6',
					'listening_ip_address_version_6_node_id',
					'node_id',
					'node_process_type',
					'port_number_version_4',
					'port_number_version_6',
					'source_ip_address_version_4',
					'source_ip_address_version_6'
				),
				'in' => $parameters['system_databases']['node_process_recursive_dns_destinations'],
				'where' => array(
					'either' => array(
						'node_id' => $nodeNodeId,
						'node_node_id' => $nodeNodeId
					)
				)
			), $response);
			$nodeReservedInternalDestinations = _list(array(
				'data' => array(
					'ip_address',
					'ip_address_version_number'
				),
				'in' => $parameters['system_databases']['node_reserved_internal_destinations'],
				'where' => array(
					'assigned_status' => '1',
					'either' => array(
						'node_id' => $nodeNodeId,
						'node_node_id' => $nodeNodeId
					)
				)
			), $response);
			$nodeReservedInternalSources = _list(array(
				'data' => array(
					'ip_address',
					'ip_address_block_length',
					'ip_address_version_number'
				),
				'in' => $parameters['system_databases']['node_reserved_internal_sources'],
				'where' => array(
					'node_id' => $nodeNodeId
				)
			), $response);
			$nodes = _list(array(
				'data' => array(
					'activated_status',
					'external_ip_address_version_4',
					'external_ip_address_version_6',
					'id',
					'internal_ip_address_version_4',
					'internal_ip_address_version_6'
				),
				'in' => $parameters['system_databases']['nodes'],
				'where' => array(
					'either' => array(
						'id' => $nodeNodeId,
						'node_id' => $nodeNodeId
					)
				)
			), $response);

			foreach ($nodes as $node) {
				$response['data']['nodes'][$node['id']] = $node;
				unset($response['data']['nodes'][$node['id']]['id']);

				foreach ($response['data']['node_ip_address_version_numbers'] as $nodeIpAddressVersionNumber) {
					$nodeIpAddresses = array(
						$node['external_ip_address_version_' . $nodeIpAddressVersionNumber],
						$node['internal_ip_address_version_' . $nodeIpAddressVersionNumber]
					);

					foreach (array_filter($nodeIpAddresses) as $nodeIpAddress) {
						$response['data']['node_ip_addresses'][$nodeIpAddressVersionNumber][$nodeIpAddress] = $nodeIpAddress;
					}
				}
			}

			foreach (array_values($response['data']['node_ip_address_versions']) as $nodeIpAddressVersionNumberKey => $nodeIpAddressVersionNumber) {
				if (empty($response['data']['node_ip_addresses'][$nodeIpAddressVersionNumber]) === true) {
					unset($response['data']['node_ip_address_version_numbers'][(128 / 4) + (96 * $nodeIpAddressVersionNumberKey)]);
				}
			}

			$nodeProcessPartKeys = array();

			foreach ($parameters['data']['node_process_types'] as $nodeProcessType) {
				$nodeProcessPartKeys[$nodeProcessType] = 0;
			}

			foreach ($nodeProcesses as $nodeProcess) {
				$response['data']['node_processes'][$nodeProcess['type']][$nodeProcessPartKeys[$nodeProcess['type']]][$nodeProcess['node_id']][$nodeProcess['id']] = $nodeProcess['port_number'];
				$nodeProcessPartKeys[$nodeProcess['type']] = abs($nodeProcessPartKeys[$nodeProcess['type']] + -1);
			}

			foreach ($nodeProcessCryptocurrencyDestinations as $nodeProcessCryptocurrencyDestination) {
				$response['data']['node_process_cryptocurrency_destinations'][$nodeProcessCryptocurrencyDestination['node_process_type']] = $nodeProcessCryptocurrencyDestination;
				unset($response['data']['node_process_cryptocurrency_destinations'][$nodeProcessCryptocurrencyDestination['node_process_type']]['node_process_type']);
			}

			foreach ($nodeProcessForwardingDestinations as $nodeProcessForwardingDestination) {
				$response['data']['node_process_forwarding_destinations'][$nodeProcessForwardingDestination['node_process_type']][$nodeProcessForwardingDestination['node_id']] = $nodeProcessForwardingDestination;
				unset($response['data']['node_process_forwarding_destinations'][$nodeProcessForwardingDestination['node_process_type']][$nodeProcessForwardingDestination['node_id']]['node_id']);
			}

			if (empty($nodeProcessNodeUsers) === false) {
				foreach ($nodeProcessNodeUsers as $nodeProcessNodeUser) {
					$response['data']['node_process_node_users'][$nodeProcessNodeUser['node_process_type']][$nodeProcessNodeUser['node_id']][$nodeProcessNodeUser['node_user_id']] = $nodeProcessNodeUser['node_user_id'];
					$response['data']['node_users'][$nodeProcessNodeUser['node_user_id']] = array(
						'authentication_strict_only_allowed_status' => $nodeProcessNodeUser['node_user_authentication_strict_only_allowed_status'],
						'node_request_destinations_only_allowed_status' => $nodeProcessNodeUser['node_user_node_request_destinations_only_allowed_status'],
						'node_request_logs_allowed_status' => $nodeProcessNodeUser['node_user_node_request_logs_allowed_status']
					);
				}

				foreach ($nodeProcessNodeUserAuthenticationCredentials as $nodeProcessNodeUserAuthenticationCredential) {
					$response['data']['node_users'][$nodeProcessNodeUserAuthenticationCredential['node_user_id']]['node_user_authentication_credentials'] = array(
						'password' => $nodeProcessNodeUserAuthenticationCredential['node_user_authentication_credential_password'],
						'username' => $nodeProcessNodeUserAuthenticationCredential['node_user_authentication_credential_username']
					);
				}

				foreach ($nodeProcessNodeUserAuthenticationSources as $nodeProcessNodeUserAuthenticationSource) {
					$response['data']['node_users'][$nodeProcessNodeUserAuthenticationSource['node_user_id']]['node_user_authentication_sources'][] = $nodeProcessNodeUserAuthenticationSource['node_user_authentication_source_ip_address'] . '/' . $nodeProcessNodeUserAuthenticationSource['node_user_authentication_source_ip_address_block_length'];
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
							if (empty($response['data']['node_users'][$nodeProcessNodeUserNodeRequestLimitRule['node_user_id']]['node_request_destinations_only_allowed_status']) === false) {
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

				foreach ($response['data']['node_ip_address_version_numbers'] as $nodeIpAddressVersionNumber) {
					if (empty($nodeProcessRecursiveDnsDestination['source_ip_address_version_' . $nodeIpAddressVersionNumber]) === false) {
						$response['data']['node_ip_addresses'][$nodeIpAddressVersionNumber][$nodeProcessRecursiveDnsDestination['listening_ip_address_version_' . $nodeIpAddressVersionNumber]] = $nodeProcessRecursiveDnsDestination['listening_ip_address_version_' . $nodeIpAddressVersionNumber];

						if (empty($response['data']['nodes'][$nodeProcessRecursiveDnsDestination['listening_ip_address_version_' . $nodeIpAddressVersionNumber . '_node_id']]['internal_ip_address_version_' . $nodeIpAddressVersionNumber]) === false) {
							$response['data']['node_process_recursive_dns_destinations'][$nodeProcessRecursiveDnsDestination['node_process_type']][$nodeProcessRecursiveDnsDestination['node_id']]['source_ip_address_version_' . $nodeIpAddressVersionNumber] = $response['data']['nodes'][$nodeProcessRecursiveDnsDestination['node_id']]['internal_ip_address_version_' . $nodeIpAddressVersionNumber];
						}
					}

					unset($response['data']['node_process_recursive_dns_destinations'][$nodeProcessRecursiveDnsDestination['node_process_type']][$nodeProcessRecursiveDnsDestination['node_id']]['listening_ip_address_version_' . $nodeIpAddressVersionNumber . '_node_id']);
				}
			}

			foreach ($nodeReservedInternalDestinations as $nodeReservedInternalDestination) {
				$response['data']['node_ip_addresses'][$nodeReservedInternalDestination['ip_address_version_number']][$nodeReservedInternalDestination['ip_address']] = $response['data']['node_reserved_internal_destination_ip_addresses'][$nodeReservedInternalDestination['ip_address_version_number']][$nodeReservedInternalDestination['ip_address']] = $nodeReservedInternalDestination['ip_address'];
				$response['data']['node_reserved_internal_destinations'][$nodeReservedInternalDestination['node_id']][$nodeReservedInternalDestination['ip_address_version_number']] = array(
					'ip_address' => $nodeReservedInternalDestination['ip_address'],
					'ip_address_version_number' => $nodeReservedInternalDestination['ip_address_version_number']
				);
			}

			foreach ($nodeReservedInternalSources as $nodeReservedInternalSource) {
				$response['data']['node_reserved_internal_sources'][$nodeReservedInternalSource['ip_address_version_number']][] = $nodeReservedInternalDestination['ip_address'] . '/' . $nodeReservedInternalDestination['ip_address_block_length'];
			}
		}

		$response['message'] = 'Nodes processed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'process_node') === true) {
		$response = _processNode($parameters, $response);
		_output($response);
	}
?>
