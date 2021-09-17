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
			$proxyNodeConfiguration = array(
				'maxconn 20000',
				'nobandlimin',
				'nobandlimout',
				'process_id' => false,
				'stacksize 0',
				'flush',
				'allow * * * * HTTP',
				'allow * * * * HTTPS',
				'log' => false
			);

			foreach ($this->nodeData['node_recursive_dns_destinations']['recursive_dns'] as $nodeRecursiveDnsDestination) {
				foreach ($this->nodeData['node_ip_versions'] as $nodeIpVersion) {
					$proxyNodeConfiguration[] = 'nserver ' . $nodeRecursiveDnsDestination['listening_ip_version_' . $nodeIpVersion] . '[:' . $nodeRecursiveDnsDestination['port_number_version_' . $nodeIpVersion] . ']';
				}
			}

			$this->nodeData['proxy_node_process_types'] = array(
				'proxy' => 'http_proxy',
				'socks' => 'socks_proxy'
			);

			// todo: only add node-specific ACLs to avoid bloated config file and log each process by node_id
			foreach ($this->nodeData['proxy_node_process_types'] as $proxyNodeProcessTypeServiceName => $proxyNodeProcessType) {
				$proxyNodeConfiguration['log'] = 'log /var/log/' . $proxyNodeProcessType;

				if (empty($this->nodeData['node_processes'][$proxyNodeProcessType]) === false) {
					$proxyNodeIndex = 0;
					$proxyNodeUserAuthentication = array(
						$proxyNodeUserAuthentication['listening_address_' . $proxyNodeIndex] => $proxyNodeProcessTypeServiceName . ' -a '
					);

					foreach ($this->nodeData['data']['node_ip_versions'] as $nodeIpVersion) {
						$proxyNodeUserAuthentication['listening_address_' . $proxyNodeIndex] .= ' -e' . $this->nodeData['private_networking']['reserved_node_ip'][$nodeIpVersion] . ' -i' . $this->nodeData['private_networking']['reserved_node_ip'][$nodeIpVersion];
					}

					$proxyNodeIndex++;
					$proxyNodeUsers = array();

					foreach ($this->nodeData['data']['node_ip_versions'] as $nodeIpVersion) {
						$proxyNodeUserAuthentication['listening_address_' . $proxyNodeIndex] .= ' -e' . $this->nodeData['private_networking']['reserved_node_ip'][$nodeIpVersion] . ' -i' . $this->nodeData['private_networking']['reserved_node_ip'][$nodeIpVersion];
					}

					foreach ($this->nodeData['node_users'][$proxyNodeProcessType] as $proxyNodeId => $proxyNodeUserIds) {
						$proxyNodeUserAuthentication[] = 'auth iponly strong';
						$proxyNodeUserAuthenticationUsernames = $proxyNodeUserAuthenticationWhitelists = array();

						foreach ($proxyNodeUserIds as $proxyNodeUserId) {
							$proxyNodeUser = $this->nodeData['users'][$proxyNodeProcessType][$proxyNodeUserId];
							// todo: add deny ACLs for user request_destination_ids exceeded if user status_allowing_request_destinations_only is false

							if (
								(
									(empty($proxyNodeUser['request_destination_id']) === false) ||
									(empty($proxyNodeUser['status_allowing_request_destinations_only']) === true)
								) &&
								(
									(empty($proxyNodeUser['authentication_username']) === false) ||
									(empty($proxyNodeUser['authentication_whitelist']) === false)
								)
							) {
								$proxyNodeLogFormat = 'nolog';

								if (empty($proxyNodeUser['status_allowing_request_logs']) === false) {
									$proxyNodeLogFormat = 'logformat " %I _ %O _ %Y-%m-%d %H-%M-%S.%. _ %n _ %R _ ' . $proxyNodeId . ' _ ' . $proxyNodeUserId . ' _ %E _ %C _ %U"';
								}

								$proxyNodeUserDestinationParts = array(
									array(
										'*'
									)
								);

								if (empty($proxyNodeUser['status_allowing_request_destinations_only']) === false) {
									$proxyNodeUserDestinations = $proxyNodeUser['request_destination_id'];

									foreach ($proxyNodeUserDestinations as $proxyNodeUserDestinationKey => $proxyNodeUserDestinationId) {
										$proxyNodeUserDestinations[$proxyNodeUserDestinationKey] = $this->nodeData['request_destinations'][$proxyNodeProcessType][$proxyNodeUserDestinationId];
									}

									$proxyNodeUserDestinationParts = array_map(function($proxyNodeUserDestinationPart) {
										return implode(',', $proxyNodeUserDestinationPart);
									}, array_chunk($proxyNodeUserDestinations, 10));
								}

								$proxyNodeUsername = $proxyNodeUser['authentication_username'];

								if (empty($proxyNodeUser['status_requiring_strict_authentication']) === true) {
									if (empty($proxyNodeUsername) === false) {
										foreach ($proxyNodeUserDestinationParts as $proxyNodeUserDestinationPart) {
											$proxyNodeUserAuthenticationUsernames[] = 'allow ' . $proxyNodeUsername . ' * ' . $proxyNodeUserDestinationPart;
											$proxyNodeUserAuthenticationUsernames[] = $proxyNodeLogFormat;
										}
									}

									$proxyNodeUsername = '*';
								}

								if (empty($proxyNodeUser['authentication_whitelist']) === false) {
									$proxyNodeUserAuthenticationWhitelistParts = array_chunk(explode("\n", $proxyNodeUser['authentication_whitelist']), 10);

									foreach ($proxyNodeUserAuthenticationWhitelistParts as $proxyNodeUserAuthenticationWhitelistPart) {
										foreach ($proxyNodeUserDestinationParts as $proxyNodeUserDestinationPart) {
											$proxyNodeUserAuthenticationWhitelists[] = 'allow ' . $proxyNodeUserName . ' ' . implode(',', $proxyNodeUserAuthenticationWhitelistPart) . ' ' . $proxyNodeUserDestinationPart;
											$proxyNodeUserAuthenticationWhitelists[] = $proxyNodeLogFormat;
										}
									}
								}
							}
						}

						$proxyNodeUserAuthentication['listening_address_' . $proxyNodeIndex] = $proxyNodeProcessTypeServiceName . ' -a ';

						foreach ($this->nodeData['data']['node_ip_versions'] as $nodeIpVersion) {
							$proxyNodeProcessInterfaceIp = $this->nodeData['nodes'][$proxyNodeId]['external_ip_version_' . $nodeIpVersion];

							if (empty($proxyNode['internal_ip_version_' . $nodeIpVersion]) === false) {
								$proxyNodeProcessInterfaceIp = $this->nodeData['nodes'][$proxyNodeId]['internal_ip_version_' . $nodeIpVersion];
							}

							$proxyNodeUserAuthentication['listening_address_' . $proxyNodeIndex] .= ' -e' . $proxyNodeProcessInterfaceIp . ' -i' . $proxyNodeProcessInterfaceIp;
							// todo: test per-ACL nserver options
							$proxyNodeUserAuthentication[] = 'nserver ' . $this->nodeData['node_recursive_dns_destinations'][$proxyNodeId]['ip_version_' . $nodeIpVersion] . '[:' . $this->nodeData['node_recursive_dns_destinations'][$proxyNodeId]['port_number_version_' . $nodeIpVersion] . ']';
						}

						$proxyNodeUserAuthentication[] = 'deny *';
						$proxyNodeUserAuthentication[] = 'flush';
						$proxyNodeIndex++;
					}

					$proxyNodeUserAuthentication[] = 'deny *';
					$proxyNodeUserAuthentication[] = 'flush';

					foreach ($this->nodeData['users'][$proxyNodeProcessType] as $proxyNodeId => $proxyNodeUser) {
						$proxyNodeUsers[$proxyNodeUser['authentication_username']] = $proxyNodeUser['authentication_username'] . ':CL:' . $proxyNodeUser['authentication_password'];
					}

					foreach (array_chunk($proxyNodeUsers, 10) as $proxyNodeUserPartKey => $proxyNodeUserParts) {
						$proxyNodeUsers[$proxyNodeUserPartKey] = 'users ' . implode(' ', $proxyNodeUserParts);
					}

					$this->nodeData['proxy_node_configuration'][$proxyNodeType] = array_merge($proxyNodeConfiguration, $proxyNodeUsers, $proxyNodeUserAuthentication, array(
						'deny *'
					));
				}
			}

			$recursiveDnsNodeConfiguration = array(
				// todo: add new ACL with internal node IPs instaed of private network blocks if privateNetworkIpBlocks doesn't work internally
				'acl privateNetworkIpBlocks {',
				$this->nodeData['private_network']['ip_blocks'][4],
				$this->nodeData['private_network']['ip_blocks'][6],
				$this->nodeData['private_network']['reserved_internal_ip'][4],
				$this->nodeData['private_network']['reserved_internal_ip'][6],
				'};',
				'options {',
				'cleaning-interval 1;',
				'directory' => false,
				'dnssec-enable yes;',
				'dnssec-must-be-secure mydomain.local no;',
				'dnssec-validation yes;',
				'empty-zones-enable no;',
				'lame-ttl 0;',
				'max-cache-ttl 1;',
				'max-ncache-ttl 1;',
				'max-zone-ttl 1;',
				'process_id' => false,
				'rate-limit {',
				'exempt-clients {',
				'any;',
				'};',
				'};',
				'resolver-query-timeout 10;',
				'tcp-clients 1000000000;',
				'};'
			);
			// todo: remove bind9 views and log each process by node_id

			if (empty($this->nodeData['node_processes']['node_recursive_dns_destinations']) === false) {
				$recursiveDnsNodeUserAuthentication = array();
				$recursiveDnsNodeIndex = 0;

				foreach ($this->nodeData['node_ip_versions'] as $nodeIpVersion) {
					$recursiveDnsNodeUserAuthentication['listening_address_version_' . $nodeIpVersion . '_' . $$recursiveDnsNodeIndex] = $this->nodeData['private_networking']['reserved_node_ip'][$nodeIpVersion];
				}

				$recursiveDnsNodeIndex++;

				foreach ($this->nodeData['node_recursive_dns_destinations'] as $nodeProcessType => $nodeRecursiveDnsDestination) {
					if (
						(empty($nodeRecursiveDnsDestination['source_ip_version_4']) === false) ||
						(empty($nodeRecursiveDnsDestination['source_ip_version_6']) === false)
					) {
						$recursiveDnsNodeUserAuthentication[] = 'view _' . $nodeRecursiveDnsDestination['id'] . ' {';
						$recursiveDnsNodeUserAuthentication[] = 'match-clients {';
						$recursiveDnsNodeUserAuthentication[] = 'privateNetworkIpBlocks;';
						$recursiveDnsNodeUserAuthentication[] = '};';

						if (empty($nodeRecursiveDnsDestination['listening_ip_version_4']) === false) {
							$recursiveDnsNodeUserAuthentication[] = 'listen-on {';
							$recursiveDnsNodeUserAuthentication['listening_address_version_4_' . $recursiveDnsNodeIndex] = $nodeRecursiveDnsDestination['listening_ip_version_4'];
							$recursiveDnsNodeUserAuthentication[] = '};';
							$recursiveDnsNodeUserAuthentication[] = 'query-source address ' . $nodeRecursiveDnsDestination['source_ip_version_4'] . ';';
						}

						if (empty($nodeRecursiveDnsDestination['listening_ip_version_6']) === false) {
							$recursiveDnsNodeUserAuthentication[] = 'listen-on-v6 {';
							$recursiveDnsNodeUserAuthentication['listening_address_version_6_' . $recursiveDnsNodeIndex] = $nodeRecursiveDnsDestination['listening_ip_version_6'];
							$recursiveDnsNodeUserAuthentication[] = '};';
							$recursiveDnsNodeUserAuthentication[] = 'query-source-v6 address ' . $nodeRecursiveDnsDestination['source_ip_version_6'] . ';';
						}

						$recursiveDnsNodeIndex++;
					}
				}

				foreach ($this->nodeData['node_users']['recursive_dns'] as $recursiveDnsNodeId => $recursiveDnsNodeUserIds) {
					foreach ($recursiveDnsNodeUserIds as $recursiveDnsNodeUserId) {
						$recursiveDnsNodeUserAuthentication[] = 'view ' . $recursiveDnsNodeId . '_' . $recursiveDnsNodeUserId . ' {';
						$recursiveDnsNodeUserAuthentication[] = 'match-clients {';

						if (empty($this->nodeData['users']['recursive_dns'][$recursiveDnsNodeUserId]) === true) {
							$recursiveDnsNodeUserAuthentication[] = 'any;';
						} elseif (empty($this->nodeData['users'][$recursiveDnsNodeProcessType][$recursiveDnsNodeUserId]['whitelist']) === false) {
							$recursiveDnsNodeUserAuthenticationWhitelists = explode("\n", $this->nodeData['users']['recursive_dns'][$recursiveDnsNodeUserId]['authentication_whitelist']);

							foreach ($recursiveDnsNodeUserAuthenticationWhitelists as $recursiveDnsNodeUserAuthenticationWhitelist) {
								$recursiveDnsNodeUserAuthentication[] = $recursiveDnsNodeUserAuthenticationWhitelist . ';';
							}
						}

						$recursiveDnsNodeUserAuthentication[] = '};';
						$recursiveDnsNodeUserAuthentication[] = 'options {';
						$recursiveDnsNodeUserAuthentication[] = 'allow-query {';
						$recursiveDnsNodeUserAuthentication[] = 'privateNetworkIpBlocks;';
						$recursiveDnsNodeUserAuthentication[] = '};';

						foreach ($this->nodeData['node_ip_versions'] as $nodeIpVersion) {
							$recursiveDnsNodeIps = array_filter(array(
								$this->nodeData['nodes'][$recursiveDnsNodeId]['internal_ip_version_' . $nodeIpVersion],
								$this->nodeData['nodes'][$recursiveDnsNodeId]['external_ip_version_' . $nodeIpVersion]
							));
							$recursiveDnsNodeIp = current($recursiveDnsNodeIps);
							$recursiveDnsNodeListeningIpOption = 'listen-on';
							$recursiveDnsNodeSourceIpOption = 'query-source';

							if ($nodeIpVersion === 6) {
								$recursiveDnsNodeListeningIpOption .= '-v6';
								$recursiveDnsNodeSourceIpOption .= '-v6';
							}

							$recursiveDnsNodeUserAuthentication[] = $recursiveDnsNodeSourceIpOption . ' address ' . $recursiveDnsNodeIp . ';';
							$recursiveDnsNodeUserAuthentication[] = $recursiveDnsNodeListeningIpOption . ' {';
							$recursiveDnsNodeUserAuthentication['listening_address_version_' . $nodeIpVersion . '_' . $recursiveDnsNodeIndex] = $recursiveDnsNodeIp;
							$recursiveDnsNodeUserAuthentication[] = '};';
						}

						if (empty($recursiveDnsNodeId) === false) {
							// todo: verify multiple DNS processes can write to the same logfile
							$recursiveDnsNodeUserAuthentication[] = 'logging {';
							$recursiveDnsNodeUserAuthentication[] = 'channel ' . $recursiveDnsNodeId . ' {';
							$recursiveDnsNodeUserAuthentication[] = 'file "/var/log/named/' . $recursiveDnsNodeId . '" versions unlimited size 10m increment';
							$recursiveDnsNodeUserAuthentication[] = '}';
							$recursiveDnsNodeUserAuthentication[] = 'print-time yes';
							$recursiveDnsNodeUserAuthentication[] = '}';
						}

						$recursiveDnsNodeConfiguration[] = $recursiveDnsNodeUserAuthentication[] = '};';
						$recursiveDnsNodeIndex++;
					}
				}

				$this->nodeData['recursive_dns_node_configuration'] = array_merge($recursiveDnsNodeConfiguration, $recursiveDnsNodeUserAuthentication);
			}

			$this->nodeData['node_process_types'] = array_merge($this->nodeData['proxy_node_process_types'], array('recursive_dns'));

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

				foreach ($this->nodeData['node_processes']['recursive_dns'][$nodeProcessPartKey] as $recursiveDnsNodeProcessId => $recursiveDnsNodeProcessPortNumber) {
					while ($this->_verifyNodeProcessConnections($recursiveDnsNodeProcessPortNumber) === true) {
						sleep(1);
					}

					$recursiveDnsNodeProcessName = 'recursive_dns_' . $recursiveDnsNodeProcessId;

					if (file_exists('/etc/bind_' . $recursiveDnsNodeProcessName . '/named.conf') === true) {
						$recursiveDnsNodeProcessProcessIds = $this->fetchProcessIds($recursiveDnsNodeProcessName . ' ', '_' . $recursiveDnsNodeProcessName . '/');

						if (empty($recursiveDnsNodeProcessProcessIds) === false) {
							$this->_killProcessIds($recursiveDnsNodeProcessProcessIds);
						}
					}

					$recursiveDnsNodeProcessConfigurationOptions = $this->nodeData['recursive_dns_node_configuration'];
					$recursiveDnsNodeProcessConfigurationOptions['directory'] = '"/var/cache/bind_' . $recursiveDnsNodeProcessId . '";';
					$recursiveDnsNodeProcessConfigurationOptions['process_id'] = 'pid-file "/var/run/named/' . $recursiveDnsNodeProcessName . '.pid";';
					// todo: delete named_ + bind_ prefixes when possible since processes are prefixed with recursive_dns_

					foreach ($this->nodeData['node_ip_versions'] as $nodeIpVersion) {
						$recursiveDnsNodeIndex = 0;

						while (isset($recursiveDnsNodeProcessConfigurationOptions['listening_address_version_4_' . $recursiveDnsNodeIndex]) === true) {
							$recursiveDnsNodeProcessConfigurationOptions['listening_address_version_' . $nodeIpVersion . '_' . $recursiveDnsNodeIndex] .= ':' . $recursiveDnsNodeProcessPortNumber;
							$recursiveDnsNodeIndex++;
						}
					}

					shell_exec('cd /usr/sbin && sudo ln /usr/sbin/named named_' . $recursiveDnsNodeProcessName);
					$recursiveDnsNodeProcessService = array(
						'[Service]',
						'ExecStart=/usr/sbin/named_' . $recursiveDnsNodeProcessName . ' -f -c /etc/bind_' . $recursiveDnsNodeProcessName . '/named.conf -S 40000 -u root'
					);
					$recursiveDnsNodeProcessServiceName = $recursiveDnsNodeProcessDefaultServiceName . '_' . $recursiveDnsNodeProcessName;
					file_put_contents('/lib/systemd/system/' . $recursiveDnsNodeProcessServiceName . '.service', implode("\n", $recursiveDnsNodeProcessService));

					if (file_exists('/etc/default/' . $recursiveDnsNodeProcessServiceName) === false) {
						copy('/etc/default/' . $recursiveDnsNodeProcessDefaultServiceName, '/etc/default/' . $recursiveDnsNodeProcessServiceName);
					}

					if (file_exists('/etc/bind_' . $recursiveDnsNodeProcessName) === false) {
						shell_exec('sudo cp -r /etc/bind /etc/bind_' . $recursiveDnsNodeProcessName);
						$recursiveDnsNodeProcessConfiguration = array(
							'include "/etc/bind_' . $recursiveDnsNodeProcessName . '/named.conf.options";',
							'include "/etc/bind_' . $recursiveDnsNodeProcessName . '/named.conf.local";',
							'include "/etc/bind_' . $recursiveDnsNodeProcessName . '/named.conf.default-zones";'
						);
						file_put_contents('/etc/bind_' . $recursiveDnsNodeProcessName . '/named.conf', implode("\n", $recursiveDnsNodeProcessConfiguration));
					}

					$recursiveDnsNodeProcessConfigurationOptions = array_filter($recursiveDnsNodeProcessConfigurationOptions);
					file_put_contents('/etc/bind_' . $recursiveDnsNodeProcessName . '/named.conf.options', implode("\n", $recursiveDnsNodeProcessConfigurationOptions));

					if (is_dir('/var/cache/bind_' . $recursiveDnsNodeProcessName) === false) {
						mkdir('/var/cache/bind_' . $recursiveDnsNodeProcessName);
					}

					shell_exec('sudo ' . $this->nodeData['binary_files']['systemctl'] . ' daemon-reload');
					unlink('/var/run/named/' . $recursiveDnsNodeProcessName . '.pid');
					$recursiveDnsNodeProcessEnded = false;
					$recursiveDnsNodeProcessEndedTime = time();

					while ($recursiveDnsNodeProcessEnded === false) {
						$recursiveDnsNodeProcessEnded = ($this->_verifyNodeProcess($recursiveDnsNodeProcessPortNumber, 'recursive_dns') === false);
						sleep(1);
					}

					$recursiveDnsNodeProcessStarted = false;
					$recursiveDnsNodeProcessStartedTime = time();

					while ($recursiveDnsNodeProcessStarted === false) {
						shell_exec('sudo ' . $this->nodeData['binary_files']['service'] . ' ' . $recursiveDnsNodeProcessServiceName . ' start');
						$recursiveDnsNodeProcessStarted = ($this->_verifyNodeProcess($recursiveDnsNodeProcessPortNumber, 'recursive_dns') === true);
						sleep(2);
					}

					if (file_exists('/var/run/named/' . $recursiveDnsNodeProcess['id'] . '.pid') === true) {
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

			$this->nodeData['node_processes'] = $nodeProcesses;

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

				foreach ($this->nodeData['proxy_node_process_types'] as $proxyNodeProcessTypeServiceName => $proxyNodeProcessType) {
					foreach ($this->nodeData['node_processes'][$proxyNodeProcessType][$nodeProcessPartKey] as $proxyNodeProcessId => $proxyNodeProcessPortNumber) {
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

						$proxyNodeIndex = 0;
						$proxyNodeProcessConfiguration = $this->nodeData['proxy_node_configuration'][$proxyNodeProcessType];

						while (isset($proxyNodeProcessConfigurationOptions['listening_address_' . $proxyNodeIndex]) === true) {
							$proxyNodeProcessConfiguration['listening_address_' . $proxyNodeIndex] .= ' -p' . $proxyNodeProcessPortNumber;
							$proxyNodeIndex++;
						}

						$proxyNodeProcessConfiguration['process_id'] = 'pidfile /var/run/3proxy/' . $proxyNodeProcessName . '.pid';
						shell_exec('cd /bin && sudo ln /bin/3proxy ' . $proxyNodeProcessName);
						$systemdServiceContents = array(
							'[Service]',
							'ExecStart=/bin/' . $proxyNodeProcessName . ' ' . ($proxyNodeProcessConfigurationPath = '/etc/3proxy/' . $proxyNodeProcessName . '.cfg')
						);
						file_put_contents('/etc/systemd/system/' . $proxyNodeProcessName . '.service', implode("\n", $systemdServiceContents));
						file_put_contents($proxyNodeProcessConfigurationPath, implode("\n", $proxyNodeProcessConfiguration));
						chmod($proxyNodeProcessConfigurationPath, 0755);
						shell_exec('sudo ' . $this->nodeData['binary_files']['systemctl'] . ' daemon-reload');
						unlink('/var/run/3proxy/' . $proxyNodeProcessName . '.pid');
						$proxyNodeProcessEnded = false;
						$proxyNodeProcessEndedTime = time();

						while ($proxyNodeProcessEnded === false) {
							$proxyNodeProcessEnded = ($this->_verifyNodeProcess($proxyNodeProcessPortNumber, $proxyNodeProcessType) === false);
							sleep(1);
						}

						$proxyNodeProcessStarted = false;
						$proxyNodeProcessStartedTime = time();

						while ($proxyNodeProcessStarted === false) {
							shell_exec('sudo ' . $this->nodeData['binary_files']['service'] . ' ' . $proxyNodeProcessName . ' start');
							$proxyNodeProcessStarted = ($this->_verifyNodeProcess($proxyNodeProcessPortNumber, $proxyNodeProcessType) === true);
							sleep(2);
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

			$this->nodeData['node_processes'] = $nodeProcesses;

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
