<?php
	$extend = true;
	require_once($configuration->settings['base_path'] . '/models/main.php');

	class ServerNodesModel extends MainModel {

		public function add($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error adding server node, please try again.')
				)
			);

			if (
				!empty($parameters['data']['external_ip']) &&
				!empty($parameters['data']['server_id'])
			) {
				$response['message']['text'] = 'Invalid external IP address, please try again.';
				$serverNodeIps = array();
				$validServerNodeIps = true;

				if ($validServerNodeExternalIp = current(current($this->_validateIps($parameters['data']['external_ip'])))) {
					$response['message']['text'] = $defaultMessage;
					$server = $this->fetch(array(
						'fields' => array(
							'status_activated',
							'status_deployed'
						),
						'from' => 'servers',
						'where' => array(
							'id' => ($serverId = $parameters['data']['server_id'])
						)
					));

					if (!empty($server['count'])) {
						$serverNodeData = array(
							array(
								'external_ip' => ($serverNodeIps[$validServerNodeExternalIp] = $validServerNodeExternalIp),
								'processing' => true,
								'server_id' => $serverId,
								'status' => 'inactive'
							)
						);

						if (!empty($parameters['data']['internal_ip'])) {
							$response['message']['text'] = 'Invalid internal IP address, please try again.';
							$validServerNodeIps = false;

							if ($validServerNodeInternalIp = current(current($this->_validateIps($parameters['data']['internal_ip'])))) {
								$serverNodeIps[$validServerNodeInternalIp] = $serverNodeData[0]['internal_ip'] = $validServerNodeInternalIp;
								$validServerNodeIps = true;
							}
						}

						if ($validServerNodeIps === true) {
							$existingIpParameters = array(
								'fields' => array(
									'external_ip',
									'internal_ip'
								),
								'where' => array(
									'OR' => array(
										array(
											'AND' => array(
												'server_id' => $serverId,
												'OR' => array(
													'external_ip' => ($serverNodeIps = array_values($serverNodeIps)),
													'internal_ip' => $serverNodeIps
												)
											)
										),
										array(
											'AND' => array(
												'server_id !=' => $serverId,
												'OR' => array(
													'external_ip' => $serverNodeIps
												)
											)
										)
									)
								)
							);
							;
							$serverNodePublicIps = array();

							foreach ($serverNodeIps as $serverNodeIp) {
								$serverNodeIpDetails = $this->_fetchIpDetails($serverNodeIp);

								if ($serverNodeIpDetails['type'] === 'public') {
									$serverNodePublicIps[] = $serverNodeIp;
								}
							}

							if (!empty($serverNodePublicIps)) {
								$existingIpParameters['where']['OR'][1]['AND']['OR']['internal_ip'] = $serverNodePublicIps;
							}

							$existingIpParameters['from'] = 'server_nodes';
							$existingServerNodeIps = $this->fetch($existingIpParameters);
							unset($existingIpParameters['where']['OR'][0]);
							$existingIpParameters['from'] = 'servers';
							$existingIpParameters['where']['OR'][1]['AND']['id !='] = $serverId;
							$existingServerIps = $this->fetch($existingIpParameters);

							if (
								!empty($existingServerNodeIps['count']) ||
								!empty($existingServerIps['count'])
							) {
								$response['message']['text'] = 'IPs already in use, please try again.';
								$validServerNodeIps = false;
							}
						}

						if ($validServerNodeIps === true) {
							$response['message']['text'] = $defaultMessage;

							if (!empty($server['data'][0]['status_activated'])) {
								$serverNodeData[0]['status'] = 'active';
							}

							if (!empty($server['data'][0]['status_deployed'])) {
								$serverNodeData[0]['processing'] = false;
							}

							if (
								$this->save(array(
									'data' => $serverNodeData,
									'to' => 'server_nodes'
								))
							) {
								$serverNodes = $this->fetch(array(
									'fields' => array(
										'id'
									),
									'from' => 'server_nodes',
									'limit' => 1,
									'where' => array(
										'server_id' => $serverId
									)
								));

								if (!empty($server['data'][0]['status_deployed'])) {
									$serverNodeId = $this->fetch(array(
										'fields' => array(
											'id'
										),
										'from' => 'server_nodes',
										'limit' => 1,
										'where' => $serverNodeData[0]
									));

									if (!empty($serverNodeId['count'])) {
										$proxyData = array(
											array_merge(array_diff_key($serverNodeData[0], array(
												'processing' => true
											)), array(
												'server_node_id' => $serverNodeId['data'][0],
												'status' => !empty($server['data'][0]['status_activated']) ? 'active' : 'inactive'
											))
										);

										$this->save(array(
											'data' => $proxyData,
											'to' => 'proxies'
										));
									}
								}

								if (
									isset($serverNodes['count']) &&
									is_numeric($serverNodes['count'])
								) {
									$this->save(array(
										'data' => array(
											array(
												'id' => $serverId,
												'ip_count' => $serverNodes['count']
											)
										),
										'to' => 'servers'
									));
								}

								$response['message'] = array(
									'status' => 'success',
									'text' => 'Server node added successfully.'
								);
							}
						}
					}
				}
			}

			return $response;
		}

		public function edit($parameters) {
			$response = array(
				'data' => array(),
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error editing server node, please try again.')
				)
			);

			if (
				is_string($parameters['data']['external_ip']) &&
				is_string($parameters['data']['internal_ip']) &&
				!empty($parameters['where']['id']) &&
				is_string($parameters['where']['id'])
			) {
				$response['message']['text'] = 'Invalid external IP address, please try again.';
				$proxyData = $serverData = $serverNodeData = $serverNodeIps = array();
				$validServerNodeIps = true;

				if ($validServerNodeExternalIp = current(current($this->_validateIps($parameters['data']['external_ip'])))) {
					$response['message']['text'] = $defaultMessage;
					$serverId = $parameters['server_id'];
					$serverNodeData = array(
						array(
							'id' => ($serverNodeId = $parameters['where']['id']),
							'internal_ip' => $parameters['data']['internal_ip']
						)
					);
					$serverNode = $this->fetch(array(
						'fields' => array(
							'external_ip',
							'id',
							'internal_ip'
						),
						'from' => 'server_nodes',
						'where' => array_intersect_key($serverNodeData[0], array(
							'id' => true
						))
					));
					$serverNodeData[0]['external_ip'] = $serverNodeIps[$validServerNodeExternalIp] = $validServerNodeExternalIp;

					if (!empty($serverNode['count'])) {
						$response['message']['text'] = $defaultMessage;
						$serverIp = $this->fetch(array(
							'fields' => array(
								'ip'
							),
							'from' => 'servers',
							'where' => array(
								'id' => $serverId
							)
						));

						if (!empty($serverIp['count'])) {
							$response['data']['server']['ip'] = $serverIp = $serverIp['data'][0];

							if ($serverNode['data'][0]['external_ip'] === $serverIp) {
								$serverData = array(
									array(
										'id' => $serverId,
										'ip' => ($response['data']['server']['ip'] = $validServerNodeExternalIp)
									)
								);
							}

							if (!empty($parameters['data']['internal_ip'])) {
								$response['message']['text'] = 'Invalid internal IP address, please try again.';
								$validServerNodeIps = false;

								if ($validServerNodeInternalIp = current(current($this->_validateIps($parameters['data']['internal_ip'])))) {
									$serverNodeData[0]['internal_ip'] = $serverNodeIps[$validServerNodeInternalIp] = $validServerNodeInternalIp;
									$validServerNodeIps = true;
								}
							}

							if (
								!empty($serverNode['data'][0]['allocated']) &&
								(
									$serverNode['data'][0]['external_ip'] !== $validServerNodeExternalIp ||
									$serverNode['data'][0]['internal_ip'] !== $validServerNodeInternalIp
								)
							) {
								$existingProxies = $this->fetch(array(
									'fields' => array(
										'external_ip',
										'id',
										'internal_ip',
										'type'
									),
									'from' => 'proxies',
									'where' => array(
										'OR' => array(
											array(
												'external_ip' => $serverNode['data'][0]['external_ip']
											),
											array(
												'internal_ip' => $serverNode['data'][0]['internal_ip']
											)
										)
									)
								));

								if (!empty($existingProxies['count'])) {
									foreach ($existingProxies['data'] as $existingProxy) {
										$proxyData[] = array(
											'external_ip' => $validServerNodeExternalIp,
											'id' => $existingProxy['id'],
											'internal_ip' => $validServerNodeInternalIp
										);
									}
								}
							}

							if ($validServerNodeIps === true) {
								$existingIpParameters = array(
									'fields' => array(
										'external_ip',
										'internal_ip'
									),
									'where' => array(
										'id !=' => $parameters['where']['id'],
										'OR' => array(
											array(
												'AND' => array(
													'server_id' => $serverId,
													'OR' => array(
														'external_ip' => $serverNodeIps = array_values($serverNodeIps),
														'internal_ip' => $serverNodeIps
													)
												)
											),
											array(
												'AND' => array(
													'server_id !=' => $serverId,
													'OR' => array(
														'external_ip' => $serverNodeIps
													)
												)
											)
										)
									)
								);
								$serverNodePublicIps = array();

								foreach ($serverNodeIps as $serverNodeIp) {
									$serverNodeIpDetails = $this->_fetchIpDetails($serverNodeIp);

									if ($serverNodeIpDetails['type'] === 'public') {
										$serverNodePublicIps[] = $serverNodeIp;
									}
								}

								if (!empty($serverNodePublicIps)) {
									$existingIpParameters['where']['OR'][1]['AND']['OR']['internal_ip'] = $serverNodePublicIps;
								}

								$existingIpParameters['from'] = 'server_nodes';
								$existingServerNodeIps = $this->fetch($existingIpParameters);
								unset($existingIpParameters['where']['OR'][0]);
								$existingIpParameters['from'] = 'servers';
								$existingIpParameters['where']['OR'][1]['AND']['id !='] = $serverId;
								$existingServerIps = $this->fetch($existingIpParameters);

								if (
									!empty($existingServerNodeIps['count']) ||
									!empty($existingServerIps['count'])
								) {
									$response['message']['text'] = 'IPs already in use, please try again.';
									$validServerNodeIps = false;
								}
							}
						}

						if ($validServerNodeIps === true) {
							$response['message']['text'] = $defaultMessage;
							$serverNameserverListeningIpData = $serverNameserverProcessData = array();
							$serverNameserverProcessIps = array(
								$serverNode['data'][0]['external_ip'] => $validServerNodeExternalIp,
								$serverNode['data'][0]['internal_ip'] => $validServerNodeInternalIp
							);
							$existingServerNameserverListeningIps = $this->fetch(array(
								'fields' => ($serverNameserverListeningIpFields = array(
									'id',
									'listening_ip',
									'server_id'
								)),
								'from' => 'server_nameserver_listening_ips',
								'where' => array(
									'listening_ip' => ($serverNameserverProcessOldIps = array_keys($serverNameserverProcessIps)),
									'server_id' => $serverId
								)
							));
							$existingServerNameserverProcesses = $this->fetch(array(
								'fields' => array_merge($serverNameserverListeningIpFields, array(
									'external_source_ip',
									'internal_source_ip'
								)),
								'from' => 'server_nameserver_processes',
								'where' => array(
									'AND' => array(
										'OR' => array(
											'external_source_ip' => $serverNameserverProcessOldIps,
											'internal_source_ip' => $serverNameserverProcessOldIps,
											'listening_ip' => $serverNameserverProcessOldIps
										)
									),
									'server_id' => $serverId
								)
							));

							foreach ($existingServerNameserverListeningIps['data'] as $existingServerNameserverListeningIp) {
								if (!empty($serverNameserverProcessIps[$existingServerNameserverListeningIp])) {
									$serverNameserverListeningIpData[] = array_merge($existingServerNameserverListeningIp, array(
										'listening_ip' => $serverNameserverProcessIps[$existingServerNameserverListeningIp]
									));
								}
							}

							foreach ($existingServerNameserverProcesses['data'] as $existingServerNameserverProcessKey => $existingServerNameserverProcess) {
								foreach ($existingServerNameserverProcess as $existingServerNameserverProcessFieldKey => $existingServerNameserverProcessFieldValue) {
									if (!empty($serverNameserverProcessIps[$existingServerNameserverProcessFieldValue])) {
										$existingServerNameserverProcess[$existingServerNameserverProcessFieldKey] = $serverNameserverProcessIps[$existingServerNameserverProcessFieldValue];
										$serverNameserverProcessData[$existingServerNameserverProcessKey] = $existingServerNameserverProcess;
									}
								}
							}

							if (
								$this->save(array(
									'data' => $serverData,
									'to' => 'servers'
								)) &&
								$this->save(array(
									'data' => $serverNodeData,
									'to' => 'server_nodes'
								)) &&
								$this->save(array(
									'data' => $proxyData,
									'to' => 'proxies'
								)) &&
								$this->save(array(
									'data' => $serverNameserverListeningIpData,
									'to' => 'server_nameserver_listening_ips'
								)) &&
								$this->save(array(
									'data' => $serverNameserverProcessData,
									'to' => 'server_nameserver_processes'
								))
							) {
								$response = array(
									'data' => array_merge($serverNodeData[0], $response['data']),
									'message' => array(
										'status' => 'success',
										'text' => 'Server node edited successfully.'
									)
								);
							}
						}
					}
				}
			}

			return $response;
		}

		public function remove($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => 'Error removing server nodes, please try again.'
				)
			);

			if (
				!empty($parameters['items'][$parameters['item_list_name']]['data']) &&
				($serverNodeIds = $parameters['items'][$parameters['item_list_name']]['data'])
			) {
				$proxyIds = $this->fetch(array(
					'fields' => array(
						'id'
					),
					'from' => 'proxies',
					'where' => array(
						'server_node_id' => $serverNodeIds
					)
				));
				$proxyIds = !empty($proxyIds['count']) ? $proxyIds['data'] : array();
				$serverIds = $serverNodeData = $serverNodeIps = array();
				$serverNodes = $this->fetch(array(
					'fields' => array(
						'external_ip',
						'id',
						'server_id'
					),
					'from' => 'server_nodes',
					'where' => array(
						'id' => $serverNodeIds
					)
				));

				if (!empty($serverNodes['count'])) {
					foreach ($serverNodes['data'] as $serverNode) {
						$serverIds[$serverNode['server_id']] = $serverNode['server_id'];
						$serverNodeData[] = array(
							'id' => $serverNode['id'],
							'removed' => true
						);
						$serverNodeIps[$serverNode['external_ip']] = $serverNode['external_ip'];
					}

					if (!empty($serverIds)) {
						$serverIps = $this->fetch(array(
							'fields' => array(
								'ip'
							),
							'from' => 'servers',
							'where' => array(
								'id' => array_values($serverIds)
							)
						));

						if (!empty($serverIps['count'])) {
							$serverIps = array_intersect(array_values($serverNodeIps), array_unique($serverIps['data']));

							if (!empty($serverIps)) {
								$response['message']['text'] = 'Unable to delete main IP, please try again.';
							} elseif (
								$this->delete(array(
									'from' => 'proxies',
									'where' => array(
										'id' => $proxyIds
									)
								)) &&
								$this->save(array(
									'data' => $serverNodeData,
									'to' => 'server_nodes'
								))
							) {
								$serverNodes = $this->fetch(array(
									'fields' => array(
										'id'
									),
									'from' => 'server_nodes',
									'limit' => 1,
									'where' => array(
										'server_id' => ($serverId = $parameters['where']['server_id'])
									)
								));

								if (
									isset($serverNodes['count']) &&
									is_numeric($serverNodes['count'])
								) {
									$this->save(array(
										'data' => array(
											array(
												'id' => $serverId,
												'ip_count' => $serverNodes['count']
											)
										),
										'to' => 'servers'
									));
								}

								$response['message'] = array(
									'status' => 'success',
									'text' => 'Server nodes removed successfully.'
								);
							}
						}
					}
				}
			}

			return $response;
		}

	}

	if (!empty($configuration->parameters)) {
		$serverNodesModel = new ServerNodesModel();
		$data = $serverNodesModel->route($configuration->parameters);
	}
?>
