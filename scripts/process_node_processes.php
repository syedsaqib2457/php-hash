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
			$firewallBinaryFiles = array(
				4 => $this->nodeData['binary_files']['iptables-restore'],
				6 => $this->nodeData['binary_files']['ip6tables-restore']
			);
			$nodeProcessPartKeys = array(
				0,
				1
			);

			if (empty($nodeProcessPartKey) === false) {
				$nodeProcessPartKeys = array_intersect($nodeProcessPartKeys, array($nodeProcessPartKey));
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

				foreach ($nodeProcessPartKeys as $nodeProcessPartKey) {
					foreach ($this->nodeData['node_process_types'] as $nodeProcessType) {
						krsort($this->nodeData['node_processes'][$nodeProcessType][$nodeProcessPartKey]);
						$nodeProcessParts = array_chunk($this->nodeData['node_processes'][$nodeProcessType][$nodeProcessPartKey], 10);

						foreach ($nodeProcessParts as $nodeProcessPart) {
							foreach ($this->nodeData['node_processes'][$nodeProcessType][$nodeProcessPartKey] as $nodeProcessKey => $nodeProcess) {
								$nodeProcessLoadBalancer = '';

								if ($nodeProcessKey > 0) {
									$nodeProcessLoadBalancer = '-m statistic --mode nth --every ' . ($nodeProcessKey + 1) . ' --packet 0 ';
								}

								$nodeProcessTransportProtocols = array(
									'tcp',
									'udp'
								);

								if ($nodeProcessType === 'http_proxy') {
									unset($nodeProcessTransportProtocols[1]);
								}

								foreach ($nodeProcessTransportProtocols as $nodeProcessTransportProtocol) {
									$firewallRules[] = '-A PREROUTING -p ' . $nodeProcessTransportProtocol . ' -m multiport ! -d ' . $this->nodeData['private_networking']['reserved_node_ip'][$nodeIpVersion] . ' --dports ' . implode(',', $nodeProcessPart) . ' ' . $nodeProcessLoadBalancer . ' -j DNAT --to-destination :' . $nodeProcess['port_number'] . ' --persistent';
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

		protected function _verifyNodeProcess($nodeProcess) {
			$response = false;

			switch ($nodeProcess['type']) {
				case 'http_proxy':
				case 'socks_proxy':
					$parameters = array(
						'http_proxy' => '-x',
						'socks_proxy' => '--socks5-hostname'
					);
					exec('curl ' . $parameters[$nodeProcess['type']] . ' ' . $this->nodeData['private_network']['reserved_internal_ip'][4] . ':' . $nodeProcess['port_number'] . ' http://ghostcompute' . uniqid() . time() . ' -v --connect-timeout 1 --max-time | grep " refused" 1 2>&1', $proxyNodeProcessResponse);
					$response = (empty($proxyNodeProcessResponse) === true);
					break;
				case 'recursive_dns':
					exec('dig +time=1 +tries=1 ghostcompute @' . $this->nodeData['private_network']['reserved_internal_ip'][4] . ' -p ' . $nodeProcess['port_number'] . ' | grep "Got answer" 2>&1', $recursiveDnsNodeProcessResponse);
					$response = (empty($recursiveDnsNodeProcessResponse) === false);
					break;
			}

			return $response;
		}

		public function fetchProcessIds($processName, $processFile = false) {
			$processIds = array();
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
						$processIds[] = $processColumns[key($processColumns)];
					}
				}
			}

			return $processIds;
		}

		public function process() {
			$nodeProcesses = json_decode($nodeProcesses, file_get_contents('/tmp/node_processes'));

			if (empty($this->nodeData['nodes']) === true) {
				if (empty(nodeProcesses) === false) {
					foreach ($nodeProcesses as $nodeProcessType => $nodeProcessPortNumbers) {
						foreach ($nodeProcessPortNumbers as $nodeProcessPortNumber) {
							$nodeProcess = array(
								'port_number' => $nodeProcessPortNumber,
								'type' => $nodeProcessType
							);

							if ($this->verifyNodeProcess($nodeProcess) === false) {
								exec('sudo curl -s --form-string "json={\"action\":\"process\",\"data\":{\"processed\":false}}" ' . $this->parameters['system_url'] . '/endpoint/nodes 2>&1', $response);
								exit;
							}
						}
					}
				}

				// todo: log node processing errors if processes won't start after X seconds, processing time per request, timeouts, number of logs processed for each request, etc
				return;
			}

			// todo: make sure options are set to primary interface and lo instead of just default

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
				'net.netfilter.nf_conntrack_max = 100000000',
				'net.netfilter.nf_conntrack_tcp_loose = 0',
				'net.netfilter.nf_conntrack_tcp_timeout_close = 10',
				'net.netfilter.nf_conntrack_tcp_timeout_close_wait = 10',
				'net.netfilter.nf_conntrack_tcp_timeout_established = 10',
				'net.netfilter.nf_conntrack_tcp_timeout_fin_wait = 10',
				'net.netfilter.nf_conntrack_tcp_timeout_last_ack = 10',
				'net.netfilter.nf_conntrack_tcp_timeout_syn_recv = 10',
				'net.netfilter.nf_conntrack_tcp_timeout_syn_sent = 10',
				'net.netfilter.nf_conntrack_tcp_timeout_time_wait = 10',
				'net.nf_conntrack_max = 100000000',
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
				$interfaceNodeIpsToProcess['add'][] = $this->nodeData['private_networking']['reserved_node_ip'][$nodeIpVersion];

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
			$this->nodeData['proxy_node_process_types'] = array(
				'proxy' => 'http_proxy',
				'socks' => 'socks_proxy'
			);

			foreach ($this->nodeData['proxy_node_process_types'] as $proxyNodeProcessTypeServiceName => $proxyNodeProcessType) {
				if (empty($this->nodeData['node_processes'][$proxyNodeProcessType]) === false) {
					$proxyNodeConfiguration['log'] = 'log /var/log/' . $proxyNodeProcessType;
					$proxyNodeIndex = 0;
					$proxyNodeUserAuthentication = $proxyNodeUsers = array();

					foreach ($this->nodeData['node_users'][$proxyNodeProcessType] as $proxyNodeId => $proxyNodeUserIds) {
						$proxyNodeUserAuthentication[] = 'auth iponly strong';
						$proxyNodeUserAuthenticationUsernames = $proxyNodeUserAuthenticationWhitelists = array();

						foreach ($proxyNodeUserIds as $proxyNodeUserId) {
							$proxyNodeUser = $this->nodeData['users'][$proxyNodeProcessType][$proxyNodeUserId];

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

					$proxyNodeUserAuthentication['internal_reserved_listening_address'] = $proxyNodeProcessTypeServiceName . ' -a ';

					foreach ($this->nodeData['data']['node_ip_versions'] as $nodeIpVersion) {
						$proxyNodeUserAuthentication['internal_reserved_listening_address'] .= ' -e' . $this->nodeData['private_networking']['reserved_node_ip'][$nodeIpVersion] . ' -i' . $this->nodeData['private_networking']['reserved_node_ip'][$nodeIpVersion];
						$proxyNodeUserAuthentication[] = 'nserver ' . $this->nodeData['node_recursive_dns_destinations'][$proxyNodeId]['ip_version_' . $nodeIpVersion] . '[:' . $this->nodeData['node_recursive_dns_destinations'][$proxyNodeId]['port_number_version_' . $nodeIpVersion] . ']';
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

			if (empty($this->nodeData['node_processes']['recursive_dns']) === false) {
				$recursiveDnsNodeIndex = 0;
				$recursiveDnsNodeUserAuthentication = array();
				// todo: allow different DNS source IPs to be set for system and individual nodes

				if (empty($this->nodeData['node_recursive_dns_destinations']) === false) {
					foreach ($this->nodeData['node_recursive_dns_destinations'] as $nodeIpVersion => $nodeRecursiveDnsDestination) {
						if (in_array($nodeRecursiveDnsDestination['ip'], $this->nodeData['node_ips'][$nodeIpVersion]) === true) {
							$recursiveDnsNodeUserAuthentication[] = 'view _' . $nodeIpVersion . ' {';
							$recursiveDnsNodeUserAuthentication[] = 'match-clients {';
							$recursiveDnsNodeUserAuthentication[] = 'privateNetworkIpBlocks;';
							$recursiveDnsNodeUserAuthentication[] = '};';
							$recursiveDnsNodeListeningIpOption = 'listen-on';
							$recursiveDnsNodeSourceIpOption = 'query-source';

							if ($nodeIpVersion === 6) {
								$recursiveDnsNodeListeningIpOption .= '-v6';
								$recursiveDnsNodeSourceIpOption .= '-v6';
							}

							$recursiveDnsNodeUserAuthentication[] = $recursiveDnsNodeSourceIpOption . ' address ' . $nodeRecursiveDnsDestination['ip'] . ';';
							$recursiveDnsNodeUserAuthentication[] = $recursiveDnsNodeListeningIpOption . ' {';
							$recursiveDnsNodeUserAuthentication['node_process_listening_address_version_' . $nodeIpVersion] = $nodeRecursiveDnsDestination['ip'];
							$recursiveDnsNodeUserAuthentication[] = '};';
						}
					}
				}

				foreach ($this->nodeData['node_users']['recursive_dns'] as $recursiveDnsNodeId => $recursiveDnsNodeUserIds) {
					// todo: add $this->nodeData['node_users']['recursive_dns'] with $recursiveDnsNodeId 0 and whitelist containing privateNetworkIpBlocks ACL string

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
						} else {
							$recursiveDnsNodeUserAuthentication[] = 'none;';
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
							$recursiveDnsNodeUserAuthentication['internal_reserved_listening_address_version_' . $nodeIpVersion] = $this->nodeData['private_networking']['reserved_node_ip'][$nodeIpVersion];
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
			$nodeProcessesToRemove = array();

			foreach ($nodeProcesses as $nodeProcessType => $nodeProcessPorts) {
				foreach ($nodeProcessPorts as $nodeProcessId => $nodeProcessPort) {
					if (
						(empty($this->nodeData['node_processes'][$nodeProcessType][0]) === true) &&
						(empty($this->nodeData['node_processes'][$nodeProcessType][1]) === true)
					) {
						$nodeProcessesToRemove[$nodeProcessType][$nodeProcessId] = $nodeProcessId;
					}
				}
			}

			if (empty($recursiveDnsNodeProcessDefaultServiceName) === true) {
				$recursiveDnsNodeProcessDefaultServiceName = 'named';

				if (is_dir('/etc/default/bind9') === true) {
					$recursiveDnsNodeProcessDefaultServiceName = 'bind9';
				}
			}

			foreach (array(0, 1) as $nodeProcessPartKey) {
				foreach ($this->nodeData['node_process_types'] as $nodeProcessType) {
					foreach ($this->nodeData['node_processes'][$nodeProcessType][$nodeProcessPartKey] as $nodeProcessKey => $nodeProcess) {
						if ($this->verifyNodeProcess($nodeProcess) === true) {
							$nodeProcesses[$nodeProcessType][$nodeProcess['id']] = $nodeProcess['port_number'];
						} else {
							unset($nodeProcesses[$nodeProcessType][$nodeProcess['id']]);
							unset($this->nodeData['node_processes'][$nodeProcessType][$nodeProcessPartKey][$nodeProcessKey]);
						}
					}
				}

				$this->_processFirewall($nodeProcessPartKey);
				$nodeProcessPartKey = intval((empty($nodeProcessPartKey) === true));
				// todo: verify no active sockets for processes using $nodeProcessPartKey after applying firewall

				foreach ($this->nodeData['proxy_node_process_types'] as $proxyNodeProcessTypeServiceName => $proxyNodeProcessType) {
					foreach ($this->nodeData['node_processes'][$proxyNodeProcessType][$nodeProcessPartKey] as $proxyNodeProcessKey => $proxyNodeProcess) {
						$proxyNodeProcessName = $proxyNodeProcessType . '_proxy_' . $proxyNodeProcess['id'];

						if (file_exists('/etc/3proxy/' . $proxyNodeProcessName . '.cfg') === true) {
							$proxyNodeProcessProcessIds = $this->fetchProcessIds($proxyNodeProcessName . ' ', '/etc/3proxy/' . $proxyNodeProcessName . '.cfg');

							if (empty($proxyNodeProcessProcessIds) === false) {
								$this->_killProcessIds($proxyNodeProcessProcessIds);
							}
						}

						$proxyNodeIndex = 0;
						$proxyNodeProcessConfiguration = $this->nodeData['proxy_node_configuration'][$proxyNodeProcess['type']];
						$proxyNodeProcessConfiguration['internal_reserved_listening_address'] .= ':' . $proxyNodeProcess['port_number'];

						while (isset($proxyNodeProcessConfigurationOptions['listening_address_' . $proxyNodeIndex]) === true) {
							$proxyNodeProcessConfiguration['listening_address_' . $proxyNodeIndex] .= ' -p' . $proxyNodeProcess['port_number'];
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
							$proxyNodeProcessEnded = ($this->_verifyNodeProcess($proxyNodeProcess) === false);
							sleep(1);
						}

						$proxyNodeProcessStarted = false;
						$proxyNodeProcessStartedTime = time();

						while ($proxyNodeProcessStarted === false) {
							shell_exec('sudo ' . $this->nodeData['binary_files']['service'] . ' ' . $proxyNodeProcessName . ' start');
							$proxyNodeProcessStarted = ($this->_verifyNodeProcess($proxyNodeProcess) === true);
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

				foreach ($this->nodeData['node_processes']['recursive_dns'][$nodeProcessPartKey] as $recursiveDnsNodeProcessKey => $recursiveDnsNodeProcess) {
					$recursiveDnsNodeProcessName = $recursiveDnsNodeProcessType . '_' . $recursiveDnsNodeProcess['id'];

					if (file_exists('/etc/bind_' . $recursiveDnsNodeProcessName . '/named.conf') === true) {
						$recursiveDnsNodeProcessProcessIds = $this->fetchProcessIds($recursiveDnsNodeProcessName . ' ', '_' . $recursiveDnsNodeProcessName . '/');

						if (empty($recursiveDnsNodeProcessProcessIds) === false) {
							$this->_killProcessIds($recursiveDnsNodeProcessProcessIds);
						}
					}

					$recursiveDnsNodeProcessConfigurationOptions = $this->nodeData['recursive_dns_node_configuration'][$recursiveDnsNodeProcess['type']];
					$recursiveDnsNodeProcessConfigurationOptions['directory'] = '"/var/cache/bind_' . $recursiveDnsNodeProcess['id'] . '";';
					$recursiveDnsNodeProcessConfigurationOptions['process_id'] = 'pid-file "/var/run/named/named_' . $recursiveDnsNodeProcess['id'] . '.pid";';

					foreach ($this->nodeData['node_ip_versions'] as $nodeIpVersion) {
						$recursiveDnsNodeIndex = 0;
						$recursiveDnsNodeProcessConfigurationOptions['internal_reserved_listening_address_version_' . $nodeIpVersion] .= ':' . $recursiveDnsNodeProcess['port_number'];

						if (empty($recursiveDnsNodeProcessConfigurationOptions['node_process_listening_address_version_' . $nodeIpVersion]) === false) {
							$recursiveDnsNodeProcessConfigurationOptions['node_process_listening_address_version_' . $nodeIpVersion] .= ':' . $recursiveDnsNodeProcess['port_number'];
						}

						while (isset($recursiveDnsNodeProcessConfigurationOptions['listening_address_version_4_' . $recursiveDnsNodeIndex]) === true) {
							$recursiveDnsNodeProcessConfigurationOptions['listening_address_version_' . $nodeIpVersion . '_' . $recursiveDnsNodeIndex] .= ':' . $recursiveDnsNodeProcess['port_number'];
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
					unlink('/var/run/named/named_' . $recursiveDnsNodeProcessName . '.pid');
					$recursiveDnsNodeProcessEnded = false;
					$recursiveDnsNodeProcessEndedTime = time();

					while ($recursiveDnsNodeProcessEnded === false) {
						$recursiveDnsNodeProcessEnded = ($this->_verifyNodeProcess($recursiveDnsNodeProcess) === false);
						sleep(1);
					}

					$recursiveDnsNodeProcessStarted = false;
					$recursiveDnsNodeProcessStartedTime = time();

					while ($recursiveDnsNodeProcessStarted === false) {
						shell_exec('sudo ' . $this->nodeData['binary_files']['service'] . ' ' . $recursiveDnsNodeProcessServiceName . ' start');
						$recursiveDnsNodeProcessStarted = ($this->_verifyNodeProcess($recursiveDnsNodeProcess) === true);
						sleep(2);
					}

					if (file_exists('/var/run/named/named_' . $recursiveDnsNodeProcess['id'] . '.pid') === true) {
						$recursiveDnsNodeProcessProcessId = file_get_contents('/var/run/named/named_' . $recursiveDnsNodeProcess['id'] . '.pid');

						if (is_numeric($recursiveDnsNodeProcessProcessId) === true) {
							shell_exec('sudo ' . $this->nodeData['binary_files']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -n1000000000');
							shell_exec('sudo ' . $this->nodeData['binary_files']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -n=1000000000');
							shell_exec('sudo ' . $this->nodeData['binary_files']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -s"unlimited"');
							shell_exec('sudo ' . $this->nodeData['binary_files']['prlimit'] . ' -p ' . $recursiveDnsNodeProcessProcessId . ' -s=unlimited');
						}
					}
				}
			}

			$this->_processFirewall();
			$nodeRecursiveDnsDestinations = array();

			foreach ($this->nodeData['node_recursive_dns_destinations'] as $nodeRecursiveDnsDestination) {
				$nodeRecursiveDnsDestinations[] = 'nameserver [' . $nodeRecursiveDnsDestination['ip'] . ']:' . $nodeRecursiveDnsDestination['port_number'];
			}

			file_put_contents('/tmp/node_processes', json_encode($nodeProcesses));
			file_put_contents('/etc/node_recursive_dns_destinations.conf', implode("\n", $nodeRecursiveDnsDestinations));

			foreach ($nodeProcessesToRemove as $nodeProcessType => $nodeProcessId) {
				$nodeProcessName = $nodeProcessType . '_' . $nodeProcessId;

				switch ($nodeProcessType) {
					case 'http_proxy':
					case 'socks_proxy':
						if (file_exists('/var/run/3proxy/' . $nodeProcessName . '.pid') === true) {
							$nodeProcessProcessId = file_get_contents('/var/run/3proxy/' . $nodeProcessName . '.pid');
						}

						unlink('/bin/' . $nodeProcessName);
						unlink('/etc/3proxy/' . $nodeProcessName . '.cfg');
						unlink('/etc/systemd/system/' . $nodeProcessName . '.service');
						unlink('/var/run/3proxy/' . $nodeProcessName . '.pid');
						break;
					case 'recursive_dns':
						if (file_exists('/var/run/named/named_' . $nodeProcess['id'] . '.pid') === true) {
							$nodeProcessProcessId = file_get_contents('/var/run/named/named_' . $nodeProcess['id'] . '.pid');
						}

						rmdir('/etc/bind_' . $nodeProcessName);
						rmdir('/var/cache/bind_' . $nodeProcessName);
						unlink('/etc/default/' . $recursiveDnsNodeProcessDefaultServiceName . '_' . $nodeProcessName);
						unlink('/lib/systemd/system/' . $recursiveDnsNodeProcessDefaultServiceName . '_' . $nodeProcessName . '.service');
						unlink('/usr/sbin/named_' . $nodeProcessName);
						unlink('/var/run/named/named_' . $nodeProcessName . '.pid');
						break;
				}

				if (
					(empty($nodeProcessProcessId) === false) &&
					(is_numeric($nodeProcessProcessId) === true)
				) {
					$this->_killProcessIds(array($nodeProcessProcessId));
				}
			}

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
