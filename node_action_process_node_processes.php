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
			'4' => $parameters['binary_files']['iptables-restore'],
			'6' => $parameters['binary_files']['ip6tables-restore']
		);
		$nodeProcessPartKeys = array(
			0,
			1
		);

		if (
			(isset($parameters['node_process_part_key']) === true) &&
			(in_array($parameters['node_process_part_key'], $nodeProcessPartKeys) === true)
		) {
			$nodeProcessPartKeys = array(
				$parameters['node_process_part_key']
			);
		}

		foreach ($nodeProcessPartKeys as $nodeProcessPartKey) {
			$parameters['node_process_part_key'] = $nodeProcessPartKey;
			$parameters = _processFirewallRuleSets($parameters);
		}

		foreach ($parameters['data']['next']['node_ip_address_version_numbers'] as $nodeIpAddressVersionNetworkMask => $nodeIpAddressVersionNumber) {
			$firewallRules = array(
				'*filter',
				':INPUT ACCEPT [0:0]',
				':FORWARD ACCEPT [0:0]',
				':OUTPUT ACCEPT [0:0]',
				'-A INPUT -p icmp -m hashlimit --hashlimit-above 1/second --hashlimit-burst 2 --hashlimit-htable-gcinterval 100000 --hashlimit-htable-expire 10000 --hashlimit-mode srcip --hashlimit-name icmp --hashlimit-srcmask ' . $nodeIpAddressVersionNetworkMask . ' -j DROP'
			);

			foreach ($parameters['data']['next']['node_ssh_port_numbers'] as $nodeSshPortNumber) {
				$firewallRules[] = '-A INPUT -p tcp --dport ' . $nodeSshPortNumber . ' -m hashlimit --hashlimit-above 1/minute --hashlimit-burst 10 --hashlimit-htable-gcinterval 600000 --hashlimit-htable-expire 60000 --hashlimit-mode srcip --hashlimit-name ssh --hashlimit-srcmask ' . $nodeIpAddressVersionNetworkMask . ' -j DROP';
			}

			$firewallRules[] = 'COMMIT';
			$firewallRules[] = '*nat';
			$firewallRules[] = ':PREROUTING ACCEPT [0:0]';
			$firewallRules[] = ':INPUT ACCEPT [0:0]';
			$firewallRules[] = ':OUTPUT ACCEPT [0:0]';
			$firewallRules[] = ':POSTROUTING ACCEPT [0:0]';

			// todo: make sure prerouting NAT load balancing works with DNS from system requests and proxy process requests, use output instead of prerouting if not

			foreach ($parameters['data']['next']['node_process_types'] as $nodeProcessType) {
				$nodeProcessTypeFirewallRuleSetPortNumberIndexes = array();

				foreach ($nodeProcessPartKeys as $nodeProcessPartKey) {
					if (empty($parameters['node_process_type_firewall_rule_set_port_numbers'][$parameters['node_process_type_firewall_rule_set_index']][$parameters['node_process_type_process_part_data_keys'][$nodeProcessType][$nodeProcessPartKey]][$nodeProcessType][$nodeProcessPartKey][$nodeIpAddressVersionNumber]) === false) {
						foreach ($parameters['node_process_type_firewall_rule_set_port_numbers'][$parameters['node_process_type_process_part_data_keys'][$nodeProcessType][$nodeProcessPartKey]][$nodeProcessType][$nodeProcessPartKey][$nodeIpAddressVersionNumber] as $nodeProcessTypeFirewallRuleSet => $nodeProcessPortNumbers) {
							if (empty($nodeProcessTypeFirewallRuleSetPortNumberIndexes[$nodeProcessTypeFirewallRuleSet]) === true) {
								$nodeProcessTypeFirewallRuleSetPortNumberIndexes[$nodeProcessTypeFirewallRuleSet] = 0;
							}

							$nodeProcessTypeFirewallRuleSetPortNumberIndexes[$nodeProcessTypeFirewallRuleSet] += count($nodeProcessPortNumbers);
						}
					}
				}

				foreach ($nodeProcessTypeFirewallRuleSetPortNumberIndexes as $nodeProcessTypeFirewallRuleSet => $nodeProcessTypeFirewallRuleSetPortNumberIndex) {
					foreach ($nodeProcessPartKeys as $nodeProcessPartKey) {
						foreach ($parameters['node_process_type_firewall_rule_set_port_numbers'][$parameters['node_process_type_firewall_rule_set_index']][$parameters['node_process_type_process_part_data_keys'][$nodeProcessType][$nodeProcessPartKey]][$nodeProcessType][$nodeProcessPartKey][$nodeIpAddressVersionNumber][$nodeProcessTypeFirewallRuleSet] as $nodeProcessPortNumbers) {
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

			foreach ($parameters['data']['next']['node_reserved_internal_sources'][$nodeIpAddressVersionNumber] as $nodeReservedInternalSource) {
				foreach ($nodeReservedInternalSources as $nodeReservedInternalSource) {
					$firewallRules[] = '-A PREROUTING ! -i lo -s ' . $nodeReservedInternalSource . ' -j DROP';
				}
			}

			foreach ($parameters['data']['next']['node_process_cryptocurrency_destinations'] as $cryptocurrencyNodeProcessType => $nodeProcessCryptocurrencyDestination) {
				$firewallRules[] = '-A PREROUTING ! -i lo -d ' . $nodeProcessCryptocurrencyDestination['ip_address'] . ' --dport ' . current($parameters['data']['next']['node_processes'][$cryptocurrencyNodeProcessType][0]) . ' -j DROP';
			}

			if (empty($parameters['data']['cryptocurrency_firewall_rules']) === false) {
				foreach ($parameters['data']['cryptocurrency_firewall_rules'] as $cryptocurrencyFirewallRule) {
					$firewallRules[] = $cryptocurrencyFirewallRule;
				}
			}

			foreach ($parameters['data']['next']['node_ssh_port_numbers'] as $nodeSshPortNumber) {
				$firewallRules[] = '-A PREROUTING -p tcp --dport ' . $nodeSshPortNumber . ' -j ACCEPT';
			}

			foreach ($parameters['node_process_type_firewall_rule_sets'] as $nodeProcessTypeFirewallRuleSet) {
				$firewallRules[] = '-A PREROUTING -m set --match-set ' . $nodeProcessTypeFirewallRuleSet . ' dst,src -j ACCEPT';
			}

			$firewallRules[] = '-A PREROUTING -i ' . $parameters['interface_name'] . ' -m set ! --match-set _ dst,src -j DROP';
			$firewallRules[] = 'COMMIT';
			unlink('/usr/local/nodecompute/firewall_ip_address_version_' . $nodeIpAddressVersionNumber . '.dat');
			touch('/usr/local/nodecompute/firewall_ip_address_version_' . $nodeIpAddressVersionNumber . '.dat');
			$firewallRuleParts = array_chunk($firewallRules, 1000);

			foreach ($firewallRuleParts as $firewallRulePart) {
				$firewallRulePart = implode("\n", $firewallRulePart);
				shell_exec('sudo echo "' . $firewallRulePart . '" >> /usr/local/nodecompute/firewall_ip_address_version_' . $nodeIpAddressVersionNumber . '.dat');
			}

			shell_exec('sudo ' . $firewallBinaryFiles[$nodeIpAddressVersionNumber] . ' < /usr/local/nodecompute/firewall_ip_address_version_' . $nodeIpAddressVersionNumber . '.dat');
			unlink('/usr/local/nodecompute/firewall_ip_address_version_' . $nodeIpAddressVersionNumber . '.dat');
			sleep(1);
		}

		return $parameters;
	}

	function _processFirewallRuleSets($parameters) {
		foreach ($parameters['node_process_type_process_part_data_keys'] as $nodeProcessType => $nodeProcessTypeProcessPartDataKeys) {
			$nodeProcessPortNumberHash = '';

			foreach ($parameters['data'][$nodeProcessTypeProcessPartDataKeys[$parameters['node_process_part_key']]]['node_processes'][$nodeProcessType][$parameters['node_process_part_key']] as $nodeProcessNodeId => $nodeProcessPortNumbers) {
				$nodeReservedInternalDestinationIpAddressVersionNumber = key($parameters['data'][$nodeProcessTypeProcessPartDataKeys[$parameters['node_process_part_key']]]['node_reserved_internal_destinations'][$nodeProcessNodeId]);
				$nodeProcessPortNumbersVerified = array();

				foreach ($nodeProcessPortNumbers as $nodeProcessId => $nodeProcessPortNumber) {
					if (_verifyNodeProcess($parameters['binary_files'], $parameters['data'][$nodeProcessTypeProcessPartDataKeys[$parameters['node_process_part_key']]]['node_reserved_internal_destinations'][$nodeProcessNodeId][$nodeReservedInternalDestinationIpAddressVersionNumber]['ip_address'], $nodeReservedInternalDestinationIpAddressVersionNumber, $nodeProcessPortNumber, $nodeProcessType) === true) {
						$nodeProcessPortNumberHash .= '_' . $nodeProcessPortNumber;
						$nodeProcessPortNumbersVerified[] = $nodeProcessPortNumber;
					}
				}

				$nodeProcessPortNumberHash = sha1($nodeProcessPortNumberHash);

				foreach ($parameters['data'][$nodeProcessTypeProcessPartDataKeys[$parameters['node_process_part_key']]]['node_ip_address_version_numbers'] as $nodeIpAddressVersionNumber) {
					if (empty($parameters['data'][$nodeProcessTypeProcessPartDataKeys[$parameters['node_process_part_key']]]['nodes'][$nodeProcessNodeId]['external_ip_address_version_' . $nodeIpAddressVersionNumber]) === false) {
						$nodeProcessNodeIpAddress = $parameters['data'][$nodeProcessTypeProcessPartDataKeys[$parameters['node_process_part_key']]]['nodes'][$nodeProcessNodeId]['external_ip_address_version_' . $nodeIpAddressVersionNumber];

						if (empty($parameters['data'][$nodeProcessTypeProcessPartDataKeys[$parameters['node_process_part_key']]]['nodes'][$nodeProcessNodeId]['internal_ip_address_version_' . $nodeIpAddressVersionNumber]) === false) {
							$nodeProcessNodeIpAddress = $parameters['data'][$nodeProcessTypeProcessPartDataKeys[$parameters['node_process_part_key']]]['nodes'][$nodeProcessNodeId]['internal_ip_address_version_' . $nodeIpAddressVersionNumber];
						}

						$parameters['node_process_type_firewall_rule_set_port_numbers'][$parameters['node_process_type_firewall_rule_set_index']][$nodeProcessTypeProcessPartDataKeys[$parameters['node_process_part_key']]][$nodeProcessType][$parameters['node_process_part_key']][$nodeIpAddressVersionNumber][($nodeProcessTypeFirewallRuleSet = $nodeProcessTypeProcessPartDataKeys[$parameters['node_process_part_key']] . '_' . $nodeIpAddressVersionNumber . '_' . $parameters['node_process_part_key'] . '_' . $nodeProcessType . '_' . $nodeProcessPortNumberHash . '_' . $parameters['node_process_type_firewall_rule_set_index'])] = $nodeProcessPortNumbersVerified;
						shell_exec('sudo ' . $parameters['binary_files']['ipset'] . ' create ' . $nodeProcessTypeProcessPartDataKeys[$parameters['node_process_part_key']] . '_' . $nodeIpAddressVersionNumber . '_' . $parameters['node_process_part_key'] . '_' . $nodeProcessType . '_' . $nodeProcessPortNumberHash . '_' . $parameters['node_process_type_firewall_rule_set_index'] . ' hash:ip,port family ' . $parameters['ip_address_versions'][$nodeIpAddressVersionNumber]['interface_type'] . ' timeout 0');

						foreach ($nodeProcessPortNumbers as $nodeProcessPortNumber) {
							shell_exec('sudo ' . $parameters['binary_files']['ipset'] . ' add ' . $nodeProcessTypeProcessPartDataKeys[$parameters['node_process_part_key']] . '_' . $nodeIpAddressVersionNumber . '_' . $parameters['node_process_part_key'] . '_' . $nodeProcessType . '_' . $nodeProcessPortNumberHash . '_' . $parameters['node_process_type_firewall_rule_set_index'] . ' ' . $nodeProcessNodeIpAddress . ',tcp:' . $nodeProcessPortNumber);
							shell_exec('sudo ' . $parameters['binary_files']['ipset'] . ' add ' . $nodeProcessTypeProcessPartDataKeys[$parameters['node_process_part_key']] . '_' . $nodeIpAddressVersionNumber . '_' . $parameters['node_process_part_key'] . '_' . $nodeProcessType . '_' . $nodeProcessPortNumberHash . '_' . $parameters['node_process_type_firewall_rule_set_index'] . ' ' . $nodeProcessNodeIpAddress . ',udp:' . $nodeProcessPortNumber);
						}

						$parameters['node_process_type_firewall_rule_sets'][$nodeProcessTypeProcessPartDataKeys[$parameters['node_process_part_key']] . '_' . $nodeIpAddressVersionNumber . '_' . $parameters['node_process_part_key'] . '_' . $nodeProcessType . '_' . $nodeProcessPortNumberHash . '_' . $parameters['node_process_type_firewall_rule_set_index']] = $nodeProcessTypeProcessPartDataKeys[$parameters['node_process_part_key']] . '_' . $nodeIpAddressVersionNumber . '_' . $parameters['node_process_part_key'] . '_' . $nodeProcessType . '_' . $nodeProcessPortNumberHash . '_' . $parameters['node_process_type_firewall_rule_set_index'];
						$nodeReservedInternalDestinationIpAddressVersionNumber = $nodeIpAddressVersionNumber;
					}
				}

				if ($parameters['node_process_type_firewall_rule_set_index'] === 4) {
					$parameters['data']['next']['node_process_type_firewall_rule_set_reserved_internal_destinations'][$nodeProcessTypeProcessPartDataKeys[$parameters['node_process_part_key']] . '_' . $nodeIpAddressVersionNumber . '_' . $parameters['node_process_part_key'] . '_' . $nodeProcessType . '_' . $nodeProcessPortNumberHash . '_' . $parameters['node_process_type_firewall_rule_set_index']][$nodeProcessNodeId] = $parameters['data'][$nodeProcessTypeProcessPartDataKeys[$parameters['node_process_part_key']]]['node_reserved_internal_destinations'][$nodeProcessNodeId][$nodeReservedInternalDestinationIpAddressVersionNumber];
				}
			}
		}

		$parameters['node_process_type_firewall_rule_set_index']++;
		return $parameters;
	}

	function _processNodeProcesses($parameters, $response) {
		$parameters['processing_progress_checkpoints'] = array(
			'listing_node_parameters',
			'processing_next_node_processes',
			'verifying_current_cryptocurrency_node_processes',
			'deploying_next_cryptocurrency_node_processes',
			'verifying_current_http_proxy_node_processes',
			'verifying_current_load_balancer_node_processes',
			'verifying_current_recursive_dns_node_processes',
			'verifying_current_socks_proxy_node_processes',
			'processing_next_node_kernel_options',
			'processing_next_node_interfaces',
			'processing_next_recursive_dns_node_processes',
			'processing_next_proxy_node_processes',
			'processing_firewall',
			'deleting_previous_node_processes',
			'processing_current_node_processes',
			'processing_completed'
		);
		end($parameters['processing_progress_checkpoints']);
		$parameters['processing_progress_checkpoint_count'] = (key($parameters['processing_progress_checkpoints']) + 1);
		reset($parameters['processing_progress_checkpoints']);
		$systemActionProcessNodeParameters = array(
			'action' => 'process_node',
			'data' => array(
				'processing_status' => '1'
			),
			'node_authentication_token' => $parameters['node_authentication_token']
		);
		$parameters['processing_progress_checkpoints'] = _updateNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);
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

		if (file_exists('/usr/local/nodecompute/system_action_process_node_current_response.json') === true) {
			$systemActionProcessNodeResponse = file_get_contents('/usr/local/nodecompute/system_action_process_node_current_response.json');
			$systemActionProcessNodeResponse = json_decode($systemActionProcessNodeResponse, true);

			if (empty($systemActionProcessNodeResponse) === false) {
				$parameters['data']['current'] = $systemActionProcessNodeResponse;
			}
		}

		$parameters['processing_progress_checkpoints'] = _updateNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);
		$systemActionProcessNodeParameterData = $systemActionProcessNodeParameters['data'];
		unset($systemActionProcessNodeParameters['data']);
		$encodedSystemActionProcessNodeParameters = json_encode($systemActionProcessNodeParameters);

		if ($encodedSystemActionProcessNodeParameters === false) {
			$response['message'] = 'Error processing node, please try again.';
			return $response;
		}

		shell_exec('sudo ' . $parameters['binary_files']['wget'] . ' -O /usr/local/nodecompute/system_action_process_node_next_response.json --no-dns-cache --post-data \'json=' . $encodedSystemActionProcessNodeParameters . '\' --timeout=600 ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php');

		if (file_exists('/usr/local/nodecompute/system_action_process_node_next_response.json') === false) {
			$response['message'] = 'Error processing node, please try again.';
			return $response;
		}

		$systemActionProcessNodeResponse = file_get_contents('/usr/local/nodecompute/system_action_process_node_next_response.json');
		$systemActionProcessNodeResponse = json_decode($systemActionProcessNodeResponse, true);
		unlink('/usr/local/nodecompute/system_action_process_node_next_response.json');

		if ($systemActionProcessNodeResponse === false) {
			$response['message'] = 'Error processing node, please try again.';
			return $response;
		}

		if (empty($systemActionProcessNodeResponse['data']) === false) {
			$parameters['data']['next'] = $systemActionProcessNodeResponse['data'];

			if (empty($parameters['data']['current']) === true) {
				$parameters['data']['current'] = $parameters['data']['next'];
			}
		}

		$systemActionProcessNodeParameters['data'] = $systemActionProcessNodeParameterData;
		unset($systemActionProcessNodeParameterData);

		if (empty($parameters['data']['next']['nodes']) === false) {
			$parameters['processing_progress_checkpoints'] = _updateNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);

			foreach ($parameters['data']['next']['cryptocurrency_node_process_types'] as $cryptocurrencyNodeProcessType) {
				if (empty($parameters['data']['next']['node_processes'][$cryptocurrencyNodeProcessType]) === false) {
					// todo: send start signal to crypto daemon if file exists
					// todo: differentiate crypto daemon process verification from mining process verification using node_node_id

					if (_verifyNodeProcess($parameters['binary_files'], false, false, false, $cryptocurrencyNodeProcessType) === false) {
						if (empty($parameters['processing_progress_checkpoints'][3]) === false) {
							$parameters['processing_progress_checkpoints'] = _updateNodeProcessingProgress($parameters['binary_files']['wget'], $systemActionProcessNodeParameters, $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count']);
						}

						require_once('/usr/local/nodecompute/node_action_deploy_' . $cryptocurrencyNodeProcessType . '_node_process.php');
					}

					// todo: add crontab commands for updating mining block template data if crypto daemon exists
					// todo: add crontab commands for mining block headers if crypto daemon doesn't exist
				}
			}

			unset($parameters['processing_progress_checkpoints'][3]);
		} else {
			unset($parameters['processing_progress_checkpoints'][2]);
			unset($parameters['processing_progress_checkpoints'][3]);

			if (empty($parameters['data']['current']) === false) {
				foreach ($parameters['data']['current']['cryptocurrency_node_process_types'] as $cryptocurrencyNodeProcessType) {
					// todo: update cryptocurrency node block download progress if node_process_cryptocurrency_destinations address exists for process
				}

				$parameters['data']['current']['node_process_types'] = array_diff($parameters['data']['current']['node_process_types'], $parameters['data']['current']['cryptocurrency_node_process_types']);

				foreach ($parameters['data']['current']['node_process_types'] as $nodeProcessType) {
					if (empty($parameters['data']['current']['node_process_type_firewall_rule_set_port_numbers'][$nodeProcessType]) === false) {
						$parameters['processing_progress_checkpoints'] = _updateNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);

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
											_updateNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);
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
			_updateNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);
			$response = $systemActionProcessNodeResponse;
			return $response;
		}

		unset($parameters['processing_progress_checkpoints'][4]);
		unset($parameters['processing_progress_checkpoints'][5]);
		unset($parameters['processing_progress_checkpoints'][6]);
		unset($parameters['processing_progress_checkpoints'][7]);
		$parameters['processing_progress_checkpoints'] = _updateNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);
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

		foreach ($parameters['data']['next']['node_ip_address_version_numbers'] as $nodeIpAddressVersionNumber) {
			$kernelOptions['net.ipv' . $nodeIpAddressVersion . '.tcp_mem'] = $memoryCapacityPages . ' ' . $memoryCapacityPages . ' ' . $memoryCapacityPages;
			$kernelOptions['net.ipv' . $nodeIpAddressVersion . '.tcp_rmem'] = 1 . ' ' . $defaultSocketBufferMemoryBytes . ' ' . ($defaultSocketBufferMemoryBytes * 2);
			$kernelOptions['net.ipv' . $nodeIpAddressVersion . '.tcp_wmem'] = $kernelOptions['net.ipv' . $nodeIpAddressVersionNumber . '.tcp_rmem'];
			$kernelOptions['net.ipv' . $nodeIpAddressVersion . '.udp_mem'] = $kernelOptions['net.ipv' . $nodeIpAddressVersionNumber . '.tcp_mem'];
		}

		foreach ($kernelOptions as $kernelOptionKey => $kernelOptionValue) {
			shell_exec('sudo ' . $parameters['binary_files']['sysctl'] . ' -w ' . $kernelOptionKey . '="' . $kernelOptionValue . '"');
		}

		$parameters['processing_progress_checkpoints'] = _updateNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);
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

			$nodeIpAddressesToDelete[$ipAddressVersionNumber] = array_diff($existingNodeIpAddresses, $parameters['data']['next']['node_ip_addresses'][$ipAddressVersionNumber]);
			shell_exec('sudo ' . $parameters['binary_files']['ipset'] . ' create _ hash:ip family ' . $ipAddressVersion['interface_type'] . ' timeout 0');

			foreach ($parameters['data']['next']['node_reserved_internal_destination_ip_addresses'][$ipAddressVersionNumber] as $nodeReservedInternalDestinationIpAddress) {
				shell_exec('sudo ' . $parameters['binary_files']['ipset'] . ' add _ ' . $nodeReservedInternalDestinationIpAddress);
			}
		}

		array_unshift($nodeInterfaces, '<?php');
		$nodeInterfaces = implode("\n", $nodeInterfaces);

		if (file_put_contents('/usr/local/nodecompute/node_interfaces.php', $nodeInterfaces) === false) {
			$response['message'] = 'Error adding node interfaces, please try again.';
			return $response;
		}

		$parameters['processing_progress_checkpoints'] = _updateNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);

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

			foreach ($parameters['data']['next']['node_processes']['recursive_dns'][$nodeProcessPartKey] as $recursiveDnsNodeProcessNodeId => $recursiveDnsNodeProcessPortNumbers) {
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

				foreach ($parameters['data']['next']['node_reserved_internal_sources'] as $nodeReservedInternalSourceIpAddressVersionNumber => $nodeReservedInternalSources) {
					foreach ($nodeReservedInternalSources as $nodeReservedInternalSource) {
						$recursiveDnsNodeProcessConfiguration['a' . $recursiveDnsNodeProcessConfigurationIndexes['a']] = $nodeReservedInternalSource . ';';
						$recursiveDnsNodeProcessConfigurationIndexes['a']++;
					}
				}

				if (empty($parameters['data']['next']['node_process_node_users']['recursive_dns'][$recursiveDnsNodeProcessNodeId]) === false) {
					$recursiveDnsNodeProcessConfiguration['h' . str_pad($recursiveDnsNodeProcessConfigurationIndexes['h'], 6, '0', STR_PAD_LEFT)] = 'logging {';
					$recursiveDnsNodeProcessConfigurationIndexes['h']++;

					foreach ($parameters['data']['next']['node_process_node_users']['recursive_dns'][$recursiveDnsNodeProcessNodeId] as $recursiveDnsNodeProcessNodeUserId) {
						if (empty($parameters['data']['next']['node_users'][$recursiveDnsNodeProcessNodeUserId]['node_user_authentication_sources']) === false) {
							foreach ($parameters['data']['next']['node_users'][$recursiveDnsNodeProcessNodeUserId]['node_user_authentication_sources'] as $recursiveDnsNodeProcessNodeUserAuthenticationSource) {
								$recursiveDnsNodeProcessConfiguration['b' . $recursiveDnsNodeProcessConfigurationIndexes['b']] = $recursiveDnsNodeProcessNodeUserAuthenticationSource . ';';
								$recursiveDnsNodeProcessConfigurationIndexes['b']++;
							}

							$recursiveDnsNodeProcessConfiguration['h' . str_pad($recursiveDnsNodeProcessConfigurationIndexes['h'], 6, '0', STR_PAD_LEFT)] = 'channel ' . $recursiveDnsNodeProcessNodeId . '_' . $recursiveDnsNodeProcessNodeUserId . ' {';
							$recursiveDnsNodeProcessConfigurationIndexes['h']++;
							$recursiveDnsNodeProcessConfiguration['h' . str_pad($recursiveDnsNodeProcessConfigurationIndexes['h'], 6, '0', STR_PAD_LEFT)] = 'file "/var/log/recursive_dns/' . $recursiveDnsNodeProcessNodeId . '_' . $recursiveDnsNodeProcessNodeUserId . '"';
							$recursiveDnsNodeProcessConfigurationIndexes['h']++;
							$recursiveDnsNodeProcessConfiguration['h' . str_pad($recursiveDnsNodeProcessConfigurationIndexes['h'], 6, '0', STR_PAD_LEFT)] = 'print-time yes';
							$recursiveDnsNodeProcessConfigurationIndexes['h']++;
							$recursiveDnsNodeProcessConfiguration['h' . str_pad($recursiveDnsNodeProcessConfigurationIndexes['h'], 6, '0', STR_PAD_LEFT)] = '};';
							$recursiveDnsNodeProcessConfigurationIndexes['h']++;
							$recursiveDnsNodeProcessConfiguration['h' . str_pad($recursiveDnsNodeProcessConfigurationIndexes['h'], 6, '0', STR_PAD_LEFT)] = 'category ' . $recursiveDnsNodeProcessNodeId . '_' . $recursiveDnsNodeProcessNodeUserId . ' {';
							$recursiveDnsNodeProcessConfigurationIndexes['h']++;
							$recursiveDnsNodeProcessConfiguration['h' . str_pad($recursiveDnsNodeProcessConfigurationIndexes['h'], 6, '0', STR_PAD_LEFT)] = 'queries_log;';
							$recursiveDnsNodeProcessConfigurationIndexes['h']++;
							$recursiveDnsNodeProcessConfiguration['h' . str_pad($recursiveDnsNodeProcessConfigurationIndexes['h'], 6, '0', STR_PAD_LEFT)] = '};';
							$recursiveDnsNodeProcessConfigurationIndexes['h']++;
						}
					}

					$recursiveDnsNodeProcessConfiguration['h' . str_pad($recursiveDnsNodeProcessConfigurationIndexes['h'], 6, '0', STR_PAD_LEFT)] = '};';
				}

				$recursiveDnsNodeProcessesStart = true;
				$recursiveDnsNodeProcessInterfaceDestinationIpAddresses = $recursiveDnsNodeProcessNodeIpAddresses = array();

				foreach ($parameters['data']['next']['node_ip_address_version_numbers'] as $recursiveDnsNodeIpAddressVersionNumber) {
					$recursiveDnsNodeProcessConfigurationOptionSuffix = '';

					if ($recursiveDnsNodeIpAddressVersionNumber === '6') {
						$recursiveDnsNodeProcessConfigurationOptionSuffix = '-v6';
					}

					if (empty($parameters['data']['next']['nodes'][$recursiveDnsNodeProcessNodeId]['external_ip_address_version_' . $recursiveDnsNodeIpAddressVersionNumber]) === false) {
						$recursiveDnsNodeProcessConfiguration['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = 'listen-on' . $recursiveDnsNodeProcessConfigurationOptionSuffix . ' {';
						$recursiveDnsNodeProcessConfigurationIndexes['c']++;
						$recursiveDnsNodeProcessInterfaceSourceIpAddress = $recursiveDnsNodeProcessNodeIpAddresses[$recursiveDnsNodeIpAddressVersionNumber] = $parameters['data']['next']['nodes'][$recursiveDnsNodeProcessNodeId]['external_ip_address_version_' . $recursiveDnsNodeIpAddressVersionNumber];
						$recursiveDnsNodeProcessConfiguration['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = false;

						if (empty($parameters['data']['next']['nodes'][$recursiveDnsNodeProcessNodeId]['internal_ip_address_version_' . $recursiveDnsNodeIpAddressVersionNumber]) === false) {
							$recursiveDnsNodeProcessInterfaceDestinationIpAddresses['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = $parameters['data']['next']['nodes'][$recursiveDnsNodeProcessNodeId]['internal_ip_address_version_' . $recursiveDnsNodeIpAddressVersionNumber];
							$recursiveDnsNodeProcessInterfaceSourceIpAddress = $recursiveDnsNodeProcessNodeIpAddresses[$recursiveDnsNodeIpAddressVersionNumber] = $parameters['data']['next']['nodes'][$recursiveDnsNodeProcessNodeId]['internal_ip_address_version_' . $recursiveDnsNodeIpAddressVersionNumber];
						} else {
							$recursiveDnsNodeProcessInterfaceDestinationIpAddresses['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = $parameters['data']['next']['nodes'][$recursiveDnsNodeProcessNodeId]['external_ip_address_version_' . $recursiveDnsNodeIpAddressVersionNumber];
						}

						if (empty($parameters['data']['current']['node_reserved_internal_destination_ip_addresses'][$recursiveDnsNodeIpAddressVersionNumber][$recursiveDnsNodeProcessInterfaceDestinationIpAddresses['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']]]) === false) {
							$recursiveDnsNodeProcessesStart = false;
						}

						$recursiveDnsNodeProcessConfigurationIndexes['c']++;
						$recursiveDnsNodeProcessConfiguration['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = false;
						$recursiveDnsNodeProcessInterfaceDestinationIpAddresses['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = $parameters['data']['next']['node_reserved_internal_destinations'][$recursiveDnsNodeProcessNodeId][$recursiveDnsNodeIpAddressVersionNumber]['ip_address'];
						$recursiveDnsNodeProcessConfigurationIndexes['c']++;

						if (empty($parameters['data']['next']['node_process_node_users']['recursive_dns'][$recursiveDnsNodeProcessNodeId]) === false) {
							$recursiveDnsNodeProcessConfiguration['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = false;
							$recursiveDnsNodeProcessInterfaceDestinationIpAddresses['c' . $recursiveDnsNodeProcessConfigurationIndexes['c']] = $recursiveDnsNodeProcessInterfaceSourceIpAddress;
							$recursiveDnsNodeProcessConfigurationIndexes['c']++;

							if (empty($parameters['data']['current']['node_reserved_internal_destination_ip_addresses'][$recursiveDnsNodeIpAddressVersionNumber][$recursiveDnsNodeProcessInterfaceSourceIpAddress]) === false) {
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
					while (_verifyNodeProcessConnections($parameters['binary_files'], $recursiveDnsNodeProcessNodeIpAddresses, $recursiveDnsNodeProcessPortNumber) === true) {
						sleep(1);
					}

					if (file_exists('/etc/recursive_dns_' . $recursiveDnsNodeProcessId . '/named.conf') === true) {
						$recursiveDnsNodeProcessProcessIds = _listProcessIds('recursive_dns_' . $recursiveDnsNodeProcessId . ' ', 'recursive_dns_' . $recursiveDnsNodeProcessId . '/');

						if (empty($recursiveDnsNodeProcessProcessIds) === false) {
							_killProcessIds($parameters['binary_files'], $parameters['action'], $parameters['process_id'], $recursiveDnsNodeProcessProcessIds);
						}
					}

					foreach ($recursiveDnsNodeProcessInterfaceDestinationIpAddresses as $recursiveDnsNodeProcessInterfaceDestinationIpAddressIndex => $recursiveDnsNodeProcessInterfaceDestinationIpAddress) {
						$recursiveDnsNodeProcessConfiguration[$recursiveDnsNodeProcessInterfaceDestinationIpAddressIndex] = $recursiveDnsNodeProcessInterfaceDestinationIpAddress . ':' . $recursiveDnsNodeProcessPortNumber . ';';
					}

					$recursiveDnsNodeProcessConfiguration['e'] = '"/var/cache/recursive_dns_' . $recursiveDnsNodeProcessId . '";';
					$recursiveDnsNodeProcessConfiguration['f'] = 'pid-file "/var/run/named/recursive_dns_' . $recursiveDnsNodeProcessId . '.pid";';
					$recursiveDnsNodeProcessConfiguration = implode("\n", $recursiveDnsNodeProcessConfiguration);
					file_put_contents('/etc/recursive_dns_' . $recursiveDnsNodeProcessId . '/named.conf.options', $recursiveDnsNodeProcessConfiguration);
					shell_exec('cd /usr/sbin && sudo ln /usr/sbin/named recursive_dns_' . $recursiveDnsNodeProcessId);
					$recursiveDnsNodeProcessService = array(
						'[Service]',
						'ExecStart=/usr/sbin/recursive_dns_' . $recursiveDnsNodeProcessId . ' -f -c /etc/recursive_dns_' . $recursiveDnsNodeProcessId . '/named.conf -S 40000 -u root'
					);
					$recursiveDnsNodeProcessService = implode("\n", $recursiveDnsNodeProcessService);
					file_put_contents('/lib/systemd/system/recursive_dns_' . $recursiveDnsNodeProcessId . '.service', $recursiveDnsNodeProcessService);

					if (file_exists('/etc/default/recursive_dns_' . $recursiveDnsNodeProcessId) === false) {
						copy('/etc/default/' . $recursiveDnsNodeProcessDefaultServiceName, '/etc/default/recursive_dns_' . $recursiveDnsNodeProcessId);
					}

					if (file_exists('/etc/recursive_dns_' . $recursiveDnsNodeProcessId) === false) {
						shell_exec('sudo cp -r /etc/bind /etc/recursive_dns_' . $recursiveDnsNodeProcessId);
						file_put_contents('/etc/recursive_dns_' . $recursiveDnsNodeProcessId . '/named.conf', 'include "/etc/recursive_dns_' . $recursiveDnsNodeProcessId . '/named.conf.options"; include "/etc/recursive_dns_' . $recursiveDnsNodeProcessId . '/named.conf.local"; include "/etc/recursive_dns_' . $recursiveDnsNodeProcessId . '/named.conf.default-zones";');
					}

					if (is_dir('/var/cache/recursive_dns_' . $recursiveDnsNodeProcessId) === false) {
						mkdir('/var/cache/recursive_dns_' . $recursiveDnsNodeProcessId);
					}

					shell_exec('sudo ' . $parameters['binary_files']['systemctl'] . ' daemon-reload');
					unlink('/var/run/named/recursive_dns_' . $recursiveDnsNodeProcessId . '.pid');
					// todo: add default node timeout column to wait for X seconds before proceeding with processing after processes start + stop
					$recursiveDnsNodeProcessResponse = false;

					while ($recursiveDnsNodeProcessResponse === false) {
						$recursiveDnsNodeProcessResponse = (_verifyNodeProcess($parameters['binary_files'], $parameters['data']['next']['node_reserved_internal_destinations'][$recursiveDnsNodeProcessNodeId][$recursiveDnsNodeIpAddressVersionNumber]['ip_address'], $recursiveDnsNodeIpAddressVersion, $recursiveDnsNodeProcessPortNumber, 'recursive_dns') === false);
						sleep(1);
					}

					$recursiveDnsNodeProcessResponse = false;

					if ($recursiveDnsNodeProcessesStart === true) {
						while ($recursiveDnsNodeProcessResponse === false) {
							shell_exec('sudo ' . $parameters['binary_files']['service'] . ' recursive_dns_' . $recursiveDnsNodeProcessId . ' start');
							$recursiveDnsNodeProcessResponse = (_verifyNodeProcess($parameters['binary_files'], $parameters['data']['next']['node_reserved_internal_destinations'][$recursiveDnsNodeProcessNodeId][$recursiveDnsNodeIpAddressVersionNumber]['ip_address'], $recursiveDnsNodeIpAddressVersion, $recursiveDnsNodeProcessPortNumber, 'recursive_dns') === true);
							sleep(1);
						}
					} else {
						$systemActionProcessNodeParameters['data']['processed_status'] = '0';
					}

					if (file_exists('/var/run/named/recursive_dns_' . $recursiveDnsNodeProcessId . '.pid') === true) {
						$recursiveDnsNodeProcessProcessId = file_get_contents('/var/run/named/recursive_dns_' . $recursiveDnsNodeProcessId . '.pid');

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
				if (empty($nodeRecursiveDnsDestination['destination_ip_address_version_' . $ipAddressVersionNumber]) === false) {
					$nodeRecursiveDnsDestinations[] = 'nameserver [' . $nodeRecursiveDnsDestination['destination_ip_address_version_' . $ipAddressVersionNumber] . ']:' . $nodeRecursiveDnsDestination['port_number_version_' . $ipAddressVersionNumber];
				}
			}
		}

		$nodeRecursiveDnsDestinations = implode("\n", $nodeRecursiveDnsDestinations);

		if (file_put_contents('/usr/local/nodecompute/resolv.conf', $nodeRecursiveDnsDestinations) === false) {
			$response['message'] = 'Error adding node recursive DNS destinations, please try again.';
			return $response;
		}

		$parameters['processing_progress_checkpoints'] = _updateNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);
		$parameters['node_process_type_process_part_data_keys']['recursive_dns'] = array(
			'next',
			'next'
		);

		foreach (array(0, 1) as $nodeProcessPartKey) {
			$parameters['node_process_part_key'] = $nodeProcessPartKey;
			$parameters = _processFirewall($parameters);
			$nodeProcessPartKey = abs($nodeProcessPartKey - 1);

			foreach ($parameters['data']['next']['proxy_node_process_types'] as $proxyNodeProcessTypeServiceName => $proxyNodeProcessType) {
				if (empty($parameters['data']['next']['node_processes'][$proxyNodeProcessType][$nodeProcessPartKey]) === false) {
					foreach ($parameters['data']['next']['node_processes'][$proxyNodeProcessType][$nodeProcessPartKey] as $proxyNodeProcessNodeId => $proxyNodeProcessPortNumbers) {
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

						foreach ($parameters['data']['next']['node_process_node_users'][$proxyNodeProcessType][$proxyNodeProcessNodeId] as $proxyNodeProcessNodeUserIds) {
							$proxyNodeProcessConfiguration['c' . str_pad($proxyNodeProcessConfigurationIndexes['c'], 10, '0', STR_PAD_LEFT)] = 'auth iponly strong';
							$proxyNodeProcessConfigurationIndexes['c']++;

							foreach ($proxyNodeProcessNodeUserIds as $proxyNodeProcessNodeUserId) {
								$proxyNodeProcessNodeUser = $parameters['data']['next']['node_users'][$proxyNodeProcessNodeUserId];
								$proxyNodeProcessNodeUserAuthenticationCredentialParts = array();

								if (empty($proxyNodeProcessNodeUser['node_user_authentication_credentials']) === false) {
									foreach ($proxyNodeProcessNodeUser['node_user_authentication_credentials'] as $proxyNodeProcessNodeUserAuthenticationCredential) {	
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
										(empty($proxyNodeProcessNodeUser['node_request_destination_ids']) === false) ||
										(empty($proxyNodeProcessNodeUser['node_request_destinations_only_allowed_status']) === true)
									) &&
									(
										(empty($proxyNodeProcessNodeUser['node_user_authentication_credentials']) === false) ||
										(empty($proxyNodeProcessNodeUser['node_user_authentication_sources']) === false)
									)
								) {
									$proxyNodeProcessNodeUserNodeRequestDestinationParts = array(
										'*'
									);

									foreach ($proxyNodeProcessNodeUser['node_request_destination_ids'] as $proxyNodeProcessNodeUserNodeRequestDestinationId) {
										if (($proxyNodeProcessConfigurationIndexes['h'] % 10) === 0) {
											$proxyNodeProcessConfigurationPartIndexes['h'] = $proxyNodeProcessConfigurationIndexes['h'];
											$proxyNodeProcessNodeUserNodeRequestDestinationParts[$proxyNodeProcessConfigurationPartIndexes['h']] = $parameters['data']['next']['node_request_destinations'][$proxyNodeProcessNodeUserNodeRequestDestinationId];
										} else {
											$proxyNodeProcessNodeUserNodeRequestDestinationParts[$proxyNodeProcessConfigurationPartIndexes['h']] .= ',' . $parameters['data']['next']['node_request_destinations'][$proxyNodeProcessNodeUserNodeRequestDestinationId];
										}

										$proxyNodeProcessConfigurationIndexes['h']++;
									}

									if (empty($proxyNodeProcessNodeUser['node_request_logs_allowed_status']) === false) {
										$proxyNodeProcessConfiguration['c' . str_pad($proxyNodeProcessConfigurationIndexes['c'], 10, '0', STR_PAD_LEFT)] = 'log /var/log/' . $proxyNodeProcessType . '/' . $proxyNodeProcessNodeId . '_' . $proxyNodeProcessNodeUserId;
										$proxyNodeProcessConfigurationIndexes['c']++;
										$proxyNodeProcessConfiguration['c' . str_pad($proxyNodeProcessConfigurationIndexes['c'], 10, '0', STR_PAD_LEFT)] = 'logformat " %I _ %O _ %Y-%m-%d %H-%M-%S.%. _ %n _ %R _ %E _ %C"';
										$proxyNodeProcessConfigurationIndexes['c']++;
									}

									if (
										(empty($proxyNodeProcessNodeUser['node_request_destinations_only_allowed_status']) === true) &&
										(empty($proxyNodeProcessNodeUserNodeRequestDestinationParts) === false)
									) {
										foreach ($proxyNodeProcessNodeUserNodeRequestDestinationParts as $proxyNodeProcessNodeUserNodeRequestDestinationPart) {
											$proxyNodeProcessConfiguration['c' . str_pad($proxyNodeProcessConfigurationIndexes['c'], 10, '0', STR_PAD_LEFT)] = 'deny * * ' . $proxyNodeProcessNodeUserNodeRequestDestinationPart;
											$proxyNodeProcessConfigurationIndexes['c']++;
										}
									}

									if (empty($proxyNodeProcessNodeUser['authentication_strict_only_allowed_status']) === true) {
										if (
											(empty($proxyNodeProcessNodeUser['node_request_destinations_only_allowed_status']) === false) &&
											(empty($proxyNodeProcessNodeUser['node_user_authentication_credentials']) === false)
										) {
											foreach ($proxyNodeProcessNodeUserAuthenticationCredentialParts as $proxyNodeProcessNodeUserAuthenticationCredentialPart) {
												foreach ($proxyNodeProcessNodeUserNodeRequestDestinationParts as $proxyNodeProcessNodeUserNodeRequestDestinationPart) {
													$proxyNodeProcessConfiguration['d' . str_pad($proxyNodeProcessConfigurationIndexes['d'], 10, '0', STR_PAD_LEFT)] = 'allow ' . $proxyNodeProcessNodeUserAuthenticationCredentialPart . ' * ' . $proxyNodeProcessNodeUserNodeRequestDestinationPart;
													$proxyNodeProcessConfigurationIndexes['d']++;
												}
											}
										}

										$proxyNodeProcessNodeUserAuthenticationCredentialParts = array(
											'*'
										);
									}

									if (empty($proxyNodeProcessNodeUser['node_user_authentication_sources']) === false) {
										$proxyNodeProcessNodeUserAuthenticationSourceParts = array();

										foreach ($proxyNodeProcessNodeUser['node_user_authentication_sources'] as $proxyNodeProcessNodeUserAuthenticationSource) {
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
													$proxyNodeProcessConfiguration['c' . str_pad($proxyNodeProcessConfigurationIndexes['c'], 10, '0', STR_PAD_LEFT)] = 'allow ' . $proxyNodeProcessNodeUserAuthenticationCredentialPart . ' ' . $proxyNodeProcessNodeUserAuthenticationSourcePart . ' ' . $proxyNodeProcessNodeUserNodeRequestDestinationPart;
													$proxyNodeProcessConfigurationIndexes['c']++;
												}
											}
										}
									}

									$proxyNodeProcessConfiguration['d' . str_pad($proxyNodeProcessConfigurationIndexes['d'], 10, '0', STR_PAD_LEFT)] = 'deny *';
									$proxyNodeProcessConfigurationIndexes['d']++;
									$proxyNodeProcessConfiguration['d' . str_pad($proxyNodeProcessConfigurationIndexes['d'], 10, '0', STR_PAD_LEFT)] = 'flush';
									$proxyNodeProcessConfigurationIndexes['d']++;
								}
							}
						}

						$proxyNodeProcessesStart = true;
						$proxyNodeProcessNodeIpAddresses = array();

						foreach ($parameters['data']['next']['node_ip_address_version_numbers'] as $proxyNodeIpAddressVersionNumber) {
							if (empty($parameters['data']['next']['node_process_recursive_dns_destinations'][$proxyNodeProcessType][$proxyNodeProcessNodeId]['destination_ip_address_version_' . $proxyNodeIpAddressVersionNumber]) === false) {
								$proxyNodeProcessConfiguration['e' . $proxyNodeProcessConfigurationIndexes['e']] = 'nserver ' . $parameters['data']['next']['node_process_recursive_dns_destinations'][$proxyNodeProcessType][$proxyNodeProcessNodeId]['destination_ip_address_version_' . $proxyNodeIpAddressVersionNumber] . '[:' . $parameters['data']['next']['node_process_recursive_dns_destinations'][$proxyNodeProcessType][$proxyNodeProcessNodeId]['port_number_version_' . $proxyNodeIpAddressVersionNumber] . ']';
								$proxyNodeProcessConfigurationIndexes['e']++;
							}

							if (empty($parameters['data']['next']['nodes'][$proxyNodeProcessNodeId]['external_ip_address_version_' . $proxyNodeIpAddressVersionNumber]) === false) {
								$proxyNodeProcessInterfaceDestinationIpAddress = $proxyNodeProcessNodeIpAddresses[$proxyNodeIpAddressVersionNumber] = $parameters['data']['next']['nodes'][$proxyNodeProcessNodeId]['external_ip_address_version_' . $proxyNodeIpAddressVersionNumber];

								if (empty($parameters['data']['next']['nodes'][$proxyNodeProcessNodeId]['internal_ip_address_version_' . $proxyNodeIpAddressVersionNumber]) === false) {
									$proxyNodeProcessInterfaceDestinationIpAddress = $proxyNodeProcessNodeIpAddresses[$proxyNodeIpAddressVersionNumber] = $parameters['data']['next']['nodes'][$proxyNodeProcessNodeId]['internal_ip_address_version_' . $proxyNodeIpAddressVersionNumber];

									if (empty($parameters['data']['current']['node_reserved_internal_destination_ip_addresses'][$proxyNodeIpAddressVersionNumber][$proxyNodeProcessInterfaceDestinationIpAddress]) === false) {
										$proxyNodeProcessesStart = false;
									}
								}

								$proxyNodeProcessConfiguration['f'] .= ' -e' . $proxyNodeProcessInterfaceDestinationIpAddress . ' -i' . $proxyNodeProcessInterfaceDestinationIpAddress;
								$proxyNodeProcessConfiguration['g'] .= ' -e' . $parameters['data']['next']['node_reserved_internal_destinations'][$proxyNodeProcessNodeId][$proxyNodeIpAddressVersionNumber]['ip_address'] . ' -i' . $parameters['data']['next']['node_reserved_internal_destinations'][$proxyNodeProcessNodeId][$proxyNodeIpAddressVersionNumber]['ip_address'];
							}
						}

						ksort($proxyNodeProcessConfiguration);
						$proxyNodeProcessInterfaceConfigurations = array(
							'f' => $proxyNodeProcessConfiguration['f'],
							'g' => $proxyNodeProcessConfiguration['g']
						);

						foreach ($proxyNodeProcessPortNumbers as $proxyNodeProcessId => $proxyNodeProcessPortNumber) {
							while (_verifyNodeProcessConnections($parameters['binary_files'], $proxyNodeProcessNodeIpAddresses, $proxyNodeProcessPortNumber) === true) {
								sleep(1);
							}

							if (file_exists('/etc/3proxy/' . $proxyNodeProcessType . '_' . $proxyNodeProcessId . '.cfg') === true) {
								$proxyNodeProcessProcessIds = _listProcessIds($proxyNodeProcessType . '_' . $proxyNodeProcessId . ' ', '/etc/3proxy/' . $proxyNodeProcessType . '_' . $proxyNodeProcessId . '.cfg');

								if (empty($proxyNodeProcessProcessIds) === false) {
									_killProcessIds($parameters['binary_files'], $parameters['action'], $parameters['process_id'], $proxyNodeProcessProcessIds);
								}
							}

							shell_exec('cd /bin && sudo ln /bin/3proxy ' . $proxyNodeProcessType . '_' . $proxyNodeProcessId);
							$proxyNodeProcessService = array(
								'[Service]',
								'ExecStart=/bin/' . $proxyNodeProcessType . '_' . $proxyNodeProcessId . ' /etc/3proxy/' . $proxyNodeProcessType . '_' . $proxyNodeProcessId . '.cfg')
							);
							$proxyNodeProcessService = implode("\n", $proxyNodeProcessService);
							file_put_contents('/etc/systemd/system/' . $proxyNodeProcessType . '_' . $proxyNodeProcessId . '.service', $proxyNodeProcessService);
							$proxyNodeProcessConfiguration['a8'] = 'pidfile /var/run/3proxy/' . $proxyNodeProcessType . '_' . $proxyNodeProcessId . '.pid';
							$proxyNodeProcessConfiguration['f'] = $proxyNodeProcessInterfaceConfigurations['f'] . ' -p' . $proxyNodeProcessPortNumber;
							$proxyNodeProcessConfiguration['g'] = $proxyNodeProcessInterfaceConfigurations['g'] . ' -p' . $proxyNodeProcessPortNumber;
							$proxyNodeProcessConfiguration = implode("\n", $proxyNodeProcessConfiguration);
							file_put_contents('/etc/3proxy/' . $proxyNodeProcessType . '_' . $proxyNodeProcessId . '.cfg', $proxyNodeProcessConfiguration);
							chmod('/etc/3proxy/' . $proxyNodeProcessType . '_' . $proxyNodeProcessId . '.cfg', 0755);
							shell_exec('sudo ' . $parameters['binary_files']['systemctl'] . ' daemon-reload');
							unlink('/var/run/3proxy/' . $proxyNodeProcessType . '_' . $proxyNodeProcessId . '.pid');
							// todo: add default node timeout column to wait for X seconds before proceeding with processing after processes start + stop
							$proxyNodeProcessResponse = false;

							while ($proxyNodeProcessResponse === false) {
								$proxyNodeProcessResponse = _verifyNodeProcess($parameters['binary_files'], $parameters['data']['next']['node_reserved_internal_destinations'][$proxyNodeProcessNodeId][$proxyNodeIpAddressVersionNumber]['ip_address'], $proxyNodeIpAddressVersionNumber, $proxyNodeProcessPortNumber, $proxyNodeProcessType) === false);
								sleep(1);
							}

							$proxyNodeProcessResponse = false;

							if ($proxyNodeProcessesStart === true) {
								while ($proxyNodeProcessResponse === false) {
									shell_exec('sudo ' . $parameters['binary_files']['service'] . ' ' . $proxyNodeProcessType . '_' . $proxyNodeProcessId . ' start');
									$proxyNodeProcessResponse = _verifyNodeProcess($parameters['binary_files'], $parameters['data']['next']['node_reserved_internal_destinations'][$proxyNodeProcessNodeId][$proxyNodeIpAddressVersionNumber]['ip_address'], $proxyNodeIpAddressVersionNumber, $proxyNodeProcessPortNumber, $proxyNodeProcessType) === true);
									sleep(1);
								}
							} else {
								$systemActionProcessNodeParameters['data']['processed_status'] = '0';
							}

							if (file_exists('/var/run/3proxy/' . $proxyNodeProcessType . '_' . $proxyNodeProcessId . '.pid') === true) {
								$proxyNodeProcessProcessId = file_get_contents('/var/run/3proxy/' . $proxyNodeProcessType . '_' . $proxyNodeProcessId . '.pid');

								if (is_numeric($proxyNodeProcessProcessId) === true) {
									shell_exec('sudo ' . $parameters['binary_files']['prlimit'] . ' -p ' . $proxyNodeProcessProcessId . ' -n1000000000');
									shell_exec('sudo ' . $parameters['binary_files']['prlimit'] . ' -p ' . $proxyNodeProcessProcessId . ' -n=1000000000');
									shell_exec('sudo ' . $parameters['binary_files']['prlimit'] . ' -p ' . $proxyNodeProcessProcessId . ' -s"unlimited"');
									shell_exec('sudo ' . $parameters['binary_files']['prlimit'] . ' -p ' . $proxyNodeProcessProcessId . ' -s=unlimited');
								}
							}
						}
					}
				}
			}
		}

		$nodeProcessTypeFirewallRuleSetsToDestroy = $parameters['node_process_type_firewall_rule_sets'];
		$parameters['node_process_type_firewall_rule_sets'] = array();

		foreach ($parameters['data']['next']['node_process_types'] as $nodeProcessType) {
			$parameters['node_process_type_process_part_data_keys'][$nodeProcessType] = array(
				'next',
				'next'
			);
		}

		$parameters['processing_progress_checkpoints'] = _updateNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);
		$parameters = _processFirewall($parameters);

		foreach ($nodeProcessTypeFirewallRuleSetsToDestroy as $nodeProcessTypeFirewallRuleSetToDestroy) {
			shell_exec('sudo ' . $parameters['binary_files']['ipset'] . ' destroy ' . $nodeProcessTypeFirewallRuleSetToDestroy);
		}

		foreach ($parameters['ip_address_versions'] as $ipAddressVersionNumber => $ipAddressVersion) {
			if (empty($nodeIpAddressesToDelete[$ipAddressVersionNumber]) === false) {
				foreach ($nodeIpAddressesToDelete[$ipAddressVersionNumber] as $nodeIpAddressToDelete) {
					shell_exec('sudo ' . $parameters['binary_files']['ip'] . ' -' . $ipAddressVersionNumber . ' addr delete ' . $nodeIpAddressToDelete . '/' . $ipAddressVersion['network_mask'] . ' dev ' . $parameters['interface_name']) . '\');';
				}
			}

			foreach ($parameters['data']['current']['node_reserved_internal_destination_ip_addresses'][$ipAddressVersionNumber] as $nodeReservedInternalDestinationIpAddress) {
				if (empty($parameters['data']['next']['node_reserved_internal_destination_ip_addresses'][$ipAddressVersionNumber][$nodeReservedInternalDestinationIpAddress]) === true) {
					shell_exec('sudo ' . $parameters['binary_files']['ipset'] . ' del _ ' . $nodeReservedInternalDestinationIpAddress);
				}
			}
		}

		$parameters['processing_progress_checkpoints'] = _updateNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);

		foreach ($nodeProcessesToRemove as $nodeProcessType => $nodeProcessIds) {
			$nodeProcessProcessIds = array();

			foreach ($nodeProcessIds as $nodeProcessId) {
				switch ($nodeProcessType) {
					case 'http_proxy':
					case 'socks_proxy':
						if (file_exists('/var/run/3proxy/' . $nodeProcessType . '_' . $nodeProcessId . '.pid') === true) {
							$nodeProcessProcessIds[] = file_get_contents('/var/run/3proxy/' . $nodeProcessType . '_' . $nodeProcessId . '.pid');
						}

						unlink('/bin/' . $nodeProcessType . '_' . $nodeProcessId);
						unlink('/etc/3proxy/' . $nodeProcessType . '_' . $nodeProcessId . '.cfg');
						unlink('/etc/systemd/system/' . $nodeProcessType . '_' . $nodeProcessId . '.service');
						unlink('/var/run/3proxy/' . $nodeProcessType . '_' . $nodeProcessId . '.pid');
						break;
					case 'recursive_dns':
						if (file_exists('/var/run/named/' . $nodeProcessType . '_' . $nodeProcess['id'] . '.pid') === true) {
							$nodeProcessProcessIds[] = file_get_contents('/var/run/named/' . $nodeProcessType . '_' . $nodeProcess['id'] . '.pid');
						}

						shell_exec('sudo rm -rf /etc/' . $nodeProcessType . '_' . $nodeProcessId . ' /var/cache/' . $nodeProcessType . '_' . $nodeProcessId);
						unlink('/etc/default/' . $recursiveDnsNodeProcessDefaultServiceName . '_' . $nodeProcessType . '_' . $nodeProcessId);
						unlink('/lib/systemd/system/' . $recursiveDnsNodeProcessDefaultServiceName . '_' . $nodeProcessType . '_' . $nodeProcessId . '.service');
						unlink('/usr/sbin/' . $nodeProcessType . '_' . $nodeProcessId);
						unlink('/var/run/named/' . $nodeProcessType . '_' . $nodeProcessId . '.pid');
						break;
				}
			}

			if (empty($nodeProcessProcessIds) === false) {
				$nodeProcessProcessIds = array_filter($nodeProcessProcessIds);
				_killProcessIds($parameters['binary_files'], $parameters['action'], $parameters['process_id'], $nodeProcessProcessIds);
			}
		}

		$parameters['processing_progress_checkpoints'] = _updateNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);
		$parameters['data']['current'] = array_intersect_key($parameters['data']['next'], array(
			'node_processes' => true,
			'node_process_type_firewall_rule_set_reserved_internal_destinations' => true,
			'node_process_types' => true,
			'node_recursive_dns_destinations' => true,
			'node_reserved_internal_destination_ip_addresses' => true,
			'node_reserved_internal_destinations' => true,
			'node_reserved_internal_sources' => true,
			'node_ssh_port_numbers' => true
		));
		$parameters['data']['current']['node_process_type_firewall_rule_set_port_numbers'] = $parameters['node_process_type_firewall_rule_set_port_numbers'][4]['next'];
		$encodedSystemActionProcessNodeResponse = json_encode($parameters['data']['current']);

		if (
			($encodedSystemActionProcessNodeResponse === false) ||
			(file_put_contents('/usr/local/nodecompute/system_action_process_node_current_response.json', $encodedSystemActionProcessNodeResponse) === false)
		) {
			$response['message'] = 'Error processing node, please try again.';
			return $response;
		}

		if (isset($systemActionProcessNodeParameters['data']['processed_status']) === false) {
			$systemActionProcessNodeParameters['data']['processed_status'] = '1';
		}

		$systemActionProcessNodeParameters['data']['processing_status'] = '0';
		_updateNodeProcessingProgress($parameters['binary_files'], $parameters['process_id'], $parameters['processing_progress_checkpoints'], $parameters['processing_progress_checkpoint_count'], $systemActionProcessNodeParameters, $parameters['system_endpoint_destination_address']);
		unlink('/usr/local/nodecompute/system_action_process_node_next_response.json');
		unset($systemActionProcessNodeResponse['data']);
		$response = $systemActionProcessNodeResponse;
		return $response;
	}

	function _updateNodeProcessingProgress($binaryFiles, $processId, $processingProgressCheckpoints, $processingProgressCheckpointCount, $systemActionProcessNodeParameters, $systemEndpointDestinationAddress) {
		exec('ps -h -o pid -o cmd $(pgrep php) | grep "node_endpoint.php node_action_process_node_processes" | awk \'{print $1}\'', $nodeProcessProcessIds);

		if (
			(empty($nodeProcessProcessIds[2]) === false) &&
			((current($processingProgressCheckpoints) === 'listing_node_parameters') === true)
		) {
			exit;
		}

		$processingProgressCheckpointIndex = key($processingProgressCheckpoints);
		$systemActionProcessNodeParameters['data'] += array(
			'processing_progress_checkpoint' => $processingProgressCheckpoints[$processingProgressCheckpointIndex],
			'processing_progress_percentage' => ceil((($processingProgressCheckpointIndex + 1) / $processingProgressCheckpointCount) * 100)
		);
		unset($processingProgressCheckpoints[$processingProgressCheckpointIndex]);
		$encodedSystemActionProcessNodeParameters = json_encode($systemActionProcessNodeParameters);

		if (empty($encodedSystemActionProcessNodeParameters) === false) {
			shell_exec('sudo ' . $binaryFiles['wget'] . ' -O /usr/local/nodecompute/system_action_process_node_processing_status_' . $processId . '_response.json --no-dns-cache --post-data \'json=' . $encodedSystemActionProcessNodeParameters . '\' --timeout=10 ' . $systemEndpointDestinationAddress . '/system_endpoint.php');
		}

		if (file_exists('/usr/local/nodecompute/system_action_process_node_processing_status_' . $processId . '_response.json') === true) {
			$systemActionProcessNodeProcessingStatusResponse = file_get_contents('/usr/local/nodecompute/system_action_process_node_processing_status_' . $processId . '_response.json');
			$systemActionProcessNodeProcessingStatusResponse = json_decode($systemActionProcessNodeResponse, true);

			if (empty($systemActionProcessNodeProcessingStatusResponse['data']['processing_progress_override_status']) === false) {
				exec('ps -h -o pid -o cmd $(pgrep php) | grep "node_endpoint.php node_action_process_node_processes" | awk \'{print $1}\'', $nodeProcessProcessIds);
				_killProcessIds($binaryFiles, 'process_node_processes', $processId, $nodeProcessProcessIds);
				exit;
			} elseif (
				(empty($nodeProcessProcessIds[1]) === false) &&
				((current($processingProgressCheckpoints) === 'listing_node_parameters') === true)
			) {
				exit;
			}
		}

		return $processingProgressCheckpoints;
	}

	function _verifyNodeProcess($binaryFiles, $nodeProcessNodeIpAddress, $nodeProcessNodeIpAddressVersionNumber, $nodeProcessPortNumber, $nodeProcessType) {
		$response = false;

		switch ($nodeProcessType) {
			case 'bitcoin_cryptocurrency':
				break;
			case 'http_proxy':
			case 'socks_proxy':
				$nodeProcessTypeParameters = array(
					'http_proxy' => '-x',
					'socks_proxy' => '--socks5-hostname'
				);
				exec('sudo ' . $binaryFiles['curl'] . ' -' . $nodeProcessNodeIpAddressVersionNumber . ' ' . $nodeProcessTypeParameters[$nodeProcessType] . ' ' . $nodeProcessNodeIpAddress . ':' . $nodeProcessPortNumber . ' http://nodecompute -v --connect-timeout 2 | grep " refused" 1 2>&1', $proxyNodeProcessResponse);
				$response = (empty($proxyNodeProcessResponse) === true);
				break;
			case 'monero_cryptocurrency':
				break;
			case 'recursive_dns':
				// todo: add dig to $parameters['binary_files']
				exec('dig -' . $nodeProcessNodeIpAddressVersionNumber . ' +time=2 +tries=1 nodecompute @' . $nodeProcessNodeIpAddress . ' -p ' . $nodeProcessPortNumber . ' | grep "Got answer" 2>&1', $recursiveDnsNodeProcessResponse);
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

	if (($parameters['action'] === 'process_node_processes') === true) {
		$response = _processNodeProcesses($parameters, $response);
	}
?>
