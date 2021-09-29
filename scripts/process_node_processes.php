<?php
	/*
		todo: use the following ipset firewall sequence optimized for current|next data sets with unique node process port selections among additional nodes (node_node_id !== null)

	+	Set reserved internal destination ipset
	+		Rare occurrences of conflicting reserved internal destination ips won’t get in the way of firewall processing since they’re skipped with forced reprocessing afterwards
	+		0: current + next

	+	0 + 1 node process part key alternate

	+		Verify node process types
	+			http_proxy
	+				0: current
	+				1: next
	+			recursive_dns
	+				0: current
	+				1: next
	+			socks_proxy
	+				0: current
	+				1: next

	+		Set process destination ip:port ipset as all ports (not from verification process)
	+			front-end validates and prevents conflicts between current + next process ports
	+			0: current + next

	+		Process firewall
			Set alternate key (key + -1)
			Reconfigure recursive_dns processes

			Firewall ipset rules have to accommodate current proxy processes with nserver ip addresses + ports that were deleted

		0 + 1 alternate

			Verify node process types
				http_proxy
					0: current
					1: next
				recursive_dns
					0: next
					1: next
				socks_proxy
					0: current
					1: next

			Process firewall
			Set alternate key
			Reconfigure http_proxy processes
			Reconfigure socks_proxy processes

		0 + 1 simultaneous

			Set process destination ip:port ipset as all ports (not from verification process)
				front-end validates and prevents conflicts between current + next process ports
				0 + 1: next

			Set reserved internal destination ipset
				Rare occurrences of conflicting reserved internal destination ips won’t get in the way of firewall processing since they’re skipped with forced reprocessing afterwards
				0 + 1: next

			Verify node process types
				http_proxy
					0: next
					1: next
				recursive_dns
					0: next
					1: next
				socks_proxy
					0: next
					1: next

			Process firewall
	+		Delete current node reserved internal ips that aren't in next node reserved internal ips from ipset
	*/

	class ProcessNodeProcesses {

		public $parameters;

		public function __construct($parameters) {
			$this->ipVersions = array(
				4 => array(
					'interface_type' => 'inet',
					'network_mask' => 32
				),
				6 => array(
					'interface_type' => 'inet6',
					'network_mask' => 128
				)
			);
			exec('free -b | grep "Mem:" | grep -v free | awk \'{print $2}\'', $memoryCapacityBytes);
			$this->memoryCapacityBytes = current($memoryCapacityBytes);
			$this->parameters = $parameters;
			exec('getconf PAGE_SIZE 2>&1', $kernelPageSize);
			$this->kernelPageSize = current($kernelPageSize);
		}

		protected function _killProcessIds($processIds) {
			$commands = array(
				'#!/bin/bash'
			);
			$processIdParts = array_chunk($processIds, 10);

			foreach ($processIdParts as $processIds) {
				$commands[] = 'sudo kill -9 ' . implode(' ', $processIds);
			}

			$commands = array_merge($commands, array(
				'sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk \'{print $1}\')',
				'sudo ' . $this->nodeData['next']['binary_files']['telinit'] . ' u'
			));
			$commandsFile = '/tmp/commands.sh';

			if (file_exists($commandsFile) === true) {
				unlink($commandsFile);
			}

			file_put_contents($commandsFile, implode("\n", $commands));
			chmod($commandsFile, 0755);
			shell_exec('cd /tmp/ && sudo ./' . basename($commandsFile));
			unlink($commandsFile);
			return;
		}

		protected function _processFirewall($nodeProcessPartKey = false) {
			$firewallBinaryFiles = array(
				4 => $this->nodeData['next']['binary_files']['iptables-restore'],
				6 => $this->nodeData['next']['binary_files']['ip6tables-restore']
			);
			$nodeProcessPartKeys = array(
				0,
				1
			);

			if (
				($nodeProcessPartKey !== false) &&
				(in_array($nodeProcessPartKey, $nodeProcessPartKeys) === true)
			) {
				$nodeProcessPartKeys = array($nodeProcessPartKey);
			}

			foreach ($this->nodeData['next']['node_ip_versions'] as $nodeIpVersionNetworkMask => $nodeIpVersion) {
				$firewallRules = array(
					'*filter',
					':INPUT ACCEPT [0:0]',
					':FORWARD ACCEPT [0:0]',
					':OUTPUT ACCEPT [0:0]',
					'-A INPUT -p icmp -m hashlimit --hashlimit-above 1/second --hashlimit-burst 2 --hashlimit-htable-gcinterval 100000 --hashlimit-htable-expire 10000 --hashlimit-mode srcip --hashlimit-name icmp --hashlimit-srcmask ' . $nodeIpVersionNetworkMask . ' -j DROP'
				);

				if (empty($this->nodeData['next']['node_ssh_port_numbers']) === false) {
					foreach ($this->nodeData['next']['node_ssh_port_numbers'] as $nodeSshPortNumber) {
						$firewallRules[] = '-A INPUT -p tcp --dport ' . $nodeSshPortNumber . ' -m hashlimit --hashlimit-above 1/minute --hashlimit-burst 10 --hashlimit-htable-gcinterval 600000 --hashlimit-htable-expire 60000 --hashlimit-mode srcip --hashlimit-name ssh --hashlimit-srcmask ' . $nodeIpVersionNetworkMask . ' -j DROP';
					}
				}

				$firewallRules[] = 'COMMIT';
				$firewallRules[] = '*nat';
				$firewallRules[] = ':PREROUTING ACCEPT [0:0]';
				$firewallRules[] = ':INPUT ACCEPT [0:0]';
				$firewallRules[] = ':OUTPUT ACCEPT [0:0]';
				$firewallRules[] = ':POSTROUTING ACCEPT [0:0]';

				// todo: make sure prerouting NAT load balancing works with DNS from system requests and proxy process requests, use output instead of prerouting if not

				foreach ($this->nodeData['next']['node_process_types'] as $nodeProcessType) {
					$nodeProcessTypeFirewallRuleSetPortNumberIndexes = array();

					foreach ($nodeProcessPartKeys as $nodeProcessPartKey) {
						$nodeDataKey = $this->nodeData['node_process_type_process_part_data_keys'][$nodeProcessType][$nodeProcessPartKey];

						if (empty($this->nodeData['node_process_type_firewall_rule_set_port_numbers'][$nodeDataKey][$nodeProcessType][$nodeProcessPartKey][$nodeIpVersion]) === false) {
							foreach ($this->nodeData['node_process_type_firewall_rule_set_port_numbers'][$nodeDataKey][$nodeProcessType][$nodeProcessPartKey][$nodeIpVersion] as $nodeProcessTypeFirewallRuleSet => $nodeProcessPortNumbers) {
								if (empty($nodeProcessTypeFirewallRuleSetPortNumberIndexes[$nodeProcessTypeFirewallRuleSet]) === true) {
									$nodeProcessTypeFirewallRuleSetPortNumberIndexes[$nodeProcessTypeFirewallRuleSet] = 0;
								}

								$nodeProcessTypeFirewallRuleSetPortNumberIndexes[$nodeProcessTypeFirewallRuleSet] += count($nodeProcessPortNumbers);
							}
						}
					}

					foreach ($nodeProcessTypeFirewallRuleSetPortNumberIndexes as $nodeProcessTypeFirewallRuleSet => $nodeProcessTypeFirewallRuleSetPortNumberIndex) {
						foreach ($nodeProcessPartKeys as $nodeProcessPartKey) {
							foreach ($this->nodeData['node_process_type_firewall_rule_set_port_numbers'][$nodeDataKey][$nodeProcessType][$nodeProcessPartKey][$nodeIpVersion][$nodeProcessTypeFirewallRuleSet] as $nodeProcessPortNumbers) {
								foreach ($nodeProcessPortNumbers as $nodeProcessPortNumber) {
									$nodeProcessTypeFirewallRuleSetLoadBalancer = '-m statistic --mode nth --every ' . $nodeProcessTypeFirewallRuleSetPortNumberIndex . ' --packet 0 ';

									if ($nodeProcessTypeFirewallRuleSetPortNumberIndex === 0) {
										$nodeProcessTypeFirewallRuleSetLoadBalancer = '';
									}

									$nodeProcessTransportProtocols = array(
										'tcp',
										'udp'
									);

									if ($nodeProcessType === 'http_proxy') {
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
				// todo: allow dropping external packets from additional public IP blocks with per-node settings

				if (empty($this->nodeData['next']['private_network']['ip_blocks'][$nodeIpVersion]) === false) {
					foreach ($this->nodeData['next']['private_network']['ip_blocks'][$nodeIpVersion] as $privateNetworkIpBlock) {
						$firewallRules[] = '-A PREROUTING ! -i lo -s ' . $privateNetworkIpBlock . ' -j DROP';
					}
				}

				$firewallRules[] = 'COMMIT';
				$firewallRulesFile = '/tmp/firewall_' . $nodeIpVersion;
				unlink($firewallRulesFile);
				touch($firewallRulesFile);
				$firewallRuleParts = array_chunk($firewallRules, 1000);

				foreach ($firewallRuleParts as $firewallRulePart) {
					$saveFirewallRules = implode("\n", $firewallRulePart);
					shell_exec('sudo echo "' . $saveFirewallRules . '" >> ' . $firewallRulesFile);
				}

				shell_exec('sudo ' . $firewallBinaryFiles[$nodeIpVersion] . ' < ' . $firewallRulesFile);
				sleep(1);
			}

			return;
		}

		protected function _verifyNodeProcess($nodeProcessNodeIp, $nodeProcessNodeIpVersion, $nodeProcessPortNumber, $nodeProcessType) {
			// todo: add options for ipv4 + ipv6 if not auto-detected based on $nodeProcessIpVersion
			$response = false;

			switch ($nodeProcessType) {
				case 'http_proxy':
				case 'socks_proxy':
					$parameters = array(
						'http_proxy' => '-x',
						'socks_proxy' => '--socks5-hostname'
					);
					exec('curl ' . $parameters[$nodeProcessType] . ' ' . $nodeProcessNodeIp . ':' . $nodeProcessPortNumber . ' http://ghostcompute -v --connect-timeout 1 --max-time | grep " refused" 1 2>&1', $proxyNodeProcessResponse);
					$response = (empty($proxyNodeProcessResponse) === true);
					break;
				case 'recursive_dns':
					exec('dig +time=1 +tries=1 ghostcompute @' . $nodeProcessNodeIp . ' -p ' . $nodeProcessPortNumber . ' | grep "Got answer" 2>&1', $recursiveDnsNodeProcessResponse);
					$response = (empty($recursiveDnsNodeProcessResponse) === false);
					break;
			}

			return $response;
		}

		protected function _verifyNodeProcessConnections($nodeProcessNodeIps, $nodeProcessPortNumber) {
			foreach ($nodeProcessNodeIps as $nodeProcessNodeIpVersion => $nodeProcessNodeIp) {
				if ($nodeProcessNodeIpVersion === 6) {
					$nodeProcessNodeIp = '[' . $nodeProcessNodeIp . ']';
				}

				exec('sudo ' . $this->nodeData['next']['binary_files']['ss'] . ' -p -t -u state connected "( sport = :' . $nodeProcessPortNumber . ' )" src ' . $nodeProcessNodeIp . ' | head -1 2>&1', $response);

				if (is_array($response) === false) {
					$response = $this->_verifyNodeProcessConnections($nodeProcessNodeIps, $nodeProcessPortNumber);
				}

				$response = boolval($response);

				if ($response === true) {
					return $response;
				}
			}

			return $response;
		}

		public function fetchProcessIds($processName, $processFile = false) {
			$response = array();
			exec('ps -h -o pid -o cmd $(pgrep -f "' . $processName . '") | grep "' . $processName . '" | grep -v grep 2>&1', $processes);

			if (empty($processes) === false) {
				foreach ($processes as $process) {
					$processColumns = array_filter(explode(' ', $process));

					if (
						(empty($processColumns) === false) &&
						(
							(empty($processFile) === true) ||
							(strpos($process, $processFile) !== false)
						)
					) {
						$response[] = $processColumns[key($processColumns)];
					}
				}
			}

			return $response;
		}

		public function process() {
			// todo: if more than 10 ports fail from timing out during reconfig, send status data to system to increase process count if bottleneck is from congested ports and isn't from low system resources
				// increase timeout for verifynodeprocess requests until latency is measured from a successful response, add latency to resource usage data

			if (empty($this->nodeData['next']['nodes']) === true) {
				if (empty($this->nodeData['current']) === false) {
					foreach ($this->nodeData['current']['node_processes'] as $nodeProcessType => $nodeProcessNodeParts) {
						foreach ($nodeProcessNodeParts as $nodeProcessNodePart) {
							foreach ($nodeProcessNodePart as $nodeProcessNodeId => $nodeProcessPortNumbers) {
								$nodeReservedInternalDestinationIpVersion = key($this->nodeData['current']['node_reserved_internal_destinations'][$nodeProcessNodeId]);

								foreach ($nodeProcessPortNumbers as $nodeProcessPortNumber) {
									if ($this->_verifyNodeProcess($this->nodeData['current']['node_reserved_internal_destinations'][$nodeProcessNodeId][$nodeReservedInternalDestinationIpVersion], $nodeReservedInternalDestinationIpVersion, $nodeProcessPortNumber, $nodeProcessType) === false) {
										exec('sudo curl -s --form-string "json={\"action\":\"process\",\"data\":{\"processed\":false}}" ' . $this->parameters['system_url'] . '/endpoint/nodes 2>&1', $response);
										return $response;
									}
								}
							}
						}
					}
				}

				// todo: add default $response with "no new node data to process, etc"
				return;
			}

			$nodeProcessesToRemove = array();

			foreach ($this->nodeData['current']['node_processes'] as $nodeProcessType => $nodeProcessNodeParts) {
				foreach ($nodeProcessNodeParts as $nodeProcessNodePart) {
					foreach ($nodeProcessNodePart as $nodeProcessNodeId => $nodeProcessPortNumbers) {
						foreach ($nodeProcessPortNumbers as $nodeProcessId => $nodeProcessPortNumber) {
							if (
								(empty($this->nodeData['next']['node_processes'][$nodeProcessType][0][$nodeProcessNodeId][$nodeProcessId]) === true) &&
								(empty($this->nodeData['next']['node_processes'][$nodeProcessType][1][$nodeProcessNodeId][$nodeProcessId]) === true)
							) {
								$nodeProcessesToRemove[$nodeProcessType][$nodeProcessId] = $nodeProcessId;
							}
						}
					}
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
			file_put_contents('/etc/sysctl.conf', implode("\n", $kernelOptions));
			shell_exec('sudo ' . $this->nodeData['next']['binary_files']['sysctl'] . ' -p');
			$dynamicKernelOptions = array(
				'kernel.shmall' => floor($this->memoryCapacityBytes / $this->kernelPageSize),
				'kernel.shmmax' => $this->memoryCapacityBytes,
				'net.core.optmem_max' => ceil($this->memoryCapacityBytes * 0.02),
				'net.core.rmem_default' => ($defaultSocketBufferMemoryBytes = ceil($this->memoryCapacityBytes * 0.00034)),
				'net.core.rmem_max' => ($defaultSocketBufferMemoryBytes * 2),
				'net.core.wmem_default' => $defaultSocketBufferMemoryBytes,
				'net.core.wmem_max' => ($defaultSocketBufferMemoryBytes * 2)
			);
			$memoryCapacityPages = ceil($this->memoryCapacityBytes / $this->kernelPageSize);

			foreach ($this->nodeData['next']['node_ip_versions'] as $nodeIpVersionNetworkMask => $nodeIpVersion) {
				$dynamicKernelOptions['net.ipv' . $nodeIpVersion . '.tcp_mem'] = $memoryCapacityPages . ' ' . $memoryCapacityPages . ' ' . $memoryCapacityPages;
				$dynamicKernelOptions['net.ipv' . $nodeIpVersion . '.tcp_rmem'] = 1 . ' ' . $defaultSocketBufferMemoryBytes . ' ' . ($defaultSocketBufferMemoryBytes * 2);
				$dynamicKernelOptions['net.ipv' . $nodeIpVersion . '.tcp_wmem'] = $dynamicKernelOptions['net.ipv' . $nodeIpVersion . '.tcp_rmem'];
				$dynamicKernelOptions['net.ipv' . $nodeIpVersion . '.udp_mem'] = $dynamicKernelOptions['net.ipv' . $nodeIpVersion . '.tcp_mem'];
			}

			foreach ($dynamicKernelOptions as $dynamicKernelOptionKey => $dynamicKernelOptionValue) {
				shell_exec('sudo ' . $this->nodeData['next']['binary_files']['sysctl'] . ' -w ' . $dynamicKernelOptionKey . '="' . $dynamicKernelOptionValue . '"');
			}

			$nodeInterfacesFileContents = $nodeIpsToDelete = array();

			foreach ($this->ipVersions as $ipVersionNumber => $ipVersion) {
				$existingNodeIps = array();
				exec('sudo ' . $this->nodeData['next']['binary_files']['ip'] . ' addr show dev ' . $this->nodeData['next']['interface_name'] . ' | grep "' . $ipVersion['interface_type'] . ' " | grep "' . $ipVersion['network_mask'] . ' " | awk \'{print substr($2, 0, length($2) - ' . ($ipVersionNumber / 2) . ')}\'', $existingNodeIps);

				if (empty($this->nodeData['next']['node_ips'][$ipVersionNumber]) === false) {
					foreach ($this->nodeData['next']['node_ips'][$ipVersionNumber] as $nodeIp) {
						$nodeInterfacesFileContents[] = 'shell_exec(\'' . ($command = 'sudo ' . $this->nodeData['next']['binary_files']['ip'] . ' -' . $ipVersion . ' addr add ' . $nodeIp . '/' . $ipVersion['network_mask'] . ' dev ' . $this->nodeData['next']['interface_name']) . '\');';
						shell_exec($command);
					}
				}

				$nodeIpsToDelete[$ipVersion] = array_diff(current($existingNodeIps), $this->nodeData['next']['node_ips'][$ipVersionNumber]);
				shell_exec('sudo ' . $this->nodeData['binary_files']['ipset'] . ' create _ hash:ip family ' . $this->ipVersions[$ipVersionNumber]['interface_type'] . ' timeout 0');

				foreach ($this->nodeData['next']['node_reserved_internal_destination_ip_addresses'][$ipVersionNumber] as $nodeReservedInternalDestinationIpAddress) {
					shell_exec('sudo ' . $this->nodeData['binary_files']['ipset'] . ' add _ ' . $nodeReservedInternalDestinationIpAddress);
				}
			}

			array_unshift($nodeInterfacesFileContents, '<?php');
			file_put_contents('/usr/local/ghostcompute/node_interfaces.php', implode("\n", $nodeInterfacesFileContents));

			if (empty($recursiveDnsNodeProcessDefaultServiceName) === true) {
				$recursiveDnsNodeProcessDefaultServiceName = 'named';

				if (is_dir('/etc/default/bind9') === true) {
					$recursiveDnsNodeProcessDefaultServiceName = 'bind9';
				}
			}

			foreach ($this->nodeData['next']['node_process_types'] as $nodeProcessType) {
				$this->nodeData['node_process_type_process_part_data_keys'][$nodeProcessType] = array(
					'current',
					'next'
				);
			};

			foreach (array(0, 1) as $nodeProcessPartKey) {
				foreach ($this->nodeData['node_process_type_process_part_data_keys'] as $nodeProcessType => $nodeProcessTypeProcessPartDataKeys) {
					$nodeDataKey = $nodeProcessTypeProcessPartDataKeys[$nodeProcessPartKey];
					$nodeProcessPortNumberIdentifier = '';

					foreach ($this->nodeData[$nodeDataKey]['node_processes'][$nodeProcessType][$nodeProcessPartKey] as $nodeProcessNodeId => $nodeProcessPortNumbers) {
						$nodeReservedInternalDestinationIpVersion = key($this->nodeData[$nodeDataKey]['node_reserved_internal_destinations'][$nodeProcessNodeId]);
						$nodeProcessPortNumbersVerified = array();

						foreach ($nodeProcessPortNumbers as $nodeProcessId => $nodeProcessPortNumber) {
							if ($this->_verifyNodeProcess($this->nodeData[$nodeDataKey]['node_reserved_internal_destinations'][$nodeProcessNodeId][$nodeReservedInternalDestinationIpVersion], $nodeReservedInternalDestinationIpVersion, $nodeProcessPortNumber, $nodeProcessType) === true) {
								$nodeProcessPortNumberIdentifier .= '_' . $nodeProcessPortNumber;
								$nodeProcessPortNumbersVerified[] = $nodeProcessPortNumber;
							}
						}

						$nodeProcessPortNumberIdentifier = sha1($nodeProcessPortNumberIdentifier);

						foreach ($this->nodeData[$nodeDataKey]['node_ip_versions'] as $nodeIpVersion) {
							$nodeProcessNodeIp = $this->nodeData[$nodeDataKey]['nodes'][$nodeProcessNodeId]['external_ip_version_' . $nodeIpVersion];

							if (empty($this->nodeData[$nodeDataKey]['nodes'][$nodeProcessNodeId]['internal_ip_version_' . $nodeIpVersion]) === false) {
								$nodeProcessNodeIp = $this->nodeData[$nodeDataKey]['nodes'][$nodeProcessNodeId]['internal_ip_version_' . $nodeIpVersion];
							}

							$this->nodeData['node_process_type_firewall_rule_set_port_numbers'][$nodeDataKey][$nodeProcessType][$nodeProcessPartKey][$nodeIpVersion][($nodeProcessTypeFirewallRuleSet = $nodeDataKey . '_' . $nodeIpVersion . '_' . $nodeProcessPartKey . '_' . $nodeProcessType . '_' . $nodeProcessPortNumberIdentifier . '_')] = $nodeProcessPortNumbersVerified;
							shell_exec('sudo ' . $this->nodeData['binary_files']['ipset'] . ' create ' . $nodeProcessTypeFirewallRuleSet . ' hash:ip,port family ' . $this->ipVersions[$nodeIpVersion]['interface_type'] . ' timeout 0');

							foreach ($nodeProcessPortNumbers as $nodeProcessPortNumber) {
								shell_exec('sudo ' . $this->nodeData['binary_files']['ipset'] . ' add ' . $nodeProcessTypeFirewallRuleSet . ' ' . $nodeProcessNodeIp . ',tcp:' . $nodeProcessPortNumber);
								shell_exec('sudo ' . $this->nodeData['binary_files']['ipset'] . ' add ' . $nodeProcessTypeFirewallRuleSet . ' ' . $nodeProcessNodeIp . ',udp:' . $nodeProcessPortNumber);
							}
						}
					}
				}

				$this->_processFirewall($nodeProcessPartKey);
				$nodeProcessPartKey = abs($nodeProcessPartKey - 1);

				foreach ($this->nodeData['next']['node_processes']['recursive_dns'][$nodeProcessPartKey] as $recursiveDnsNodeProcessNodeId => $recursiveDnsNodeProcessPortNumbers) {
					$recursiveDnsNodeProcessConfiguration = array(
						'a0' => 'acl privateNetworkIpBlocks {',
						'a1' => $this->nodeData['next']['private_network']['ip_blocks'][4],
						'a2' => $this->nodeData['next']['private_network']['ip_blocks'][6],
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

					if (empty($this->nodeData['next']['node_process_users']['recursive_dns']) === false) {
						$recursiveDnsNodeProcessConfiguration['g' . $recursiveDnsNodeProcessConfigurationIndexes['g']] = 'logging {';
						$recursiveDnsNodeProcessConfigurationIndexes['g']++;

						foreach ($this->nodeData['next']['node_process_users']['recursive_dns'][$recursiveDnsNodeProcessNodeId] as $recursiveDnsNodeProcessUserIds) {
							foreach ($recursiveDnsNodeProcessUserIds as $recursiveDnsNodeProcessUserId) {
								$recursiveDnsNodeProcessUser = $this->nodeData['next']['users'][$recursiveDnsNodeProcessUserId];

								if (
									(empty($recursiveDnsNodeProcessConfigurationIndexes['user_ids'][$recursiveDnsNodeProcessUserId]) === true) &&
									(empty($recursiveDnsNodeProcessUser['authentication_whitelist']) === false)
								) {
									$recursiveDnsNodeProcessConfigurationIndexes['user_ids'][$recursiveDnsNodeProcessUserId] = true;
									$recursiveDnsNodeProcessWhitelistedSources = explode("\n", $recursiveDnsNodeProcessUser['authentication_whitelist']);

									foreach ($recursiveDnsNodeProcessWhitelistedSources as $recursiveDnsNodeProcessWhitelistedSource) {
										$recursiveDnsNodeProcessConfiguration['a' . $recursiveDnsNodeProcessConfigurationIndexes['a']] = $recursiveDnsNodeProcessWhitelistedSource . ';';
										$recursiveDnsNodeProcessConfigurationIndexes['a']++;
									}
								}

								$recursiveDnsNodeProcessName = $recursiveDnsNodeProcessNodeId . '_' . $recursiveDnsNodeProcessUserId;
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
					$recursiveDnsNodeProcessInterfaceDestinationIps = $recursiveDnsNodeProcessNodeIps = array();

					foreach ($this->nodeData['next']['node_ip_versions'] as $recursiveDnsNodeIpVersion) {
						$recursiveDnsNodeProcessConfigurationOptionSuffix = '';

						if ($recursiveDnsNodeIpVersion === 6) {
							$recursiveDnsNodeProcessConfigurationOptionSuffix = '-v6';
						}

						if (empty($this->nodeData['next']['nodes'][$recursiveDnsNodeProcessNodeId]['external_ip_version_' . $recursiveDnsNodeIpVersion]) === false) {
							$recursiveDnsNodeProcessInterfaceSourceIp = $recursiveDnsNodeProcessNodeIps[$recursiveDnsNodeIpVersion] = $this->nodeData['next']['nodes'][$recursiveDnsNodeProcessNodeId]['external_ip_version_' . $recursiveDnsNodeIpVersion];

							if (empty($this->nodeData['next']['nodes'][$recursiveDnsNodeProcessNodeId]['internal_ip_version_' . $recursiveDnsNodeIpVersion]) === false) {
								$recursiveDnsNodeProcessInterfaceSourceIp = $recursiveDnsNodeProcessNodeIps[$recursiveDnsNodeIpVersion] = $this->nodeData['next']['nodes'][$recursiveDnsNodeProcessNodeId]['internal_ip_version_' . $recursiveDnsNodeIpVersion];
							}

							$recursiveDnsNodeProcessConfiguration['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = 'listen-on' . $recursiveDnsNodeProcessConfigurationOptionSuffix . ' {';
							$recursiveDnsNodeProcessConfigurationIndexes['b']++;
							$recursiveDnsNodeProcessConfiguration['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = false;
							$recursiveDnsNodeProcessInterfaceDestinationIps['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = $this->nodeData['next']['node_process_recursive_dns_destinations']['recursive_dns'][$recursiveDnsNodeProcessNodeId]['listening_ip_version_' . $recursiveDnsNodeIpVersion];
							$recursiveDnsNodeProcessConfigurationIndexes['b']++;

							if (empty($this->nodeData['current']['node_reserved_internal_destination_ip_addresses'][$recursiveDnsNodeIpVersion][$this->nodeData['next']['node_process_recursive_dns_destinations']['recursive_dns'][$recursiveDnsNodeProcessNodeId]['listening_ip_version_' . $recursiveDnsNodeIpVersion]]) === false) {
								$recursiveDnsNodeProcessesStart = false;
							}

							$recursiveDnsNodeProcessConfiguration['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = false;
							$recursiveDnsNodeProcessInterfaceDestinationIps['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = $this->nodeData['next']['node_reserved_internal_destinations'][$recursiveDnsNodeProcessNodeId][$recursiveDnsNodeIpVersion];
							$recursiveDnsNodeProcessConfigurationIndexes['b']++;

							if (empty($this->nodeData['node_process_users']['recursive_dns'][$recursiveDnsNodeProcessNodeId]) === false) {
								$recursiveDnsNodeProcessConfiguration['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = false;
								$recursiveDnsNodeProcessInterfaceDestinationIps['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = $recursiveDnsNodeProcessInterfaceSourceIp;
								$recursiveDnsNodeProcessConfigurationIndexes['b']++;

								if (empty($this->nodeData['current']['node_reserved_internal_destination_ip_addresses'][$recursiveDnsNodeIpVersion][$recursiveDnsNodeProcessInterfaceSourceIp]) === false) {
									$recursiveDnsNodeProcessesStart = false;
								}
							}

							$recursiveDnsNodeProcessConfiguration['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = '};';
							$recursiveDnsNodeProcessConfigurationIndexes['b']++;
							$recursiveDnsNodeProcessConfiguration['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = 'query-source' . $recursiveDnsNodeProcessConfigurationOptionSuffix . ' address ' . $recursiveDnsNodeProcessInterfaceSourceIp . ';';
							$recursiveDnsNodeProcessConfigurationIndexes['c']++;
						}
					}

					ksort($recursiveDnsNodeProcessConfiguration);

					foreach ($recursiveDnsNodeProcessPortNumbers as $recursiveDnsNodeProcessId => $recursiveDnsNodeProcessPortNumber) {
						while ($this->_verifyNodeProcessConnections($recursiveDnsNodeProcessNodeIps, $recursiveDnsNodeProcessPortNumber) === true) {
							sleep(1);
						}

						$recursiveDnsNodeProcessName = 'recursive_dns_' . $recursiveDnsNodeProcessId;

						if (file_exists('/etc/' . $recursiveDnsNodeProcessName . '/named.conf') === true) {
							$recursiveDnsNodeProcessProcessIds = $this->fetchProcessIds($recursiveDnsNodeProcessName . ' ', $recursiveDnsNodeProcessName . '/');

							if (empty($recursiveDnsNodeProcessProcessIds) === false) {
								$this->_killProcessIds($recursiveDnsNodeProcessProcessIds);
							}
						}

						foreach ($recursiveDnsNodeProcessInterfaceDestinationIps as $recursiveDnsNodeProcessInterfaceDestinationIpIndex => $recursiveDnsNodeProcessInterfaceDestinationIp) {
							$recursiveDnsNodeProcessConfiguration[$recursiveDnsNodeProcessInterfaceDestinationIpIndex] = $recursiveDnsNodeProcessInterfaceDestinationIp . ':' . $recursiveDnsNodeProcessPortNumber . ';';
						}

						$recursiveDnsNodeProcessConfiguration['d'] = '"/var/cache/' . $recursiveDnsNodeProcessName . '";';
						$recursiveDnsNodeProcessConfiguration['e'] = 'pid-file "/var/run/named/' . $recursiveDnsNodeProcessName . '.pid";';
						file_put_contents('/etc/' . $recursiveDnsNodeProcessName . '/named.conf.options', implode("\n", $recursiveDnsNodeProcessConfiguration));
						shell_exec('cd /usr/sbin && sudo ln /usr/sbin/named ' . $recursiveDnsNodeProcessName);
						// todo: start all node process ports with same service file instead of daemon-reload for each process port
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

						shell_exec('sudo ' . $this->nodeData['next']['binary_files']['systemctl'] . ' daemon-reload');
						unlink('/var/run/named/' . $recursiveDnsNodeProcessName . '.pid');
						$recursiveDnsNodeProcessEnded = false;
						$recursiveDnsNodeProcessEndedTime = time();

						while ($recursiveDnsNodeProcessEnded === false) {
							$recursiveDnsNodeProcessEnded = ($this->_verifyNodeProcess($this->nodeData['next']['node_reserved_internal_destinations'][$recursiveDnsNodeProcessNodeId][$recursiveDnsNodeIpVersion], $recursiveDnsNodeIpVersion, $recursiveDnsNodeProcessPortNumber, 'recursive_dns') === false);
							sleep(1);
						}

						$recursiveDnsNodeProcessStarted = false;
						$recursiveDnsNodeProcessStartedTime = time();

						if ($recursiveDnsNodeProcessesStart === true) {
							while ($recursiveDnsNodeProcessStarted === false) {
								shell_exec('sudo ' . $this->nodeData['next']['binary_files']['service'] . ' ' . $recursiveDnsNodeProcessName . ' start');
								$recursiveDnsNodeProcessStarted = ($this->_verifyNodeProcess($this->nodeData['next']['node_reserved_internal_destinations'][$recursiveDnsNodeProcessNodeId][$recursiveDnsNodeIpVersion], $recursiveDnsNodeIpVersion, $recursiveDnsNodeProcessPortNumber, 'recursive_dns') === true);
								sleep(1);
							}
						} else {
							$this->reprocess = true;
						}

						if (file_exists('/var/run/named/' . $recursiveDnsNodeProcessName . '.pid') === true) {
							$recursiveDnsNodeProcessProcessId = file_get_contents('/var/run/named/' . $recursiveDnsNodeProcessName . '.pid');

							if (is_numeric($recursiveDnsNodeProcessProcessId) === true) {
								shell_exec('sudo ' . $this->nodeData['next']['binary_files']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -n1000000000');
								shell_exec('sudo ' . $this->nodeData['next']['binary_files']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -n=1000000000');
								shell_exec('sudo ' . $this->nodeData['next']['binary_files']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -s"unlimited"');
								shell_exec('sudo ' . $this->nodeData['next']['binary_files']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -s=unlimited');
							}
						}
					}
				}
			}

			$this->nodeData['node_process_type_firewall_rule_set_port_numbers'] = array();
			$this->nodeData['node_process_type_process_part_data_keys']['recursive_dns'] = array(
				'next',
				'next'
			);

			foreach (array(0, 1) as $nodeProcessPartKey) {
				foreach ($this->nodeData['node_process_type_process_part_data_keys'] as $nodeProcessType => $nodeProcessTypeProcessPartDataKeys) {
					$nodeDataKey = $nodeProcessTypeProcessPartDataKeys[$nodeProcessPartKey];
					$nodeProcessPortNumberIdentifier = '';

					foreach ($this->nodeData[$nodeDataKey]['node_processes'][$nodeProcessType][$nodeProcessPartKey] as $nodeProcessNodeId => $nodeProcessPortNumbers) {
						$nodeReservedInternalDestinationIpVersion = key($this->nodeData[$nodeDataKey]['node_reserved_internal_destinations'][$nodeProcessNodeId]);
						$nodeProcessPortNumbersVerified = array();

						foreach ($nodeProcessPortNumbers as $nodeProcessId => $nodeProcessPortNumber) {
							if ($this->_verifyNodeProcess($this->nodeData[$nodeDataKey]['node_reserved_internal_destinations'][$nodeProcessNodeId][$nodeReservedInternalDestinationIpVersion], $nodeReservedInternalDestinationIpVersion, $nodeProcessPortNumber, $nodeProcessType) === true) {
								$nodeProcessPortNumberIdentifier .= '_' . $nodeProcessPortNumber;
								$nodeProcessPortNumbersVerified[] = $nodeProcessPortNumber;
							}
						}

						$nodeProcessPortNumberIdentifier = sha1($nodeProcessPortNumberIdentifier);

						foreach ($this->nodeData[$nodeDataKey]['node_ip_versions'] as $nodeIpVersion) {
							$nodeProcessNodeIp = $this->nodeData[$nodeDataKey]['nodes'][$nodeProcessNodeId]['external_ip_version_' . $nodeIpVersion];

							if (empty($this->nodeData[$nodeDataKey]['nodes'][$nodeProcessNodeId]['internal_ip_version_' . $nodeIpVersion]) === false) {
								$nodeProcessNodeIp = $this->nodeData[$nodeDataKey]['nodes'][$nodeProcessNodeId]['internal_ip_version_' . $nodeIpVersion];
							}

							$this->nodeData['node_process_type_firewall_rule_set_port_numbers'][$nodeDataKey][$nodeProcessType][$nodeProcessPartKey][$nodeIpVersion][($nodeProcessTypeFirewallRuleSet = $nodeDataKey . '_' . $nodeIpVersion . '_' . $nodeProcessPartKey . '_' . $nodeProcessType . '_' . $nodeProcessPortNumberIdentifier . '__')] = $nodeProcessPortNumbersVerified;
							shell_exec('sudo ' . $this->nodeData['binary_files']['ipset'] . ' create ' . $nodeProcessTypeFirewallRuleSet . ' hash:ip,port family ' . $this->ipVersions[$nodeIpVersion]['interface_type'] . ' timeout 0');

							foreach ($nodeProcessPortNumbers as $nodeProcessPortNumber) {
								shell_exec('sudo ' . $this->nodeData['binary_files']['ipset'] . ' add ' . $nodeProcessTypeFirewallRuleSet . ' ' . $nodeProcessNodeIp . ',tcp:' . $nodeProcessPortNumber);
								shell_exec('sudo ' . $this->nodeData['binary_files']['ipset'] . ' add ' . $nodeProcessTypeFirewallRuleSet . ' ' . $nodeProcessNodeIp . ',udp:' . $nodeProcessPortNumber);
							}
						}
					}
				}

				$this->_processFirewall($nodeProcessPartKey);
				$nodeProcessPartKey = abs($nodeProcessPartKey - 1);

				foreach ($this->nodeData['next']['proxy_node_process_types'] as $proxyNodeProcessTypeServiceName => $proxyNodeProcessType) {
					if (empty($this->nodeData['next']['node_processes'][$proxyNodeProcessType][$nodeProcessPartKey]) === false) {
						foreach ($this->nodeData['next']['node_processes'][$proxyNodeProcessType][$nodeProcessPartKey] as $proxyNodeProcessNodeId => $proxyNodeProcessPortNumbers) {
							$proxyNodeProcessConfiguration = array(
								'a0' => 'maxconn 20000',
								'a1' => 'nobandlimin',	
								'a2' => 'nobandlimout',
								'a3' => 'stacksize 0',
								'a4' => 'flush',
								'a5' => 'allow * * * * HTTP',
								'a6' => 'allow * * * * HTTPS',
								'a7' => 'nolog',
								'a8' => false
							);
							$proxyNodeProcessConfigurationIndexes = $proxyNodeProcessConfigurationPartIndexes = array(
								'b' => 0,
								'c' => 0,
								'd' => 0,
								'e' => 0,
								'f' => 0,
								'g' => 0
							);

							foreach ($this->nodeData['next']['node_process_users'][$proxyNodeProcessType][$proxyNodeProcessNodeId] as $proxyNodeProcessUserIds) {
								$proxyNodeProcessConfiguration['c' . $proxyNodeProcessConfigurationIndexes['c']] = 'auth iponly strong';
								$proxyNodeProcessConfigurationIndexes['c']++;

								foreach ($proxyNodeProcessUserIds as $proxyNodeProcessUserId) {
									$proxyNodeProcessConfigurationIndexes['f'] = $proxyNodeProcessConfigurationPartIndexes['f'] = $proxyNodeProcessConfigurationIndexes['g'] = $proxyNodeProcessConfigurationPartIndexes['g'] = 0;
									$proxyNodeProcessUser = $this->nodeData['next']['users'][$proxyNodeProcessUserId];

									if (($proxyNodeProcessConfigurationIndexes['b'] % 10) === 0) {
										$proxyNodeProcessConfiguration['b' . $proxyNodeProcessConfigurationIndexes['b']] = 'users';
										$proxyNodeProcessConfigurationPartIndexes['b'] = $proxyNodeProcessConfigurationIndexes['b'];
									}

									$proxyNodeProcessConfiguration['b' . $proxyNodeProcessConfigurationPartIndexes['b']] .= ' ' . $proxyNodeProcessUser['authentication_username'] . ':CL:' . $proxyNodeProcessUser['authentication_password'];
									$proxyNodeProcessConfigurationIndexes['b']++;

									if (
										(
											(empty($proxyNodeProcessUser['request_destination_ids']) === false) ||
											(empty($proxyNodeProcessUser['status_allowing_request_destinations_only']) === true)
										) &&
										(
											(empty($proxyNodeProcessUser['authentication_username']) === false) ||
											(empty($proxyNodeProcessUser['authentication_whitelist']) === false)
										)
									) {
										$proxyNodeProcessUserRequestDestinationParts = array(
											array(
												'*'
											)
										);
										$proxyNodeProcessUserRequestDestinationParts = array();

										foreach ($proxyNodeProcessUser['request_destination_ids'] as $proxyNodeProcessUserDestinationId) {
											if (($proxyNodeProcessConfigurationIndexes['h'] % 10) === 0) {
												$proxyNodeProcessUserRequestDestinationParts[$proxyNodeProcessConfigurationIndexes['h']] = $this->nodeData['next']['request_destinations'][$proxyNodeProcessUserDestinationId];
												$proxyNodeProcessConfigurationPartIndexes['h'] = $proxyNodeProcessConfigurationIndexes['h'];
											} else {
												$proxyNodeProcessUserRequestDestinationParts[$proxyNodeProcessUserRequestDestinationPartIndexes['h']] .= ',' . $this->nodeData['next']['request_destinations'][$proxyNodeProcessUserDestinationId];
											}

											$proxyNodeProcessConfigurationIndexes['h']++;
										}

										if (empty($proxyNodeProcessUser['status_allowing_request_logs']) === false) {
											$proxyNodeProcessConfiguration['c' . $proxyNodeProcessConfigurationIndexes['c']] = 'log /var/log/' . $proxyNodeProcessType . '/' . $proxyNodeProcessNodeId . '_' . $proxyNodeProcessUserId;
											$proxyNodeProcessConfigurationIndexes['c']++;
											$proxyNodeProcessConfiguration['c' . $proxyNodeProcessConfigurationIndexes['c']] = 'logformat " %I _ %O _ %Y-%m-%d %H-%M-%S.%. _ %n _ %R _ %E _ %C _ %U"';
											$proxyNodeProcessConfigurationIndexes['c']++;
										}

										if (
											(empty($proxyNodeUser['status_allowing_request_destinations_only']) === true) &&
											(empty($proxyNodeUserRequestDestinationParts) === false)
										) {
											foreach ($proxyNodeUserRequestDestinationParts as $proxyNodeUserRequestDestinationPart) {
												$proxyNodeProcessConfiguration['c' . $proxyNodeProcessConfigurationIndexes['c']] = 'deny * * ' . $proxyNodeUserRequestDestinationPart;
												$proxyNodeProcessConfigurationIndexes['c']++;
											}
										}

										if (empty($proxyNodeProcessUser['status_requiring_strict_authentication']) === true) {
											if (
												(empty($proxyNodeProcessUser['authentication_username']) === false) &&
												(empty($proxyNodeProcessUser['status_allowing_request_destinations_only']) === false)
											) {
												foreach ($proxyNodeProcessUserRequestDestinationParts as $proxyNodeProcessUserRequestDestinationPart) {
													$proxyNodeProcessConfiguration['d' . $proxyNodeProcessConfigurationIndexes['d']] = 'allow ' . $proxyNodeProcessUser['authentication_username'] . ' * ' . $proxyNodeProcessUserRequestDestinationPart;
													$proxyNodeProcessConfigurationIndexes['d']++;
												}
											}

											$proxyNodeProcessUser['authentication_username'] = '*';
										}

										if (empty($proxyNodeProcessUser['authentication_whitelist']) === false) {
											$proxyNodeProcessUser['authentication_whitelist'] = explode("\n", $proxyNodeProcessUser['authentication_whitelist']);
											$proxyNodeProcessUserAuthenticationWhitelistParts = array();

											foreach ($proxyNodeProcessUser['authentication_whitelist'] as $proxyNodeProcessUserAuthenticationWhitelist) {
												if (($proxyNodeProcessConfigurationIndexes['i'] % 10) === 0) {
													$proxyNodeProcessUserAuthenticationWhitelistParts[$proxyNodeProcessConfigurationIndexes['i']] = $proxyNodeProcessUserAuthenticationWhitelist;
													$proxyNodeProcessConfigurationPartIndexes['i'] = $proxyNodeProcessConfigurationIndexes['i'];
												} else {
													$proxyNodeProcessUserAuthenticationWhitelistParts[$proxyNodeProcessConfigurationPartIndexes['i']] .= ',' . $proxyNodeProcessUserAuthenticationWhitelist;
												}

												$proxyNodeProcessConfigurationIndexes['i']++;
											}

											foreach ($proxyNodeProcessUserAuthenticationWhitelistParts as $proxyNodeProcessUserAuthenticationWhitelistPart) {
												foreach ($proxyNodeProcessUserRequestDestinationParts as $proxyNodeProcessUserRequestDestinationPart) {
													$proxyNodeProcessConfiguration['c' . $proxyNodeProcessConfigurationIndexes['c']] = 'allow ' . $proxyNodeProcessUser['authentication_username'] . ' ' . implode(',', $proxyNodeProcessUserAuthenticationWhitelistPart) . ' ' . $proxyNodeProcessUserDestinationPart;
													$proxyNodeProcessConfigurationIndexes['c']++;
												}
											}
										}

										$proxyNodeProcessConfiguration['d' . $proxyNodeProcessConfigurationIndexes['d']] = 'deny *';
										$proxyNodeProcessConfigurationIndexes['d']++;
										$proxyNodeProcessConfiguration['d' . $proxyNodeProcessConfigurationIndexes['d']] = 'flush';
										$proxyNodeProcessConfigurationIndexes['d']++;
									}
								}
							}

							$proxyNodeProcessesStart = true;
							$proxyNodeProcessNodeIps = array();

							foreach ($this->nodeData['next']['node_ip_versions'] as $proxyNodeIpVersion) {
								if (empty($this->nodeData['next']['node_process_recursive_dns_destinations'][$proxyNodeProcessType][$proxyNodeProcessNodeId]['listening_ip_version_' . $proxyNodeIpVersion]) === false) {
									$proxyNodeProcessConfiguration['e' . $proxyNodeProcessConfigurationIndexes['e']] = 'nserver ' . $this->nodeData['next']['node_process_recursive_dns_destinations'][$proxyNodeProcessType][$proxyNodeProcessNodeId]['listening_ip_version_' . $proxyNodeIpVersion] . '[:' . $this->nodeData['next']['node_process_recursive_dns_destinations'][$proxyNodeProcessType][$proxyNodeProcessNodeId]['listening_port_number_version_' . $proxyNodeIpVersion] . ']';
									$proxyNodeProcessConfigurationIndexes['e']++;
								}

								if (empty($proxyNodeProcessInterfaceDestinationIps[$proxyNodeIpVersion] = $this->nodeData['next']['nodes'][$proxyNodeProcessNodeId]['external_ip_version_' . $proxyNodeIpVersion]) === false) {
									$proxyNodeProcessConfiguration['f'] = $proxyNodeProcessConfiguration['g'] = $proxyNodeProcessTypeServiceName . ' -a ';
									$proxyNodeProcessInterfaceDestinationIp = $proxyNodeProcessNodeIps[$proxyNodeIpVersion] = $this->nodeData['nodes'][$proxyNodeProcessNodeId]['external_ip_version_' . $proxyNodeIpVersion];

									if (empty($this->nodeData['next']['nodes'][$proxyNodeProcessNodeId]['internal_ip_version_' . $proxyNodeIpVersion]) === false) {
										$proxyNodeProcessInterfaceDestinationIp = $proxyNodeProcessNodeIps[$proxyNodeIpVersion] = $this->nodeData['next']['nodes'][$proxyNodeProcessNodeId]['internal_ip_version_' . $proxyNodeIpVersion];

										if (empty($this->nodeData['current']['node_reserved_internal_destination_ip_addresses'][$proxyNodeIpVersion][$proxyNodeProcessInterfaceDestinationIp]) === false) {
											$proxyNodeProcessesStart = false;
										}
									}

									$proxyNodeProcessConfiguration['f'] .= ' -e' . $proxyNodeProcessInterfaceDestinationIp . ' -i' . $proxyNodeProcessInterfaceDestinationIp;
									$proxyNodeProcessConfiguration['g'] .= ' -e' . $this->nodeData['node_reserved_internal_destinations'][$proxyNodeProcessNodeId][$proxyNodeIpVersion] . ' -i' . $this->nodeData['next']['node_reserved_internal_destinations'][$proxyNodeProcessNodeId][$proxyNodeIpVersion];
								}
							}

							ksort($proxyNodeProcessConfiguration);
							$proxyNodeProcessInterfaceConfigurations = array(
								'f' => $proxyNodeProcessConfiguration['f'],
								'g' => $proxyNodeProcessConfiguration['g']
							);

							foreach ($proxyNodeProcessPortNumbers as $proxyNodeProcessId => $proxyNodeProcessPortNumber) {
								while ($this->_verifyNodeProcessConnections($proxyNodeProcessNodeIps, $proxyNodeProcessPortNumber) === true) {
									sleep(1);
								}

								$proxyNodeProcessName = $proxyNodeProcessType . '_' . $proxyNodeProcessId;

								if (file_exists('/etc/3proxy/' . $proxyNodeProcessName . '.cfg') === true) {
									$proxyNodeProcessProcessIds = $this->fetchProcessIds($proxyNodeProcessName . ' ', '/etc/3proxy/' . $proxyNodeProcessName . '.cfg');

									if (empty($proxyNodeProcessProcessIds) === false) {
										$this->_killProcessIds($proxyNodeProcessProcessIds);
									}
								}

								shell_exec('cd /bin && sudo ln /bin/3proxy ' . $proxyNodeProcessName);
								// todo: start all node process ports with same service file instead of daemon-reload for each process port
								$proxyNodeProcessSystemdServiceFileContents = array(
									'[Service]',
									'ExecStart=/bin/' . $proxyNodeProcessName . ' ' . ($proxyNodeProcessConfigurationFile = '/etc/3proxy/' . $proxyNodeProcessName . '.cfg')
								);
								file_put_contents('/etc/systemd/system/' . $proxyNodeProcessName . '.service', implode("\n", $proxyNodeProcessSystemdServiceFileContents));
								$proxyNodeProcessConfiguration['a8'] = 'pidfile /var/run/3proxy/' . $proxyNodeProcessName . '.pid';
								$proxyNodeProcessConfiguration['f'] = $proxyNodeProcessInterfaceConfigurations['f'] . ' -p' . $proxyNodeProcessPortNumber;
								$proxyNodeProcessConfiguration['g'] = $proxyNodeProcessInterfaceConfigurations['g'] . ' -p' . $proxyNodeProcessPortNumber;
								file_put_contents($proxyNodeProcessConfigurationFile, implode("\n", $proxyNodeProcessConfiguration));
								chmod($proxyNodeProcessConfigurationFile, 0755);
								shell_exec('sudo ' . $this->nodeData['next']['binary_files']['systemctl'] . ' daemon-reload');
								unlink('/var/run/3proxy/' . $proxyNodeProcessName . '.pid');
								$proxyNodeProcessEnded = false;
								$proxyNodeProcessEndedTime = time();

								while ($proxyNodeProcessEnded === false) {
									$proxyNodeProcessEnded = ($this->_verifyNodeProcess($this->nodeData['next']['node_reserved_internal_destinations'][$proxyNodeProcessNodeId][$proxyNodeIpVersion], $proxyNodeIpVersion, $proxyNodeProcessPortNumber, $proxyNodeProcessType) === false);
									sleep(1);
								}

								$proxyNodeProcessStarted = false;
								$proxyNodeProcessStartedTime = time();

								if ($proxyNodeProcessesStart === true) {
									while ($proxyNodeProcessStarted === false) {
										shell_exec('sudo ' . $this->nodeData['binary_files']['service'] . ' ' . $proxyNodeProcessName . ' start');
										$proxyNodeProcessStarted = ($this->_verifyNodeProcess($this->nodeData['next']['node_reserved_internal_destinations'][$proxyNodeProcessNodeId][$proxyNodeIpVersion], $proxyNodeIpVersion, $proxyNodeProcessPortNumber, $proxyNodeProcessType) === true);
										sleep(1);
									}
								} else {
									$this->reprocess = true;
								}

								if (file_exists('/var/run/3proxy/' . $proxyNodeProcessName . '.pid') === true) {
									$proxyNodeProcessProcessId = file_get_contents('/var/run/3proxy/' . $proxyNodeProcessName . '.pid');

									if (is_numeric($proxyNodeProcessProcessId) === true) {
										shell_exec('sudo ' . $this->nodeData['next']['binary_files']['prlimit'] . ' -p ' . $proxyNodeProcessProcessId . ' -n1000000000');
										shell_exec('sudo ' . $this->nodeData['next']['binary_files']['prlimit'] . ' -p ' . $proxyNodeProcessProcessId . ' -n=1000000000');
										shell_exec('sudo ' . $this->nodeData['next']['binary_files']['prlimit'] . ' -p ' . $proxyNodeProcessProcessId . ' -s"unlimited"');
										shell_exec('sudo ' . $this->nodeData['next']['binary_files']['prlimit'] . ' -p ' . $proxyNodeProcessProcessId . ' -s=unlimited');
									}
								}
							}
						}
					}
				}
			}

			foreach (array(0, 1) as $nodeProcessPartKey) {
				// todo: add current ipset rules to variable for easy deletion at end of script
				// todo: create new ipset rules with no $nodeProcessPartKey so deleting split ipset rules is faster

				foreach ($this->nodeData['next']['node_process_types'] as $nodeProcessType) {
					foreach ($this->nodeData['next']['node_processes'][$nodeProcessType][$nodeProcessPartKey] as $nodeProcessNodeId => $nodeProcessPortNumbers) {
						$nodeReservedInternalDestinationIpVersion = key($this->nodeData['next']['node_reserved_internal_destinations'][$nodeProcessNodeId]);

						foreach ($nodeProcessPortNumbers as $nodeProcessId => $nodeProcessPortNumber) {
							if ($this->_verifyNodeProcess($this->nodeData['next']['node_reserved_internal_destinations'][$nodeProcessNodeId][$nodeReservedInternalDestinationIpVersion], $nodeReservedInternalDestinationIpVersion, $nodeProcessPortNumber, $nodeProcessType) === false) {
								unset($this->nodeData['next']['node_processes'][$nodeProcessType][$nodeProcessPartKey][$nodeProcessNodeId][$nodeProcessId]);
							}
						}
					}
				}
			}

			$this->_processFirewall();
			// todo: update resolv.conf before proxy process reconfig
			// todo: add DROP rules to all ports that don't exist in public listening port ipset rule "__"
			$nodeRecursiveDnsDestinations = array();

			foreach ($this->nodeData['next']['node_recursive_dns_destinations']['recursive_dns'] as $nodeRecursiveDnsDestination) {
				foreach ($this->nodeData['next']['node_ip_versions'] as $nodeIpVersion) {
					$nodeRecursiveDnsDestinations[] = 'nameserver [' . $nodeRecursiveDnsDestination['listening_ip_version_' . $nodeIpVersion] . ']:' . $nodeRecursiveDnsDestination['port_number_version_' . $nodeIpVersion];
				}
			}

			file_put_contents('/usr/local/ghostcompute/resolv.conf', implode("\n", $nodeRecursiveDnsDestinations));

			foreach ($this->ipVersions as $ipVersionNumber => $ipVersion) {
				if (empty($nodeIpsToDelete[$ipVersionNumber]) === false) {
					foreach ($nodeIpsToDelete[$ipVersionNumber] as $nodeIpToDelete) {
						shell_exec('sudo ' . $this->nodeData['next']['binary_files']['ip'] . ' -' . $ipVersionNumber . ' addr delete ' . $nodeIpToDelete . '/' . $ipVersion['network_mask'] . ' dev ' . $this->nodeData['next']['interface_name']) . '\');';
					}
				}

				foreach ($this->nodeData['current']['node_reserved_internal_destination_ip_addresses'][$ipVersionNumber] as $nodeReservedInternalDestinationIpAddress) {
					if (empty($this->nodeData['next']['node_reserved_internal_destination_ip_addresses'][$ipVersionNumber][$nodeReservedInternalDestinationIpAddress]) === true) {
						shell_exec('sudo ' . $this->nodeData['binary_files']['ipset'] . ' del _ ' . $nodeReservedInternalDestinationIpAddress);
					}
				}
			}

			foreach ($nodeProcessesToRemove as $nodeProcessType => $nodeProcessIds) {
				$nodeProcessProcessIds = array();

				foreach ($nodeProcessIds as $nodeProcessId) {
					$nodeProcessName = $nodeProcessType . '_' . $nodeProcessId;

					switch ($nodeProcessType) {
						case 'http_proxy':
						case 'socks_proxy':
							if (file_exists('/var/run/3proxy/' . $nodeProcessName . '.pid') === true) {
								$nodeProcessProcessIds[] = file_get_contents('/var/run/3proxy/' . $nodeProcessName . '.pid');
							}

							unlink('/bin/' . $nodeProcessName);
							unlink('/etc/3proxy/' . $nodeProcessName . '.cfg');
							unlink('/etc/systemd/system/' . $nodeProcessName . '.service');
							unlink('/var/run/3proxy/' . $nodeProcessName . '.pid');
							break;
						case 'recursive_dns':
							if (file_exists('/var/run/named/named_' . $nodeProcess['id'] . '.pid') === true) {
								$nodeProcessProcessIds[] = file_get_contents('/var/run/named/named_' . $nodeProcess['id'] . '.pid');
							}

							rmdir('/etc/bind_' . $nodeProcessName);
							rmdir('/var/cache/bind_' . $nodeProcessName);
							unlink('/etc/default/' . $recursiveDnsNodeProcessDefaultServiceName . '_' . $nodeProcessName);
							unlink('/lib/systemd/system/' . $recursiveDnsNodeProcessDefaultServiceName . '_' . $nodeProcessName . '.service');
							unlink('/usr/sbin/named_' . $nodeProcessName);
							unlink('/var/run/named/' . $nodeProcessName . '.pid');
							break;
					}
				}

				if (empty($nodeProcessProcessIds) === false) {
					$this->_killProcessIds($nodeProcessProcessIds);
				}
			}

			// todo: delete current node_reserved_internal_destination_ip_addresses from _ rule and delete split firewall rule sets
			$this->nodeData['current'] = array_intersect_key($this->nodeData['next'], array(
				'node_processes' => true,
				'node_reserved_internal_destination_ip_addresses' => true,
				'node_recursive_dns_destinations' => true,
				'node_ssh_port_numbers' => true
			));
			// todo: send node_ssh_port_numbers to API to prevent process port conflictions on main node ip
			file_put_contents('/tmp/node_data', json_encode($this->nodeData['current']));
			exec('sudo curl -s --form-string "json={\"action\":\"process\",\"data\":{\"processed\":' . (empty($this->reprocess) === true) . '}}" ' . $this->parameters['system_url'] . '/endpoint/nodes 2>&1', $response);
			$response = json_decode(current($response), true);
			return $response;
		}

		public function processNodeData() {
			if (empty($this->nodeData['current']) === true) {
				$nodeDataFileContents = json_decode(file_get_contents('/tmp/node_data'));

				if (empty($nodeDataFileContents) === false) {
					$this->nodeData['current'] = $nodeDataFileContents;
				}
			}

			if (empty($this->nodeData['next']) === true) {
				unlink($nodeProcessResponseFile);
				shell_exec('sudo wget -O ' . ($nodeProcessResponseFile = '/tmp/node_process_response') . ' --no-dns-cache --post-data "json={\"action\":\"process\",\"where\":{\"id\":\"' . $this->parameters['id'] . '\"}}" --retry-connrefused --timeout=60 --tries=2 ' . $this->parameters['url'] . '/endpoint/nodes');

				if (file_exists($nodeProcessResponseFile) === false) {
					echo 'Error processing node, please try again.' . "\n";
					exit;
				}

				$nodeProcessResponseFileContents = json_decode(file_get_contents($nodeProcessResponseFile), true);

				if (empty($nodeProcessResponseFileContents['data']) === false) {
					$this->nodeData['next'] = $nodeProcessResponseFileContents['data'];
					$binaries = array(
						array(
							'command' => ($uniqueId = '_' . uniqid() . time()),
							'name' => 'ip',
							'output' => 'ip help',
							'package' => 'iproute2'
						),
						array(
							'command' => '-h',
							'name' => 'ip6tables-restore',
							'output' => 'tables-restore ',
							'package' => 'iptables'
						),
						array(
							'command' => '-' . $uniqueId,
							'name' => 'ipset',
							'output' => 'argument',
							'package' => 'ipset'
						),
						array(
							'command' => '-h',
							'name' => 'iptables-restore',
							'output' => 'tables-restore ',
							'package' => 'iptables'
						),
						array(
							'command' => '-' . $uniqueId,
							'name' => 'netstat',
							'output' => 'invalid option',
							'package' => 'net-tools'
						),
						array(
							'command' => '-v',
							'name' => 'php',
							'output' => 'PHP ',
							'package' => 'php'
						),
						array(
							'command' => '-' . $uniqueId,
							'name' => 'prlimit',
							'output' => 'invalid option',
							'package' => 'util-linux'
						),
						array(
							'command' => $uniqueId,
							'name' => 'service',
							'output' => 'unrecognized service',
							'package' => 'systemd'
						),
						array(
							'command' => ($uniqueId = '_' . uniqid() . time()),
							'name' => 'ss',
							'output' => 'inet prefix',
							'package' => 'iproute2'
						),
						array(
							'command' => $uniqueId,
							'name' => 'sysctl',
							'output' => 'cannot',
							'package' => 'procps'
						),
						array(
							'command' => '-' . $uniqueId,
							'name' => 'systemctl',
							'output' => 'invalid option',
							'package' => 'systemd'
						),
						array(
							'command' => $uniqueId,
							'name' => 'telinit',
							'output' => 'single',
							'package' => 'systemd'
						)
					);

					foreach ($binaries as $binary) {
						$commands = array(
							'#!/bin/bash',
							'whereis ' . $binary['name'] . ' | awk \'{ for (i=2; i<=NF; i++) print $i }\' | while read -r binaryFile; do echo $((sudo $binaryFile "' . $binary['command'] . '") 2>&1) | grep -c "' . $binary['output'] . '" && echo $binaryFile && break; done | tail -1'
						);
						$commandsFile = '/tmp/commands.sh';

						if (file_exists($commandsFile) === true) {
							unlink($commandsFile);
						}

						file_put_contents($commandsFile, implode("\n", $commands));
						shell_exec('sudo chmod +x ' . $commandsFile);
						exec('cd /tmp/ && sudo ./' . basename($commandsFile), $binaryFile);
						$binaryFile = current($binaryFile);
						unlink($commandsFile);

						if (empty($binaryFile) === true) {
							echo 'Error detecting ' . $binary['name'] . ' binary file, please try again.' . "\n";
							shell_exec('sudo apt-get update');
							shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install ' . $binary['package']);
							exit;
						}

						$this->nodeData['next']['binary_files'][$binary['name']] = $binaryFile;
					}

					exec('sudo ' . $this->nodeData['next']['binary_files']['netstat'] . ' -i | grep -v : | grep -v face | grep -v lo | awk \'NR==1{print $1}\' 2>&1', $interfaceName);
					$this->nodeData['next']['interface_name'] = current($interfaceName);

					// todo: monitor ssh_ports and reconfigure firewall if new SSH ports are opened, add ssh_ports to cached node data

					if (file_exists('/etc/ssh/sshd_config') === true) {
						exec('grep "Port " /etc/ssh/sshd_config | grep -v "#" | awk \'{print $2}\' 2>&1', $sshPortNumbers);

						foreach ($sshPortNumbers as $sshPortNumberKey => $sshPortNumber) {
							if (
								(strlen($sshPortNumber) > 5) ||
								(is_numeric($sshPortNumber) === false)
							) {
								unset($sshPorts[$sshPortNumberKey]);
							}
						}

						if (empty($sshPortNumbers) === false) {
							$this->nodeData['next']['node_ssh_port_numbers'] = $sshPortNumbers;
						}
					}
				}

				if (empty($this->nodeData['current']) === true) {
					$this->nodeData['current'] = $this->nodeData['next'];
				}
			}

			return;
		}

	}
?>
