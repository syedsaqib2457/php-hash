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

		$nodeIds = array_filter(array(
			$node['id'],
			$node['node_id']
		));

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
				'in' => $parameters['databases']['nodes'],
				'where' => array(
					'either' => array(
						'id' => $nodeIds,
						'node_id' => $nodeIds
					)
				)
			));
		}

		foreach ($nodes as $node) {
			$response['data']['nodes'][$node['id']] = array(
				'external_ip_address_version_4' => $node['external_ip_address_version_4'],
				'external_ip_address_version_6' => $node['external_ip_address_version_6'],
				'internal_ip_address_version_4' => $node['internal_ip_address_version_4'],
				'internal_ip_address_version_6' => $node['internal_ip_address_version_6'],
				'status_activated' => $node['status_activated']
			);

			foreach ($response['data']['node_ip_address_versions'] as $nodeIpAddressVersion) {
				$nodeIpAddressess = array(
					$node['external_ip_address_version_' . $nodeIpAddressVersion],
					$node['internal_ip_address_version_' . $nodeIpAddressVersion]
				);

				foreach (array_filter($nodeIpAddressess) as $nodeIpAddress) {
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
			$response['data']['node_process_forwarding_destinations'][$nodeProcessForwardingDestination['node_process_type']][$nodeProcessForwardingDestination['node_id']] = array(
				'address_version_4' => $nodeProcessForwardingDestination['address_version_4'],
				'address_version_6' => $nodeProcessForwardingDestination['address_version_6'],
				'node_process_type' => $nodeProcessForwardingDestination['node_process_type'],
				'port_number_version_4' => $nodeProcessForwardingDestination['port_number_version_4'],
				'port_number_version_6' => $nodeProcessForwardingDestination['port_number_version_6']
			);
		}

		foreach ($nodeProcessRecursiveDnsDestinations as $nodeProcessRecursiveDnsDestination) {
			$response['data']['node_process_recursive_dns_destinations'][$nodeProcessRecursiveDnsDestination['node_process_type']][$nodeProcessRecursiveDnsDestination['node_id']] = array(
				'listening_ip_address_version_4' => $nodeProcessRecursiveDnsDestination['listening_ip_address_version_4'],
				'listening_ip_address_version_4_node_id' => $nodeProcessRecursiveDnsDestination['listening_ip_address_version_4_node_id'],
				'listening_ip_address_version_6' => $nodeProcessRecursiveDnsDestination['listening_ip_address_version_6'],
				'listening_ip_address_version_6_node_id' => $nodeProcessRecursiveDnsDestination['listening_ip_address_version_6_node_id'],
				'listening_port_number_version_4' => $nodeProcessRecursiveDnsDestination['listening_port_number_version_4'],
				'listening_port_number_version_6' => $nodeProcessRecursiveDnsDestination['listening_port_number_version_6'],
				'node_process_type' => $nodeProcessRecursiveDnsDestination['node_process_type'],
				'source_ip_address_version_4' => $nodeProcessRecursiveDnsDestination['source_ip_address_version_4'],
				'source_ip_address_version_6' => $nodeProcessRecursiveDnsDestination['source_ip_address_version_6']
			);

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

/*
if (empty($nodeProcessNodeUsers) === false) {
	$nodeProcessNodeUserIds = array();

	foreach ($nodeProcessNodeUsers as $nodeProcessNodeUser) {
		$response['data']['node_process_node_users'][$nodeProcessNodeUser['node_process_type']][$nodeProcessNodeUser['node_id']][$nodeProcessNodeUser['node_user_id']] = $nodeProcessNodeUser['node_user_id'];
		$nodeProcessNodeUserIds[$nodeProcessNodeUser['node_user_id']] = $nodeProcessNodeUser['node_user_id'];
	}

	$nodeUserNodeRequestDestinations = _list(array(
		'in' => $parameters['databases']['node_user_node_request_destinations'],
		'where' => array(
			'node_user_id' => $nodeProcessNodeUserIds
		)
	));
	$nodeUserNodeRequestLimitRules = _list(array(
		'in' => $parameters['databases']['node_user_node_request_limit_rules'],
		'where' => array(
			'node_user_id' => $nodeProcessNodeUserIds
		)
	));
	$nodeUsers = _list(array(
		'in' => $parameters['databases']['node_users'],
		'where' => array(
			'id' => $nodeProcessNodeUserIds
		)
	));
	// todo: list node user source ip addresses

	if (empty($nodeUsers) === false) {

foreach ($nodeUsers as $nodeUser) {
	$response['data']['node_users'][$nodeUser['id']] = array(
	);
						unset($response['data']['users'][$user['id']]['id']);
					}
					if 
					(empty($userRequestDestinations) 
					=== 
					false) 
					{
						$requestDestinationIds 
						= 
						array(); 
						foreach 
						($userRequestDestinations 
						as 
						$userRequestDestination) 
						{
							if 
							(empty($response['data']['users'][$user['id']]['status_allowing_request_destinations_only']) 
							=== 
							false) 
							{
								$requestDestinationIds[$userRequestDestination['request_destination_id']] 
								= 
								$response['data']['users'][$user['id']]['request_destination_ids'][$userRequestDestination['request_destination_id']] 
								= 
								$userRequestDestination['request_destination_id'];
							}
						}
						$requestDestinations 
						= 
						$this->fetch(array(
							'fields' 
							=> 
							array(
								'address', 
								'id'
							), 
							'from' 
							=> 
							'request_destinations', 
							'where' 
							=> 
							array(
								'id' 
								=> 
								$requestDestinationIds
							) 
						)); 
						$response['status_valid'] 
						= 
						($requestDestinations 
						!== 
						false); 
						if 
						($response['status_valid'] 
						=== 
						false) 
						{
							return 
							$response;
						}
						foreach 
						($requestDestinations 
						as 
						$requestDestination) 
						{
							$response['data']['request_destinations'][$requestDestination['id']] 
							= 
							$requestDestination['address'];
						}
					}
					if 
					(empty($userRequestLimitRules) 
					=== 
					false) 
					{
						foreach 
						($userRequestLimitRules 
						as 
						$userRequestLimitRule) 
						{
							if 
							(empty($userRequestLimitRule['request_destination_id']) 
							=== 
							false) 
							{
								if 
								(empty($response['data']['users'][$userRequestLimitRule['user_id']]['status_allowing_request_destinations_only']) 
								=== 
								false) 
								{
									if 
									(empty($response['data']['users'][$userRequestLimitRule['user_id']]['request_destination_ids']) 
									=== 
									false) 
									{
										unset($response['data']['users'][$userRequestLimitRule['user_id']]['request_destination_ids'][$userRequestLimitRule['request_destination_id']]);
									} else 
									} {
										unset($response['data']['users'][$userRequestLimitRule['user_id']]);
									}
								} else 
								} {
									$response['data']['users'][$userRequestLimitRule['user_id']]['request_destination_ids'][$userRequestLimitRule['request_destination_id']] 
									= 
									$userRequestLimitRule['request_destination_id'];
								}
							} else 
							} {
								unset($response['data']['users'][$userRequestLimitRule['user_id']]);
							}
						}
					}
				}
			}
			foreach 
			($nodeReservedInternalDestinations 
			as 
			$nodeReservedInternalDestination) 
			{
				$response['data']['node_ips'][$nodeReservedInternalDestination['ip_address_version']][$nodeReservedInternalDestination['ip_address']] 
				= 
				$response['data']['node_reserved_internal_destination_ip_addresses'][$nodeReservedInternalDestination['ip_address_version']][$nodeReservedInternalDestination['ip_address']] 
				= 
				$nodeReservedInternalDestination['ip_address']; 
				$response['data']['node_reserved_internal_destinations'][$nodeReservedInternalDestination['node_id']][$nodeReservedInternalDestination['ip_address_version']] 
				= array(
					'ip_address' 
					=> 
					$nodeReservedInternalDestination['ip_address'], 
					'ip_address_version' 
					=> 
					$nodeReservedInternalDestination['ip_address_version']
				);
			}
			$response['message'] = 
			'Nodes processed 
			successfully.'; return 
			$response;
		}
	*/
	}
	if ($parameters['action'] === 'process_node') {
		$response = _processNode($parameters, $response);
		_output($response);
	}
?>
