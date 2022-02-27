<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _listProcessIds($processName, $processFile = false) {
		$response = array();
		exec('ps -h -o pid -o cmd $(pgrep -f "' . $processName . '") | grep "' . $processName . '" | grep -v grep 2>&1', $processes);

		if (empty($processes) === false) {
			foreach ($processes as $process) {
				$processColumns = explode(' ', $process);
				$processColumns = array_filter($processColumns);

				if (
					(empty($processColumns) === false) &&
					(
						(empty($processFile) === true) ||
						((strpos($process, $processFile) === false) === true)
					)
				) {
					$processColumnKey = key($processColumns);
					$response[] = $processColumns[$processColumnKey];
				}
			}
		}

		return $response;
	}

	function _processFirewall($parameters) {
		$firewallBinaryFiles = array(
			'4' => $parameters['binaryFiles']['iptables-restore'],
			'6' => $parameters['binaryFiles']['ip6tables-restore']
		);
		$nodeProcessPartKeys = array(
			0,
			1
		);

		if (
			(isset($parameters['nodeProcessPartKey']) === true) &&
			(in_array($parameters['nodeProcessPartKey'], $nodeProcessPartKeys) === true)
		) {
			$nodeProcessPartKeys = array(
				$parameters['nodeProcessPartKey']
			);
		}

		foreach ($nodeProcessPartKeys as $nodeProcessPartKey) {
			$parameters['nodeProcessPartKey'] = $nodeProcessPartKey;
			$parameters = _processFirewallRuleSets($parameters);
		}

		foreach ($parameters['data']['next']['nodeIpAddressVersionNumbers'] as $nodeIpAddressVersionNetworkMask => $nodeIpAddressVersionNumber) {
			$firewallRules = array(
				'*filter',
				':INPUT ACCEPT [0:0]',
				':FORWARD ACCEPT [0:0]',
				':OUTPUT ACCEPT [0:0]',
				'-A INPUT -p icmp -m hashlimit --hashlimit-above 1/second --hashlimit-burst 2 --hashlimit-htable-gcinterval 100000 --hashlimit-htable-expire 10000 --hashlimit-mode srcip --hashlimit-name icmp --hashlimit-srcmask ' . $nodeIpAddressVersionNetworkMask . ' -j DROP'
			);

			foreach ($parameters['data']['next']['nodeSshPortNumbers'] as $nodeSshPortNumber) {
				$firewallRules[] = '-A INPUT -p tcp --dport ' . $nodeSshPortNumber . ' -m hashlimit --hashlimit-above 1/minute --hashlimit-burst 10 --hashlimit-htable-gcinterval 600000 --hashlimit-htable-expire 60000 --hashlimit-mode srcip --hashlimit-name ssh --hashlimit-srcmask ' . $nodeIpAddressVersionNetworkMask . ' -j DROP';
			}

			$firewallRules[] = 'COMMIT';
			$firewallRules[] = '*nat';
			$firewallRules[] = ':PREROUTING ACCEPT [0:0]';
			$firewallRules[] = ':INPUT ACCEPT [0:0]';
			$firewallRules[] = ':OUTPUT ACCEPT [0:0]';
			$firewallRules[] = ':POSTROUTING ACCEPT [0:0]';

			// todo: make sure prerouting NAT load balancing works with DNS from system requests and proxy process requests, use output instead of prerouting if not

			foreach ($parameters['data']['next']['nodeProcessTypes'] as $nodeProcessType) {
				$nodeProcessTypeFirewallRuleSetPortNumberIndexes = array();

				foreach ($nodeProcessPartKeys as $nodeProcessPartKey) {
					if (empty($parameters['nodeProcessTypeFirewallRuleSetPortNumbers'][$parameters['nodeProcessTypeFirewallRuleSetIndex']][$parameters['nodeProcessTypeProcessPartDataKeys'][$nodeProcessType][$nodeProcessPartKey]][$nodeProcessType][$nodeProcessPartKey][$nodeIpAddressVersionNumber]) === false) {
						foreach ($parameters['nodeProcessTypeFirewallRuleSetPortNumbers'][$parameters['nodeProcessTypeProcessPartDataKeys'][$nodeProcessType][$nodeProcessPartKey]][$nodeProcessType][$nodeProcessPartKey][$nodeIpAddressVersionNumber] as $nodeProcessTypeFirewallRuleSet => $nodeProcessPortNumbers) {
							if (empty($nodeProcessTypeFirewallRuleSetPortNumberIndexes[$nodeProcessTypeFirewallRuleSet]) === true) {
								$nodeProcessTypeFirewallRuleSetPortNumberIndexes[$nodeProcessTypeFirewallRuleSet] = 0;
							}

							$nodeProcessTypeFirewallRuleSetPortNumberIndexes[$nodeProcessTypeFirewallRuleSet] += count($nodeProcessPortNumbers);
						}
					}
				}

				foreach ($nodeProcessTypeFirewallRuleSetPortNumberIndexes as $nodeProcessTypeFirewallRuleSet => $nodeProcessTypeFirewallRuleSetPortNumberIndex) {
					foreach ($nodeProcessPartKeys as $nodeProcessPartKey) {
						foreach ($parameters['nodeProcessTypeFirewallRuleSetPortNumbers'][$parameters['nodeProcessTypeFirewallRuleSetIndex']][$parameters['nodeProcessTypeProcessPartDataKeys'][$nodeProcessType][$nodeProcessPartKey]][$nodeProcessType][$nodeProcessPartKey][$nodeIpAddressVersionNumber][$nodeProcessTypeFirewallRuleSet] as $nodeProcessPortNumbers) {
							foreach ($nodeProcessPortNumbers as $nodeProcessPortNumber) {
								$nodeProcessTypeFirewallRuleSetLoadBalancer = '-m statistic --mode nth --every ' . $nodeProcessTypeFirewallRuleSetPortNumberIndex . ' --packet 0 ';

								if ($nodeProcessTypeFirewallRuleSetPortNumberIndex === 0) {
									$nodeProcessTypeFirewallRuleSetLoadBalancer = '';
								}

								$nodeProcessTransportProtocols = array(
									'tcp',
									'udp'
								);

								if ($nodeProcessType === 'httpProxy') {
									unset($nodeProcessTransportProtocols[1]);
								}

								foreach ($nodeProcessTransportProtocols as $nodeProcessTransportProtocol) {
									$firewallRules[] = '-A PREROUTING -p ' . $nodeProcessTransportProtocol . ' -m set ! --match-set _ dst,src -m set --match-set ' . $nodeProcessTypeFirewallRuleSet . ' dst,src ' . $nodeProcessTypeFirewallRuleSetLoadBalancer . '-j DNAT --to-destination :' . $nodeProcessPortNumber . ' --persistent';
								}

								$nodeProcessTypeFirewallRuleSetPortNumberIndex--;
							}
						}
					}
				}
			}

			$firewallRules[] = 'COMMIT';
			$firewallRules[] = '*raw';
			$firewallRules[] = ':PREROUTING ACCEPT [0:0]';
			$firewallRules[] = ':OUTPUT ACCEPT [0:0]';

			if (($parameters['systemEndpointDestinationIpAddressType'] === 'publicNetwork') === true) {
				foreach ($parameters['data']['next']['nodeReservedInternalSources'][$nodeIpAddressVersionNumber] as $nodeReservedInternalSource) {
					foreach ($nodeReservedInternalSources as $nodeReservedInternalSource) {
						$firewallRules[] = '-A PREROUTING ! -i lo -s ' . $nodeReservedInternalSource . ' -j DROP';
					}
				}
			}

			foreach ($parameters['data']['next']['nodeSshPortNumbers'] as $nodeSshPortNumber) {
				$firewallRules[] = '-A PREROUTING -p tcp --dport ' . $nodeSshPortNumber . ' -j ACCEPT';
			}

			foreach ($parameters['nodeProcessTypeFirewallRuleSets'] as $nodeProcessTypeFirewallRuleSet) {
				$firewallRules[] = '-A PREROUTING -m set --match-set ' . $nodeProcessTypeFirewallRuleSet . ' dst,src -j ACCEPT';
			}

			$firewallRules[] = '-A PREROUTING -i ' . $parameters['interfaceName'] . ' -m set ! --match-set _ dst,src -j DROP';
			$firewallRules[] = 'COMMIT';
			unlink('/usr/local/firewall-security-api/ip-address-version-' . $nodeIpAddressVersionNumber . '-firewall-rules.dat');
			touch('/usr/local/firewall-security-api/ip-address-version-' . $nodeIpAddressVersionNumber . '-firewall-rules.dat');
			$firewallRuleParts = array();
			$firewallRulePartsKey = 0;

			foreach ($firewallRules as $firewallRulesKey => $firewallRule) {
				if ((($firewallRulesKey % 1000) === 0) === true) {
					$firewallRulePartsKey++;
				}

				$firewallRuleParts[$firewallRulePartsKey][] = $firewallRule;
			}

			foreach ($firewallRuleParts as $firewallRulePart) {
				$firewallRulePart = implode("\n", $firewallRulePart);
				shell_exec('sudo echo "' . $firewallRulePart . '" >> /usr/local/firewall-security-api/ip-address-version-' . $nodeIpAddressVersionNumber . '-firewall-rules.dat');
			}

			shell_exec('sudo ' . $firewallBinaryFiles[$nodeIpAddressVersionNumber] . ' < /usr/local/firewall-security-api/ip-address-version-' . $nodeIpAddressVersionNumber . '.dat');
			unlink('/usr/local/firewall-security-api/ip-address-version-' . $nodeIpAddressVersionNumber . '-firewall-rules.dat');
			sleep(1);
		}

		return $parameters;
	}

	function _processFirewallRuleSets($parameters) {
		foreach ($parameters['nodeProcessTypeProcessPartDataKeys'] as $nodeProcessType => $nodeProcessTypeProcessPartDataKeys) {
			$nodeProcessPortNumberHash = '';

			foreach ($parameters['data'][$nodeProcessTypeProcessPartDataKeys[$parameters['nodeProcessPartKey']]]['nodeProcesses'][$nodeProcessType][$parameters['nodeProcessPartKey']] as $nodeProcessNodeId => $nodeProcessPortNumbers) {
				$nodeReservedInternalDestinationIpAddressVersionNumber = key($parameters['data'][$nodeProcessTypeProcessPartDataKeys[$parameters['nodeProcessPartKey']]]['nodeReservedInternalDestinations'][$nodeProcessNodeId]);
				$nodeProcessPortNumbersVerified = array();

				foreach ($nodeProcessPortNumbers as $nodeProcessId => $nodeProcessPortNumber) {
					if (_verifyNodeProcess($parameters['binaryFiles'], $parameters['data'][$nodeProcessTypeProcessPartDataKeys[$parameters['nodeProcessPartKey']]]['nodeReservedInternalDestinations'][$nodeProcessNodeId][$nodeReservedInternalDestinationIpAddressVersionNumber]['ipAddress'], $nodeReservedInternalDestinationIpAddressVersionNumber, $nodeProcessPortNumber, $nodeProcessType) === true) {
						$nodeProcessPortNumberHash .= '_' . $nodeProcessPortNumber;
						$nodeProcessPortNumbersVerified[] = $nodeProcessPortNumber;
					}
				}

				$nodeProcessPortNumberHash = sha1($nodeProcessPortNumberHash);

				foreach ($parameters['data'][$nodeProcessTypeProcessPartDataKeys[$parameters['nodeProcessPartKey']]]['nodeIpAddressVersionNumbers'] as $nodeIpAddressVersionNumber) {
					if (empty($parameters['data'][$nodeProcessTypeProcessPartDataKeys[$parameters['nodeProcessPartKey']]]['nodes'][$nodeProcessNodeId]['externalIpAddressVersion' . $nodeIpAddressVersionNumber]) === false) {
						$nodeProcessNodeIpAddress = $parameters['data'][$nodeProcessTypeProcessPartDataKeys[$parameters['nodeProcessPartKey']]]['nodes'][$nodeProcessNodeId]['externalIpAddressVersion' . $nodeIpAddressVersionNumber];

						if (empty($parameters['data'][$nodeProcessTypeProcessPartDataKeys[$parameters['nodeProcessPartKey']]]['nodes'][$nodeProcessNodeId]['internalIpAddressVersion' . $nodeIpAddressVersionNumber]) === false) {
							$nodeProcessNodeIpAddress = $parameters['data'][$nodeProcessTypeProcessPartDataKeys[$parameters['nodeProcessPartKey']]]['nodes'][$nodeProcessNodeId]['internalIpAddressVersion' . $nodeIpAddressVersionNumber];
						}

						$parameters['nodeProcessTypeFirewallRuleSetPortNumbers'][$parameters['nodeProcessTypeFirewallRuleSetIndex']][$nodeProcessTypeProcessPartDataKeys[$parameters['nodeProcessPartKey']]][$nodeProcessType][$parameters['nodeProcessPartKey']][$nodeIpAddressVersionNumber][($nodeProcessTypeFirewallRuleSet = $nodeProcessTypeProcessPartDataKeys[$parameters['nodeProcessPartKey']] . '_' . $nodeIpAddressVersionNumber . '_' . $parameters['nodeProcessPartKey'] . '_' . $nodeProcessType . '_' . $nodeProcessPortNumberHash . '_' . $parameters['nodeProcessTypeFirewallRuleSetIndex'])] = $nodeProcessPortNumbersVerified;
						shell_exec('sudo ' . $parameters['binaryFiles']['ipset'] . ' create ' . $nodeProcessTypeProcessPartDataKeys[$parameters['nodeProcessPartKey']] . '_' . $nodeIpAddressVersionNumber . '_' . $parameters['nodeProcessPartKey'] . '_' . $nodeProcessType . '_' . $nodeProcessPortNumberHash . '_' . $parameters['nodeProcessTypeFirewallRuleSetIndex'] . ' hash:ip,port family ' . $parameters['ipAddressVersions'][$nodeIpAddressVersionNumber]['interfaceType'] . ' timeout 0');

						foreach ($nodeProcessPortNumbers as $nodeProcessPortNumber) {
							shell_exec('sudo ' . $parameters['binaryFiles']['ipset'] . ' add ' . $nodeProcessTypeProcessPartDataKeys[$parameters['nodeProcessPartKey']] . '_' . $nodeIpAddressVersionNumber . '_' . $parameters['nodeProcessPartKey'] . '_' . $nodeProcessType . '_' . $nodeProcessPortNumberHash . '_' . $parameters['nodeProcessTypeFirewallRuleSetIndex'] . ' ' . $nodeProcessNodeIpAddress . ',tcp:' . $nodeProcessPortNumber);
							shell_exec('sudo ' . $parameters['binaryFiles']['ipset'] . ' add ' . $nodeProcessTypeProcessPartDataKeys[$parameters['nodeProcessPartKey']] . '_' . $nodeIpAddressVersionNumber . '_' . $parameters['nodeProcessPartKey'] . '_' . $nodeProcessType . '_' . $nodeProcessPortNumberHash . '_' . $parameters['nodeProcessTypeFirewallRuleSetIndex'] . ' ' . $nodeProcessNodeIpAddress . ',udp:' . $nodeProcessPortNumber);
						}

						$parameters['nodeProcessTypeFirewallRuleSets'][$nodeProcessTypeProcessPartDataKeys[$parameters['nodeProcessPartKey']] . '_' . $nodeIpAddressVersionNumber . '_' . $parameters['nodeProcessPartKey'] . '_' . $nodeProcessType . '_' . $nodeProcessPortNumberHash . '_' . $parameters['nodeProcessTypeFirewallRuleSetIndex']] = $nodeProcessTypeProcessPartDataKeys[$parameters['nodeProcessPartKey']] . '_' . $nodeIpAddressVersionNumber . '_' . $parameters['nodeProcessPartKey'] . '_' . $nodeProcessType . '_' . $nodeProcessPortNumberHash . '_' . $parameters['nodeProcessTypeFirewallRuleSetIndex'];
						$nodeReservedInternalDestinationIpAddressVersionNumber = $nodeIpAddressVersionNumber;
					}
				}

				if ($parameters['nodeProcessTypeFirewallRuleSetIndex'] === 4) {
					$parameters['data']['next']['nodeProcessTypeFirewallRuleSetReservedInternalDestinations'][$nodeProcessTypeProcessPartDataKeys[$parameters['nodeProcessPartKey']] . '_' . $nodeIpAddressVersionNumber . '_' . $parameters['nodeProcessPartKey'] . '_' . $nodeProcessType . '_' . $nodeProcessPortNumberHash . '_' . $parameters['nodeProcessTypeFirewallRuleSetIndex']][$nodeProcessNodeId] = $parameters['data'][$nodeProcessTypeProcessPartDataKeys[$parameters['nodeProcessPartKey']]]['nodeReservedInternalDestinations'][$nodeProcessNodeId][$nodeReservedInternalDestinationIpAddressVersionNumber];
				}
			}
		}

		$parameters['nodeProcessTypeFirewallRuleSetIndex']++;
		return $parameters;
	}

	function _processNodeProcesses($parameters, $response) {
		$parameters['processingProgressCheckpoints'] = array(
			'processingNodeProcesses',
			'verifyingNodeProcesses',
			'processingRecursiveDnsNodeProcesses',
			'processingProxyNodeProcesses',
			'processingFirewall',
			'processingCompleted'
		);
		end($parameters['processingProgressCheckpoints']);
		$parameters['processingProgressCheckpointCount'] = (key($parameters['processingProgressCheckpoints']) + 1);
		reset($parameters['processingProgressCheckpoints']);
		exec('sudo ' . $parameters['binaryFiles']['netstat'] . ' -i | grep -v : | grep -v face | grep -v lo | awk \'NR==1{print $1}\' 2>&1', $interfaceName);
		$parameters['interfaceName'] = current($interfaceName);
		$parameters['ipAddressVersions'] = array(
			'4' => array(
				'interfaceType' => 'inet',
				'networkMask' => '32'
			),
			'6' => array(
				'interfaceType' => 'inet6',
				'networkMask' => '128'
			)
		);
		exec('getconf PAGE_SIZE 2>&1', $kernelPageSize);
		$parameters['kernelPageSize'] = current($kernelPageSize);
		exec('free -b | grep "Mem:" | grep -v free | awk \'{print $2}\'', $memoryCapacityBytes);
		$parameters['memoryCapacityBytes'] = current($memoryCapacityBytes);
		$parameters['nodeProcessTypeFirewallRuleSetIndex'] = 0;

		if (file_exists('/etc/ssh/sshd_config') === true) {
			exec('grep "Port " /etc/ssh/sshd_config | grep -v "#" | awk \'{print $2}\' 2>&1', $sshPortNumbers);
			$parameters['nodeSshPortNumbers'] = $sshPortNumbers;

			foreach ($parameters['nodeSshPortNumbers'] as $sshPortNumberKey => $sshPortNumber) {
				if (
					((strlen($sshPortNumber) > 5) === true) ||
					(is_numeric($sshPortNumber) === false)
				) {
					unset($parameters['node_ssh_port_numbers'][$sshPortNumberKey]);
				}
			}
		}

		if (file_exists('/usr/local/firewall-security-api/system-action-process-node-current-response.json') === true) {
			$systemActionProcessNodeResponse = file_get_contents('/usr/local/firewall-security-api/system-action-process-node-current-response.json');
			$systemActionProcessNodeResponse = json_decode($systemActionProcessNodeResponse, true);

			if (empty($systemActionProcessNodeResponse) === false) {
				$parameters['data']['current'] = $systemActionProcessNodeResponse;
			}
		}

		$systemActionProcessNodeParameters = array(
			'action' => 'process-node',
			'data' => array(
				'processingStatus' => '1'
			),
			'nodeAuthenticationToken' => $parameters['nodeAuthenticationToken']
		);
		$parameters['processing_progress_checkpoints'] = _processNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);
		$systemActionProcessNodeParameterData = $systemActionProcessNodeParameters['data'];
		unset($systemActionProcessNodeParameters['data']);
		$encodedSystemActionProcessNodeParameters = json_encode($systemActionProcessNodeParameters);

		if ($encodedSystemActionProcessNodeParameters === false) {
			$response['message'] = 'Error processing node, please try again.';
			return $response;
		}

		shell_exec('sudo ' . $parameters['binary_files']['wget'] . ' -O /usr/local/cloud_node_automation_api/system_action_process_node_next_response.json --no-dns-cache --post-data \'json=' . $encodedSystemActionProcessNodeParameters . '\' --timeout=600 ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php');
		$systemActionProcessNodeParameters['data'] = $systemActionProcessNodeParameterData;
		unset($systemActionProcessNodeParameterData);

		if (file_exists('/usr/local/cloud_node_automation_api/system_action_process_node_next_response.json') === false) {
			$response['message'] = 'Error processing node, please try again.';
			return $response;
		}

		$systemActionProcessNodeResponse = file_get_contents('/usr/local/cloud_node_automation_api/system_action_process_node_next_response.json');
		$systemActionProcessNodeResponse = json_decode($systemActionProcessNodeResponse, true);
		unlink('/usr/local/cloud_node_automation_api/system_action_process_node_next_response.json');

		if ($systemActionProcessNodeResponse === false) {
			$response['message'] = 'Error processing node, please try again.';
			return $response;
		}

		$parameters['node_process_data_key'] = 'current';

		if (empty($systemActionProcessNodeResponse['data']) === false) {
			$parameters['data']['next'] = $systemActionProcessNodeResponse['data'];
			$parameters['node_process_data_key'] = 'next';

			if (empty($parameters['data']['current']) === true) {
				$parameters['data']['current'] = $parameters['data']['next'];
			}
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

		if (file_put_contents('/etc/sysctl.conf', $kernelOptions) === false) {
			$response['message'] = 'Error adding kernel options, please try again.';
			return $response;
		}

		shell_exec('sudo ' . $parameters['binary_files']['sysctl'] . ' -p');
		$defaultSocketBufferMemoryBytes = ceil($parameters['memory_capacity_bytes'] * 0.0003);
		$kernelOptions = array(
			'kernel.shmall' => floor($parameters['memory_capacity_bytes'] / $parameters['kernel_page_size']),
			'kernel.shmmax' => $parameters['memory_capacity_bytes'],
			'net.core.optmem_max' => ceil($parameters['memory_capacity_bytes'] * 0.02),
			'net.core.rmem_default' => $defaultSocketBufferMemoryBytes,
			'net.core.rmem_max' => ($defaultSocketBufferMemoryBytes * 2),
			'net.core.wmem_default' => $defaultSocketBufferMemoryBytes,
			'net.core.wmem_max' => ($defaultSocketBufferMemoryBytes * 2)
		);
		$memoryCapacityPages = ceil($parameters['memory_capacity_bytes'] / $parameters['kernel_page_size']);

		foreach ($parameters['data'][$parameters['node_process_data_key']]['node_ip_address_version_numbers'] as $nodeIpAddressVersionNumber) {
			$kernelOptions['net.ipv' . $nodeIpAddressVersion . '.tcp_mem'] = $memoryCapacityPages . ' ' . $memoryCapacityPages . ' ' . $memoryCapacityPages;
			$kernelOptions['net.ipv' . $nodeIpAddressVersion . '.tcp_rmem'] = 1 . ' ' . $defaultSocketBufferMemoryBytes . ' ' . ($defaultSocketBufferMemoryBytes * 2);
			$kernelOptions['net.ipv' . $nodeIpAddressVersion . '.tcp_wmem'] = $kernelOptions['net.ipv' . $nodeIpAddressVersionNumber . '.tcp_rmem'];
			$kernelOptions['net.ipv' . $nodeIpAddressVersion . '.udp_mem'] = $kernelOptions['net.ipv' . $nodeIpAddressVersionNumber . '.tcp_mem'];
		}

		foreach ($kernelOptions as $kernelOptionKey => $kernelOptionValue) {
			shell_exec('sudo ' . $parameters['binary_files']['sysctl'] . ' -w ' . $kernelOptionKey . '="' . $kernelOptionValue . '"');
		}

		$nodeActionProcessNetworkInterfaceIpAddressesCommands = array(
			'<?php',
			'if (empty($parameters) === true) {',
			'exit;',
			'}'
		);
		$nodeIpAddressesToDelete = array();

		foreach ($parameters['ip_address_versions'] as $ipAddressVersionNumber => $ipAddressVersion) {
			$existingNodeIpAddresses = false;
			exec('sudo ' . $parameters['binary_files']['ip'] . ' addr show dev ' . $parameters['interface_name'] . ' | grep "' . $ipAddressVersion['interface_type'] . ' " | grep "' . $ipAddressVersion['network_mask'] . ' " | awk \'{print substr($2, 0, length($2) - ' . ($ipAddressVersionNumber / 2) . ')}\'', $existingNodeIpAddresses);

			if (empty($parameters['data'][$parameters['node_process_data_key']]['node_ip_addresses'][$ipAddressVersionNumber]) === false) {
				foreach ($parameters['data'][$parameters['node_process_data_key']]['node_ip_addresses'][$ipAddressVersionNumber] as $nodeIpAddress) {
					$nodeActionProcessNetworkInterfaceIpAddressesCommands[] = 'shell_exec(\'' . 'sudo ' . $parameters['binary_files']['ip'] . ' -' . $ipAddressVersionNumber . ' addr add ' . $nodeIpAddress . '/' . $ipAddressVersion['network_mask'] . ' dev ' . $parameters['interface_name'] . '\');';
					shell_exec('sudo ' . $parameters['binary_files']['ip'] . ' -' . $ipAddressVersionNumber . ' addr add ' . $nodeIpAddress . '/' . $ipAddressVersion['network_mask'] . ' dev ' . $parameters['interface_name']);
				}
			}

			$nodeIpAddressesToDelete[$ipAddressVersionNumber] = array_diff($existingNodeIpAddresses, $parameters['data'][$parameters['node_process_data_key']]['node_ip_addresses'][$ipAddressVersionNumber]);
			shell_exec('sudo ' . $parameters['binary_files']['ipset'] . ' create _ hash:ip family ' . $ipAddressVersion['interface_type'] . ' timeout 0');

			foreach ($parameters['data'][$parameters['node_process_data_key']]['node_reserved_internal_destination_ip_addresses'][$ipAddressVersionNumber] as $nodeReservedInternalDestinationIpAddress) {
				shell_exec('sudo ' . $parameters['binary_files']['ipset'] . ' add _ ' . $nodeReservedInternalDestinationIpAddress);
			}
		}

		$nodeActionProcessNetworkInterfaceIpAddressesCommands = implode("\n", $nodeActionProcessNetworkInterfaceIpAddressesCommands);

		if (file_put_contents('/usr/local/cloud_node_automation_api/node_action_process_network_interface_ip_addresses.php', $nodeActionProcessNetworkInterfaceIpAddressesCommands) === false) {
			$response['message'] = 'Error processing network interface IP addresses, please try again.';
			return $response;
		}

		if (empty($parameters['data']['next']['nodes']) === true) {
			if (empty($parameters['data']['current']) === false) {
				$parameters['processing_progress_checkpoints'] = _processNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);

				foreach ($parameters['data']['current']['node_process_types'] as $nodeProcessType) {
					if (empty($parameters['data']['current']['node_process_type_firewall_rule_set_port_numbers'][$nodeProcessType]) === false) {
						foreach (array(0, 1) as $nodeProcessPartKey) {
							$nodeIpAddressVersionNumber = key($parameters['data']['current']['node_process_type_firewall_rule_set_port_numbers'][$nodeProcessType][$nodeProcessPartKey]);

							foreach ($parameters['data']['current']['node_process_type_firewall_rule_set_port_numbers'][$nodeProcessType][$nodeProcessPartKey][$nodeIpAddressVersionNumber] as $nodeProcessTypeFirewallRuleSet => $nodeProcessPortNumbers) {
								foreach ($parameters['data']['current']['node_process_type_firewall_rule_set_reserved_internal_destinations'][$nodeProcessTypeFirewallRuleSet] as $nodeReservedInternalDestination) {
									foreach ($nodeProcessPortNumbers as $nodeProcessPortNumber) {
										$verifyNodeProcessResponse = _verifyNodeProcess($parameters['binary_files'], $nodeReservedInternalDestination['ip_address'], $nodeReservedInternalDestination['ip_address_version'], $nodeProcessPortNumber, $nodeProcessType) === false) {

										if ($verifyNodeProcessResponse === false) {
											$systemActionProcessNodeParameters['data'] = array(
												'processed_status' => '0',
												'processing_progress_checkpoint' => 'processing_queued',
												'processing_progress_percentage' => '0',
												'processing_status' => '0'
											);
											_processNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);
											$response['message'] = 'Queueing node processing after node process verification error on destination address ' . $nodeReservedInternalDestination['ip_address'] . ':' . $nodeProcessPortNumber . '.';
											return $response;
										}
									}
								}
							}
						}
					}
				}
			}

			$systemActionProcessNodeParameters['data'] = array(
				'processed_status' => '1',
				'processing_progress_checkpoint' => 'processing_completed',
				'processing_progress_percentage' => '100',
				'processing_status' => '0'
			);
			_processNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);
			$response = $systemActionProcessNodeResponse;
			return $response;
		}

		unset($parameters['processing_progress_checkpoints'][2]);
		$parameters['processing_progress_checkpoints'] = _processNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);

		if (empty($recursiveDnsNodeProcessDefaultServiceName) === true) {
			$recursiveDnsNodeProcessDefaultServiceName = 'named';

			if (is_dir('/etc/default/bind9') === true) {
				$recursiveDnsNodeProcessDefaultServiceName = 'bind9';
			}
		}

		$nodeProcessesToRemove = $nodeRecursiveDnsDestinations = array();

		foreach ($parameters['data']['next']['node_process_types'] as $nodeProcessType) {
			$parameters['node_process_type_process_part_data_keys'][$nodeProcessType] = array(
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
			$parameters['node_process_part_key'] = $nodeProcessPartKey;
			$parameters = _processFirewall($parameters);
			$nodeProcessPartKey = abs($nodeProcessPartKey - 1);

			foreach ($parameters['data']['next']['nodeProcesses']['recursiveDns'][$nodeProcessPartKey] as $recursiveDnsNodeProcessNodeId => $recursiveDnsNodeProcessPortNumbers) {
				$recursiveDnsNodeProcessConfiguration = array(
					'a0' => 'acl nodeReservedInternalSources {',
					'b0' => '};',
					'b1' => 'acl nodeUserAuthenticationSources {',
					'c00' => '};',
					'c01' => 'options {',
					'c02' => 'allow-query {',
					'c03' => 'nodeReservedInternalSources;',
					'c04' => 'nodeUserAuthenticationSources;',
					'c05' => '}',
					'c06' => 'allow-recursion {',
					'c07' => 'nodeReservedInternalSources;',
					'c08' => '}',
					'c09' => 'cleaning-interval 1;',
					'c10' => 'dnssec-enable yes;',
					'c11' => 'dnssec-must-be-secure mydomain.local no;',
					'c12' => 'dnssec-validation yes;',
					'c13' => 'empty-zones-enable no;',
					'c14' => 'lame-ttl 0;',
					'c15' => 'max-cache-ttl 1;',
					'c16' => 'max-ncache-ttl 1;',
					'c17' => 'max-zone-ttl 1;',
					'c18' => 'rate-limit {',
					'c19' => 'exempt-clients {',
					'c20' => 'any;',
					'c21' => '};',
					'c22' => '};',
					'c23' => 'resolver-query-timeout 10;',
					'c24' => 'tcp-clients 1000000000;',
					'e' => false,
					'f' => false,
					'g' => '};'
				);
				$recursiveDnsNodeProcessConfigurationIndexes = array(
					'a' => 1,
					'b' => 2,
					'c' => 25,
					'd' => 0,
					'h' => 0
				);

				foreach ($parameters['data']['next']['nodeReservedInternalSources'] as $nodeReservedInternalSourceIpAddressVersionNumber => $nodeReservedInternalSources) {
					foreach ($nodeReservedInternalSources as $nodeReservedInternalSource) {
						$recursiveDnsNodeProcessConfiguration['a' . $recursiveDnsNodeProcessConfigurationIndexes['a']] = $nodeReservedInternalSource . ';';
						$recursiveDnsNodeProcessConfigurationIndexes['a']++;
					}
				}

				if (empty($parameters['data']['next']['nodeProcessNodeUsers']['recursiveDns'][$recursiveDnsNodeProcessNodeId]) === false) {
					$recursiveDnsNodeProcessConfiguration['h' . sprintf('%06u', $recursiveDnsNodeProcessConfigurationIndexes['h'])] = 'logging {';
					$recursiveDnsNodeProcessConfigurationIndexes['h']++;

					foreach ($parameters['data']['next']['nodeProcessNodeUsers']['recursiveDns'][$recursiveDnsNodeProcessNodeId] as $recursiveDnsNodeProcessNodeUserId) {
						if (empty($parameters['data']['next']['nodeUsers'][$recursiveDnsNodeProcessNodeUserId]['nodeUserAuthenticationSources']) === false) {
							foreach ($parameters['data']['next']['nodeUsers'][$recursiveDnsNodeProcessNodeUserId]['nodeUserAuthenticationSources'] as $recursiveDnsNodeProcessNodeUserAuthenticationSource) {
								$recursiveDnsNodeProcessConfiguration['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = $recursiveDnsNodeProcessNodeUserAuthenticationSource . ';';
								$recursiveDnsNodeProcessConfigurationIndexes['b']++;
							}

							$recursiveDnsNodeProcessConfiguration['h' . sprintf('%06u', $recursiveDnsNodeProcessConfigurationIndexes['h'])] = 'channel ' . $recursiveDnsNodeProcessNodeId . $recursiveDnsNodeProcessNodeUserId . ' {';
							$recursiveDnsNodeProcessConfigurationIndexes['h']++;
							$recursiveDnsNodeProcessConfiguration['h' . sprintf('%06u', $recursiveDnsNodeProcessConfigurationIndexes['h'])] = 'file "/var/log/recursiveDns/' . $recursiveDnsNodeProcessNodeId . $recursiveDnsNodeProcessNodeUserId . '"';
							$recursiveDnsNodeProcessConfigurationIndexes['h']++;
							$recursiveDnsNodeProcessConfiguration['h' . sprintf('%06u', $recursiveDnsNodeProcessConfigurationIndexes['h'])] = 'print-time yes';
							$recursiveDnsNodeProcessConfigurationIndexes['h']++;
							$recursiveDnsNodeProcessConfiguration['h' . sprintf('%06u', $recursiveDnsNodeProcessConfigurationIndexes['h'])] = '};';
							$recursiveDnsNodeProcessConfigurationIndexes['h']++;
							$recursiveDnsNodeProcessConfiguration['h' . sprintf('%06u', $recursiveDnsNodeProcessConfigurationIndexes['h'])] = 'category ' . $recursiveDnsNodeProcessNodeId . $recursiveDnsNodeProcessNodeUserId . ' {';
							$recursiveDnsNodeProcessConfigurationIndexes['h']++;
							$recursiveDnsNodeProcessConfiguration['h' . sprintf('%06u', $recursiveDnsNodeProcessConfigurationIndexes['h'])] = 'queries_log;';
							$recursiveDnsNodeProcessConfigurationIndexes['h']++;
							$recursiveDnsNodeProcessConfiguration['h' . sprintf('%06u', $recursiveDnsNodeProcessConfigurationIndexes['h'])] = '};';
							$recursiveDnsNodeProcessConfigurationIndexes['h']++;
						}
					}

					$recursiveDnsNodeProcessConfiguration['h' . sprintf('%06u', $recursiveDnsNodeProcessConfigurationIndexes['h'])] = '};';
				}

				$recursiveDnsNodeProcessesStart = true;
				$recursiveDnsNodeProcessInterfaceDestinationIpAddresses = $recursiveDnsNodeProcessNodeIpAddresses = array();

				foreach ($parameters['data']['next']['nodeIpAddressVersionNumbers'] as $recursiveDnsNodeIpAddressVersionNumber) {
					$recursiveDnsNodeProcessConfigurationOptionSuffix = '';

					if ($recursiveDnsNodeIpAddressVersionNumber === '6') {
						$recursiveDnsNodeProcessConfigurationOptionSuffix = '-v6';
					}

					if (empty($parameters['data']['next']['nodes'][$recursiveDnsNodeProcessNodeId]['externalIpAddressVersion' . $recursiveDnsNodeIpAddressVersionNumber]) === false) {
						$recursiveDnsNodeProcessConfiguration['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = 'listen-on' . $recursiveDnsNodeProcessConfigurationOptionSuffix . ' {';
						$recursiveDnsNodeProcessConfigurationIndexes['c']++;
						$recursiveDnsNodeProcessInterfaceSourceIpAddress = $recursiveDnsNodeProcessNodeIpAddresses[$recursiveDnsNodeIpAddressVersionNumber] = $parameters['data']['next']['nodes'][$recursiveDnsNodeProcessNodeId]['externalIpAddressVersion' . $recursiveDnsNodeIpAddressVersionNumber];
						$recursiveDnsNodeProcessConfiguration['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = false;

						if (empty($parameters['data']['next']['nodes'][$recursiveDnsNodeProcessNodeId]['internalIpAddressVersion' . $recursiveDnsNodeIpAddressVersionNumber]) === false) {
							$recursiveDnsNodeProcessInterfaceDestinationIpAddresses['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = $parameters['data']['next']['nodes'][$recursiveDnsNodeProcessNodeId]['internalIpAddressVersion' . $recursiveDnsNodeIpAddressVersionNumber];
							$recursiveDnsNodeProcessInterfaceSourceIpAddress = $recursiveDnsNodeProcessNodeIpAddresses[$recursiveDnsNodeIpAddressVersionNumber] = $parameters['data']['next']['nodes'][$recursiveDnsNodeProcessNodeId]['internalIpAddressVersion' . $recursiveDnsNodeIpAddressVersionNumber];
						} else {
							$recursiveDnsNodeProcessInterfaceDestinationIpAddresses['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = $parameters['data']['next']['nodes'][$recursiveDnsNodeProcessNodeId]['internalIpAddressVersion' . $recursiveDnsNodeIpAddressVersionNumber];
						}

						if (empty($parameters['data']['current']['nodeReservedInternalDestinationIpAddresses'][$recursiveDnsNodeIpAddressVersionNumber][$recursiveDnsNodeProcessInterfaceDestinationIpAddresses['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']]]) === false) {
							$recursiveDnsNodeProcessesStart = false;
						}

						$recursiveDnsNodeProcessConfigurationIndexes['c']++;
						$recursiveDnsNodeProcessConfiguration['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = false;
						$recursiveDnsNodeProcessInterfaceDestinationIpAddresses['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = $parameters['data']['next']['nodeReservedInternalDestinations'][$recursiveDnsNodeProcessNodeId][$recursiveDnsNodeIpAddressVersionNumber]['ipAddress'];
						$recursiveDnsNodeProcessConfigurationIndexes['c']++;

						if (empty($parameters['data']['next']['nodeProcessNodeUsers']['recursiveDns'][$recursiveDnsNodeProcessNodeId]) === false) {
							$recursiveDnsNodeProcessConfiguration['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = false;
							$recursiveDnsNodeProcessInterfaceDestinationIpAddresses['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = $recursiveDnsNodeProcessInterfaceSourceIpAddress;
							$recursiveDnsNodeProcessConfigurationIndexes['c']++;

							if (empty($parameters['data']['current']['nodeReservedInternalDestinationIpAddresses'][$recursiveDnsNodeIpAddressVersionNumber][$recursiveDnsNodeProcessInterfaceSourceIpAddress]) === false) {
								$recursiveDnsNodeProcessesStart = false;
							}
						}

						$recursiveDnsNodeProcessConfiguration['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = '};';
						$recursiveDnsNodeProcessConfigurationIndexes['c']++;
						$recursiveDnsNodeProcessConfiguration['d' . $recursiveDnsNodeProcessConfigurationIndexes['d']] = 'query-source' . $recursiveDnsNodeProcessConfigurationOptionSuffix . ' address ' . $recursiveDnsNodeProcessInterfaceSourceIpAddress . ';';
						$recursiveDnsNodeProcessConfigurationIndexes['d']++;
					}
				}

				ksort($recursiveDnsNodeProcessConfiguration);

				foreach ($recursiveDnsNodeProcessPortNumbers as $recursiveDnsNodeProcessId => $recursiveDnsNodeProcessPortNumber) {
					// todo: add default node timeout column to wait for X seconds before closing open connections
					while (_verifyNodeProcessConnections($parameters['binaryFiles'], $recursiveDnsNodeProcessNodeIpAddresses, $recursiveDnsNodeProcessPortNumber) === true) {
						sleep(1);
					}

					if (file_exists('/etc/recursiveDns' . $recursiveDnsNodeProcessId . '/named.conf') === true) {
						$recursiveDnsNodeProcessProcessIds = _listProcessIds('recursiveDns' . $recursiveDnsNodeProcessId . ' ', 'recursiveDns' . $recursiveDnsNodeProcessId . '/');

						if (empty($recursiveDnsNodeProcessProcessIds) === false) {
							_killProcessIds($parameters['binaryFiles'], $parameters['action'], $parameters['processId'], $recursiveDnsNodeProcessProcessIds);
						}
					}

					foreach ($recursiveDnsNodeProcessInterfaceDestinationIpAddresses as $recursiveDnsNodeProcessInterfaceDestinationIpAddressIndex => $recursiveDnsNodeProcessInterfaceDestinationIpAddress) {
						$recursiveDnsNodeProcessConfiguration[$recursiveDnsNodeProcessInterfaceDestinationIpAddressIndex] = $recursiveDnsNodeProcessInterfaceDestinationIpAddress . ':' . $recursiveDnsNodeProcessPortNumber . ';';
					}

					$recursiveDnsNodeProcessConfiguration['e'] = '"/var/cache/recursiveDns' . $recursiveDnsNodeProcessId . '";';
					$recursiveDnsNodeProcessConfiguration['f'] = 'pid-file "/var/run/named/recursiveDns' . $recursiveDnsNodeProcessId . '.pid";';
					$recursiveDnsNodeProcessConfiguration = implode("\n", $recursiveDnsNodeProcessConfiguration);
					file_put_contents('/etc/recursiveDns' . $recursiveDnsNodeProcessId . '/named.conf.options', $recursiveDnsNodeProcessConfiguration);
					shell_exec('cd /usr/sbin && sudo ln /usr/sbin/named recursiveDns' . $recursiveDnsNodeProcessId);
					$recursiveDnsNodeProcessService = array(
						'[Service]',
						'ExecStart=/usr/sbin/recursiveDns' . $recursiveDnsNodeProcessId . ' -f -c /etc/recursiveDns' . $recursiveDnsNodeProcessId . '/named.conf -S 40000 -u root'
					);
					$recursiveDnsNodeProcessService = implode("\n", $recursiveDnsNodeProcessService);
					file_put_contents('/lib/systemd/system/recursiveDns' . $recursiveDnsNodeProcessId . '.service', $recursiveDnsNodeProcessService);

					if (file_exists('/etc/default/recursiveDns' . $recursiveDnsNodeProcessId) === false) {
						copy('/etc/default/' . $recursiveDnsNodeProcessDefaultServiceName, '/etc/default/recursiveDns' . $recursiveDnsNodeProcessId);
					}

					if (file_exists('/etc/recursiveDns' . $recursiveDnsNodeProcessId) === false) {
						shell_exec('sudo cp -r /etc/bind /etc/recursiveDns' . $recursiveDnsNodeProcessId);
						file_put_contents('/etc/recursiveDns' . $recursiveDnsNodeProcessId . '/named.conf', 'include "/etc/recursiveDns' . $recursiveDnsNodeProcessId . '/named.conf.options"; include "/etc/recursiveDns' . $recursiveDnsNodeProcessId . '/named.conf.local"; include "/etc/recursiveDns' . $recursiveDnsNodeProcessId . '/named.conf.default-zones";');
					}

					if (is_dir('/var/cache/recursiveDns' . $recursiveDnsNodeProcessId) === false) {
						mkdir('/var/cache/recursiveDns' . $recursiveDnsNodeProcessId);
					}

					shell_exec('sudo ' . $parameters['binaryFiles']['systemctl'] . ' daemon-reload');
					unlink('/var/run/named/recursiveDns' . $recursiveDnsNodeProcessId . '.pid');
					// todo: add default node timeout column to wait for X seconds before proceeding with processing after processes start + stop
					$recursiveDnsNodeProcessResponse = false;

					while ($recursiveDnsNodeProcessResponse === false) {
						$recursiveDnsNodeProcessResponse = (_verifyNodeProcess($parameters['binaryFiles'], $parameters['data']['next']['nodeReservedInternalDestinations'][$recursiveDnsNodeProcessNodeId][$recursiveDnsNodeIpAddressVersionNumber]['ipAddress'], $recursiveDnsNodeIpAddressVersion, $recursiveDnsNodeProcessPortNumber, 'recursiveDns') === false);
						sleep(1);
					}

					$recursiveDnsNodeProcessResponse = false;

					if ($recursiveDnsNodeProcessesStart === true) {
						while ($recursiveDnsNodeProcessResponse === false) {
							shell_exec('sudo ' . $parameters['binaryFiles']['service'] . ' recursiveDns' . $recursiveDnsNodeProcessId . ' start');
							$recursiveDnsNodeProcessResponse = (_verifyNodeProcess($parameters['binaryFiles'], $parameters['data']['next']['nodeReservedInternalDestinations'][$recursiveDnsNodeProcessNodeId][$recursiveDnsNodeIpAddressVersionNumber]['ipAddress'], $recursiveDnsNodeIpAddressVersion, $recursiveDnsNodeProcessPortNumber, 'recursiveDns') === true);
							sleep(1);
						}
					} else {
						$systemActionProcessNodeParameters['data']['processedStatus'] = '0';
					}

					if (file_exists('/var/run/named/recursiveDns' . $recursiveDnsNodeProcessId . '.pid') === true) {
						$recursiveDnsNodeProcessProcessId = file_get_contents('/var/run/named/recursiveDns' . $recursiveDnsNodeProcessId . '.pid');

						if (is_numeric($recursiveDnsNodeProcessProcessId) === true) {
							shell_exec('sudo ' . $parameters['binaryFiles']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -n1000000000');
							shell_exec('sudo ' . $parameters['binaryFiles']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -n=1000000000');
							shell_exec('sudo ' . $parameters['binaryFiles']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -s"unlimited"');
							shell_exec('sudo ' . $parameters['binaryFiles']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -s=unlimited');
						}
					}
				}
			}
		}

		$nodeRecursiveDnsDestinations = array();

		foreach ($parameters['data']['next']['nodeRecursiveDnsDestinations']['recursiveDns'] as $nodeRecursiveDnsDestination) {
			foreach ($parameters['ipAddressVersions'] as $ipAddressVersionNumber => $ipAddressVersion) {
				if (empty($nodeRecursiveDnsDestination['destinationIpAddressVersion' . $ipAddressVersionNumber]) === false) {
					$nodeRecursiveDnsDestinations[] = 'nameserver [' . $nodeRecursiveDnsDestination['destinationIpAddressVersion' . $ipAddressVersionNumber] . ']:' . $nodeRecursiveDnsDestination['portNumberVersion' . $ipAddressVersionNumber];
				}
			}
		}

		$nodeRecursiveDnsDestinations = implode("\n", $nodeRecursiveDnsDestinations);

		if (file_put_contents('/usr/local/firewall-security-api/resolv.conf', $nodeRecursiveDnsDestinations) === false) {
			$response['message'] = 'Error adding node recursive DNS destinations, please try again.';
			return $response;
		}

		$parameters['processingProgressCheckpoints'] = _processNodeProcessingProgress($parameters['binaryFiles'], $parameters['processId'], $parameters['processingProgressCheckpoints'], $parameters['processingProgressCheckpointCount'], $systemActionProcessNodeParameters, $parameters['systemEndpointDestinationAddress']);
		$parameters['nodeProcessTypeProcessPartDataKeys']['recursiveDns'] = array(
			'next',
			'next'
		);

		foreach (array(0, 1) as $nodeProcessPartKey) {
			$parameters['nodeProcessPartKey'] = $nodeProcessPartKey;
			$parameters = _processFirewall($parameters);
			$nodeProcessPartKey = abs($nodeProcessPartKey - 1);

			foreach ($parameters['data']['next']['proxyNodeProcessTypes'] as $proxyNodeProcessTypeServiceName => $proxyNodeProcessType) {
				if (empty($parameters['data']['next']['nodeProcesses'][$proxyNodeProcessType][$nodeProcessPartKey]) === false) {
					foreach ($parameters['data']['next']['nodeProcesses'][$proxyNodeProcessType][$nodeProcessPartKey] as $proxyNodeProcessNodeId => $proxyNodeProcessPortNumbers) {
						$proxyNodeProcessConfiguration = array(
							'a0' => 'maxconn 20000',
							'a1' => 'nobandlimin',	
							'a2' => 'nobandlimout',
							'a3' => 'stacksize 0',
							'a4' => 'flush',
							'a5' => 'allow * * * * HTTP',
							'a6' => 'allow * * * * HTTPS',
							'a7' => 'nolog',
							'a8' => false,
							'f' => $proxyNodeProcessTypeServiceName . ' -a ',
							'g' => $proxyNodeProcessTypeServiceName . ' -a '
						);
						$proxyNodeProcessConfigurationIndexes = $proxyNodeProcessConfigurationPartIndexes = array(
							'b' => 0,
							'c' => 0,
							'd' => 0,
							'e' => 0,
							'h' => 0,
							'i' => 0
						);

						foreach ($parameters['data']['next']['nodeProcessNodeUsers'][$proxyNodeProcessType][$proxyNodeProcessNodeId] as $proxyNodeProcessNodeUserIds) {
							$proxyNodeProcessConfiguration['c' . sprintf('%010u', $proxyNodeProcessConfigurationIndexes['c'])] = 'auth iponly strong';
							$proxyNodeProcessConfigurationIndexes['c']++;

							foreach ($proxyNodeProcessNodeUserIds as $proxyNodeProcessNodeUserId) {
								$proxyNodeProcessNodeUser = $parameters['data']['next']['nodeUsers'][$proxyNodeProcessNodeUserId];
								$proxyNodeProcessNodeUserAuthenticationCredentialParts = array();

								if (empty($proxyNodeProcessNodeUser['nodeUserAuthenticationCredentials']) === false) {
									foreach ($proxyNodeProcessNodeUser['nodeUserAuthenticationCredentials'] as $proxyNodeProcessNodeUserAuthenticationCredential) {	
										if (($proxyNodeProcessConfigurationIndexes['b'] % 10) === 0) {
											$proxyNodeProcessConfiguration['b' . $proxyNodeProcessConfigurationPartIndexes['b']] = 'users ' . $proxyNodeProcessNodeUserAuthenticationCredential['username'] . ':CL:' . $proxyNodeProcessNodeUserAuthenticationCredential['password'];
											$proxyNodeProcessConfigurationPartIndexes['b'] = $proxyNodeProcessConfigurationIndexes['b'];
											$proxyNodeProcessNodeUserAuthenticationCredentialParts[$proxyNodeProcessConfigurationPartIndexes['b']] = $proxyNodeProcessNodeUserAuthenticationCredential['username'];
										} else {
											$proxyNodeProcessConfiguration['b' . $proxyNodeProcessConfigurationPartIndexes['b']] .= ' ' . $proxyNodeProcessNodeUserAuthenticationCredential['username'] . ':CL:' . $proxyNodeProcessNodeUserAuthenticationCredential['password'];
											$proxyNodeProcessNodeUserAuthenticationCredentialParts[$proxyNodeProcessConfigurationPartIndexes['b']] .= ',' . $proxyNodeProcessNodeUserAuthenticationCredential['username'];
										}

										$proxyNodeProcessConfigurationIndexes['b']++;
									}
								}

								if (
									(
										(empty($proxyNodeProcessNodeUser['nodeRequestDestinationIds']) === false) ||
										(empty($proxyNodeProcessNodeUser['nodeRequestDestinationsOnlyAllowedStatus']) === true)
									) &&
									(
										(empty($proxyNodeProcessNodeUser['nodeUserAuthenticationCredentials']) === false) ||
										(empty($proxyNodeProcessNodeUser['nodeUserAuthenticationSources']) === false)
									)
								) {
									$proxyNodeProcessNodeUserNodeRequestDestinationParts = array(
										'*'
									);

									foreach ($proxyNodeProcessNodeUser['nodeRequestDestinationIds'] as $proxyNodeProcessNodeUserNodeRequestDestinationId) {
										if (($proxyNodeProcessConfigurationIndexes['h'] % 10) === 0) {
											$proxyNodeProcessConfigurationPartIndexes['h'] = $proxyNodeProcessConfigurationIndexes['h'];
											$proxyNodeProcessNodeUserNodeRequestDestinationParts[$proxyNodeProcessConfigurationPartIndexes['h']] = $parameters['data']['next']['nodeRequestDestinations'][$proxyNodeProcessNodeUserNodeRequestDestinationId];
										} else {
											$proxyNodeProcessNodeUserNodeRequestDestinationParts[$proxyNodeProcessConfigurationPartIndexes['h']] .= ',' . $parameters['data']['next']['nodeRequestDestinations'][$proxyNodeProcessNodeUserNodeRequestDestinationId];
										}

										$proxyNodeProcessConfigurationIndexes['h']++;
									}

									if (empty($proxyNodeProcessNodeUser['nodeRequestLogsAllowedStatus']) === false) {
										$proxyNodeProcessConfiguration['c' . sprintf('%010u', $proxyNodeProcessConfigurationIndexes['c'])] = 'log /var/log/' . $proxyNodeProcessType . '/' . $proxyNodeProcessNodeId . $proxyNodeProcessNodeUserId;
										$proxyNodeProcessConfigurationIndexes['c']++;
										$proxyNodeProcessConfiguration['c' . sprintf('%010u', $proxyNodeProcessConfigurationIndexes['c'])] = 'logformat " %I _ %O _ %Y-%m-%d %H-%M-%S.%. _ %n _ %R _ %E _ %C"';
										$proxyNodeProcessConfigurationIndexes['c']++;
									}

									if (
										(empty($proxyNodeProcessNodeUser['nodeRequestDestinationsOnlyAllowedStatus']) === true) &&
										(empty($proxyNodeProcessNodeUserNodeRequestDestinationParts) === false)
									) {
										foreach ($proxyNodeProcessNodeUserNodeRequestDestinationParts as $proxyNodeProcessNodeUserNodeRequestDestinationPart) {
											$proxyNodeProcessConfiguration['c' . sprintf('%010u', $proxyNodeProcessConfigurationIndexes['c'])] = 'deny * * ' . $proxyNodeProcessNodeUserNodeRequestDestinationPart;
											$proxyNodeProcessConfigurationIndexes['c']++;
										}
									}

									if (empty($proxyNodeProcessNodeUser['authenticationStrictOnlyAllowedStatus']) === true) {
										if (
											(empty($proxyNodeProcessNodeUser['nodeRequestDestinationsOnlyAllowedStatus']) === false) &&
											(empty($proxyNodeProcessNodeUser['nodeUserAuthenticationCredentials']) === false)
										) {
											foreach ($proxyNodeProcessNodeUserAuthenticationCredentialParts as $proxyNodeProcessNodeUserAuthenticationCredentialPart) {
												foreach ($proxyNodeProcessNodeUserNodeRequestDestinationParts as $proxyNodeProcessNodeUserNodeRequestDestinationPart) {
													$proxyNodeProcessConfiguration['d' . sprintf('%010u', $proxyNodeProcessConfigurationIndexes['d'])] = 'allow ' . $proxyNodeProcessNodeUserAuthenticationCredentialPart . ' * ' . $proxyNodeProcessNodeUserNodeRequestDestinationPart;
													$proxyNodeProcessConfigurationIndexes['d']++;
												}
											}
										}

										$proxyNodeProcessNodeUserAuthenticationCredentialParts = array(
											'*'
										);
									}

									if (empty($proxyNodeProcessNodeUser['nodeUserAuthenticationSources']) === false) {
										$proxyNodeProcessNodeUserAuthenticationSourceParts = array();

										foreach ($proxyNodeProcessNodeUser['nodeUserAuthenticationSources'] as $proxyNodeProcessNodeUserAuthenticationSource) {
											if (($proxyNodeProcessConfigurationIndexes['i'] % 10) === 0) {
												$proxyNodeProcessNodeUserAuthenticationSourceParts[$proxyNodeProcessConfigurationIndexes['i']] = $proxyNodeProcessNodeUserAuthenticationSource;
												$proxyNodeProcessConfigurationPartIndexes['i'] = $proxyNodeProcessConfigurationIndexes['i'];
											} else {
												$proxyNodeProcessNodeUserAuthenticationSourceParts[$proxyNodeProcessConfigurationPartIndexes['i']] .= ',' . $proxyNodeProcessNodeUserAuthenticationSource;
											}

											$proxyNodeProcessConfigurationIndexes['i']++;
										}

										foreach ($proxyNodeProcessNodeUserAuthenticationCredentialParts as $proxyNodeProcessNodeUserAuthenticationCredentialPart) {
											foreach ($proxyNodeProcessNodeUserAuthenticationSourceParts as $proxyNodeProcessNodeUserAuthenticationSourcePart) {
												foreach ($proxyNodeProcessNodeUserNodeRequestDestinationParts as $proxyNodeProcessNodeUserNodeRequestDestinationPart) {
													$proxyNodeProcessConfiguration['c' . sprintf('%010u', $proxyNodeProcessConfigurationIndexes['c'])] = 'allow ' . $proxyNodeProcessNodeUserAuthenticationCredentialPart . ' ' . $proxyNodeProcessNodeUserAuthenticationSourcePart . ' ' . $proxyNodeProcessNodeUserNodeRequestDestinationPart;
													$proxyNodeProcessConfigurationIndexes['c']++;
												}
											}
										}
									}

									$proxyNodeProcessConfiguration['d' . sprintf('%010u', $proxyNodeProcessConfigurationIndexes['d'])] = 'deny *';
									$proxyNodeProcessConfigurationIndexes['d']++;
									$proxyNodeProcessConfiguration['d' . sprintf('%010u', $proxyNodeProcessConfigurationIndexes['d'])] = 'flush';
									$proxyNodeProcessConfigurationIndexes['d']++;
								}
							}
						}

						$proxyNodeProcessesStart = true;
						$proxyNodeProcessNodeIpAddresses = array();

						foreach ($parameters['data']['next']['nodeIpAddressVersionNumbers'] as $proxyNodeIpAddressVersionNumber) {
							if (empty($parameters['data']['next']['nodeProcessRecursiveDnsDestinations'][$proxyNodeProcessType][$proxyNodeProcessNodeId]['destinationIpAddressVersion' . $proxyNodeIpAddressVersionNumber]) === false) {
								$proxyNodeProcessConfiguration['e' . $proxyNodeProcessConfigurationIndexes['e']] = 'nserver ' . $parameters['data']['next']['nodeProcessRecursiveDnsDestinations'][$proxyNodeProcessType][$proxyNodeProcessNodeId]['destinationIpAddressVersion' . $proxyNodeIpAddressVersionNumber] . '[:' . $parameters['data']['next']['nodeProcessRecursiveDnsDestinations'][$proxyNodeProcessType][$proxyNodeProcessNodeId]['portNumberVersion' . $proxyNodeIpAddressVersionNumber] . ']';
								$proxyNodeProcessConfigurationIndexes['e']++;
							}

							if (empty($parameters['data']['next']['nodes'][$proxyNodeProcessNodeId]['externalIpAddressVersion' . $proxyNodeIpAddressVersionNumber]) === false) {
								$proxyNodeProcessInterfaceDestinationIpAddress = $proxyNodeProcessNodeIpAddresses[$proxyNodeIpAddressVersionNumber] = $parameters['data']['next']['nodes'][$proxyNodeProcessNodeId]['externalIpAddressVersion' . $proxyNodeIpAddressVersionNumber];

								if (empty($parameters['data']['next']['nodes'][$proxyNodeProcessNodeId]['internalIpAddressVersion' . $proxyNodeIpAddressVersionNumber]) === false) {
									$proxyNodeProcessInterfaceDestinationIpAddress = $proxyNodeProcessNodeIpAddresses[$proxyNodeIpAddressVersionNumber] = $parameters['data']['next']['nodes'][$proxyNodeProcessNodeId]['internalIpAddressVersion' . $proxyNodeIpAddressVersionNumber];

									if (empty($parameters['data']['current']['nodeReservedInternalDestinationIpAddresses'][$proxyNodeIpAddressVersionNumber][$proxyNodeProcessInterfaceDestinationIpAddress]) === false) {
										$proxyNodeProcessesStart = false;
									}
								}

								$proxyNodeProcessConfiguration['f'] .= ' -e' . $proxyNodeProcessInterfaceDestinationIpAddress . ' -i' . $proxyNodeProcessInterfaceDestinationIpAddress;
								$proxyNodeProcessConfiguration['g'] .= ' -e' . $parameters['data']['next']['nodeReservedInternalDestinations'][$proxyNodeProcessNodeId][$proxyNodeIpAddressVersionNumber]['ipAddress'] . ' -i' . $parameters['data']['next']['nodeReservedInternalDestinations'][$proxyNodeProcessNodeId][$proxyNodeIpAddressVersionNumber]['ipAddress'];
							}
						}

						ksort($proxyNodeProcessConfiguration);
						$proxyNodeProcessInterfaceConfigurations = array(
							'f' => $proxyNodeProcessConfiguration['f'],
							'g' => $proxyNodeProcessConfiguration['g']
						);

						foreach ($proxyNodeProcessPortNumbers as $proxyNodeProcessId => $proxyNodeProcessPortNumber) {
							while (_verifyNodeProcessConnections($parameters['binaryFiles'], $proxyNodeProcessNodeIpAddresses, $proxyNodeProcessPortNumber) === true) {
								sleep(1);
							}

							if (file_exists('/etc/3proxy/' . $proxyNodeProcessType . $proxyNodeProcessId . '.cfg') === true) {
								$proxyNodeProcessProcessIds = _listProcessIds($proxyNodeProcessType . $proxyNodeProcessId . ' ', '/etc/3proxy/' . $proxyNodeProcessType . $proxyNodeProcessId . '.cfg');

								if (empty($proxyNodeProcessProcessIds) === false) {
									_killProcessIds($parameters['binaryFiles'], $parameters['action'], $parameters['processId'], $proxyNodeProcessProcessIds);
								}
							}

							shell_exec('cd /bin && sudo ln /bin/3proxy ' . $proxyNodeProcessType . $proxyNodeProcessId);
							$proxyNodeProcessService = array(
								'[Service]',
								'ExecStart=/bin/' . $proxyNodeProcessType . $proxyNodeProcessId . ' /etc/3proxy/' . $proxyNodeProcessType . $proxyNodeProcessId . '.cfg')
							);
							$proxyNodeProcessService = implode("\n", $proxyNodeProcessService);
							file_put_contents('/etc/systemd/system/' . $proxyNodeProcessType . $proxyNodeProcessId . '.service', $proxyNodeProcessService);
							$proxyNodeProcessConfiguration['a8'] = 'pidfile /var/run/3proxy/' . $proxyNodeProcessType . $proxyNodeProcessId . '.pid';
							$proxyNodeProcessConfiguration['f'] = $proxyNodeProcessInterfaceConfigurations['f'] . ' -p' . $proxyNodeProcessPortNumber;
							$proxyNodeProcessConfiguration['g'] = $proxyNodeProcessInterfaceConfigurations['g'] . ' -p' . $proxyNodeProcessPortNumber;
							$proxyNodeProcessConfiguration = implode("\n", $proxyNodeProcessConfiguration);
							file_put_contents('/etc/3proxy/' . $proxyNodeProcessType . $proxyNodeProcessId . '.cfg', $proxyNodeProcessConfiguration);
							chmod('/etc/3proxy/' . $proxyNodeProcessType . $proxyNodeProcessId . '.cfg', 0755);
							shell_exec('sudo ' . $parameters['binaryFiles']['systemctl'] . ' daemon-reload');
							unlink('/var/run/3proxy/' . $proxyNodeProcessType . $proxyNodeProcessId . '.pid');
							// todo: add default node timeout column to wait for X seconds before proceeding with processing after processes start + stop
							$proxyNodeProcessResponse = false;

							while ($proxyNodeProcessResponse === false) {
								$proxyNodeProcessResponse = _verifyNodeProcess($parameters['binaryFiles'], $parameters['data']['next']['nodeReservedInternalDestinations'][$proxyNodeProcessNodeId][$proxyNodeIpAddressVersionNumber]['ipAddress'], $proxyNodeIpAddressVersionNumber, $proxyNodeProcessPortNumber, $proxyNodeProcessType) === false);
								sleep(1);
							}

							$proxyNodeProcessResponse = false;

							if ($proxyNodeProcessesStart === true) {
								while ($proxyNodeProcessResponse === false) {
									shell_exec('sudo ' . $parameters['binaryFiles']['service'] . ' ' . $proxyNodeProcessType . $proxyNodeProcessId . ' start');
									$proxyNodeProcessResponse = _verifyNodeProcess($parameters['binaryFiles'], $parameters['data']['next']['nodeReservedInternalDestinations'][$proxyNodeProcessNodeId][$proxyNodeIpAddressVersionNumber]['ipAddress'], $proxyNodeIpAddressVersionNumber, $proxyNodeProcessPortNumber, $proxyNodeProcessType) === true);
									sleep(1);
								}
							} else {
								$systemActionProcessNodeParameters['data']['processedStatus'] = '0';
							}

							if (file_exists('/var/run/3proxy/' . $proxyNodeProcessType . $proxyNodeProcessId . '.pid') === true) {
								$proxyNodeProcessProcessId = file_get_contents('/var/run/3proxy/' . $proxyNodeProcessType . $proxyNodeProcessId . '.pid');

								if (is_numeric($proxyNodeProcessProcessId) === true) {
									shell_exec('sudo ' . $parameters['binaryFiles']['prlimit'] . ' -p ' . $proxyNodeProcessProcessId . ' -n1000000000');
									shell_exec('sudo ' . $parameters['binaryFiles']['prlimit'] . ' -p ' . $proxyNodeProcessProcessId . ' -n=1000000000');
									shell_exec('sudo ' . $parameters['binaryFiles']['prlimit'] . ' -p ' . $proxyNodeProcessProcessId . ' -s"unlimited"');
									shell_exec('sudo ' . $parameters['binaryFiles']['prlimit'] . ' -p ' . $proxyNodeProcessProcessId . ' -s=unlimited');
								}
							}
						}
					}
				}
			}
		}

		$nodeProcessTypeFirewallRuleSetsToDestroy = $parameters['nodeProcessTypeFirewallRuleSets'];
		$parameters['nodeProcessTypeFirewallRuleSets'] = array();

		foreach ($parameters['data']['next']['nodeProcessTypes'] as $nodeProcessType) {
			$parameters['nodeProcessTypeProcessPartDataKeys'][$nodeProcessType] = array(
				'next',
				'next'
			);
		}

		$parameters['processingProgressCheckpoints'] = _processNodeProcessingProgress($parameters['binaryFiles'], $parameters['processId'], $parameters['processingProgressCheckpoints'], $parameters['processingProgressCheckpointCount'], $systemActionProcessNodeParameters, $parameters['systemEndpointDestinationAddress']);
		$parameters = _processFirewall($parameters);

		foreach ($nodeProcessTypeFirewallRuleSetsToDestroy as $nodeProcessTypeFirewallRuleSetToDestroy) {
			shell_exec('sudo ' . $parameters['binaryFiles']['ipset'] . ' destroy ' . $nodeProcessTypeFirewallRuleSetToDestroy);
		}

		foreach ($parameters['ipAddressVersions'] as $ipAddressVersionNumber => $ipAddressVersion) {
			if (empty($nodeIpAddressesToDelete[$ipAddressVersionNumber]) === false) {
				foreach ($nodeIpAddressesToDelete[$ipAddressVersionNumber] as $nodeIpAddressToDelete) {
					shell_exec('sudo ' . $parameters['binaryFiles']['ip'] . ' -' . $ipAddressVersionNumber . ' addr delete ' . $nodeIpAddressToDelete . '/' . $ipAddressVersion['networkMask'] . ' dev ' . $parameters['interfaceName']) . '\');';
				}
			}

			foreach ($parameters['data']['current']['nodeReservedInternalDestinationIpAddresses'][$ipAddressVersionNumber] as $nodeReservedInternalDestinationIpAddress) {
				if (empty($parameters['data']['next']['nodeReservedInternalDestinationIpAddresses'][$ipAddressVersionNumber][$nodeReservedInternalDestinationIpAddress]) === true) {
					shell_exec('sudo ' . $parameters['binaryFiles']['ipset'] . ' del _ ' . $nodeReservedInternalDestinationIpAddress);
				}
			}
		}

		foreach ($nodeProcessesToRemove as $nodeProcessType => $nodeProcessIds) {
			$nodeProcessProcessIds = array();

			foreach ($nodeProcessIds as $nodeProcessId) {
				switch ($nodeProcessType) {
					case 'httpProxy':
					case 'socksProxy':
						if (file_exists('/var/run/3proxy/' . $nodeProcessType . $nodeProcessId . '.pid') === true) {
							$nodeProcessProcessIds[] = file_get_contents('/var/run/3proxy/' . $nodeProcessType . $nodeProcessId . '.pid');
						}

						unlink('/bin/' . $nodeProcessType . $nodeProcessId);
						unlink('/etc/3proxy/' . $nodeProcessType . $nodeProcessId . '.cfg');
						unlink('/etc/systemd/system/' . $nodeProcessType . $nodeProcessId . '.service');
						unlink('/var/run/3proxy/' . $nodeProcessType . $nodeProcessId . '.pid');
						break;
					case 'recursiveDns':
						if (file_exists('/var/run/named/' . $nodeProcessType . $nodeProcess['id'] . '.pid') === true) {
							$nodeProcessProcessIds[] = file_get_contents('/var/run/named/' . $nodeProcessType . $nodeProcess['id'] . '.pid');
						}

						shell_exec('sudo rm -rf /etc/' . $nodeProcessType . $nodeProcessId . ' /var/cache/' . $nodeProcessType . $nodeProcessId);
						unlink('/etc/default/' . $recursiveDnsNodeProcessDefaultServiceName . $nodeProcessType . $nodeProcessId);
						unlink('/lib/systemd/system/' . $recursiveDnsNodeProcessDefaultServiceName . $nodeProcessType . $nodeProcessId . '.service');
						unlink('/usr/sbin/' . $nodeProcessType . $nodeProcessId);
						unlink('/var/run/named/' . $nodeProcessType . $nodeProcessId . '.pid');
						break;
				}
			}

			if (empty($nodeProcessProcessIds) === false) {
				$nodeProcessProcessIds = array_filter($nodeProcessProcessIds);
				_killProcessIds($parameters['binaryFiles'], $parameters['action'], $parameters['processId'], $nodeProcessProcessIds);
			}
		}

		$parameters['data']['current'] = array(
			'nodeIpAddressVersionNumbers' => $parameters['data']['next']['nodeIpAddressVersionNumbers'],
			'nodeIpAddresses' => $parameters['data']['next']['nodeIpAddresses'],
			'nodeProcesses' => $parameters['data']['next']['nodeProcesses'],
			'nodeProcessTypeFirewallRuleSetReservedInternalDestinations' => $parameters['data']['next']['nodeProcessTypeFirewallRuleSetReservedInternalDestinations'],
			'nodeProcessTypes' => $parameters['data']['next']['nodeProcessTypes'],
			'nodeRecursiveDnsDestinations' => $parameters['data']['next']['nodeRecursiveDnsDestinations'],
			'nodeReservedInternalDestinationIpAddresses' => $parameters['data']['next']['nodeReservedInternalDestinationIpAddresses'],
			'nodeReservedInternalDestinations' => $parameters['data']['next']['nodeReservedInternalDestinations'],
			'nodeReservedInternalSources' => $parameters['data']['next']['nodeReservedInternalSources'],
			'nodeSshPortNumbers' => $parameters['data']['next']['nodeSshPortNumbers']
		);
		$parameters['data']['current']['nodeProcessTypeFirewallRuleSetPortNumbers'] = $parameters['nodeProcessTypeFirewallRuleSetPortNumbers'][4]['next'];
		$encodedSystemActionProcessNodeResponse = json_encode($parameters['data']['current']);

		if (
			($encodedSystemActionProcessNodeResponse === false) ||
			(file_put_contents('/usr/local/firewall-security-api/system-action-process-node-current-response.json', $encodedSystemActionProcessNodeResponse) === false)
		) {
			$response['message'] = 'Error processing node, please try again.';
			return $response;
		}

		if (isset($systemActionProcessNodeParameters['data']['processingStatus']) === false) {
			$systemActionProcessNodeParameters['data']['processingStatus'] = '1';
		}

		$systemActionProcessNodeParameters['data']['processingStatus'] = '0';
		_processNodeProcessingProgress($parameters['binaryFiles'], $parameters['processId'], $parameters['processingProgressCheckpoints'], $parameters['processingProgressCheckpointCount'], $systemActionProcessNodeParameters, $parameters['systemEndpointDestinationAddress']);
		unlink('/usr/local/firewall-security-api/system-action-process-node-next-response.json');
		unset($systemActionProcessNodeResponse['data']);
		$response = $systemActionProcessNodeResponse;
		return $response;
	}

	function _processNodeProcessingProgress($binaryFiles, $currentProcessId, $processingProgressCheckpoints, $processingProgressCheckpointCount, $systemActionProcessNodeParameters, $systemEndpointDestinationAddress) {
		exec('ps -h -o pid -o cmd $(pgrep php) | grep "node-endpoint.php node-action-process-node-processes" | awk \'{print $1}\'', $nodeProcessProcessIds);

		if (
			(empty($nodeProcessProcessIds[2]) === false) &&
			((current($processingProgressCheckpoints) === 'listingNodeParameters') === true)
		) {
			exit;
		}

		$processingProgressCheckpointIndex = key($processingProgressCheckpoints);
		$systemActionProcessNodeParameters['data'] += array(
			'processingProgressCheckpoint' => $processingProgressCheckpoints[$processingProgressCheckpointIndex],
			'processingProgressPercentage' => ceil((($processingProgressCheckpointIndex + 1) / $processingProgressCheckpointCount) * 100)
		);
		$encodedSystemActionProcessNodeParameters = json_encode($systemActionProcessNodeParameters);
		unset($processingProgressCheckpoints[$processingProgressCheckpointIndex]);
		$processingProgressCheckpoint = current($processingProgressCheckpoints);

		if (
			($encodedSystemActionProcessNodeParameters === false) &&
			(($processingProgressCheckpoint === 'listingNodeParameters') === true)
		) {
			exit;
		}

		if (empty($encodedSystemActionProcessNodeParameters) === false) {
			shell_exec('sudo ' . $binaryFiles['wget'] . ' -O /usr/local/firewall-security-api/system-action-process-node-processing-status-' . $currentProcessId . '-response.json --no-dns-cache --post-data \'json=' . $encodedSystemActionProcessNodeParameters . '\' --timeout=10 ' . $systemEndpointDestinationAddress . '/system_endpoint.php');

			if (file_exists('/usr/local/firewall-security-api/system-action-process-node-processing-status-' . $currentProcessId . '-response.json') === true) {
				$systemActionProcessNodeProcessingStatusResponse = file_get_contents('/usr/local/firewall-security-api/system-action-process-node-processing-status-' . $currentProcessId . '-response.json');
				$systemActionProcessNodeProcessingStatusResponse = json_decode($systemActionProcessNodeResponse, true);

				if (empty($systemActionProcessNodeProcessingStatusResponse['data']['processing_progress_override_status']) === false) {
					exec('ps -h -o pid -o cmd $(pgrep php) | grep "node-endpoint.php node-action-process-node-processes" | awk \'{print $1}\'', $nodeProcessProcessIds);
					_killProcessIds($binaryFiles, 'processNodeProcesses', $currentProcessId, $nodeProcessProcessIds);
				} elseif (
					(empty($nodeProcessProcessIds[1]) === false) &&
					(($processingProgressCheckpoint === 'listingNodeParameters') === true)
				) {
					exit;
				}
			}
		}

		return $processingProgressCheckpoints;
	}

	function _verifyNodeProcess($binaryFiles, $nodeProcessNodeIpAddress, $nodeProcessNodeIpAddressVersionNumber, $nodeProcessPortNumber, $nodeProcessType) {
		$response = false;

		switch ($nodeProcessType) {
			case 'httpProxy':
			case 'socksProxy':
				$nodeProcessTypeParameters = array(
					'httpProxy' => '-x',
					'socksProxy' => '--socks5-hostname'
				);
				exec('sudo ' . $binaryFiles['curl'] . ' -' . $nodeProcessNodeIpAddressVersionNumber . ' ' . $nodeProcessTypeParameters[$nodeProcessType] . ' ' . $nodeProcessNodeIpAddress . ':' . $nodeProcessPortNumber . ' http://firewall-security-api -v --connect-timeout 2 | grep " refused" 1 2>&1', $proxyNodeProcessResponse);
				$response = (empty($proxyNodeProcessResponse) === true);
				break;
			case 'recursiveDns':
				exec($binaryFiles['dig'] . ' -' . $nodeProcessNodeIpAddressVersionNumber . ' +time=2 +tries=1 firewall-security-api @' . $nodeProcessNodeIpAddress . ' -p ' . $nodeProcessPortNumber . ' | grep "Got answer" 2>&1', $recursiveDnsNodeProcessResponse);
				$response = (empty($recursiveDnsNodeProcessResponse) === false);
				break;
		}

		return $response;
	}

	function _verifyNodeProcessConnections($binaryFiles, $nodeProcessNodeIpAddresses, $nodeProcessPortNumber) {
		foreach ($nodeProcessNodeIpAddresses as $nodeProcessNodeIpAddressVersionNumber => $nodeProcessNodeIpAddress) {
			if ($nodeProcessNodeIpAddressVersionNumber === '6') {
				$nodeProcessNodeIpAddress = '[' . $nodeProcessNodeIpAddress . ']';
			}

			exec('sudo ' . $binaryFiles['ss'] . ' -p -t -u state connected "( sport = :' . $nodeProcessPortNumber . ' )" src ' . $nodeProcessNodeIpAddress . ' | head -1 2>&1', $response);

			if (is_array($response) === false) {
				$response = _verifyNodeProcessConnections($binaryFiles, $nodeProcessNodeIpAddresses, $nodeProcessPortNumber);
			}

			$response = boolval($response);

			if ($response === true) {
				return $response;
			}
		}

		return $response;
	}

	if (($parameters['action'] === 'process-node-processes') === true) {
		$response = _processNodeProcesses($parameters, $response);
	}
?>
