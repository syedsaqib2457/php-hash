<?php
	class ProcessNodeProcesses {

		public $parameters;

		public function __construct($parameters) {
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
				'sudo ' . $this->nodeData['binary_files']['telinit'] . ' u'
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
			// todo: use ipset rules
			$firewallBinaryFiles = array(
				4 => $this->nodeData['binary_files']['iptables-restore'],
				6 => $this->nodeData['binary_files']['ip6tables-restore']
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

			foreach ($this->nodeData['node_ip_versions'] as $nodeIpVersionNetworkMask => $nodeIpVersion) {
				$firewallRules = array(
					'*filter',
					':INPUT ACCEPT [0:0]',
					':FORWARD ACCEPT [0:0]',
					':OUTPUT ACCEPT [0:0]',
					'-A INPUT -p icmp -m hashlimit --hashlimit-above 1/second --hashlimit-burst 2 --hashlimit-htable-gcinterval 100000 --hashlimit-htable-expire 10000 --hashlimit-mode srcip --hashlimit-name icmp --hashlimit-srcmask ' . $nodeIpVersionNetworkMask . ' -j DROP'
				);

				if (empty($this->nodeData['ssh_port_numbers']) === false) {
					foreach ($this->nodeData['ssh_port_numbers'] as $sshPortNumber) {
						$firewallRules[] = '-A INPUT -p tcp --dport ' . $sshPortNumber . ' -m hashlimit --hashlimit-above 1/minute --hashlimit-burst 10 --hashlimit-htable-gcinterval 600000 --hashlimit-htable-expire 60000 --hashlimit-mode srcip --hashlimit-name ssh --hashlimit-srcmask ' . $nodeIpVersionNetworkMask . ' -j DROP';
					}
				}

				$firewallRules[] = 'COMMIT';
				$firewallRules[] = '*nat';
				$firewallRules[] = ':PREROUTING ACCEPT [0:0]';
				$firewallRules[] = ':INPUT ACCEPT [0:0]';
				$firewallRules[] = ':OUTPUT ACCEPT [0:0]';
				$firewallRules[] = ':POSTROUTING ACCEPT [0:0]';

				//todo: make sure prerouting load balancing works with DNS from system requests and proxy process requests, use output if not
				foreach ($this->nodeData['node_process_types'] as $nodeProcessType) {
					if (empty($this->nodeData['node_processes'][$nodeProcessType]) === false) {
						foreach ($nodeProcessPartKeys as $nodeProcessPartKey) {
							krsort($this->nodeData['node_processes'][$nodeProcessType][$nodeProcessPartKey]);
							$nodeProcessParts = array_chunk($this->nodeData['node_processes'][$nodeProcessType][$nodeProcessPartKey], 10);

							foreach ($nodeProcessParts as $nodeProcessPart) {
								foreach ($this->nodeData['node_processes'][$nodeProcessType][$nodeProcessPartKey] as $nodeProcessPortNumber) {
									$nodeProcessIndex = 0;
									$nodeProcessLoadBalancer = '';

									if ($nodeProcessIndex > 0) {
										$nodeProcessLoadBalancer = '-m statistic --mode nth --every ' . $nodeProcessIndex . ' --packet 0 ';
									}

									$nodeProcessTransportProtocols = array(
										'tcp',
										'udp'
									);

									if ($nodeProcessType === 'http_proxy') {
										unset($nodeProcessTransportProtocols[1]);
									}

									foreach ($nodeProcessTransportProtocols as $nodeProcessTransportProtocol) {
										$firewallRules[] = '-A PREROUTING -p ' . $nodeProcessTransportProtocol . ' -m multiport ! -d ' . $this->nodeData['private_networking']['reserved_node_ip'][$nodeIpVersion] . ' --dports ' . implode(',', $nodeProcessPart) . ' ' . $nodeProcessLoadBalancer . ' -j DNAT --to-destination :' . $nodeProcessPortNumber . ' --persistent';
									}

									$nodeProcessIndex++;
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

				if (empty($this->nodeData['private_network']['ip_blocks'][$nodeIpVersion]) === false) {
					foreach ($this->nodeData['private_network']['ip_blocks'][$nodeIpVersion] as $privateNetworkIpBlock) {
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

		protected function _verifyNodeProcess($nodeProcessNodeIp, $nodeProcessIpVersion, $nodeProcessPortNumber, $nodeProcessType) {
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

		protected function _verifyNodeProcessConnections($nodeProcessPortNumber) {
			exec('sudo ' . $this->nodeData['binary_files']['ss'] . ' -p -t -u state connected "( sport = :' . $nodeProcessPortNumber . ' )" | head -1 2>&1', $response);

			if (is_array($response) === false) {
				$response = $this->_verifyNodeProcessConnections($nodeProcessPortNumber);
			}

			$response = boolval($response);
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
			$nodeData = json_decode(file_get_contents('/tmp/node_data'));
			// todo: if more than 10 ports fail from timing out during reconfig, send status data to system to increase process count if bottleneck is from congested ports and isn't from low system resources
				// if user enables automatic process scaling, user should be able to disable this to prevent ports from being opened
				// increase timeout for verifynodeprocess requests until latency is measured from a successful response, add latency to resource usage data

			if (empty($this->nodeData['nodes']) === true) {
				if (empty($nodeData) === false) {
					foreach ($nodeData['node_processes'] as $nodeProcessType => $nodeProcessNodeParts) {
						foreach ($nodeProcessNodeParts as $nodeProcessNodePart) {
							foreach ($nodeProcessNodePart as $nodeProcessNodeId => $nodeProcessPortNumbers) {
								$nodeReservedInternalDestinationIpVersion = key($nodeData['node_reserved_internal_destinations'][$nodeProcessNodeId]);

								foreach ($nodeProcessPortNumbers as $nodeProcessPortNumber) {
									if ($this->_verifyNodeProcess($nodeData['node_reserved_internal_destinations'][$nodeProcessNodeId][$nodeReservedInternalDestinationIpVersion], $nodeReservedInternalDestinationIpVersion, $nodeProcessPortNumber, $nodeProcessType) === false) {
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

			foreach ($nodeData['node_processes'] as $nodeProcessType => $nodeProcessNodeParts) {
				foreach ($nodeProcessNodeParts as $nodeProcessNodePart) {
					foreach ($nodeProcessNodePart as $nodeProcessNodeId => $nodeProcessPortNumbers) {
						foreach ($nodeProcessPortNumbers as $nodeProcessId => $nodeProcessPortNumber) {
							if (
								(empty($this->nodeData['node_processes'][$nodeProcessType][0][$nodeProcessNodeId][$nodeProcessId]) === true) &&
								(empty($this->nodeData['node_processes'][$nodeProcessType][1][$nodeProcessNodeId][$nodeProcessId]) === true)
							) {
								$nodeProcessesToRemove[$nodeProcessType][$nodeProcessId] = $nodeProcessId;
							}
						}
					}
				}
			}

			// todo: cache reserved internal ips for current processes before assigning $nodeProcesses to new node processes
			$nodeProcesses = $this->nodeData['node_processes'];
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
			shell_exec('sudo ' . $this->nodeData['binary_files']['sysctl'] . ' -p');
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

			foreach ($this->nodeData['node_ip_versions'] as $nodeIpVersionNetworkMask => $nodeIpVersion) {
				$dynamicKernelOptions['net.ipv' . $nodeIpVersion . '.tcp_mem'] = $memoryCapacityPages . ' ' . $memoryCapacityPages . ' ' . $memoryCapacityPages;
				$dynamicKernelOptions['net.ipv' . $nodeIpVersion . '.tcp_rmem'] = 1 . ' ' . $defaultSocketBufferMemoryBytes . ' ' . ($defaultSocketBufferMemoryBytes * 2);
				$dynamicKernelOptions['net.ipv' . $nodeIpVersion . '.tcp_wmem'] = $dynamicKernelOptions['net.ipv' . $nodeIpVersion . '.tcp_rmem'];
				$dynamicKernelOptions['net.ipv' . $nodeIpVersion . '.udp_mem'] = $dynamicKernelOptions['net.ipv' . $nodeIpVersion . '.tcp_mem'];
			}

			foreach ($dynamicKernelOptions as $dynamicKernelOptionKey => $dynamicKernelOptionValue) {
				shell_exec('sudo ' . $this->nodeData['binary_files']['sysctl'] . ' -w ' . $dynamicKernelOptionKey . '="' . $dynamicKernelOptionValue . '"');
			}

			$nodeIpVersions = array(
				32 => 4,
				128 => 6
			);

			foreach ($nodeIpVersions as $nodeIpVersionNetworkMask => $nodeIpVersion) {
				$nodeIpVersionInterfaceType = 'inet';

				if ($nodeIpVersion === 6) {
					$nodeIpVersionInterfaceType .= 6;
				}

				exec('sudo ' . $this->nodeData['binary_files']['ip'] . ' addr show dev ' . $this->nodeData['interface_name'] . ' | grep "' . $nodeIpVersionInterfaceType . ' " | grep "' . $nodeIpVersionData['network_mask'] . ' " | awk \'{print substr($2, 0, length($2) - ' . ($nodeIpVersion / 2) . ')}\'', $existingInterfaceNodeIps);
				$existingInterfaceNodeIps = current($existingInterfaceNodeIps);
				$interfaceNodeIpFileContents = array(
					'<?php'
				);
				$interfaceNodeIpsToProcess = array(
					'add' => array_diff($this->nodeData['node_ips'][$nodeIpVersion], $existingInterfaceNodeIps),
					'delete' => array_diff($existingInterfaceNodeIps, $this->nodeData['node_ips'][$nodeIpVersion])
				);

				foreach ($interfaceNodeIpsToProcess as $interfaceNodeIpAction => $interfaceNodeIps) {
					$interfaceNodeIpAction = substr($interfaceNodeIpAction, 3);

					foreach ($interfaceNodeIps as $interfaceNodeIp) {
						$command = 'sudo ' . $this->nodeData['binary_files']['ip'] . ' -' . $nodeIpVersion . ' addr ' . $interfaceNodeIpAction . ' ' . $interfaceNodeIp . '/' . $nodeIpVersionNetworkMask . ' dev ' . $this->nodeData['interface_name'];
						shell_exec($command);

						if ($interfaceNodeIpAction === 'add') {
							$interfaceNodeIpFileContents[] = 'shell_exec(\'' . $command . '\');';
						}
					}
				}
			}

			file_put_contents('/usr/local/ghostcompute/node_interfaces.php', implode("\n", $interfaceNodeIps));

			if (empty($recursiveDnsNodeProcessDefaultServiceName) === true) {
				$recursiveDnsNodeProcessDefaultServiceName = 'named';

				if (is_dir('/etc/default/bind9') === true) {
					$recursiveDnsNodeProcessDefaultServiceName = 'bind9';
				}
			}

			foreach (array(0, 1) as $nodeProcessPartKey) {
				foreach ($this->nodeData['node_process_types'] as $nodeProcessType) {
					foreach ($this->nodeData['node_processes'][$nodeProcessType][$nodeProcessPartKey] as $nodeProcessId => $nodeProcessPortNumber) {
						if ($this->_verifyNodeProcess($nodeProcessPortNumber, $nodeProcessType) === false) {
							unset($this->nodeData['node_processes'][$nodeProcessType][$nodeProcessPartKey][$nodeProcessId]);
						}
					}
				}

				$this->_processFirewall($nodeProcessPartKey);
				$nodeProcessPartKey = abs($nodeProcessPartKey - 1);

				foreach ($this->nodeData['node_processes']['recursive_dns'][$nodeProcessPartKey] as $recursiveDnsNodeProcessNodeId => $recursiveDnsNodeProcessPortNumbers) {
					$recursiveDnsNodeProcessConfiguration = array(
						'a0' => 'acl privateNetworkIpBlocks {',
						'a1' => $this->nodeData['private_network']['ip_blocks'][4],
						'a2' => $this->nodeData['private_network']['ip_blocks'][6],
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
						'e' => false,
						'f' => false,
						'g' => '};'
					);
					$recursiveDnsNodeProcessConfigurationIndexes = $recursiveDnsNodeProcessConfigurationPartIndexes = array(
						'a' => 5,
						'b' => 26,
						'c' => 0
					);

					if (empty($this->nodeData['node_process_users']['recursive_dns']) === false) {
						$recursiveDnsNodeProcessConfiguration['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = 'logging {';
						$recursiveDnsNodeProcessConfigurationIndexes['c']++;

						foreach ($this->nodeData['node_process_users']['recursive_dns'][$recursiveDnsNodeProcessNodeId] as $recursiveDnsNodeProcessUserIds) {
							foreach ($recursiveDnsNodeProcessUserIds as $recursiveDnsNodeProcessUserId) {
								$recursiveDnsNodeProcessUser = $this->nodeData['users'][$recursiveDnsNodeProcessUserId];

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
								$recursiveDnsNodeProcessConfiguration['d' . $recursiveDnsNodeProcessConfigurationIndexes['d']] = 'channel ' . $recursiveDnsNodeProcessName . ' {';
								$recursiveDnsNodeProcessConfigurationIndexes['d']++;
								$recursiveDnsNodeProcessConfiguration['d' . $recursiveDnsNodeProcessConfigurationIndexes['d']] = 'file "/var/log/named/' . $recursiveDnsNodeProcessName . '"';
								$recursiveDnsNodeProcessConfigurationIndexes['d']++;
								$recursiveDnsNodeProcessConfiguration['d' . $recursiveDnsNodeProcessConfigurationIndexes['d']] = 'print-time yes';
								$recursiveDnsNodeProcessConfigurationIndexes['d']++;
								$recursiveDnsNodeProcessConfiguration['d' . $recursiveDnsNodeProcessConfigurationIndexes['d']] = '};';
								$recursiveDnsNodeProcessConfigurationIndexes['d']++;
								$recursiveDnsNodeProcessConfiguration['d' . $recursiveDnsNodeProcessConfigurationIndexes['d']] = 'category ' . $recursiveDnsNodeProcessName . ' {';
								$recursiveDnsNodeProcessConfigurationIndexes['d']++;
								$recursiveDnsNodeProcessConfiguration['d' . $recursiveDnsNodeProcessConfigurationIndexes['d']] = 'queries_log;';
								$recursiveDnsNodeProcessConfigurationIndexes['d']++;
								$recursiveDnsNodeProcessConfiguration['d' . $recursiveDnsNodeProcessConfigurationIndexes['d']] = '};';
								$recursiveDnsNodeProcessConfigurationIndexes['d']++;
							}
						}

						$recursiveDnsNodeProcessConfiguration['d' . $recursiveDnsNodeProcessConfigurationIndexes['d']] = '};';
					}

					foreach ($this->nodeData['data']['node_ip_versions'] as $recursiveDnsNodeIpVersion) {
						$recursiveDnsNodeProcessConfigurationOptionSuffix = '';

						if ($recursiveDnsNodeIpVersion === 6) {
							$recursiveDnsNodeProcessConfigurationOptionSuffix = '-v6';
						}

						$recursiveDnsNodeProcessInterfaceSourceIp = $this->nodeData['nodes'][$recursiveDnsNodeProcessNodeId]['external_ip_version_' . $recursiveDnsNodeIpVersion];

						if (empty($this->nodeData['nodes'][$recursiveDnsNodeProcessNodeId]['internal_ip_version_' . $recursiveDnsNodeIpVersion]) === false) {
							$recursiveDnsNodeProcessInterfaceSourceIp = $this->nodeData['nodes'][$recursiveDnsNodeProcessNodeId]['internal_ip_version_' . $recursiveDnsNodeIpVersion];
						}

						$recursiveDnsNodeProcessConfiguration['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = 'listen-on' . $recursiveDnsNodeProcessConfigurationOptionSuffix . ' {';
						$recursiveDnsNodeProcessConfigurationIndexes['b']++;
						$recursiveDnsNodeProcessConfiguration['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = $this->nodeData['node_process_recursive_dns_destinations']['recursive_dns'][$recursiveDnsNodeProcessNodeId]['listening_ip_version_' . $recursiveDnsNodeIpVersion] . ';';
						$recursiveDnsNodeProcessConfigurationIndexes['b']++;
						$recursiveDnsNodeProcessConfiguration['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = $this->nodeData['node_reserved_internal_destinations'][$recursiveDnsNodeProcessNodeId][$recursiveDnsNodeIpVersion] . ';';
						$recursiveDnsNodeProcessConfigurationIndexes['b']++;

						if (empty($this->nodeData['node_process_users']['recursive_dns'][$recursiveDnsNodeProcessNodeId]) === false) {
							$recursiveDnsNodeProcessConfiguration['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = $recursiveDnsNodeProcessInterfaceSourceIp . ';';
							$recursiveDnsNodeProcessConfigurationIndexes['b']++;
						}

						$recursiveDnsNodeProcessConfiguration['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = '};';
						$recursiveDnsNodeProcessConfigurationIndexes['b']++;
						$recursiveDnsNodeProcessConfiguration['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = 'query-source' . $recursiveDnsNodeProcessConfigurationOptionSuffix . ' address ' . $recursiveDnsNodeProcessInterfaceSourceIp . ';';
						$recursiveDnsNodeProcessConfigurationIndexes['c']++;
					}

					$recursiveDnsNodeProcessConfiguration['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = '};';
					ksort($recursiveDnsNodeProcessConfiguration);

					foreach ($recursiveDnsNodeProcessPortNumbers as $recursiveDnsNodeProcessId => $recursiveDnsNodeProcessPortNumber) {
						while ($this->_verifyNodeProcessConnections($recursiveDnsNodeProcessPortNumber) === true) {
							sleep(1);
						}

						$recursiveDnsNodeProcessName = 'recursive_dns_' . $recursiveDnsNodeProcessId;

						if (file_exists('/etc/' . $recursiveDnsNodeProcessName . '/named.conf') === true) {
							$recursiveDnsNodeProcessProcessIds = $this->fetchProcessIds($recursiveDnsNodeProcessName . ' ', '_' . $recursiveDnsNodeProcessName . '/');

							if (empty($recursiveDnsNodeProcessProcessIds) === false) {
								$this->_killProcessIds($recursiveDnsNodeProcessProcessIds);
							}
						}

						$recursiveDnsNodeProcessConfiguration['e'] = '"/var/cache/' . $recursiveDnsNodeProcessName . '";';
						$recursiveDnsNodeProcessConfiguration['f'] = 'pid-file "/var/run/named/' . $recursiveDnsNodeProcessName . '.pid";';

						foreach ($this->nodeData['node_ip_versions'] as $nodeIpVersion) {
							$recursiveDnsNodeIndex = 0;

							// todo: add listening port to each indexed listening ip
							while (isset($recursiveDnsNodeProcessConfigurationOptions['listening_address_version_4_' . $recursiveDnsNodeIndex]) === true) {
								$recursiveDnsNodeProcessConfigurationOptions['listening_address_version_' . $nodeIpVersion . '_' . $recursiveDnsNodeIndex] .= ':' . $recursiveDnsNodeProcessPortNumber;
								$recursiveDnsNodeIndex++;
							}
						}

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
							$recursiveDnsNodeProcessConfiguration = array(
								'include "/etc/' . $recursiveDnsNodeProcessName . '/named.conf.options";',
								'include "/etc/' . $recursiveDnsNodeProcessName . '/named.conf.local";',
								'include "/etc/' . $recursiveDnsNodeProcessName . '/named.conf.default-zones";'
							);
							file_put_contents('/etc/' . $recursiveDnsNodeProcessName . '/named.conf', implode("\n", $recursiveDnsNodeProcessConfiguration));
						}

						$recursiveDnsNodeProcessConfigurationOptions = array_filter($recursiveDnsNodeProcessConfigurationOptions);
						file_put_contents('/etc/' . $recursiveDnsNodeProcessName . '/named.conf.options', implode("\n", $recursiveDnsNodeProcessConfigurationOptions));

						if (is_dir('/var/cache/' . $recursiveDnsNodeProcessName) === false) {
							mkdir('/var/cache/' . $recursiveDnsNodeProcessName);
						}

						shell_exec('sudo ' . $this->nodeData['binary_files']['systemctl'] . ' daemon-reload');
						unlink('/var/run/named/' . $recursiveDnsNodeProcessName . '.pid');
						$recursiveDnsNodeProcessEnded = false;
						$recursiveDnsNodeProcessEndedTime = time();

						while ($recursiveDnsNodeProcessEnded === false) {
							$recursiveDnsNodeProcessEnded = ($this->_verifyNodeProcess($this->nodeData['node_reserved_internal_destinations'][$recursiveDnsNodeProcessNodeId][$recursiveDnsNodeIpVersion], $recursiveDnsNodeIpVersion, $recursiveDnsNodeProcessPortNumber, 'recursive_dns') === false);
							sleep(1);
						}

						$recursiveDnsNodeProcessStarted = false;
						$recursiveDnsNodeProcessStartedTime = time();

						while ($recursiveDnsNodeProcessStarted === false) {
							shell_exec('sudo ' . $this->nodeData['binary_files']['service'] . ' ' . $recursiveDnsNodeProcessName . ' start');
							$recursiveDnsNodeProcessStarted = ($this->_verifyNodeProcess($this->nodeData['node_reserved_internal_destinations'][$recursiveDnsNodeProcessNodeId][$recursiveDnsNodeIpVersion], $recursiveDnsNodeIpVersion, $recursiveDnsNodeProcessPortNumber, 'recursive_dns') === true);
							sleep(1);
						}

						if (file_exists('/var/run/named/' . $recursiveDnsNodeProcessName . '.pid') === true) {
							$recursiveDnsNodeProcessProcessId = file_get_contents('/var/run/named/' . $recursiveDnsNodeProcessName . '.pid');

							if (is_numeric($recursiveDnsNodeProcessProcessId) === true) {
								shell_exec('sudo ' . $this->nodeData['binary_files']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -n1000000000');
								shell_exec('sudo ' . $this->nodeData['binary_files']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -n=1000000000');
								shell_exec('sudo ' . $this->nodeData['binary_files']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -s"unlimited"');
								shell_exec('sudo ' . $this->nodeData['binary_files']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -s=unlimited');
							}
						}
					}
				}
			}

			$this->nodeData['node_processes'] = $nodeProcesses;

			foreach (array(0, 1) as $nodeProcessPartKey) {
				// todo: use cached data set for verification if $nodeProcessPartKey === 0
				// todo: refactor _verifyNodeProcess loop for new nodeData format

				foreach ($this->nodeData['node_process_types'] as $nodeProcessType) {
					foreach ($this->nodeData['node_processes'][$nodeProcessType][$nodeProcessPartKey] as $nodeProcessId => $nodeProcessPortNumber) {
						if ($this->_verifyNodeProcess($nodeProcessPortNumber, $nodeProcessType) === false) {
							unset($this->nodeData['node_processes'][$nodeProcessType][$nodeProcessPartKey][$nodeProcessId]);
						}
					}
				}

				// todo: refactor _processFirewall for new nodeData format
				$this->_processFirewall($nodeProcessPartKey);
				$nodeProcessPartKey = abs($nodeProcessPartKey - 1);

				foreach ($this->nodeData['proxy_node_process_types'] as $proxyNodeProcessTypeServiceName => $proxyNodeProcessType) {
					if (empty($this->nodeData['node_processes'][$proxyNodeProcessType][$nodeProcessPartKey]) === false) {
						foreach ($this->nodeData['node_processes'][$proxyNodeProcessType][$nodeProcessPartKey] as $proxyNodeProcessNodeId => $proxyNodeProcessPortNumbers) {
							$proxyNodeProcessConfiguration = array(
								'a0' => 'maxconn 20000',
								'a1' => 'nobandlimin',	
								'a2' => 'nobandlimout',
								'a3' => 'stacksize 0',
								'a4' => 'flush',
								'a5' => 'allow * * * * HTTP',
								'a6' => 'allow * * * * HTTPS',
								'a7' => 'log /var/log/' . $proxyNodeProcessType . '/' . $proxyNodeProcessNodeId,
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

							foreach ($this->nodeData['node_process_users'][$proxyNodeProcessType][$proxyNodeProcessNodeId] as $proxyNodeProcessUserIds) {
								$proxyNodeProcessConfiguration['c' . $proxyNodeProcessConfigurationIndexes['c']] = 'auth iponly strong';
								$proxyNodeProcessConfigurationIndexes['c']++;

								foreach ($proxyNodeProcessUserIds as $proxyNodeProcessUserId) {
									$proxyNodeProcessConfigurationIndexes['f'] = $proxyNodeProcessConfigurationPartIndexes['f'] = $proxyNodeProcessConfigurationIndexes['g'] = $proxyNodeProcessConfigurationPartIndexes['g'] = 0;
									$proxyNodeProcessUser = $this->nodeData['users'][$proxyNodeProcessUserId];

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
												$proxyNodeProcessUserRequestDestinationParts[$proxyNodeProcessConfigurationIndexes['h']] = $this->nodeData['request_destinations'][$proxyNodeProcessUserDestinationId];
												$proxyNodeProcessConfigurationPartIndexes['h'] = $proxyNodeProcessConfigurationIndexes['h'];
											} else {
												$proxyNodeProcessUserRequestDestinationParts[$proxyNodeProcessUserRequestDestinationPartIndexes['h']] .= ',' . $this->nodeData['request_destinations'][$proxyNodeProcessUserDestinationId];
											}

											$proxyNodeProcessConfigurationIndexes['h']++;
										}

										$proxyNodeProcessUserLogFormat = 'nolog';

										if (empty($proxyNodeProcessUser['status_allowing_request_logs']) === false) {
											$proxyNodeProcessUserLogFormat = 'logformat " %I _ %O _ %Y-%m-%d %H-%M-%S.%. _ %n _ %R _ ' . $proxyNodeProcessUserId . ' _ %E _ %C _ %U"';
										}

										$proxyNodeProcessConfiguration['c' . $proxyNodeProcessConfigurationIndexes['c']] = $proxyNodeProcessUserLogFormat;
										$proxyNodeProcessConfigurationIndexes['c']++;

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

							foreach ($this->nodeData['data']['node_ip_versions'] as $proxyNodeIpVersion) {
								$proxyNodeProcessConfiguration['e' . $proxyNodeProcessConfigurationIndexes['e']] = 'nserver ' . $this->nodeData['node_process_recursive_dns_destinations'][$proxyNodeProcessType][$proxyNodeProcessNodeId]['listening_ip_version_' . $proxyNodeIpVersion] . '[:' . $this->nodeData['node_process_recursive_dns_destinations'][$proxyNodeProcessType][$proxyNodeProcessNodeId]['listening_port_number_version_' . $proxyNodeIpVersion] . ']';
								$proxyNodeProcessConfigurationIndexes['e']++;
								$proxyNodeProcessConfiguration['f'] = $proxyNodeProcessConfiguration['g'] = $proxyNodeProcessTypeServiceName . ' -a ';
								$proxyNodeProcessInterfaceListeningIp = $this->nodeData['nodes'][$proxyNodeProcessNodeId]['external_ip_version_' . $proxyNodeIpVersion];

								if (empty($this->nodeData['nodes'][$proxyNodeProcessNodeId]['internal_ip_version_' . $proxyNodeIpVersion]) === false) {
									$proxyNodeProcessInterfaceListeningIp = $this->nodeData['nodes'][$proxyNodeProcessNodeId]['internal_ip_version_' . $proxyNodeIpVersion];
								}

								$proxyNodeProcessConfiguration['f'] .= ' -e' . $proxyNodeProcessInterfaceListeningIp . ' -i' . $proxyNodeProcessInterfaceListeningIp;
								$proxyNodeProcessConfiguration['g'] .= ' -e' . $this->nodeData['node_reserved_internal_destinations'][$proxyNodeProcessNodeId][$proxyNodeIpVersion] . ' -i' . $this->nodeData['node_reserved_internal_destinations'][$proxyNodeProcessNodeId][$proxyNodeIpVersion];
							}

							ksort($proxyNodeProcessConfiguration);
							$proxyNodeProcessInterfaceConfigurations = array(
								'f' => $proxyNodeProcessConfiguration['f'],
								'g' => $proxyNodeProcessConfiguration['g']
							);

							foreach ($proxyNodeProcessPortNumbers as $proxyNodeProcessId => $proxyNodeProcessPortNumber) {
								// todo: add {dst|src} to _verifyNodeProcessConnections

								while ($this->_verifyNodeProcessConnections($proxyNodeProcessPortNumber) === true) {
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
								shell_exec('sudo ' . $this->nodeData['binary_files']['systemctl'] . ' daemon-reload');
								unlink('/var/run/3proxy/' . $proxyNodeProcessName . '.pid');
								$proxyNodeProcessEnded = false;
								$proxyNodeProcessEndedTime = time();

								while ($proxyNodeProcessEnded === false) {
									$proxyNodeProcessEnded = ($this->_verifyNodeProcess($this->nodeData['node_reserved_internal_destinations'][$proxyNodeProcessNodeId][$proxyNodeIpVersion], $proxyNodeIpVersion, $proxyNodeProcessPortNumber, $proxyNodeProcessType) === false);
									sleep(1);
								}

								$proxyNodeProcessStarted = false;
								$proxyNodeProcessStartedTime = time();

								while ($proxyNodeProcessStarted === false) {
									shell_exec('sudo ' . $this->nodeData['binary_files']['service'] . ' ' . $proxyNodeProcessName . ' start');
									$proxyNodeProcessStarted = ($this->_verifyNodeProcess($this->nodeData['node_reserved_internal_destinations'][$proxyNodeProcessNodeId][$proxyNodeIpVersion], $proxyNodeIpVersion, $proxyNodeProcessPortNumber, $proxyNodeProcessType) === true);
									sleep(1);
								}

								if (file_exists('/var/run/3proxy/' . $proxyNodeProcessName . '.pid') === true) {
									$proxyNodeProcessProcessId = file_get_contents('/var/run/3proxy/' . $proxyNodeProcessName . '.pid');

									if (is_numeric($proxyNodeProcessProcessId) === true) {
										shell_exec('sudo ' . $this->nodeData['binary_files']['prlimit'] . ' -p ' . $proxyNodeProcessProcessId . ' -n1000000000');
										shell_exec('sudo ' . $this->nodeData['binary_files']['prlimit'] . ' -p ' . $proxyNodeProcessProcessId . ' -n=1000000000');
										shell_exec('sudo ' . $this->nodeData['binary_files']['prlimit'] . ' -p ' . $proxyNodeProcessProcessId . ' -s"unlimited"');
										shell_exec('sudo ' . $this->nodeData['binary_files']['prlimit'] . ' -p ' . $proxyNodeProcessProcessId . ' -s=unlimited');
									}
								}
							}
						}
					}
				}
			}

			$this->nodeData['node_processes'] = $nodeProcesses;
			// todo: refactor _verifyNodeProcess loop for new nodeData format

			foreach (array(0, 1) as $nodeProcessPartKey) {
				foreach ($this->nodeData['node_process_types'] as $nodeProcessType) {
					foreach ($this->nodeData['node_processes'][$nodeProcessType][$nodeProcessPartKey] as $nodeProcessId => $nodeProcessPortNumber) {
						if ($this->_verifyNodeProcess($nodeProcessPortNumber, $nodeProcessType) === false) {
							unset($this->nodeData['node_processes'][$nodeProcessType][$nodeProcessPartKey][$nodeProcessId]);
						}
					}
				}
			}

			$this->_processFirewall();
			$nodeRecursiveDnsDestinations = array();

			foreach ($this->nodeData['node_recursive_dns_destinations']['recursive_dns'] as $nodeRecursiveDnsDestination) {
				foreach ($this->nodeData['node_ip_versions'] as $nodeIpVersion) {
					$nodeRecursiveDnsDestinations[] = 'nameserver [' . $nodeRecursiveDnsDestination['listening_ip_version_' . $nodeIpVersion] . ']:' . $nodeRecursiveDnsDestination['port_number_version_' . $nodeIpVersion];
				}
			}

			file_put_contents('/usr/local/ghostcompute/resolv.conf', implode("\n", $nodeRecursiveDnsDestinations));

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

			// todo: cache data with new nodeData format
			file_put_contents('/tmp/node_processes', json_encode($nodeProcesses));
			exec('sudo curl -s --form-string "json={\"action\":\"process\",\"data\":{\"processed\":true}}" ' . $this->parameters['system_url'] . '/endpoint/nodes 2>&1', $response);
			$response = json_decode(current($response), true);
			return $response;
		}

		public function processNodeData() {
			if (empty($this->nodeData) === true) {
				unlink($nodeProcessResponseFile);
				shell_exec('sudo wget -O ' . ($nodeProcessResponseFile = '/tmp/node_process_response') . ' --no-dns-cache --post-data "json={\"action\":\"process\",\"where\":{\"id\":\"' . $this->parameters['id'] . '\"}}" --retry-connrefused --timeout=60 --tries=2 ' . $this->parameters['url'] . '/endpoint/nodes');

				if (file_exists($nodeProcessResponseFile) === false) {
					echo 'Error processing node, please try again.' . "\n";
					exit;
				}

				$nodeProcessResponse = json_decode(file_get_contents($nodeProcessResponseFile), true);

				if (empty($nodeProcessResponse['data']) === false) {
					$this->nodeData = $nodeProcessResponse['data'];
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

						$this->nodeData['binary_files'][$binary['name']] = $binaryFile;
					}

					exec('sudo ' . $this->nodeData['binary_files']['netstat'] . ' -i | grep -v : | grep -v face | grep -v lo | awk \'NR==1{print $1}\' 2>&1', $interfaceName);
					$this->nodeData['interface_name'] = current($interfaceName);

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
							$this->nodeData['ssh_port_numbers'] = $sshPortNumbers;
						}
					}
				}
			}

			return;
		}

	}
?>
