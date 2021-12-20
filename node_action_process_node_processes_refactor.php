<?php
	function _processNodeProcesses($parameters, $response) {
		exec('sudo ' . $parameters['binary_files']['netstat'] . ' -i | grep -v : | grep -v face | grep -v lo | awk \'NR==1{print $1}\' 2>&1', $interfaceName);
		$parameters['interface_name'] = current($interfaceName);
		$parameters['ip_address_versions'] = array(
			'4' => array(
				'interface_type' => 'inet',
				'network_mask' => '32'
			),
			'6' => array(
				'interface_type' => 'inet6',
				'network_mask' => '128'
			)
		);
		exec('getconf PAGE_SIZE 2>&1', $kernelPageSize);
		$parameters['kernel_page_size'] = current($kernelPageSize);
		exec('free -b | grep "Mem:" | grep -v free | awk \'{print $2}\'', $memoryCapacityBytes);
		$parameters['memory_capacity_bytes'] = current($memoryCapacityBytes);
		$parameters['node_process_type_firewall_rule_set_index'] = 0;

		if (file_exists('/etc/ssh/sshd_config') === true) {
			exec('grep "Port " /etc/ssh/sshd_config | grep -v "#" | awk \'{print $2}\' 2>&1', $sshPortNumbers);
			$parameters['node_ssh_port_numbers'] = $sshPortNumbers;

			foreach ($parameters['node_ssh_port_numbers'] as $sshPortNumberKey => $sshPortNumber) {
				if (
					((strlen($sshPortNumber) > 5) === true) ||
					(is_numeric($sshPortNumber) === false)
				) {
					unset($parameters['node_ssh_port_numbers'][$sshPortNumberKey]);
				}
			}
		}

		if (file_exists('/usr/local/ghostcompute/system_action_process_node_current_response.json') === true) {
			$systemActionProcessNodeResponse = file_get_contents('/usr/local/ghostcompute/system_action_process_node_current_response.json');
			$systemActionProcessNodeResponse = json_decode($systemActionProcessNodeResponse, true);

			if (empty($systemActionProcessNodeResponse) === false) {
				$parameters['data']['current'] = $systemActionProcessNodeResponse;
			}
		}

		shell_exec('sudo ' . $parameters['binary_files']['wget'] . ' -O /usr/local/ghostcompute/system_action_process_node_next_response.json --no-dns-cache --timeout=600 --post-data "json={\"action\":\"process_node\",\"node_authentication_token\":\"' . $parameters['node_authentication_token'] . '\"}" ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php');

		if (file_exists('/usr/local/ghostcompute/system_action_process_node_next_response.json') === false) {
			$response['message'] = 'Error processing node, please try again.' . "\n";
			return $response;
		}

		$systemActionProcessNodeResponse = file_get_contents('/usr/local/ghostcompute/system_action_process_node_next_response.json');
		$systemActionProcessNodeResponse = json_decode($nodeProcessResponseFileContents, true);

		if ($systemActionProcessNodeResponse === false) {
			$response['message'] = 'Error processing node, please try again.' . "\n";
			return $response;
		}

		if (empty($systemActionProcessNodeResponse['data']) === false) {
			$parameters['data']['next'] = $systemActionProcessNodeResponse['data'];

			if (empty($parameters['data']['current']) === true) {
				$parameters['data']['current'] = $parameters['data']['next'];
			}
		}

		if (empty($parameters['data']['next']['nodes']) === true) {
			if (empty($parameters['data']['current']) === false) {
				// todo: ping api periodically for new nodes to process during current process port verification to speed up reconfiguration time

				foreach ($parameters['data']['current']['node_process_types'] as $nodeProcessType) {
					foreach (array(0, 1) as $nodeProcessPartKey) {
						$nodeIpVersion = key($parameters['data']['current']['node_process_type_firewall_rule_set_port_numbers'][$nodeProcessType][$nodeProcessPartKey]);

						foreach ($parameters['data']['current']['node_process_type_firewall_rule_set_port_numbers'][$nodeProcessType][$nodeProcessPartKey][$nodeIpVersion] as $nodeProcessTypeFirewallRuleSet => $nodeProcessPortNumbers) {
							foreach ($parameters['data']['current']['node_process_type_firewall_rule_set_reserved_internal_destinations'][$nodeProcessTypeFirewallRuleSet] as $nodeReservedInternalDestination) {
								foreach ($nodeProcessPortNumbers as $nodeProcessPortNumber) {
									$verifyNodeProcessResponse = _verifyNodeProcess($parameters, $nodeReservedInternalDestination['ip_address'], $nodeReservedInternalDestination['ip_address_version'], $nodeProcessPortNumber, $nodeProcessType) === false) {

									if ($verifyNodeProcessResponse === false) {
										// todo: add progress percentage status data
										$systemActionProcessNodeResponse = array();
										exec('sudo ' . $parameters['binary_files']['curl'] . ' --connect-timeout 60 --form-string "json={\"action\":\"process_node\",\"data\":{\"processed_status\":\"0\"},\"node_authentication_token\":\"' . $parameters['node_authentication_token'] . '\"}" --max-time 60 --silent ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php 2>&1', $systemActionProcessNodeResponse);
										return $response;
									}
								}
							}
						}
					}
				}
			}

			// todo: add default $response with "no new node data to process, etc"
			return;
		}

		$kernelOptions = array(
			'fs.aio-max-nr = 1000000000',
			'fs.file-max = 1000000000',
			'fs.nr_open = 1000000000',
			'fs.pipe-max-size = 10000000',
			'fs.suid_dumpable = 0',
			'kernel.core_uses_pid = 1',
			'kernel.hung_task_timeout_secs = 2',
			'kernel.io_delay_type = 3',
			'kernel.kptr_restrict = 2',
			'kernel.msgmax = 65535',
			'kernel.msgmnb = 65535',
			'kernel.printk = 7 7 7 7',
			'kernel.sem = 404 256000 64 2048',
			'kernel.shmmni = 32767',
			'kernel.sysrq = 0',
			'kernel.threads-max = 1000000000',
			'net.core.default_qdisc = fq',
			'net.core.dev_weight = 100000',
			'net.core.netdev_max_backlog = 1000000',
			'net.core.somaxconn = 1000000000',
			'net.ipv4.conf.all.accept_redirects = 0',
			'net.ipv4.conf.all.accept_source_route = 0',
			'net.ipv4.conf.all.arp_ignore = 1',
			'net.ipv4.conf.all.bootp_relay = 0',
			'net.ipv4.conf.all.forwarding = 0',
			'net.ipv4.conf.all.rp_filter = 1',
			'net.ipv4.conf.all.secure_redirects = 0',
			'net.ipv4.conf.all.send_redirects = 0',
			'net.ipv4.conf.all.log_martians = 0',
			'net.ipv4.icmp_echo_ignore_all = 0',
			'net.ipv4.icmp_echo_ignore_broadcasts = 0',
			'net.ipv4.icmp_ignore_bogus_error_responses = 1',
			'net.ipv4.ip_forward = 0',
			'net.ipv4.ip_local_port_range = 1024 65000',
			'net.ipv4.ipfrag_high_thresh = 64000000',
			'net.ipv4.ipfrag_low_thresh = 32000000',
			'net.ipv4.ipfrag_time = 10',
			'net.ipv4.neigh.default.gc_interval = 50',
			'net.ipv4.neigh.default.gc_stale_time = 10',
			'net.ipv4.neigh.default.gc_thresh1 = 32',
			'net.ipv4.neigh.default.gc_thresh2 = 1024',
			'net.ipv4.neigh.default.gc_thresh3 = 2048',
			'net.ipv4.route.gc_timeout = 2',
			'net.ipv4.tcp_adv_win_scale = 2',
			'net.ipv4.tcp_congestion_control = htcp',
			'net.ipv4.tcp_fastopen = 2',
			'net.ipv4.tcp_fin_timeout = 2',
			'net.ipv4.tcp_keepalive_intvl = 2',
			'net.ipv4.tcp_keepalive_probes = 2',
			'net.ipv4.tcp_keepalive_time = 2',
			'net.ipv4.tcp_low_latency = 1',
			'net.ipv4.tcp_max_orphans = 100000',
			'net.ipv4.tcp_max_syn_backlog = 1000000',
			'net.ipv4.tcp_max_tw_buckets = 100000000',
			'net.ipv4.tcp_moderate_rcvbuf = 1',
			'net.ipv4.tcp_no_metrics_save = 1',
			'net.ipv4.tcp_orphan_retries = 0',
			'net.ipv4.tcp_retries2 = 1',
			'net.ipv4.tcp_rfc1337 = 0',
			'net.ipv4.tcp_sack = 0',
			'net.ipv4.tcp_slow_start_after_idle = 0',
			'net.ipv4.tcp_syn_retries = 2',
			'net.ipv4.tcp_synack_retries = 2',
			'net.ipv4.tcp_syncookies = 0',
			'net.ipv4.tcp_thin_linear_timeouts = 1',
			'net.ipv4.tcp_timestamps = 1',
			'net.ipv4.tcp_tw_reuse = 0',
			'net.ipv4.tcp_window_scaling = 1',
			'net.ipv4.udp_rmem_min = 1',
			'net.ipv4.udp_wmem_min = 1',
			'net.ipv6.bindv6only = 0',
			'net.ipv6.conf.all.accept_redirects = 0',
			'net.ipv6.conf.all.accept_source_route = 0',
			'net.ipv6.conf.all.disable_ipv6 = 0',
			'net.ipv6.conf.all.forwarding = 0',
			'net.ipv6.ip6frag_high_thresh = 64000000',
			'net.ipv6.ip6frag_low_thresh = 32000000',
			'net.ipv6.neigh.default.gc_interval = 50',
			'net.ipv6.neigh.default.gc_stale_time = 10',
			'net.ipv6.neigh.default.gc_thresh1 = 32',
			'net.ipv6.neigh.default.gc_thresh2 = 1024',
			'net.ipv6.neigh.default.gc_thresh3 = 2048',
			'net.ipv6.route.gc_timeout = 2',
			'vm.dirty_background_ratio = 10',
			'vm.dirty_expire_centisecs = 10',
			'vm.dirty_ratio = 10',
			'vm.dirty_writeback_centisecs = 100',
			'vm.max_map_count = 1000000',
			'vm.mmap_min_addr = 4096',
			'vm.overcommit_memory = 0',
			'vm.swappiness = 0'
		);
		$kernelOptions = implode("\n", $kernelOptions);
		$filePutContentsResponse = file_put_contents('/etc/sysctl.conf', $kernelOptions);

		if (empty($filePutContentsResponse) === true) {
			$response['message'] = 'Error adding kernel options, please try again.' . "\n";
			return $response;
		}

		shell_exec('sudo ' . $parameters['binary_files']['sysctl'] . ' -p');
		$defaultSocketBufferMemoryBytes = ceil($parameters['memory_capacity_bytes'] * 0.0003);
		$dynamicKernelOptions = array(
			'kernel.shmall' => floor($parameters['memory_capacity_bytes'] / $parameters['kernel_page_size']),
			'kernel.shmmax' => $parameters['memory_capacity_bytes'],
			'net.core.optmem_max' => ceil($parameters['memory_capacity_bytes'] * 0.02),
			'net.core.rmem_default' => $defaultSocketBufferMemoryBytes,
			'net.core.rmem_max' => ($defaultSocketBufferMemoryBytes * 2),
			'net.core.wmem_default' => $defaultSocketBufferMemoryBytes,
			'net.core.wmem_max' => ($defaultSocketBufferMemoryBytes * 2)
		);
		$memoryCapacityPages = ceil($parameters['memory_capacity_bytes'] / $parameters['kernel_page_size']);

		foreach ($parameters['data']['next']['node_ip_address_version_numbers'] as $nodeIpAddressVersionNumber) {
			$dynamicKernelOptions['net.ipv' . $nodeIpAddressVersion . '.tcp_mem'] = $memoryCapacityPages . ' ' . $memoryCapacityPages . ' ' . $memoryCapacityPages;
			$dynamicKernelOptions['net.ipv' . $nodeIpAddressVersion . '.tcp_rmem'] = 1 . ' ' . $defaultSocketBufferMemoryBytes . ' ' . ($defaultSocketBufferMemoryBytes * 2);
			$dynamicKernelOptions['net.ipv' . $nodeIpAddressVersion . '.tcp_wmem'] = $dynamicKernelOptions['net.ipv' . $nodeIpAddressVersionNumber . '.tcp_rmem'];
			$dynamicKernelOptions['net.ipv' . $nodeIpAddressVersion . '.udp_mem'] = $dynamicKernelOptions['net.ipv' . $nodeIpAddressVersionNumber . '.tcp_mem'];
		}

		foreach ($dynamicKernelOptions as $dynamicKernelOptionKey => $dynamicKernelOptionValue) {
			shell_exec('sudo ' . $parameters['binary_files']['sysctl'] . ' -w ' . $dynamicKernelOptionKey . '="' . $dynamicKernelOptionValue . '"');
		}

		$nodeInterfaces = $nodeIpAddressesToDelete = array();

		foreach ($parameters['ip_address_versions'] as $ipAddressVersionNumber => $ipAddressVersion) {
			$existingNodeIpAddresses = array();
			exec('sudo ' . $parameters['binary_files']['ip'] . ' addr show dev ' . $parameters['interface_name'] . ' | grep "' . $ipAddressVersion['interface_type'] . ' " | grep "' . $ipAddressVersion['network_mask'] . ' " | awk \'{print substr($2, 0, length($2) - ' . ($ipAddressVersionNumber / 2) . ')}\'', $existingNodeIpAddresses);

			if (empty($parameters['data']['next']['node_ip_addresses'][$ipAddressVersionNumber]) === false) {
				foreach ($parameters['data']['next']['node_ip_addresses'][$ipAddressVersionNumber] as $nodeIpAddress) {
					$nodeInterfaces[] = 'shell_exec(\'' . ($command = 'sudo ' . $parameters['binary_files']['ip'] . ' -' . $ipAddressVersionNumber . ' addr add ' . $nodeIpAddress . '/' . $ipAddressVersion['network_mask'] . ' dev ' . $parameters['interface_name']) . '\');';
					shell_exec($command);
				}
			}

			$nodeIpAddressesToDelete[$ipAddressVersion] = array_diff($existingNodeIpAddresses, $parameters['data']['next']['node_ip_addresses'][$ipAddressVersionNumber]);
			shell_exec('sudo ' . $parameters['binary_files']['ipset'] . ' create _ hash:ip family ' . $ipAddressVersion['interface_type'] . ' timeout 0');

			foreach ($parameters['data']['next']['node_reserved_internal_destination_ip_addresses'][$ipAddressVersionNumber] as $nodeReservedInternalDestinationIpAddress) {
				shell_exec('sudo ' . $parameters['binary_files']['ipset'] . ' add _ ' . $nodeReservedInternalDestinationIpAddress);
			}
		}

		array_unshift($nodeInterfaces, '<?php');
		$nodeInterfaces = implode("\n", $nodeInterfaces);
		$filePutContentsResponse = file_put_contents('/usr/local/ghostcompute/node_interfaces.php', $nodeInterfaces);

		if (empty($filePutContentsResponse) === true) {
			$response['message'] = 'Error adding node interfaces, please try again.' . "\n";
			return $response;
		}

		if (empty($recursiveDnsNodeProcessDefaultServiceName) === true) {
			$recursiveDnsNodeProcessDefaultServiceName = 'named';

			if (is_dir('/etc/default/bind9') === true) {
				$recursiveDnsNodeProcessDefaultServiceName = 'bind9';
			}
		}

		$nodeProcessesToRemove = $nodeRecursiveDnsDestinations = array();

		foreach ($parameters['data']['next']['node_process_types'] as $nodeProcessType) {
			$parameters['data']['node_process_type_process_part_data_keys'][$nodeProcessType] = array(
				'current',
				'next'
			);

			foreach ($parameters['data']['current']['node_processes'][$nodeProcessType] as $nodeProcessNodeParts) {
				foreach ($nodeProcessNodeParts as $nodeProcessNodePart) {
					foreach ($nodeProcessNodePart as $nodeProcessNodeId => $nodeProcessPortNumbers) {
						foreach ($nodeProcessPortNumbers as $nodeProcessId => $nodeProcessPortNumber) {
							if (
								(empty($parameters['data']['next']['node_processes'][$nodeProcessType][0][$nodeProcessNodeId][$nodeProcessId]) === true) &&
								(empty($parameters['data']['next']['node_processes'][$nodeProcessType][1][$nodeProcessNodeId][$nodeProcessId]) === true)
							) {
								$nodeProcessesToRemove[$nodeProcessType][$nodeProcessId] = $nodeProcessId;
							}
						}
					}
				}
			}
		}

		$parameters['node_process_type_firewall_rule_sets'] = array();

		foreach (array(0, 1) as $nodeProcessPartKey) {
			_processNodeFirewall($nodeProcessPartKey);
			$nodeProcessPartKey = abs($nodeProcessPartKey - 1);

			foreach ($parameters['data']['next']['node_processes']['recursive_dns'][$nodeProcessPartKey] as $recursiveDnsNodeProcessNodeId => $recursiveDnsNodeProcessPortNumbers) {
				$recursiveDnsNodeProcessConfiguration = array(
					'a0' => 'acl privateNetworkIpBlocks {',
					'a1' => $parameters['data']['next']['reserved_network']['ip_blocks']['4'],
					'a2' => $parameters['data']['next']['reserved_network']['ip_blocks']['6'],
					'a3' => '};',
					'a4' => 'acl whitelistedSources {',
					'b0' => '};',
					'b1' => 'options {',
					'b2' => 'allow-query {',
					'b3' => 'privateNetworkIpBlocks;',
					'b4' => 'whitelistedSources;',
					'b5' => '}',
					'b6' => 'allow-recursion {',
					'b7' => 'privateNetworkIpBlocks;',
					'b8' => '}',
					'b9' => 'cleaning-interval 1;',
					'b10' => 'dnssec-enable yes;',
					'b11' => 'dnssec-must-be-secure mydomain.local no;',
					'b12' => 'dnssec-validation yes;',
					'b13' => 'empty-zones-enable no;',
					'b14' => 'lame-ttl 0;',
					'b15' => 'max-cache-ttl 1;',
					'b16' => 'max-ncache-ttl 1;',
					'b17' => 'max-zone-ttl 1;',
					'b19' => 'rate-limit {',
					'b20' => 'exempt-clients {',
					'b21' => 'any;',
					'b22' => '};',
					'b23' => '};',
					'b24' => 'resolver-query-timeout 10;',
					'b25' => 'tcp-clients 1000000000;',
					'd' => false,
					'e' => false,
					'f' => '};'
				);
				$recursiveDnsNodeProcessConfigurationIndexes = array(
					'a' => 5,
					'b' => 26,
					'c' => 0,
					'g' => 0
				);

				if (empty($parameters['data']['next']['node_process_node_users']['recursive_dns']) === false) {
					$recursiveDnsNodeProcessConfiguration['g' . $recursiveDnsNodeProcessConfigurationIndexes['g']] = 'logging {';
					$recursiveDnsNodeProcessConfigurationIndexes['g']++;

					foreach ($parameters['data']['next']['node_process_users']['recursive_dns'][$recursiveDnsNodeProcessNodeId] as $recursiveDnsNodeProcessNodeUserIds) {
						foreach ($recursiveDnsNodeProcessNodeUserIds as $recursiveDnsNodeProcessNodeUserId) {
							$recursiveDnsNodeProcessUser = $parameters['data']['next']['node_process_node_users'][$recursiveDnsNodeProcessNodeUserId];

							if (
								(empty($recursiveDnsNodeProcessConfigurationIndexes['node_process_node_user_ids'][$recursiveDnsNodeProcessNodeUserId]) === true) &&
								(empty($recursiveDnsNodeProcessNodeUser['authentication_whitelist']) === false)
							) {
								$recursiveDnsNodeProcessConfigurationIndexes['node_process_node_user_ids'][$recursiveDnsNodeProcessNodeUserId] = true;
								// todo: update authentication and whitelisted sources with new columns
								$recursiveDnsNodeProcessWhitelistedSources = explode("\n", $recursiveDnsNodeProcessNodeUser['authentication_whitelist']);

								foreach ($recursiveDnsNodeProcessWhitelistedSources as $recursiveDnsNodeProcessWhitelistedSource) {
									$recursiveDnsNodeProcessConfiguration['a' . $recursiveDnsNodeProcessConfigurationIndexes['a']] = $recursiveDnsNodeProcessWhitelistedSource . ';';
									$recursiveDnsNodeProcessConfigurationIndexes['a']++;
								}
							}

							$recursiveDnsNodeProcessName = $recursiveDnsNodeProcessNodeId . '_' . $recursiveDnsNodeProcessNodeUserId;
							$recursiveDnsNodeProcessConfiguration['g' . $recursiveDnsNodeProcessConfigurationIndexes['g']] = 'channel ' . $recursiveDnsNodeProcessName . ' {';
							$recursiveDnsNodeProcessConfigurationIndexes['g']++;
							$recursiveDnsNodeProcessConfiguration['g' . $recursiveDnsNodeProcessConfigurationIndexes['g']] = 'file "/var/log/recursive_dns/' . $recursiveDnsNodeProcessName . '"';
							$recursiveDnsNodeProcessConfigurationIndexes['g']++;
							$recursiveDnsNodeProcessConfiguration['g' . $recursiveDnsNodeProcessConfigurationIndexes['g']] = 'print-time yes';
							$recursiveDnsNodeProcessConfigurationIndexes['g']++;
							$recursiveDnsNodeProcessConfiguration['g' . $recursiveDnsNodeProcessConfigurationIndexes['g']] = '};';
							$recursiveDnsNodeProcessConfigurationIndexes['g']++;
							$recursiveDnsNodeProcessConfiguration['g' . $recursiveDnsNodeProcessConfigurationIndexes['g']] = 'category ' . $recursiveDnsNodeProcessName . ' {';
							$recursiveDnsNodeProcessConfigurationIndexes['g']++;
							$recursiveDnsNodeProcessConfiguration['g' . $recursiveDnsNodeProcessConfigurationIndexes['g']] = 'queries_log;';
							$recursiveDnsNodeProcessConfigurationIndexes['g']++;
							$recursiveDnsNodeProcessConfiguration['g' . $recursiveDnsNodeProcessConfigurationIndexes['g']] = '};';
							$recursiveDnsNodeProcessConfigurationIndexes['g']++;
						}
					}

					$recursiveDnsNodeProcessConfiguration['g' . $recursiveDnsNodeProcessConfigurationIndexes['g']] = '};';
				}

				$recursiveDnsNodeProcessesStart = true;
				$recursiveDnsNodeProcessInterfaceDestinationIpAddresses = $recursiveDnsNodeProcessNodeIpAddresses = array();

				foreach ($parameters['data']['next']['node_ip_address_version_numbers'] as $recursiveDnsNodeIpAddressVersionNumber) {
					$recursiveDnsNodeProcessConfigurationOptionSuffix = '';

					if ($recursiveDnsNodeIpAddressVersion === '6') {
						$recursiveDnsNodeProcessConfigurationOptionSuffix = '-v6';
					}

					if (empty($parameters['data']['next']['nodes'][$recursiveDnsNodeProcessNodeId]['external_ip_address_version_' . $recursiveDnsNodeIpAddressVersionNumber]) === false) {
						$recursiveDnsNodeProcessInterfaceSourceIpAddress = $recursiveDnsNodeProcessNodeIpAddresses[$recursiveDnsNodeIpAddressVersionNumber] = $parameters['data']['next']['nodes'][$recursiveDnsNodeProcessNodeId]['external_ip_address_version_' . $recursiveDnsNodeIpAddressVersionNumber];

						if (empty($parameters['data']['next']['nodes'][$recursiveDnsNodeProcessNodeId]['internal_ip_address_version_' . $recursiveDnsNodeIpAddressVersionNumber]) === false) {
							$recursiveDnsNodeProcessInterfaceSourceIpAddress = $recursiveDnsNodeProcessNodeIpAddresses[$recursiveDnsNodeIpAddressVersionNumber] = $parameters['data']['next']['nodes'][$recursiveDnsNodeProcessNodeId]['internal_ip_address_version_' . $recursiveDnsNodeIpAddressVersionNumber];
						}

						$recursiveDnsNodeProcessConfiguration['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = 'listen-on' . $recursiveDnsNodeProcessConfigurationOptionSuffix . ' {';
						$recursiveDnsNodeProcessConfigurationIndexes['b']++;
						$recursiveDnsNodeProcessConfiguration['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = false;
						$recursiveDnsNodeProcessInterfaceDestinationIpAddresses['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = $parameters['data']['next']['node_process_recursive_dns_destinations']['recursive_dns'][$recursiveDnsNodeProcessNodeId]['listening_ip_address_version_' . $recursiveDnsNodeIpAddressVersionNumber];
						$recursiveDnsNodeProcessConfigurationIndexes['b']++;

						if (empty($parameters['data']['current']['node_reserved_internal_destination_ip_addresses'][$recursiveDnsNodeIpAddressVersionNumber][$parameters['data']['next']['node_process_recursive_dns_destinations']['recursive_dns'][$recursiveDnsNodeProcessNodeId]['listening_ip_address_version_' . $recursiveDnsNodeIpAddressVersionNumber]]) === false) {
							$recursiveDnsNodeProcessesStart = false;
						}

						$recursiveDnsNodeProcessConfiguration['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = false;
						$recursiveDnsNodeProcessInterfaceDestinationIpAddressess['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = $parameters['data']['next']['node_reserved_internal_destinations'][$recursiveDnsNodeProcessNodeId][$recursiveDnsNodeIpAddressVersionNumber]['ip_address'];
						$recursiveDnsNodeProcessConfigurationIndexes['b']++;

						if (empty($parameters['data']['node_process_node_users']['recursive_dns'][$recursiveDnsNodeProcessNodeId]) === false) {
							$recursiveDnsNodeProcessConfiguration['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = false;
							$recursiveDnsNodeProcessInterfaceDestinationIpAddresses['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = $recursiveDnsNodeProcessInterfaceSourceIpAddress;
							$recursiveDnsNodeProcessConfigurationIndexes['b']++;

							if (empty($this->nodeData['current']['node_reserved_internal_destination_ip_addresses'][$recursiveDnsNodeIpVersion][$recursiveDnsNodeProcessInterfaceSourceIpAddress]) === false) {
								$recursiveDnsNodeProcessesStart = false;
							}
						}

						$recursiveDnsNodeProcessConfiguration['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = '};';
						$recursiveDnsNodeProcessConfigurationIndexes['b']++;
						$recursiveDnsNodeProcessConfiguration['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = 'query-source' . $recursiveDnsNodeProcessConfigurationOptionSuffix . ' address ' . $recursiveDnsNodeProcessInterfaceSourceIpAddress . ';';
						$recursiveDnsNodeProcessConfigurationIndexes['c']++;
					}
				}

				ksort($recursiveDnsNodeProcessConfiguration);

				foreach ($recursiveDnsNodeProcessPortNumbers as $recursiveDnsNodeProcessId => $recursiveDnsNodeProcessPortNumber) {
					// todo: add default node timeout column to wait for X seconds before closing open connections
					while (_verifyNodeProcessConnections($recursiveDnsNodeProcessNodeIpAddresses, $recursiveDnsNodeProcessPortNumber) === true) {
						sleep(1);
					}

					$recursiveDnsNodeProcessName = 'recursive_dns_' . $recursiveDnsNodeProcessId;

					if (file_exists('/etc/' . $recursiveDnsNodeProcessName . '/named.conf') === true) {
						$parameters['process_ids'] = _listProcessIds($recursiveDnsNodeProcessName . ' ', $recursiveDnsNodeProcessName . '/');

						if (empty($parameters['process_ids']) === false) {
							_killProcessIds($parameters, $response);
						}
					}

					foreach ($recursiveDnsNodeProcessInterfaceDestinationIpAddresses as $recursiveDnsNodeProcessInterfaceDestinationIpAddressIndex => $recursiveDnsNodeProcessInterfaceDestinationIpAddress) {
						$recursiveDnsNodeProcessConfiguration[$recursiveDnsNodeProcessInterfaceDestinationIpAddressIndex] = $recursiveDnsNodeProcessInterfaceDestinationIpAddress . ':' . $recursiveDnsNodeProcessPortNumber . ';';
					}

					$recursiveDnsNodeProcessConfiguration['d'] = '"/var/cache/' . $recursiveDnsNodeProcessName . '";';
					$recursiveDnsNodeProcessConfiguration['e'] = 'pid-file "/var/run/named/' . $recursiveDnsNodeProcessName . '.pid";';
					$recursiveDnsNodeProcessConfiguration = implode("\n", $recursiveDnsNodeProcessConfiguration);
					file_put_contents('/etc/' . $recursiveDnsNodeProcessName . '/named.conf.options', $recursiveDnsNodeProcessConfiguration);
					shell_exec('cd /usr/sbin && sudo ln /usr/sbin/named ' . $recursiveDnsNodeProcessName);
					$recursiveDnsNodeProcessService = array(
						'[Service]',
						'ExecStart=/usr/sbin/named_' . $recursiveDnsNodeProcessName . ' -f -c /etc/' . $recursiveDnsNodeProcessName . '/named.conf -S 40000 -u root'
					);
					file_put_contents('/lib/systemd/system/' . $recursiveDnsNodeProcessName . '.service', implode("\n", $recursiveDnsNodeProcessService));

					if (file_exists('/etc/default/' . $recursiveDnsNodeProcessName) === false) {
						copy('/etc/default/' . $recursiveDnsNodeProcessDefaultServiceName, '/etc/default/' . $recursiveDnsNodeProcessName);
					}

					if (file_exists('/etc/bind_' . $recursiveDnsNodeProcessName) === false) {
						shell_exec('sudo cp -r /etc/bind /etc/' . $recursiveDnsNodeProcessName);
						file_put_contents('/etc/' . $recursiveDnsNodeProcessName . '/named.conf', 'include "/etc/' . $recursiveDnsNodeProcessName . '/named.conf.options"; include "/etc/' . $recursiveDnsNodeProcessName . '/named.conf.local"; include "/etc/' . $recursiveDnsNodeProcessName . '/named.conf.default-zones";');
					}

					if (is_dir('/var/cache/' . $recursiveDnsNodeProcessName) === false) {
						mkdir('/var/cache/' . $recursiveDnsNodeProcessName);
					}

					shell_exec('sudo ' . $parameters['binary_files']['systemctl'] . ' daemon-reload');
					unlink('/var/run/named/' . $recursiveDnsNodeProcessName . '.pid');
					$recursiveDnsNodeProcessEnded = false;
					$recursiveDnsNodeProcessEndedTime = time();

					while ($recursiveDnsNodeProcessEnded === false) {
						$recursiveDnsNodeProcessEnded = (_verifyNodeProcess($parameter['data']['next']['node_reserved_internal_destinations'][$recursiveDnsNodeProcessNodeId][$recursiveDnsNodeIpAddressVersion]['ip_address'], $recursiveDnsNodeIpAddressVersion, $recursiveDnsNodeProcessPortNumber, 'recursive_dns') === false);
						sleep(1);
					}

					$recursiveDnsNodeProcessStarted = false;
					$recursiveDnsNodeProcessStartedTime = time();

					if ($recursiveDnsNodeProcessesStart === true) {
						while ($recursiveDnsNodeProcessStarted === false) {
							shell_exec('sudo ' . $parameters['binary_files']['service'] . ' ' . $recursiveDnsNodeProcessName . ' start');
							$recursiveDnsNodeProcessStarted = (_verifyNodeProcess($parameters['data']['next']['node_reserved_internal_destinations'][$recursiveDnsNodeProcessNodeId][$recursiveDnsNodeIpAddressVersion]['ip_address'], $recursiveDnsNodeIpAddressVersion, $recursiveDnsNodeProcessPortNumber, 'recursive_dns') === true);
							sleep(1);
						}
					} else {
						$parameters['reprocess'] = true;
					}

					if (file_exists('/var/run/named/' . $recursiveDnsNodeProcessName . '.pid') === true) {
						$recursiveDnsNodeProcessProcessId = file_get_contents('/var/run/named/' . $recursiveDnsNodeProcessName . '.pid');

						if (is_numeric($recursiveDnsNodeProcessProcessId) === true) {
							shell_exec('sudo ' . $parameters['binary_files']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -n1000000000');
							shell_exec('sudo ' . $parameters['binary_files']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -n=1000000000');
							shell_exec('sudo ' . $parameters['binary_files']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -s"unlimited"');
							shell_exec('sudo ' . $parameters['binary_files']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -s=unlimited');
						}
					}
				}
			}
		}

		$nodeRecursiveDnsDestinations = array();

		foreach ($parameters['data']['next']['node_recursive_dns_destinations']['recursive_dns'] as $nodeRecursiveDnsDestination) {
			foreach ($parameters['ip_address_versions'] as $ipAddressVersionNumber => $ipAddressVersion) {
				if (empty($nodeRecursiveDnsDestination['listening_ip_address_version_' . $ipAddressVersionNumber]) === false) {
					$nodeRecursiveDnsDestinations[] = 'nameserver [' . $nodeRecursiveDnsDestination['listening_ip_address_version_' . $ipAddressVersionNumber] . ']:' . $nodeRecursiveDnsDestination['port_number_version_' . $ipAddressVersionNumber];
				}
			}
		}

		$nodeRecursiveDnsDestinations = implode("\n", $nodeRecursiveDnsDestinations);
		file_put_contents('/usr/local/ghostcompute/resolv.conf', $nodeRecursiveDnsDestinations);
		$parameters['data']['node_process_type_process_part_data_keys']['recursive_dns'] = array(
			'next',
			'next'
		);
		// todo
	}

	function _verifyNodeProcess($parameters, $nodeProcessNodeIp, $nodeProcessNodeIpVersion, $nodeProcessPortNumber, $nodeProcessType) {
		$response = false;

		switch ($nodeProcessType) {
			case 'http_proxy':
			case 'socks_proxy':
				$nodeProcessTypeParameters = array(
					'http_proxy' => '-x',
					'socks_proxy' => '--socks5-hostname'
				);
				exec('sudo ' . $parameters['binary_files']['curl'] . ' -' . $nodeProcessNodeIpVersion . ' ' . $nodeProcessTypeParameters[$nodeProcessType] . ' ' . $nodeProcessNodeIp . ':' . $nodeProcessPortNumber . ' http://ghostcompute -v --connect-timeout 2 | grep " refused" 1 2>&1', $proxyNodeProcessResponse);
				$response = (empty($proxyNodeProcessResponse) === true);
				break;
			case 'recursive_dns':
				// todo: add dig to $parameters['binary_files']
				exec('dig -' . $nodeProcessNodeIpVersion . ' +time=2 +tries=1 ghostcompute @' . $nodeProcessNodeIp . ' -p ' . $nodeProcessPortNumber . ' | grep "Got answer" 2>&1', $recursiveDnsNodeProcessResponse);
				$response = (empty($recursiveDnsNodeProcessResponse) === false);
				break;
		}

		return $response;
	}

	if (($parameters['action'] === 'process_node_processes') === true) {
		$response = _processNodeProcesses($parameters, $response);
		_output($response);
	}
?>
