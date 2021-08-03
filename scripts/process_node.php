<?php
	class Process {

		public $parameters;

		public function __construct($parameters) {
			$this->parameters = $parameters;
		}

		public function process() {
			$this->_sendNodeRequestLogData();

			if (empty($this->nodeData['nodes'])) {
				return;
			}

			$nodeIpVersions = array(
				'4' => array(
					'network_mask' => '/32',
					'type' => 'inet'
				),
				'6' => array(
					'network_mask' => '/128',
					'type' => 'inet6'
				)
			);

			foreach ($nodeIpVersions as $nodeIpVersion => $nodeIpVersionData) {
				exec('sudo ' . $this->nodeData['binary_files']['ip'] . ' addr show dev ' . $this->nodeData['interface_name'] . ' | grep "' . $nodeIpVersionData['type'] . ' " | grep "' . $nodeIpVersionData['network_mask'] . ' " | awk \'{print substr($2, 0, length($2) - ' . ($nodeIpVersion / 2) . ')}\'', $existingInterfaceNodeIps);
				$existingInterfaceNodeIps = current($existingInterfaceNodeIps);
				$interfaceNodeIpFileContents = array(
					'<?php'
				);
				$interfaceNodeIpsToProcess = array(
					'add' => array_diff($this->nodeData['node_ip'][$nodeIpVersion], $existingInterfaceNodeIps),
					'delete' => array_diff($existingInterfaceNodeIps, $this->nodeData['node_ip'][$nodeIpVersion])
				);

				foreach ($interfaceNodeIpsToProcess as $interfaceNodeIpAction => $interfaceNodeIps) {
					$interfaceNodeIpAction = substr($interfaceNodeIpAction, 3);

					foreach ($interfaceNodeIps as $interfaceNodeIp) {
						$command = 'sudo ' . $this->nodeData['binary_files']['ip'] . ' -' . $nodeIpVersion . ' addr ' . $interfaceNodeIpAction . ' ' . $interfaceNodeIp . $nodeIpVersionData['network_mask'] . ' dev ' . $this->nodeData['interface_name'];
						shell_exec($command);

						if ($interfaceNodeIpAction === 'add') {
							$interfaceNodeIpFileContents[] = 'shell_exec(\'' . $command . '\');';
						}
					}
				}
			}

			file_put_contents('/usr/local/ghostcompute/node_interfaces.php', implode("\n", $interfaceNodeIps));
			$nodeIpVersions = array_keys($nodeIpVersions);
			$proxyNodeConfiguration = array(
				'maxconn 20000',
				'nobandlimin',
				'nobandlimout',
				'nameserver_ip_version_4' => false,
				'nameserver_ip_version_6' => false,
				'process_id' => false,
				'stacksize 0',
				'flush',
				'allow * * * * HTTP',
				'allow * * * * HTTPS',
				'log' => false
			);
			$proxyNodeProcessTypes = array(
				'proxy' => 'http_proxy',
				'socks' => 'socks_proxy'
			);

			foreach ($proxyNodeProcessTypes as $proxyNodeProcessTypeServiceName => $proxyNodeProcessType) {
				if (empty($this->nodeData['data']['node_processes'][$proxyNodeProcessType]) === false) {
					$proxyNodeConfiguration['log'] = 'log /var/log/' . $proxyNodeProcessType;
					$proxyNodeUsers = array();

					foreach ($this->nodeData['node_users'][$proxyNodeProcessType] as $proxyNodeId => $proxyNodeUserIds) {
						$proxyNode = $this->nodeData['nodes'][$proxyNodeId];
						$proxyNodeIpVersionPriority = 46;

						foreach ($nodeIpVersions as $nodeIpVersion) {
							if (empty($proxyNode['external_ip_version_' . $nodeIpVersion]) === true) {
								$proxyNodeIpVersionPriority = 10 - $nodeIpVersion;
							}
						}

						$proxyNodeUserAuthentication[] = 'auth iponly strong';
						$proxyNodeUserAuthenticationUsernames = $proxyNodeUserAuthenticationWhitelists = array();
						$proxyNodeUsers = $this->nodeData['users'][$proxyNodeProcessType];

						foreach ($proxyNodeUserIds as $proxyNodeUserId) {
							$proxyNodeUser = $proxyNodeUsers[$proxyNodeUserId];

							if (
								(
									(empty($proxyNodeUser['status_allowing_request_destinations_only']) === true) ||
									(empty($proxyNodeUser['request_destination_id']) === false)
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
									}, array_chunk($proxyNodeUserDestinations, 10);
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

						$proxyNodeUserAuthentication['_' . $proxyNodeId] = false;
						$proxyNodeUserAuthentication[] = 'deny *';
						$proxyNodeUserAuthentication[] = 'flush';
					}

					foreach ($this->nodeData['users'][$proxyNodeProcessType] as $proxyNodeUser) {
						$proxyNodeUsers[$proxyNodeUser['authentication_username']] = $proxyNodeUser['authentication_username'] . ':CL:' . $proxyNodeUser['authentication_password'];
					}

					foreach (array_chunk($proxyNodeUsers, 10) as $nodeProxyUserPartKey => $proxyNodeUserParts) {
						$proxyNodeUsers[$proxyNodeUserPartKey] = 'users ' . implode(' ', $proxyNodeUserParts);
					}

					$this->nodeData['proxy_node_configuration'][$proxyNodeType] = array_merge($proxyNodeConfiguration, $proxyNodeUsers, $proxyNodeUserAuthentication, array(
						'deny *'
					));
				}
			}

			$this->_sendNodeRequestLogData();
			$this->_verifyNameserverProcesses();
			$this->_verifyProxyProcesses(); // ..
			$firewallRulePorts = $serverData = array();
			$firewallRulePortsFile = $this->rootPath . 'cache/ports';

			if (
				// ..
			) {
				foreach ($this->decodedServerData['proxy_process_ports'] as $proxyProcessPort) {
					if ($this->_verifyProxyPort($proxyProcessPort) === true) {
						$firewallRulePorts[] = $proxyProcessPort;
					}
				}

				if (
					!empty($firewallRulePorts) &&
					file_exists($firewallRulePortsFile) &&
					($firewallRulePortData = (integer) file_get_contents($firewallRulePortsFile)) &&
					$firewallRulePortData === array_sum($firewallRulePorts)
				) {
					exit;
				}

				$firewallRulePorts = array();
			}

			$decodedServerData = json_decode($serverData, true);
			$nameserverIpsFile = $this->rootPath . 'cache/nameserver_ips';
			$nameserverIpsIdentifier = $firewallRulePortsIdentifier = 0;
			$nameserverProcessesToRemove = $proxyProcessesToRemove = array();

			foreach ($this->decodedServerData['nameserver_process_external_ips'] as $nameserverListeningIp => $nameserverSourceIps) {
				$nameserverIpsIdentifier += ip2long($nameserverListeningIp);

				foreach ($nameserverSourceIps as $nameserverSourceIp) {
					$nameserverIpsIdentifier += ip2long($nameserverSourceIp);
					$nameserverProcesses[ip2long($nameserverSourceIp) . '_' . ip2long($nameserverListeningIp)] = array(
						'listening_ip' => $nameserverListeningIp,
						'source_ip' => $nameserverSourceIp
					);
				}
			}

			if (
				!empty($nameserverIpsIdentifier) &&
				(
					!file_exists($nameserverIpsFile) ||
					(
						file_exists($nameserverIpsFile) &&
						($nameserverIpData = (integer) file_get_contents($nameserverIpsFile)) &&
						$nameserverIpData !== $nameserverIpsIdentifier
					)
				)
			) {
				if (!empty($decodedServerData['nameserver_process_external_ips'])) {
					foreach ($decodedServerData['nameserver_process_external_ips'] as $nameserverListeningIp => $nameserverSourceIps) {
						foreach ($nameserverSourceIps as $nameserverSourceIp) {
							$nameserverProcessName = long2ip($nameserverSourceIp) . '_' . long2ip($nameserverListeningIp);

							if (empty($nameserverProcesses[$nameserverProcessName])) {
								$nameserverProcessesToRemove[$nameserverProcessName] = current($this->fetchProcessIds('named', 'named_' . $nameserverProcessName . ' -f -c'));
							}
						}
					}
				}

				foreach ($nameserverProcesses as $nameserverProcessName => $nameserverProcessIps) {
					if (empty($this->fetchProcessIds('named', 'named_' . $nameserverProcessName . ' -f -c'))) {
						$this->_createNameserverProcess($nameserverProcessIps['listening_ip'], $nameserverProcessIps['source_ip']);
					}
				}
			}

			$this->_optimizeKernel();
			$allProxyProcessPorts = array();

			if (!empty($decodedServerData['proxy_process_ports'])) {
				foreach ($decodedServerData['proxy_process_ports'] as $proxyProcessPort) {
					if (empty($this->decodedServerData['proxy_process_ports'][$proxyProcessPort])) {
						$proxyProcessName = 'socks_' . $proxyProcessPort;
						$proxyProcessesToRemove[$proxyProcessName] = current($this->fetchProcessIds($proxyProcessName, $proxyProcessName . '.cfg'));
					}
				}
			}

			$proxyProcessPortParts = array_chunk($this->decodedServerData['proxy_process_ports'], round(count($this->decodedServerData['proxy_process_ports']) / 2), false);

			foreach (array(0, 1) as $proxyProcessPortPartKey) {
				foreach ($proxyProcessPortParts[$proxyProcessPortPartKey] as $proxyProcessPort) {
					$allProxyProcessPorts[] = $proxyProcessPort;

					if (!file_exists('/etc/3proxy/socks_' . $proxyProcessPort . '.cfg')) {
						$this->_createProxyProcess($proxyProcessPort);
					}
				}
			}

			foreach (array(0, 1) as $proxyProcessPortPartKey) {
				foreach ($proxyProcessPortParts[$proxyProcessPortPartKey] as $proxyProcessPort) {
					if ($this->_verifyProxyPort($proxyProcessPort)) {
						$firewallRulePorts[] = $proxyProcessPort;
					}
				}

				$this->_applyFirewall($firewallRulePorts);
				$firewallRulePorts = array();
				$proxyProcessPorts = $proxyProcessPortParts[($proxyProcessPortPartKey ? 0 : 1)];

				foreach ($proxyProcessPorts as $proxyProcessPort) {
					$this->_connect($proxyProcessPort);
				}
			}

			foreach ($allProxyProcessPorts as $proxyProcessPort) {
				if ($this->_verifyProxyPort($proxyProcessPort)) {
					$firewallRulePorts[] = $proxyProcessPort;
					$firewallRulePortsIdentifier += $proxyProcessPort;
				}
			}

			$this->_applyFirewall($firewallRulePorts);

			foreach (array_keys($nameserverProcessesToRemove) as $nameserverProcessName) {
				$this->_removeNameserverProcess($nameserverProcessName);
			}

			foreach (array_keys($proxyProcessesToRemove) as $proxyProcessName) {
				$this->_removeProxyProcess($proxyProcessName);
			}

			$this->_killProcessIds(array_merge($nameserverProcessesToRemove, $proxyProcessesToRemove));
			$this->_verifyNameserverProcesses();
			file_put_contents($firewallRulePortsFile, $firewallRulePortsIdentifier);
			file_put_contents($nameserverIpsFile, $nameserverIpsIdentifier);
			$this->_optimizeProcesses();
			// ..
			exec('sudo curl -s --form-string "json={\"action\":\"process\",\"data\":{\"processed\":true}}" ' . $this->parameters['system_url'] . '/endpoint/nodes 2>&1', $response);
			$response = json_decode(current($response), true);
			return $response;
		}

		protected function _applyFirewall($proxyProcessPorts) {
			if (empty($proxyProcessPorts)) {
				return;
			}

			$firewallRules = array(
				'*filter',
				':INPUT ACCEPT [0:0]',
				':FORWARD ACCEPT [0:0]',
				':OUTPUT ACCEPT [0:0]',
				'-A INPUT -p icmp -m hashlimit --hashlimit-above 1/second --hashlimit-burst 2 --hashlimit-htable-gcinterval 100000 --hashlimit-htable-expire 10000 --hashlimit-mode srcip --hashlimit-name icmp --hashlimit-srcmask 32 -j DROP'
			);

			if (
				!empty($this->sshPorts) &&
				is_array($this->sshPorts)
			) {
				foreach ($this->sshPorts as $sshPort) {
					$firewallRules[] = '-A INPUT -p tcp --dport ' . $sshPort . ' -m hashlimit --hashlimit-above 1/minute --hashlimit-burst 10 --hashlimit-htable-gcinterval 600000 --hashlimit-htable-expire 60000 --hashlimit-mode srcip --hashlimit-name ssh --hashlimit-srcmask 32 -j DROP';
				}
			}

			$nameserverProcessLoadBalanceIps = array();
			$nameserverProcessLoadBalanceIpKeys = array(
				'nameserver_process_external_ips',
				'nameserver_process_internal_ips'
			);
			$firewallRules[] = 'COMMIT';
			$firewallRules[] = '*nat';
			$firewallRules[] = ':PREROUTING ACCEPT [0:0]';
			$firewallRules[] = ':INPUT ACCEPT [0:0]';
			$firewallRules[] = ':OUTPUT ACCEPT [0:0]';
			$firewallRules[] = ':POSTROUTING ACCEPT [0:0]';

			foreach ($nameserverProcessLoadBalanceIpKeys as $nameserverProcessLoadBalanceIpKey) {
				foreach ($this->decodedServerData[$nameserverProcessLoadBalanceIpKey] as $sourceIp => $destinationIps) {
					if (count($destinationIps) > 1) {
						if ($nameserverProcessLoadBalanceIpKey === 'nameserver_process_external_ips') {
							$destinationIps = array_values($destinationIps);
							krsort($destinationIps);
						} else {
							$sourceIp = ' ' . current($destinationIps);
						}

						$nameserverProcessLoadBalanceIps[$sourceIp] = $destinationIps;
					}
				}
			}

			foreach ($nameserverProcessLoadBalanceIps as $sourceIp => $destinationIps) {
				$nameserverProcessLoadBalanceSourceIpParts = array(
					array(
						$sourceIp
					)
				);

				if ($sourceIp !== trim($sourceIp)) {
					$nameserverProcessLoadBalanceSourceIpParts = array_chunk($destinationIps, 10);
				}

				foreach ($nameserverProcessLoadBalanceSourceIpParts as $nameserverProcessLoadBalanceSourceIps) {
					$destinationIps = array_values($destinationIps);
					krsort($destinationIps);

					foreach ($destinationIps as $destinationIpKey => $destinationIp) {
						$loadBalancer = $destinationIpKey > 0 ? '-m statistic --mode nth --every ' . ($destinationIpKey + 1) . ' --packet 0 ' : '';

						foreach ($nameserverProcessLoadBalanceSourceIps as $nameserverProcessLoadBalanceSourceIp) {
							// todo: change dport to dports to allow custom DNS ports
							$firewallRules[] = '-A OUTPUT -d ' . $nameserverProcessLoadBalanceSourceIp . ' -p udp --dport 53 ' . $loadBalancer . '-j DNAT --to-destination ' . $destinationIp;
						}
					}
				}
			}

			krsort($proxyProcessPorts);
			$proxyProcessPortParts = array_chunk($this->decodedServerData['proxy_process_ports'], 10);

			foreach ($proxyProcessPortParts as $proxyProcessPortPartPorts) {
				foreach ($proxyProcessPorts as $proxyProcessPortKey => $proxyProcessPort) {
					$loadBalancer = $proxyProcessPortKey > 0 ? '-m statistic --mode nth --every ' . ($proxyProcessPortKey + 1) . ' --packet 0 ' : '';
					$protocols = array(
						'tcp',
						'udp'
					);

					foreach ($protocols as $protocol) {
						$firewallRules[] = '-A PREROUTING -p ' . $protocol . ' -m multiport ! -s ' . $this->decodedServerData['server']['ip'] . ' --dports ' . implode(',', $proxyProcessPortPartPorts) . ' ' . $loadBalancer . ' -j DNAT --to-destination :' . $proxyProcessPort . ' --persistent';
					}
				}
			}

			$firewallRules[] = 'COMMIT';
			$firewallRules[] = '*raw';
			$firewallRules[] = ':PREROUTING ACCEPT [0:0]';
			$firewallRules[] = ':OUTPUT ACCEPT [0:0]';
			$reservedIpRanges = array(
				'0.0.0.0/8',
				'10.0.0.0/8',
				'100.64.0.0/10',
				'127.0.0.0/8',
				'172.16.0.0/12',
				'192.0.0.0/24',
				'192.0.2.0/24',
				'192.88.99.0/24',
				'192.168.0.0/16',
				'198.18.0.0/15',
				'198.51.100.0/24',
				'203.0.113.0/24',
				'224.0.0.0/4',
				'240.0.0.0/4',
				'255.255.255.255/32'
			);

			foreach ($reservedIpRanges as $reservedIpRange) {
				$firewallRules[] = '-A PREROUTING ! -i lo -s ' . $reservedIpRange . ' -j DROP';
			}

			$firewallRules[] = 'COMMIT';
			$firewallRuleParts = array_chunk($firewallRules, 1000);
			$firewallRulesFile = $this->rootPath . 'cache/firewall';

			if (file_exists($firewallRulesFile)) {
				unlink($firewallRulesFile);
			}

			touch($firewallRulesFile);

			foreach ($firewallRuleParts as $firewallRulePart) {
				$saveFirewallRules = implode("\n", $firewallRulePart);
				shell_exec('sudo echo "' . $saveFirewallRules . '" >> ' . $firewallRulesFile);
			}

			shell_exec('sudo ' . $this->binaryFiles['iptables-restore'] . ' < ' . $firewallRulesFile);
			sleep(1 * count($firewallRuleParts));
			return;
		}

		protected function _connect($proxyProcessPort) {
			$proxyProcessName = 'socks_' . $proxyProcessPort;
			$proxyProcessIds = $this->fetchProcessIds($proxyProcessName, '/etc/3proxy/' . $proxyProcessName . '.cfg');

			if (!empty($proxyProcessIds)) {
				$this->_killProcessIds($proxyProcessIds);
			}

			if (file_exists('/var/run/3proxy/' . $proxyProcessName . '.pid')) {
				unlink('/var/run/3proxy/' . $proxyProcessName . '.pid');
			}

			$this->_createProxyProcess($proxyProcessPort);
			$proxyProcessEnded = false;
			$proxyProcessEndedTime = time();

			while ($proxyProcessEnded === false) {
				$proxyProcessEnded = true;

				if (
					$this->_verifyProxyPort($proxyProcessPort) === true ||
					(time() - $proxyProcessEndedTime) > 60
				) {
					$proxyProcessEnded = false;
					break;
				}

				sleep(1);
			}

			$proxyProcessStarted = false;
			$proxyProcessStartedTime = time();

			while ($proxyProcessStarted === false) {
				shell_exec('sudo ' . $this->binaryFiles['service'] . ' ' . $proxyProcessName . ' start');
				sleep(1);

				if (
					$this->_verifyProxyPort($proxyProcessPort) === true ||
					(time() - $proxyProcessStartedTime) > 60
				) {
					$proxyProcessStarted = true;
					break;
				}
			}

			$this->_verifyNameserverProcesses();
			return;
		}

		protected function _createNameserverProcess($nameserverListeningIp, $nameserverSourceIp) {
			// todo: create views for nameserver process authentication
			$nameserverProcessName = ip2long($nameserverSourceIp) . '_' . ip2long($nameserverListeningIp);
			$this->_removeNameserverProcess($nameserverProcessName);
			$commands = array(
				'cd /usr/sbin && sudo ln /usr/sbin/named named_' . $nameserverProcessName . ' && sudo cp /lib/systemd/system/' . $this->nameserverServiceName . '.service /lib/systemd/system/' . $this->nameserverServiceName . '_' . $nameserverProcessName . '.service',
				'sudo cp /etc/default/' . $this->nameserverServiceName . ' /etc/default/' . $this->nameserverServiceName . '_' . $nameserverProcessName,
				'sudo cp -r /etc/bind /etc/bind_' . $nameserverProcessName,
				'sudo mkdir -m 0775 /var/cache/bind_' . $nameserverProcessName
			);

			foreach ($commands as $command) {
				shell_exec($command);
			}

			$namedConfigurationContents = array(
				'include "/etc/bind_' . $nameserverProcessName . '/named.conf.options";',
				'include "/etc/bind_' . $nameserverProcessName . '/named.conf.local";',
				'include "/etc/bind_' . $nameserverProcessName . '/named.conf.default-zones";'
			);
			$namedConfigurationOptionContents = array(
				'acl internal {',
				'0.0.0.0/8;',
				'10.0.0.0/8;',
				'100.64.0.0/10;',
				'127.0.0.0/8;',
				'172.16.0.0/12;',
				'192.0.0.0/24;',
				'192.0.2.0/24;',
				'192.88.99.0/24;',
				'192.168.0.0/16;',
				'198.18.0.0/15;',
				'198.51.100.0/24;',
				'203.0.113.0/24;',
				'224.0.0.0/4;',
				'240.0.0.0/4;',
				'255.255.255.255/32;',
				'};',
				'options {',
				'allow-query {',
				'internal;',
				'};',
				'allow-recursion {',
				'internal;',
				'};',
				'auth-nxdomain yes;',
				'cleaning-interval 10;',
				'directory "/var/cache/bind_' . $nameserverProcessName . '";',
				'dnssec-enable yes;',
				'dnssec-must-be-secure mydomain.local no;',
				'dnssec-validation yes;',
				'empty-zones-enable no;',
				'filter-aaaa-on-v4 yes;',
				'lame-ttl 0;',
				'listen-on {',
				$nameserverListeningIp . '; ' . $nameserverSourceIp . ';',
				'};',
				'max-cache-ttl 1;',
				'max-ncache-ttl 1;',
				'max-zone-ttl 1;',
				'pid-file "/var/run/named/named_' . $nameserverProcessName . '.pid";',
				'query-source address ' . $nameserverSourceIp . ';',
				'resolver-query-timeout 10;',
				'tcp-clients 1000;',
				'};'
			);
			$systemdServiceContents = array(
				'[Unit]',
				'After=network.target',
				'[Service]',
				'ExecStart=/usr/sbin/named_' . $nameserverProcessName . ' -f ' . ($configurationFile = '-c /etc/bind_' . $nameserverProcessName . '/named.conf') . ' -4 -S 40000 -u root',
				'User=root',
				'[Install]',
				'WantedBy=multi-user.target'
			);
			file_put_contents('/etc/bind_' . $nameserverProcessName . '/named.conf', implode("\n", $namedConfigurationContents));
			file_put_contents('/etc/bind_' . $nameserverProcessName . '/named.conf.options', implode("\n", $namedConfigurationOptionContents));
			file_put_contents('/lib/systemd/system/' . $this->nameserverServiceName . '_' . $nameserverProcessName . '.service', implode("\n", $systemdServiceContents));
			$commands = array(
				'sudo ' . $this->binaryFiles['systemctl'] . ' daemon-reload',
				'sudo ' . $this->binaryFiles['service'] . ' ' . $this->nameserverServiceName . '_' . $nameserverProcessName . ' start',
				'sleep 10'
			);

			foreach ($commands as $command) {
				shell_exec($command);
			}

			return;
		}

		protected function _createProxyProcess($proxyProcessPort) {
			// create different http and socks processes with destination url rules included, chunk each rule by 10 destinations and 10 whitelisted source IPs
			$proxyProcessConfiguration = $this->decodedServerData['proxy_configuration'];
			$proxyProcessConfiguration['process_id'] = 'pidfile /var/run/3proxy/' . ($proxyProcessName = 'socks_' . $proxyProcessPort) . '.pid';
			$proxyProcessConfigurationPath = '/etc/3proxy/' . $proxyProcessName . '.cfg';

			foreach ($this->decodedServerData['proxies'] as $proxyIp) {
				$proxyProcessConfiguration[$proxyIp] = 'socks -a -e' . $proxyIp . ' -i' . $proxyIp . ' -n -p' . $proxyProcessPort . ' -46';
			}

			shell_exec('cd /bin && sudo ln /bin/3proxy ' . $proxyProcessName);
			$systemdServiceContents = array(
				'[Unit]',
				'After=network.target',
				'[Service]',
				'ExecStart=/bin/' . $proxyProcessName . ' ' . $proxyProcessConfigurationPath,
				'User=root',
				'[Install]',
				'WantedBy=multi-user.target'
			);
			file_put_contents('/etc/systemd/system/' . $proxyProcessName . '.service', implode("\n", $systemdServiceContents));
			file_put_contents($proxyProcessConfigurationPath, implode("\n", $proxyProcessConfiguration));
			$commands = array(
				'sudo chmod +x ' . $proxyProcessConfigurationPath,
				'sudo chown root:root ' . $proxyProcessConfigurationPath,
				'sudo chmod 0755 ' . $proxyProcessConfigurationPath,
				'sudo ' . $this->binaryFiles['systemctl'] . ' daemon-reload'
			);

			foreach ($commands as $command) {
				shell_exec($command);
			}

			return;
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
				'sudo ' . $this->binaryFiles['telinit'] . ' u'
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

		protected function _optimizeKernel() {
			// todo: revisit each setting to verify they're optimal
			// apply dynamic mem settings based on 10 min usage values
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
				'net.ipv6.conf.all.accept_redirects = 0',
				'net.ipv6.conf.all.accept_source_route = 0',
				'net.ipv6.conf.all.disable_ipv6 = 0',
				'net.ipv6.conf.all.forwarding = 0',
				'net.ipv6.ip6frag_high_thresh = 64000000',
				'net.ipv6.ip6frag_low_thresh = 32000000',
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
			shell_exec('sudo ' . $this->binaryFiles['sysctl'] . ' -p');
			exec('getconf PAGE_SIZE 2>&1', $kernelPageSize);
			exec('free | grep -v free | awk \'NR==1{print $2}\'', $totalSystemMemory);
			$kernelPageSize = current($kernelPageSize);
			$totalSystemMemory = current($totalSystemMemory);

			if (
				is_numeric($kernelPageSize) &&
				is_numeric($totalSystemMemory)
			) {
				$maximumMemoryBytes = ceil($totalSystemMemory * 0.34);
				$maximumMemoryPages = ceil($maximumMemoryBytes / $kernelPageSize);
				$dynamicKernelOptions = array(
					'kernel.shmall' => floor($totalSystemMemory / $kernelPageSize),
					'kernel.shmmax' => $totalSystemMemory,
					'net.core.optmem_max' => ($defaultSocketBufferMemoryBytes = ceil($maximumMemoryBytes * 0.10)),
					'net.core.rmem_default' => $defaultSocketBufferMemoryBytes,
					'net.core.rmem_max' => ($defaultSocketBufferMemoryBytes * 2),
					'net.ipv4.tcp_mem' => $maximumMemoryPages . ' ' . $maximumMemoryPages . ' ' . $maximumMemoryPages,
					'net.ipv4.tcp_rmem' => 1 . ' ' . $defaultSocketBufferMemoryBytes . ' ' . ($defaultSocketBufferMemoryBytes * 2)
				);
				$dynamicKernelOptions += array(
					'net.ipv4.tcp_wmem' => $dynamicKernelOptions['net.ipv4.tcp_rmem'],
					'net.ipv4.udp_mem' => $dynamicKernelOptions['net.ipv4.tcp_mem'],
					'net.core.wmem_default' => $dynamicKernelOptions['net.core.rmem_default'],
					'net.core.wmem_max' => $dynamicKernelOptions['net.core.rmem_max']
				);

				foreach ($dynamicKernelOptions as $dynamicKernelOptionKey => $dynamicKernelOptionValue) {
					shell_exec('sudo ' . $this->binaryFiles['sysctl'] . ' -w ' . $dynamicKernelOptionKey . '="' . $dynamicKernelOptionValue . '"');
				}
			}

			return;
		}

		protected function _optimizeProcesses() {
			$processIds = array_merge($this->fetchProcessIds('named'), $this->fetchProcessIds('socks', '3proxy'));

			foreach ($processIds as $processId) {
				shell_exec('sudo ' . $this->binaryFiles['prlimit'] . ' -p ' . $processId . ' -n1000000000');
				shell_exec('sudo ' . $this->binaryFiles['prlimit'] . ' -p ' . $processId . ' -n=1000000000');
				shell_exec('sudo ' . $this->binaryFiles['prlimit'] . ' -p ' . $processId . ' -s"unlimited"');
				shell_exec('sudo ' . $this->binaryFiles['prlimit'] . ' -p ' . $processId . ' -s=unlimited');
			}

			return;
		}

		protected function _removeNameserverProcess($nameserverProcessName) {
			$commands = array(
				'sudo rm /etc/default/' . $this->nameserverServiceName . '_' . $nameserverProcessName,
				'sudo rm /lib/systemd/system/' . $this->nameserverServiceName . '_' . $nameserverProcessName . '.service',
				'sudo rm /usr/sbin/named_' . $nameserverProcessName,
				'sudo rm /var/run/named/named_' . $nameserverProcessName . '.pid',
				'sudo rm -rf /etc/bind_' . $nameserverProcessName,
				'sudo rm -rf /var/cache/bind_' . $nameserverProcessName
			);

			foreach ($commands as $command) {
				shell_exec($command);
			}

			return;
		}

		protected function _removeProxyProcess($proxyProcessName) {
			$commands = array(
				'sudo rm /etc/systemd/system/' . $proxyProcessName . '.service',
				'sudo rm /bin/' . $proxyProcessName,
				'sudo rm /etc/3proxy/' . $proxyProcessName . '.cfg',
				'sudo rm /var/run/3proxy/' . $proxyProcessName . '.pid'
			);

			foreach ($commands as $command) {
				shell_exec($command);
			}

			return;
		}

		protected function _sendNodeRequestLogData() {
			if (file_exists($nodeRequestLogFile) === false) {
				return;
			}

			$nodeProcessTypes = array(
				'http_proxy',
				'nameserver',
				'socks_proxy'
			);

			foreach ($nodeProcessTypes as $nodeProcessType) {
				$nodeRequestLogFile = '/var/log/' . $nodeProcessType;

				if (file_exists($nodeRequestLogFile) === true) {
					exec('sudo curl -s --form "data=@' . $nodeRequestLogFile . '" --form-string "json={\"action\":\"archive\",\"data\":{\"type\":\"' . $nodeProcessType . '\"}}" ' . $this->parameters['system_url'] . '/endpoint/request-logs 2>&1', $response);
					$response = json_decode(current($response), true);

					if (empty($response['data']['most_recent_request_log']) === false) {
						$mostRecentNodeRequestLog = $response['data']['most_recent_request_log'];
						$nodeRequestLogFileContents = file_get_contents($proxyNodeRequestLogFile);
						$updateNodeRequestLogs = substr($nodeRequestLogFileContents, strpos($nodeRequestLogFileContents, $mostRecentNodeRequestLog) + strlen($mostRecentNodeRequestLog));
						file_put_contents($nodeRequestLogFile, trim($updatedNodeRequestLogs));
					}
				}
			}

			return;
		}

		protected function _verifyNameserverProcesses() {
			$serverData = file_exists($this->rootPath . 'cache/data') ? file_get_contents($this->rootPath . 'cache/data') : '';
			$decodedServerData = json_decode($serverData, true);

			if (empty($this->nameserverServiceName)) {
				$this->nameserverServiceName = is_dir('/etc/default/bind9') ? 'bind9' : 'named';
			}

			if (empty($decodedServerData['nameserver_process_external_ips'])) {
				return;
			}

			$nameserverIps = array();
			$nameserverProcessIps = $decodedServerData['nameserver_process_external_ips'];

			foreach ($nameserverProcessIps as $nameserverListeningIp => $nameserverSourceIps) {
				$nameserverDynamicIps = (count($nameserverSourceIps) > 1);

				foreach ($nameserverSourceIps as $nameserverSourceIp) {
					$nameserverProcesses = $nameserverResponse = array();
					exec('dig +time=2 +tries=2 +tcp proxies @' . ($nameserverDynamicIps ? $nameserverSourceIp : $nameserverListeningIp) . ' | grep "Got answer" 2>&1', $nameserverResponse);

					if (
						(
							empty($nameserverResponse[0]) ||
							strpos($nameserverResponse[0], 'Got answer') === false
						) &&
						in_array($nameserverListeningIp, $this->nameserverListeningIps)
					) {
						exec('ps -h -o pid -o cmd $(pgrep named) | grep ' . ($nameserverProcessName = ip2long($nameserverSourceIp) . '_' . ip2long($nameserverListeningIp)) . ' | grep -v grep | awk \'{print $1}\' 2>&1', $nameserverProcessId);

						if (!empty($nameserverProcessId)) {
							$this->_killProcessIds($nameserverProcessId);
							shell_exec('sudo rm /var/run/named/named_' . $nameserverProcessName . '.pid');
						}

						$this->_createNameserverProcess($nameserverListeningIp, $nameserverSourceIp);
						$this->_verifyNameserverProcesses();
					}
				}

				$nameserverIps[$nameserverListeningIp] = $nameserverListeningIp;
			}

			if (!empty($nameserverIps)) {
				$commands = array(
					'sudo rm /etc/resolv.conf && sudo touch /etc/nameservers.conf',
					'sudo ln -s /etc/nameservers.conf /etc/resolv.conf'
				);

				foreach ($commands as $command) {
					shell_exec($command);
				}

				file_put_contents('/etc/nameservers.conf', 'nameserver ' . key($nameserverIps));
			}

			return;
		}

		protected function _verifyProxyPort($proxyPort, $timeout = 2) {
			$response = false;
			exec('curl --socks5-hostname ' . $this->decodedServerData['server']['ip'] . ':' . $proxyPort . ' http://domain' . uniqid() . time() . ' -v --connect-timeout ' . $timeout . ' --max-time ' . $timeout . ' 2>&1', $proxyResponse);
			$proxyResponse = end($proxyResponse);
			$response = (strpos(strtolower($proxyResponse), 'empty ') !== false);
			return $response;
		}

		public function fetchProcessIds($processName, $processFile = false) {
			$processIds = array();
			exec('ps -h -o pid -o cmd $(pgrep ' . $processName . ') | grep "' . $processName . '" | grep -v grep 2>&1', $processes);

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

		public function processNodeData() {
			if (empty($this->nodeData) === true) {
				unlink($nodeProcessResponseFile);
				shell_exec('sudo wget -O ' . ($nodeProcessResponseFile = '/tmp/nodeProcessResponse.json') . ' --no-dns-cache --post-data "json={\"action\":\"process\",\"where\":{\"id\":\"' . $this->parameters['id'] . '\"}}" --retry-connrefused --timeout=60 --tries=2 ' . $this->parameters['url'] . '/endpoint/nodes');

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
						exec('grep "Port " /etc/ssh/sshd_config | grep -v "#" | awk \'{print $2}\' 2>&1', $sshPorts);

						foreach ($sshPorts as $sshPortKey => $sshPort) {
							if (
								(strlen($sshPort) > 5) ||
								(is_numeric($sshPort) === false)
							) {
								unset($sshPorts[$sshPortKey]);
							}
						}

						if (empty($sshPorts) === false) {
							$this->nodeData['ssh_ports'] = $sshPorts;
						}
					}
				}
			}

			return;
		}

	}
?>
