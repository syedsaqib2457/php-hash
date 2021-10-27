<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		$databases['nodes']
	), $parameters['databases'], $response);

	function processNode($parameters, $response) {
		// todo: verify no reserved internal ip duplicates before each process reconfig
		$response['data'] = array(
			'node_ip_versions' => array(
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
		$nodeParameters = array(
			'in' => $parameters['databases']['nodes']
		);

		if (empty($parameters['where']['authentication_token']) === false) {
			$nodeParameters['where']['authentication_token'] = $parameters['where']['authentication_token'];
		}

		if (empty($parameters['where']['id']) === false) {
			$nodeParameters['where']['either'] = array(
				'id' => $parameters['where']['id'],
				'node_id' => $parameters['where']['id']
			);
		}

		if (empty($nodeParameters['where']) === true) {
			$response['message'] = 'Node authentication token or ID is required, please try again.';
			return $response;
		}

		$node = _list($nodeParameters, $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node authentication token or ID, please try again';
			return $response;
		}

		/*
			($response['status_valid'] 
			=== false) {
				$this->_logUnauthorizedRequest(); 
				return 
				$response;
			}
			$nodeId = 
			$parameters['user']['node_id']; 
			if 
			(isset($parameters['data']['processed']) 
			=== true) {
				$nodeDataUpdated 
				= 
				$this->update(array(
					'data' 
					=> 
					array(
						'status_processed' 
						=> 
						boolval($parameters['data']['processed'])
					), 
					'in' 
					=> 
					'nodes', 
					'where' 
					=> 
					array(
						'id' 
						=> 
						$nodeId, 
						'node_id' 
						=> 
						$nodeId
					) )); 
				$response['status_valid'] 
				= 
				$nodeDataUpdated; 
				if 
				($response['status_valid'] 
				=== false) {
					return 
					$response;
				}
				$response['message'] 
				= 'Nodes 
				updated for 
				processing 
				successfully.'; 
				if 
				(empty($parameters['data']['processed']) 
				=== false) {
					$response['message'] 
					= 
					'Nodes 
					processed 
					successfully.';
				}
				return 
				$response;
			}
			/* 
			$existingNodeProcessPorts 
			= $this->fetch(array(
				'fields' => 
				array(
					'node_process_type', 
					'number', 
					'status_allowing'
				), 'from' => 
				'node_process_ports', 
				'where' => 
				array(
					'node_node_id' 
					=> 
					$nodeId
				) )); 
			$nodeCount = 
			$this->count(array(
				'in' => 
				'nodes', 
				'where' => 
				array(
					'status_processed' 
					=> 
					false, 
					'OR' 
					=> 
					array(
						'id' 
						=> 
						$nodeId, 
						'node_id' 
						=> 
						$nodeId
					) ) 
			)); 
			$response['status_valid'] 
			= (
				($existingNodeProcessPorts 
				!== false) && 
				(is_int($nodeCount) 
				=== true)
			); if 
			($response['status_valid'] 
			=== false) {
				return 
				$response;
			}
			$existingNodeProcessPortNumbers 
			= array(); foreach 
			($existingNodeProcessPorts 
			as 
			$existingNodeProcessPort) 
			{
				$existingNodeProcessPortNumbers[$existingNodeProcessPort['number']] 
				= 
				$existingNodeProcessPort['status_allowing'];
			}
			// todo: move node 
			// process validation 
			// to edit() method 
			// since open ports 
			// are static for 
			// automatic process 
			// load balancing
			foreach 
			($this->settings['node_process_type_default_port_numbers'] 
			as $nodeProcessType => 
			$nodeProcessTypeDefaultPortNumber) 
			{
				$nodeProcessCount 
				= 
				$this->count(array(
					'in' 
					=> 
					'node_processes', 
					'where' 
					=> 
					array(
						'node_id' 
						=> 
						$nodeId, 
						'type' 
						=> 
						$nodeProcessType
					) )); 
				if 
				((is_int($nodeProcessCount) 
				=== false)) {
					break;
				}
				if 
				($nodeProcessCount 
				< 10) {
					$nodeProcessData 
					= 
					$nodeProcessPortData 
					= 
					array(); 
					$nodeProcessPortNumber 
					= 
					$nodeProcessTypeDefaultPortNumber; 
					foreach 
					(range(1, 
					(10 - 
					$nodeProcessCount)) 
					as 
					$nodeProcessIndex) 
					{
						while 
						(
							($nodeProcessPortNumber 
							<= 
							65535) 
							&& 
							(isset($existingNodeProcessPortNumbers[$nodeProcessPortNumber]) 
							=== 
							true)
						) 
						{
							$nodeProcessPortNumber++;
						}
						if 
						(isset($existingNodeProcessPortNumbers[$nodeProcessPortNumber]) 
						=== 
						true) 
						{
							break;
						}
						$existingNodeProcessPortNumbers[$nodeProcessPortNumber] 
						= 
						false; 
						$nodeProcessCount++; 
						$nodeProcessData[] 
						= 
						array(
							'port_number' 
							=> 
							$nodeProcessPortNumber, 
							'type' 
							=> 
							$nodeProcessType
						); 
						$nodeProcessPortData[] 
						= 
						array(
							'number' 
							=> 
							$nodeProcessPortNumber, 
							'node_process_type' 
							=> 
							$nodeProcessType
						);
					}
					if 
					(empty($nodeProcessData) 
					=== 
					false) 
					{
						// todo: 
						// assign 
						// a 
						// node 
						// reserved 
						// internal 
						// ip 
						// to 
						// each 
						// new 
						// process
						$nodeProcessesSaved 
						= 
						$this->save(array(
							'data' 
							=> 
							$nodeProcessData, 
							'to' 
							=> 
							'node_processes'
						)); 
						$nodeProcessPortsSaved 
						= 
						$this->save(array(
							'data' 
							=> 
							$nodeProcessPortData, 
							'to' 
							=> 
							'node_process_ports'
						)); 
						$response['status_valid'] 
						= 
						(
							($nodeProcessesSaved 
							!== 
							false) 
							&& 
							($nodeProcessPortsSaved 
							!== 
							false)
						); 
						if 
						($response['status_valid'] 
						=== 
						false) 
						{
							return 
							$response;
						}
					}
				}
			}
			*/ $nodeCount = 
			$this->count(array(
				'in' => 
				'nodes', 
				'where' => 
				array(
					'status_processed' 
					=> 
					false, 
					'OR' 
					=> 
					array(
						'id' 
						=> 
						$nodeId, 
						'node_id' 
						=> 
						$nodeId
					) ) 
			)); 
			$response['status_valid'] 
			= (
				(is_int($nodeCount) 
				=== true) && 
				($nodeCount > 
				0)
			); if 
			($response['status_valid'] 
			=== false) {
				return 
				$response;
			}
			$nodeProcesses = 
			$this->fetch(array(
				'fields' => 
				array(
					'id', 
					'node_id', 
					'port_number', 
					'type'
				), 'from' => 
				'node_processes', 
				'where' => 
				array(
					'node_node_id' 
					=> 
					$nodeId, 
					'type' 
					=> 
					$nodeProcessType
				) )); 
			$nodeProcessForwardingDestinations 
			= $this->fetch(array(
				'fields' => 
				array(
					'address_version_4', 
					'address_version_6', 
					'node_id', 
					'node_process_type', 
					'port_number_version_4', 
					'port_number_version_6'
				), 'from' => 
				'node_process_forwarding_destinations', 
				'where' => 
				array(
					'node_node_id' 
					=> 
					$nodeId
				) )); 
			$nodeProcessRecursiveDnsDestinations 
			= $this->fetch(array(
				'fields' => 
				array(
					'listening_ip_version_4', 
					'listening_ip_version_4_node_id', 
					'listening_ip_version_6', 
					'listening_ip_version_6_node_id', 
					'listening_port_number_version_4', 
					'listening_port_number_version_4', 
					'node_id', 
					'node_process_type', 
					'source_ip_version_4', 
					'source_ip_version_6'
				), 'from' => 
				'node_process_recursive_dns_destinations', 
				'where' => 
				array(
					'node_node_id' 
					=> 
					$nodeId
				) )); 
			$nodeProcessUsers = 
			$this->fetch(array(
				'fields' => 
				array(
					'node_id', 
					'node_process_type', 
					'user_id'
				), 'from' => 
				'node_process_users', 
				'where' => 
				array(
					'node_node_id' 
					=> 
					$nodeId
				) )); 
			$nodeReservedInternalDestinations 
			= $this->fetch(array(
				'fields' => 
				array(
					'ip_address', 
					'ip_address_version', 
					'node_id'
				), 'from' => 
				'node_reserved_internal_destinations', 
				'where' => 
				array(
					'node_node_id' 
					=> 
					$nodeId, 
					'status_assigned' 
					=> 
					true
				) )); $nodes = 
			$this->fetch(array(
				'fields' => 
				array(
					'external_ip_version_4', 
					'external_ip_version_6', 
					'id', 
					'internal_ip_version_4', 
					'internal_ip_version_6', 
					'status_active'
				), 'from' => 
				'nodes', 
				'where' => 
				array(
					'OR' 
					=> 
					array(
						'id' 
						=> 
						$nodeId, 
						'node_id' 
						=> 
						$nodeId
					) ) 
			)); 
			$response['status_valid'] 
			= (
				($nodeProcesses 
				!== false) && 
				($nodeProcessForwardingDestinations 
				!== false) && 
				($nodeProcessRecursiveDnsDestinations 
				!== false) && 
				($nodeProcessUsers 
				!== false) && 
				($nodeReservedInternalDestinations 
				!== false) && 
				($nodes !== 
				false)
			); if 
			($response['status_valid'] 
			=== false) {
				return 
				$response;
			}
			$response['status_valid'] 
			= (empty($nodes) === 
			false); if 
			($response['status_valid'] 
			=== false) {
				$response['message'] 
				= 'Invalid 
				node ID, 
				please try 
				again.'; 
				return 
				$response;
			}
			$response['status_valid'] 
			= (empty($nodes) === 
			false); if 
			($response['status_valid'] 
			=== false) {
				$response['message'] 
				= 'Invalid 
				node ID, 
				please try 
				again.'; 
				return 
				$response;
			}
			foreach ($nodes as 
			$node) {
				$response['data']['nodes'][$node['id']] 
				= $node; 
				unset($response['data']['nodes'][$node['id']]['id']); 
				foreach 
				($nodeIpVersions 
				as 
				$nodeIpVersion) 
				{
					$nodeIps 
					= 
					array(
						$node['external_ip_version_' 
						. 
						$nodeIpVersion], 
						$node['internal_ip_version_' 
						. 
						$nodeIpVersion]
					); 
					foreach 
					(array_filter($nodeIps) 
					as 
					$nodeIp) 
					{
						$response['data']['node_ips'][$nodeIpVersion][$nodeIp] 
						= 
						$nodeIp;
					}
				}
			}
			$nodeIpVersions = 
			array_values($response['data']['node_ip_versions']); 
			foreach 
			($nodeIpVersions as 
			$nodeIpVersionKey => 
			$nodeIpVersion) {
				if 
				(empty($response['data']['node_ips'][$nodeIpVersion]) 
				=== true) {
					unset($response['data']['node_ip_versions'][(128 
					/ 4) + 
					(96 * 
					$nodeIpVersionKey)]);
				}
			}
			$nodeProcessPartKeys = 
			array(); foreach 
			($nodeProcesses as 
			$nodeProcess) {
				// todo: add 
				// system IP 
				// details as 
				// node to 
				// allow 
				// deployment 
				// of node 
				// processes 
				// on same 
				// system 
				// server
				if 
				(isset($nodeProcessPartKeys[$nodeProcess['node_id']]) 
				=== false) {
					$nodeProcessPartKeys[$nodeProcess['node_id']] 
					= 0;
				}
				$response['data']['node_processes'][$nodeProcess['type']][$nodeProcessPartKey][$nodeProcess['node_id']][$nodeProcess['id']] 
				= 
				$nodeProcess['port_number']; 
				$nodeProcessPartKey 
				= 
				abs($nodeProcessPartKey 
				+ -1);
			}
			foreach 
			($nodeProcessForwardingDestinations 
			as 
			$nodeProcessForwardingDestination) 
			{
				$response['data']['node_process_forwarding_destinations'][$nodeProcessForwardingDestination['node_process_type']][$nodeProcessForwardingDestination['node_id']] 
				= 
				$nodeProcessForwardingDestination; 
				unset($response['data']['node_process_forwarding_destinations'][$nodeProcessForwardingDestination['node_process_type']][$nodeProcessForwardingDestination['node_id']]);
			}
			foreach 
			($nodeProcessRecursiveDnsDestinations 
			as 
			$nodeProcessRecursiveDnsDestination) 
			{
				$response['data']['node_process_recursive_dns_destinations'][$nodeProcessRecursiveDnsDestination['node_process_type']][$nodeProcessRecursiveDnsDestination['node_id']] 
				= 
				$nodeProcessRecursiveDnsDestination; 
				foreach 
				($nodeIpVersions 
				as 
				$nodeIpVersion) 
				{
					if 
					(empty($nodeProcessRecursiveDnsDestination['source_ip_version_' 
					. 
					$nodeIpVersion]) 
					=== 
					false) 
					{
						$response['data']['node_ips'][$nodeIpVersion][$nodeProcessRecursiveDnsDestination['listening_ip_version_' 
						. 
						$nodeIpVersion]] 
						= 
						$nodeProcessRecursiveDnsDestination['listening_ip_version_' 
						. 
						$nodeIpVersion]; 
						if 
						(empty($response['data']['nodes'][$nodeProcessRecursiveDnsDestination['listening_ip_version_' 
						. 
						$nodeIpVersion 
						. 
						'_node_id']]['internal_ip_version_' 
						. 
						$nodeIpVersion]) 
						=== 
						false) 
						{
							$response['data']['node_process_recursive_dns_destinations'][$nodeProcessRecursiveDnsDestination['node_process_type']][$nodeProcessRecursiveDnsDestination['node_id']]['source_ip_version_' 
							. 
							$nodeIpVersion] 
							= 
							$response['data']['nodes'][$nodeProcessRecursiveDnsDestination['node_id']]['internal_ip_version_' 
							. 
							$nodeIpVersion];
						}
					}
					unset($response['data']['node_process_recursive_dns_destinations'][$nodeProcessRecursiveDnsDestination['node_process_type']][$nodeProcessRecursiveDnsDestination['node_id']]['listening_ip_version_' 
					. 
					$nodeIpVersion 
					. 
					'_node_id']);
				}
				unset($response['data']['node_process_recursive_dns_destinations'][$nodeProcessRecursiveDnsDestination['node_process_type']][$nodeProcessRecursiveDnsDestination['node_id']]['node_id']);
			}
			if 
			(empty($nodeProcessUsers) 
			=== false) {
				$nodeProcessUserIds 
				= array(); 
				foreach 
				($nodeProcessUsers 
				as 
				$nodeProcessUser) 
				{
					$response['data']['node_process_users'][$nodeProcessUser['node_process_type']][$nodeUser['node_id']][$nodeUser['user_id']] 
					= 
					$nodeUser['user_id']; 
					$nodeProcessUserIds[$nodeProcessUser['user_id']] 
					= 
					$nodeProcessUser['user_id'];
				}
				$userRequestDestinations 
				= 
				$this->fetch(array(
					'fields' 
					=> 
					array(
						'request_destination_id', 
						'user_id'
					), 
					'from' 
					=> 
					'user_request_destinations', 
					'where' 
					=> 
					array(
						'user_id' 
						=> 
						$nodeProcessUserIds
					) )); 
				$userRequestLimitRules 
				= 
				$this->fetch(array(
					'fields' 
					=> 
					array(
						'request_destination_id', 
						'request_limit_rule_id'
					), 
					'from' 
					=> 
					'user_request_limit_rules', 
					'where' 
					=> 
					array(
						'limit_until 
						!=' 
						=> 
						null, 
						'user_id' 
						=> 
						$nodeProcessUserIds
					) )); 
				$users = 
				$this->fetch(array(
					'fields' 
					=> 
					array(
						'authentication_password', 
						'authentication_username', 
						'authentication_whitelist', 
						'id', 
						'status_allowing_request_destinations_only', 
						'status_allowing_request_logs', 
						'status_requiring_strict_authentication'
					), 
					'from' 
					=> 
					'users', 
					'where' 
					=> 
					array(
						'id' 
						=> 
						$nodeProcessUserIds
					) )); 
				$response['status_valid'] 
				= (
					($userRequestDestinations 
					!== 
					false) 
					&& 
					($userRequestLimitRules 
					!== 
					false) 
					&& 
					($users 
					!== 
					false)
				); if 
				($response['status_valid'] 
				=== false) {
					return 
					$response;
				}
				if 
				(empty($users) 
				=== false) {
					foreach 
					($users 
					as 
					$user) 
					{
						$response['data']['users'][$user['id']] 
						= 
						$user; 
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
