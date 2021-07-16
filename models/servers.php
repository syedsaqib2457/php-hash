<?php
	$extend = true;
	require_once($configuration->settings['base_path'] . '/models/main.php');

	class ServersModel extends MainModel {

		protected function _fetchServerData($parameters) {
			//todo: refactor with server_nodes and public-facing dns instead of just proxies
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error fetching server data, please try again.')
				)
			);

			if (!empty($parameters['where']['id'])) {
				$server = $this->fetch(array(
					'from' => 'servers',
					'where' => array_intersect_key($parameters['where'], array(
						'id' => true
					))
				));

				if (!empty($server['data'])) {
					$response = array(
						'data' => array(
							'nameserver_process_external_ips' => array(),
							'nameserver_process_internal_ips' => array(),
							'nodes' => array(),
							'proxies' => array(),
							'proxy_process_ports' => $this->_call(array(
								'method_from' => 'server_proxy_processes',
								'method_name' => 'fetchServerProxyProcessPorts',
								'method_parameters' => array(
									($serverId = $server['data'][0]['id'])
								)
							)),
							'server' => array(
								'id' => $serverId,
								'ip' => $server['data'][0]['ip'],
								'type' => current($this->_fetchIpDetails($server['data'][0]['ip']))
							),
							'settings' => array_intersect_key($this->settings, array(
								'version' => true
							))
						),
						'message' => array(
							'status' => 'success',
							'text' => 'Server data fetched successfully.'
						)
					);
					$serverNameserverProcesses = $this->fetch(array(
						'fields' => array(
							'external_source_ip',
							'internal_source_ip',
							'listening_ip'
						),
						'from' => 'server_nameserver_processes',
						'sort' => array(
							'field' => 'created',
							'order' => 'DESC'
						),
						'where' => array(
							'server_id' => $serverId
						)
					));

					if (!empty($serverNameserverProcesses['count'])) {
						foreach ($serverNameserverProcesses['data'] as $serverNameserverProcess) {
							$sourceIp = !empty($serverNameserverProcess['internal_source_ip']) ? $serverNameserverProcess['internal_source_ip'] : $serverNameserverProcess['external_source_ip'];
							$response['data']['nameserver_process_external_ips'][$serverNameserverProcess['listening_ip']][$sourceIp] = $sourceIp;
							$response['data']['nameserver_process_internal_ips'][$sourceIp][$serverNameserverProcess['listening_ip']] = $serverNameserverProcess['listening_ip'];
						}
					}

					$proxies = $this->fetch(array(
						'fields' => array(
							'enable_url_request_logs',
							'external_ip',
							'id',
							'internal_ip',
							'password',
							'server_id',
							'server_node_id',
							'status',
							'username',
							'whitelisted_ips'
						),
						'from' => 'proxies',
						'sort' => array(
							'field' => 'created',
							'order' => 'ASC'
						),
						'where' => array(
							'server_id' => $serverId
						)
					));

					if (!empty($proxies['count'])) {
						$response['data']['proxies'] = $proxies['data'];
					}

					$proxyUrlRequestLimitationProxyIds = $this->fetch(array(
						'fields' => array(
							'proxy_id'
						),
						'from' => 'proxy_url_request_limitation_proxies',
						'sort' => array(
							'field' => 'proxy_id',
							'order' => 'ASC'
						),
						'where' => array(
							'server_id' => $serverId
						)
					));

					if (!empty($proxyUrlRequestLimitationProxyIds['count'])) {
						$proxyUrlRequestLimitationProxyIds = array_unique($proxyUrlRequestLimitationProxyIds['data']);
						$proxyUrlRequestLimitationProxyIps = $this->fetch(array(
							'fields' => array(
								'external_ip'
							),
							'from' => 'proxies',
							'sort' => array(
								'field' => 'id',
								'order' => 'ASC'
							),
							'where' => array(
								'id' => $proxyUrlRequestLimitationProxyIds
							)
						));

						if (
							!empty($proxyUrlRequestLimitationProxyIps['count']) &&
							count($proxyUrlRequestLimitationProxyIds) === $proxyUrlRequestLimitationProxyIps['count']
						) {
							$response['data']['proxy_url_request_limitation_proxies'] = array_combine($proxyUrlRequestLimitationProxyIps['data'], $proxyUrlRequestLimitationProxyIds);
						}

						$proxyUrlRequestLimitationUrls = $this->fetch(array(
							'fields' => array(
								'id',
								'url'
							),
							'from' => 'proxy_urls'
						));

						if (!empty($proxyUrlRequestLimitationUrls['count'])) {
							foreach($proxyUrlRequestLimitationUrls['data'] as $proxyUrlRequestLimitationUrl) {
								$response['data']['proxy_urls'][$proxyUrlRequestLimitationUrl['url']] = $proxyUrlRequestLimitationUrl['id'];
							}
						}

						// Parse URL request log data and request limitation details, then add specific proxy IP rules accordingly as array(IP => array(URLs))
					}

					$serverNodes = $this->fetch(array(
						'fields' => array(
							'external_ip',
							'internal_ip'
						),
						'from' => 'server_nodes',
						'where' => array(
							'server_id' => $serverId
						)
					));

					if (!empty($serverNodes['count'])) {
						foreach ($serverNodes['data'] as $serverNode) {
							$serverNodeIp = (!empty($serverNode['internal_ip']) ? $serverNode['internal_ip'] : $serverNode['external_ip']);
							$response['data']['nodes'][$serverNodeIp] = $serverNodeIp;
						}
					}
				}
			}

			return $response;
		}

		public function activate($parameters) {
			$response = array(
				'data' => array(),
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error activating server, please try again.')
				)
			);

			if (empty($parameters['where']['id']) === false) {
				$server = $this->fetch(array(
					'fields' => array(
						'id',
						'main_ip_version_4',
						'main_ip_version_6',
						'status_active',
						'status_deployed'
					),
					'from' => 'servers',
					'where' => array(
						'id' => ($serverId = $parameters['where']['id'])
					)
				));

				if ($server !== false) {
					$response['message']['text'] = 'Invalid server ID, please try again.';

					if (empty($server) === false) {
						$response = array(
							'data' => array(
								'deployment_command' => 'cd /tmp && rm -rf /etc/cloud/ /var/lib/cloud/ ; apt-get update ; DEBIAN_FRONTEND=noninteractive apt-get -y install sudo ; sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk \'{print $1}\') ; sudo $(whereis telinit | awk \'{print $2}\') u ; sudo rm -rf /etc/cloud/ /var/lib/cloud/ ; sudo dpkg --configure -a ; sudo apt-get update && sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php wget --fix-missing && sudo wget -O proxy.php --no-dns-cache --retry-connrefused --timeout=60 --tries=2 "' . ($url = $_SERVER['REQUEST_SCHEME'] . '://' . $this->settings['base_domain']) . '/assets/php/proxy.php?' . time() . '" && sudo php proxy.php ' . $serverId . ' ' . $url,
								'server' => $server
							),
							'message' => array(
								'status' => 'success',
								'text' => 'Server is ready for activation.'
							)
						);

						if ($server'status_active'] === true) {
							$response['message']['text'] = 'Server is already activated.';
						} elseif (empty($parameters['user']['endpoint']) === false) {
							$response['message'] = array(
								'status' => 'error',
								'text' => $defaultMessage
							);
							$serverNodeDataUpdated = $this->update(array(
								'data' => array(
									'status_active' => true
								),
								'in' => 'server_nodes',
								'where' => array(
									'server_id' => $serverId
								)
							));
							$serverDataUpdated = $this->update(array(
								'data' => array(
									'status_active' => true
								),
								'in' => 'servers',
								'where' => array(
									'id' => $serverId
								)
							));

							if (
								$serverNodeDataUpdated === true &&
								$serverDataUpdated === true
							) {
								$response['data']['server']['status_active'] = true;
								$response['message'] = array(
									'status' => 'success',
									'text' => 'Server activated successfully.'
								);
							}
						}
					}
				}
			}

			return $response;
		}

		public function add($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error adding server, please try again.')
				)
			);
			$formattedServerMainIps = $serverMainIps = array();
			$serverMainIpVersions = array(
				'4',
				'6'
			);
			$validServerMainIps = false;

			foreach ($serverMainIpVersions as $serverMainIpVersion) {
				$serverMainIpKey = 'main_ip_version_' . $serverMainIpVersion;

				if (empty($parameters['data'][$serverMainIpKey]) === false) {
					$formattedServerMainIps[$serverMainIpVersion][] = $serverMainIps = $parameters['data']['main_ip_version_' . $serverMainIpVersion];
					$validServerMainIps = true;
				}
			}

			if ($validServerMainIps === true) {
				$response['message']['text'] = 'Invalid IPs, please try again.';
				$validServerNodeIps = (
					$formattedServerMainIps === $this->_validateIps($serverMainIps) &&
					count(current($formattedServerMainIps)) === 1
				);

				if ($validServerNodeIps === true) {
					foreach ($formattedServerMainIps as $serverMainIpVersion => $serverMainIp) {
						$formattedServerMainIps[$serverMainIpVersion] = $serverMainIp = current($serverMainIp);
						$validServerNodeIps = $this->_validateIpType(current($serverMainIp), $serverMainIpVersion) === 'public';

						if ($validServerNodeIps === false) {
							$response['message']['text'] = 'Both main server IPs must be public, please try again.';
							break;
						}
					}

					if ($validServerNodeIps === true) {
						foreach ($formattedServerMainIps as $serverMainIp) {
							$existingServerNodeCount = $this->count(array(
								'in' => 'server_nodes',
								'where' => array(
									'OR' => array(
										'external_ip_version_4' => $serverMainIp,
										'external_ip_version_6' => $serverMainIp,
										'internal_ip_version_4' => $serverMainIp,
										'internal_ip_version_6' => $serverMainIp
									)
								)
							));
							$existingServerCount = $this->count(array(
								'in' => 'servers',
								'where' => array(
									'OR' => array(
										'main_ip_version_4' => $serverMainIp,
										'main_ip_version_6' => $serverMainIp
									)
								)
							));
							$validServerNodeIps = (
								intval($existingServerNodeCount) === true &&
								intval($existingServerCount) === true
							);

							if ($validServerNodeIps === false) {
								break;
							}

							$validServerNodeIps = (
								$existingServerNodeCount === 0 &&
								$existingServerCount === 0
							);

							if ($validServerNodeIps === false) {
								$response['message']['text'] = 'IPs already in use, please try again.';
								break;
							}
						)
					}
				}
			}

			if ($validServerMainIps === true) {
				$response['message']['text'] = $defaultMessage;
				$serverData = array();

				foreach ($formattedServerMainIps as $serverMainIpVersion => $serverMainIp) {
					$serverData['main_ip_version_' . $serverMainIpVersion] = $serverMainIp;
				}

				$serverData = array(
					$serverData
				);
				$serverDataSaved = $this->save(array(
					'data' => $serverData,
					'to' => 'servers'
				));

				if ($serverDataSaved === true) {
					$server = $this->fetch(array(
						'fields' => array(
							'id'
						),
						'from' => 'servers',
						'where' => current($serverData)
					));

					if (
						$server !== false) &&
						empty($server) === false
					) {
						$serverNodeData = array(
							'server_id' => $server['id'],
							'type' => 'proxy'
						);

						foreach ($formattedServerMainIps as $serverMainIpVersion => $serverMainIp) {
							foreach (range(1, 8) as $serverNameserverListeningIpSegment) {
								$serverNameserverProcessData[] = array(
									'external_source_ip_version_' . $serverMainIpVersion => $serverMainIp,
									'listening_ip_version_' . $serverMainIpVersion => ($serverMainIpVersion === 4 ? '127.0.0.' : '::') . $serverNameserverListeningIpSegment,
									'port' => 53,
									'server_id' => $serverId
								);
							}

							$serverNodeData = array_merge($serverNodeData, array(
								'external_ip_version_' . $serverMainIpVersion => $serverMainIp,
								'external_ip_version_' . $serverMainIpVersion . '_type' => 'public'
							));
							$serverProxyProcessPort = 1080;

							foreach (range(1, 10) as $serverProxyProcess) {
								$serverProxyProcessData[] = array(
									'port' => $serverProxyProcessPort++,
									'server_id' => $serverId
								);
							}
						}

						$serverNodeData = array(
							$serverNodeData
						);
						$serverNameserverProcessDataSaved = $this->save(array(
							'data' => $serverNameserverProcessData,
							'to' => 'server_nameserver_processes'
						));
						$serverNodeDataSaved = $this->save(array(
							'data' => $serverNodeData,
							'to' => 'server_nodes'
						));
						$serverProxyProcessDataSaved = $this->save(array(
							'data' => $serverProxyProcessData,
							'to' => 'server_proxy_processes'
						));

						if (
							$serverNameserverProcessDataSaved === true &&
							$serverNodeDataSaved === true &&
							$serverProxyProcessDataSaved === true
						) {
							$response['message'] = array(
								'status' => 'success',
								'text' => 'Server added successfully.'
							);
						}
					}
				}
			}

			return $response;
		}

		public function deactivate($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error deactivating server, please try again.')
				)
			);

			if (!empty($parameters['where']['id'])) {
				$response['message']['text'] = 'Invalid server ID, please try again.';
				$server = $this->fetch(array(
					'fields' => array(
						'id',
						'ip',
						'status_active'
					),
					'from' => 'servers',
					'where' => array(
						'id' => ($serverId = $parameters['where']['id'])
					)
				));

				if ($server !== false) {
					$response['message']['text'] = 'Invalid server ID, please try again.';

					if (empty($server) === false) {
						$response = array(
							'data' => array(
								'server' => $server
							),
							'message' => array(
								'status' => 'success',
								'text' => 'Server is ready for deactivation.'
							)
						);

						if ($server['status_active'] === false) {
							$response['message']['text'] = 'Server is already deactivated.';
						} elseif (empty($parameters['data']['confirm_deactivation']) === false) {
							$response['message'] = array(
								'status' => 'error',
								'text' => $defaultMessage
							);
							$serverNodeDataUpdated = $this->update(array(
								'data' => array(
									'status_active' => false,
									'status_processing' => false
								),
								'in' => 'server_nodes',
								'where' => array(
									'server_id' => $serverId
								)
							));
							$serverDataUpdated = $this->update(array(
								'data' => array(
									'status_active' => false
								),
								'in' => 'servers',
								'where' => array(
									'id' => $serverId
								)
							));

							if (
								$serverDataUpdated === true &&
								$serverNodeDataUpdated === true
							) {
								$response['data']['server']['status_active'] = false;
								$response['message'] = array(
									'status' => 'success',
									'text' => 'Server deactivated successfully.'
								);
							}
						}
					}
				}
			}

			return $response;
		}

		public function deploy($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error deploying server, please try again.')
				)
			);

			if (!empty($parameters['where']['id'])) {
				$server = $this->fetch(array(
					'fields' => array(
						'id',
						'main_ip_version_4',
						'main_ip_version_6',
						'status_active',
						'status_deployed'
					),
					'from' => 'servers',
					'where' => array(
						'id' => ($serverId = $parameters['where']['id'])
					)
				));

				if ($server !== false) {
					$response['message']['text'] = 'Invalid server ID, please try again.';

					if (empty($server) === false) {
						$response['message']['text'] = 'Server activation required before deployment, please try again.';

						if ($server['status_active'] === true) {
							$response = array(
								'data' => array(
									'server' => $server
								),
								'message' => array(
									'status' => 'success',
									'text' => 'Server is ready for deployment.'
								)
							);

							if ($response['data']['status_deployed'] === true) {
								$response['message']['text'] = 'Server is already deployed.';
							}

							if (empty($parameters['user']['endpoint']) === false) {
								$response['message'] = array(
									'status' => 'error',
									'text' => $defaultMessage
								);
								$serverNodeDataUpdated = $this->update(array(
									'data' => array(
										'status_active' => true,
										'status_processing' => false
									),
									'in' => 'server_nodes',
									'where' => array(
										'server_id' => $serverId
									)
								));
								$serverDataUpdated = $this->update(array(
									'data' => array(
										'status_deployed' => true
									),
									'in' => 'servers',
									'where' => array(
										'id' => $serverId
									)
								));

								if (
									$serverNodeDataUpdated === true &&
									$serverDataUpdated === true
								) {
									$response['data']['server']['status_deployed'] = true;
									$response['message'] = array(
										'status' => 'success',
										'text' => 'Server deployed successfully.'
									);
								}
							}
						}
					}
				}
			}

			return $response;
		}

		public function fetchServerProcessPorts($serverId, $serverProcessType = false) {
			$response = array();
			$serverProcessPortParameters = array(
				'fields' => array(
					'port'
				),
				'where' => array(
					'server_id' => $serverId
				)
			);
			$serverProcessTypes = array(
				'nameserver',
				'proxy'
			);

			if (is_string($serverProcessType) === true) {
				$serverProcessTypes = array_intersect($serverProcessTypes, array(
					$serverProcessType
				));
			}

			if (empty($serverProcessTypes) === false) {
				foreach ($serverProcessPortTypes as $serverProcessPortType) {
					$serverProcessPortParameters['from'] = 'server_' . $serverProcessPortType . '_processes';
					$serverProcessPorts = $this->fetch($serverProcessPortParameters);

					if ($serverProcessPorts === false) {
						return false;
					}

					if (empty($serverProcessPorts) === false) {
						foreach ($serverProcessPorts as $serverProcessPort) {
							$response[$serverProcessPort] = $serverProcessPort;
						}
					}
				}
			}

			return $response;
		}

		public function list() {
			// todo: use list method for /servers page to include server_node ipv4 and ipv6 counts instead of using fetch method
			return array();
		}

		public function remove($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => 'Error removing servers, please try again.'
				)
			);

			if (
				empty($parameters['items'][$parameters['item_list_name']]['data']) === false &&
				($serverIds = $parameters['items'][$parameters['item_list_name']]['data'])
			) {
				$serverRelationalTableToRemoveDataFrom = array(
					'server_nameserver_processes',
					'server_nodes',
					'server_proxy_processes'
				);
				array_walk($serverRelationalTableToRemoveDataFrom, function($serverRelationalTable) use ($serverIds) {
					$this->delete(array(
						'from' => $serverRelationalTable,
						'where' => array(
							'server_id' => $serverIds
						)
					));
				});
				$serverDataDeleted = $this->delete(array(
					'from' => 'servers',
					'where' => array(
						'id' => $serverIds
					)
				));

				if ($serverDataDeleted === true) {
					$response['message'] = array(
						'status' => 'success',
						'text' => 'Servers removed successfully.'
					);
				}
			}

			return $response;
		}

		public function view($parameters = array()) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => 'Error viewing server, please try again.'
				)
			);

			if (
				(
					empty($parameters['id']) === true ||
					is_numeric($parameters['id']) === false
				) &&
				empty($parameters['where']['id']) === false
			) {
				$response = $this->_fetchServerData($parameters);
			} else {
				$server = $this->fetch(array(
					'fields' => array(
						'id'
					),
					'from' => 'servers',
					'where' => array(
						'id' => $parameters['id']
					)
				));

				if ($server !== false) {
					$response['message']['text'] = 'Invalid server ID, please try again.';

					if (empty($server) === false) {
						$response = array(
							'server_id' => $server['id']
						);
					}
				}
			}

			return $response;
		}

	}

	if (!empty($configuration->parameters)) {
		$serversModel = new ServersModel();
		$data = $serversModel->route($configuration->parameters);
	}
?>
