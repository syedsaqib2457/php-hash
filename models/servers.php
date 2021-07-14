<?php
	$extend = true;
	require_once($configuration->settings['base_path'] . '/models/main.php');

	class ServersModel extends MainModel {

		protected function _fetchServerData($parameters) {
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

			if (!empty($parameters['where']['id'])) {
				$response['message']['text'] = 'Invalid server ID, please try again.';
				$server = $this->fetch(array(
					'fields' => array(
						'id',
						'ip',
						'server_node_count',
						'status_active'
					),
					'from' => 'servers',
					'where' => array(
						'id' => ($serverId = $parameters['where']['id'])
					)
				));

				if (!empty($server['count'])) {
					$response = array(
						'data' => array(
							'deployment_command' => 'cd /tmp && rm -rf /etc/cloud/ /var/lib/cloud/ ; apt-get update ; DEBIAN_FRONTEND=noninteractive apt-get -y install sudo ; sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk \'{print $1}\') ; sudo $(whereis telinit | awk \'{print $2}\') u ; sudo rm -rf /etc/cloud/ /var/lib/cloud/ ; sudo dpkg --configure -a ; sudo apt-get update && sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php wget --fix-missing && sudo wget -O proxy.php --no-dns-cache --retry-connrefused --timeout=60 --tries=2 "' . ($url = $_SERVER['REQUEST_SCHEME'] . '://' . $this->settings['base_domain']) . '/assets/php/proxy.php?' . time() . '" && sudo php proxy.php ' . $serverId . ' ' . $url,
							'server' => $server['data'][0]
						),
						'message' => array(
							'status' => 'success',
							'text' => 'Server is ready for activation.'
						)
					);

					if (!empty($response['data']['server']['status_active'])) {
						$response['message']['text'] = 'Server is already activated.';
					} elseif (!empty($parameters['user']['endpoint'])) {
						$response['message'] = array(
							'status' => 'error',
							'text' => $defaultMessage
						);
						$serverNodesUpdated = $this->update(array(
							'data' => array(
								'status_active' => true
							),
							'in' => 'server_nodes',
							'where' => array(
								'server_id' => $serverId
							)
						));
						$serversUpdated = $this->update(array(
							'data' => array(
								'status_active' => true
							),
							'in' => 'servers',
							'where' => array(
								'server_id' => $serverId
							)
						));

						if (
							$serverNodesUpdated &&
							$serversUpdated
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

			return $response;
		}

		public function add($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error adding server, please try again.')
				)
			);

			if (!empty($parameters['data']['ip'])) {
				$response['message']['text'] = 'Invalid IP, please try again.';
				$serverIp = $parameters['data']['ip'];

				if ($serverIp = current(current($this->_validateIps($serverIp)))) {
					$response['message']['text'] = $defaultMessage;
					$existingServerNodeIpParameters = $existingServerIpParameters = array(
						'fields' => array(
							'ip'
						),
						'where' => array(
							'ip' => $serverIp
						)
					);
					$existingServerIpParameters['from'] = 'servers';
					$existingServerNodeIpParameters['from'] = 'server_nodes';
					$existingServerNodeIpParameters['where'] = array(
						'OR' => array(
							'external_ip' => $serverIp,
							'internal_ip' => $serverIp
						)
					);
					$existingServerIps = $this->fetch($existingServerIpParameters);
					$existingServerNodeIps = $this->fetch($existingServerNodeIpParameters);
					$serverData = array(
						array(
							'ip' => $serverIp,
							'server_node_count' => 1
						)
					);

					if (
						!empty($existingServerNodeIps['count']) ||
						!empty($existingServerIps['count'])
					) {
						$response['message']['text'] = 'IPs already in use, please try again.';
					} else {
						$serverParameters = array(
							'fields' => array(
								'id'
							),
							'from' => 'servers',
							'where' => $serverData[0]
						);

						if ($this->save(array(
							'data' => $serverData,
							'to' => 'servers'
						))) {
							$server = $this->fetch($serverParameters);

							if (!empty($server['count'])) {
								$response['message']['text'] = $defaultMessage;
								$serverNodeData = array(
									'server_id' => ($serverId = $server['data'][0]),
									'status_active' => false
								);
								$serverIpDetails = $this->_fetchIpDetails($serverIp);
								$serverNodeData = array(
									array_merge($serverNodeData, array(
										'external_ip' => $serverIp
									))
								);
								$serverNodesSaved = $this->save(array(
                                                                        'data' => $serverNodeData,
                                                                        'to' => 'server_nodes'
                                                                ));

								if ($serverNodesSaved) {
									$response['message']['text'] = $defaultMessage;
									$serverNameserverProcessData = $serverProxyProcessData = array();

									foreach (range(1, 8) as $serverNameserverListeningIpSegment) {
										$serverNameserverProcessData[] = array(
											'external_source_ip' => $serverIpDetails['type'] === 'public' ? $serverIp : '127.0.0.1',
											'listening_ip' => '127.0.0.' . $serverNameserverListeningIpSegment,
											'server_id' => $serverId,
											'status_local' => true
										);
									}

									$serverProxyProcessPort = 1080;

									foreach (range(1, 10) as $serverProxyProcess) {
										$serverProxyProcessData[] = array(
											'port' => $serverProxyProcessPort,
											'server_id' => $serverId
										);
										$serverProxyProcessPort++;
									}

									$serverNameserverProcessesSaved = $this->save(array(
										'data' => $serverNameserverProcessData,
										'to' => 'server_nameserver_processes'
									));
									$serverProxyProcessesSaved = $this->save(array(
										'data' => $serverProxyProcessData,
										'to' => 'server_proxy_processes'
									));

									if (
										$serverNameserverProcessesSaved &&
										$serverProxyProcessesSaved
									) {
										$response['message'] = array(
											'status' => 'success',
											'text' => ($defaultMessage = 'Server added successfully.')
										);
									}
								}
							}
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
						'server_node_count',
						'status_active'
					),
					'from' => 'servers',
					'where' => array(
						'id' => ($serverId = $parameters['where']['id'])
					)
				));

				if (!empty($server['count'])) {
					$response = array(
						'data' => array(
							'server' => $server['data'][0]
						),
						'message' => array(
							'status' => 'success',
							'text' => 'Server is ready for deactivation.'
						)
					);

					if (empty($response['data']['server']['status_active'])) {
						$response['message']['text'] = 'Server is already deactivated.';
					} elseif (!empty($parameters['data']['confirm_deactivation'])) {
						$response['message'] = array(
							'status' => 'error',
							'text' => $defaultMessage
						);
						$serverNodesUpdated = $this->update(array(
							'data' => array(
								'status_active' => false,
								'status_processing' => false
							),
							'in' => 'server_nodes',
							'where' => array(
								'server_id' => $serverId
							)
						));
						$serversUpdated = $this->update(array(
							'data' => array(
								'status_active' => false
							),
							'in' => 'servers',
							'where' => array(
								'id' => $serverId
							)
						));

						if (
							$serversUpdated &&
							$serverNodesUpdated
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

			return $response;
		}

		public function deploy($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error deploying server, please try again.')
				)
			);

			// ..

			if (!empty($parameters['where']['id'])) {
				$response['message']['text'] = 'Invalid server ID, please try again.';
				$server = $this->fetch(array(
					'fields' => array(
						'id',
						'ip',
						'status_activated',
						'status_deployed'
					),
					'from' => 'servers',
					'where' => array_intersect_key($parameters['where'], array(
						'id' => true
					))
				));
				$serverId = $parameters['where']['id'];

				if (!empty($server['count'])) {
					$response['message']['text'] = 'Server activation required before deployment, please activate the server and try again.';
					$response['data']['server'] = $serverData = $server['data'][0];

					if (!empty($serverData['status_activated'])) {
						$response = array(
							'data' => $response['data'],
							'message' => array(
								'status' => 'success',
								'text' => 'Server is ready for deployment.'
							)
						);

						if (!empty($serverData['status_deployed'])) {
							$response['message']['text'] = 'Server is already deployed.';
						}

						if (!empty($parameters['user']['endpoint'])) {
							$response['message'] = array(
								'status' => 'error',
								'text' => $defaultMessage
							);
							$proxyParameters = $serverNodeParameters = array(
								'fields' => array(
									'external_ip',
									'id',
									'internal_ip'
								),
								'where' => array(
									'server_id' => $serverId
								)
							);
							$proxyParameters['from'] = 'proxies';
							$serverNodeParameters['from'] = 'server_nodes';
							$proxies = $this->fetch($proxyParameters);
							$serverNodes = $this->fetch($serverNodeParameters);

							if (!empty($serverNodes['count'])) {
								$proxyData = array();

								if (!empty($proxies['count'])) {
									foreach ($proxies['data'] as $proxy) {
										$proxies[$proxy['external_ip']] = $proxy['id'];
									}
								}

								foreach ($serverNodes['data'] as $serverNode) {
									$proxyData[$serverNode['id']] = array_merge($serverNode, array(
										'server_id' => $serverId,
										'server_node_id' => $serverNode['id'],
										'status' => 'active'
									));

									if (!empty($proxies[$serverNode['external_ip']])) {
										$proxyData[$serverNode['id']]['id'] = $proxies[$serverNode['external_ip']];
									}
								}

								if (
									$this->_query("UPDATE `servers` SET `status_deployed` = 1 WHERE `id` = $serverId;") &&
									$this->_query("UPDATE `server_nodes` SET `status` = 'active', `processing` = false WHERE `server_id` = $serverId;") &&
									$this->save(array(
										'data' => $proxyData,
										'to' => 'proxies'
									))
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

		public function list() {
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
				!empty($parameters['items'][$parameters['item_list_name']]['data']) &&
				($serverIds = $parameters['items'][$parameters['item_list_name']]['data'])
			) {
				$serverRelationalTableToRemoveDataFrom = array(
					'proxies',
					'server_nameserver_listening_ips',
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

				if ($this->delete(array(
					'from' => 'servers',
					'where' => array(
						'id' => $serverIds
					)
				))) {
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
					empty($parameters['id']) ||
					!is_numeric($parameters['id'])
				) &&
				!empty($parameters['where']['id'])
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

				if (!empty($server['count'])) {
					$response = array(
						'server_id' => $parameters['id']
					);
				}
			}

			if ($response['status'] === 'success') {
				$response['message'] = 'Server viewed successfully.';
			}

			return $response;
		}

	}

	if (!empty($configuration->parameters)) {
		$serversModel = new ServersModel();
		$data = $serversModel->route($configuration->parameters);
	}
?>
