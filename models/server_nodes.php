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
				empty($parameters['data']['external_ip']) === false &&
				empty($parameters['data']['server_id']) === false
			) {
				$response['message']['text'] = 'Invalid external IP address, please try again.';
				$formattedServerNodeExternalIp = $this->_validateIps($parameters['data']['external_ip'];

				if ($validServerNodeIps = (empty($formattedServerNodeExternalIp) === false)) {
					$response['message']['text'] = $defaultMessage;
					$serverNodeExternalIp = current(current($formattedServerNodeExternalIp));
					$serverNodeIps = array();
					$server = $this->fetch(array(
						'fields' => array(
							'status_active',
							'status_deployed'
						),
						'from' => 'servers',
						'where' => array(
							'id' => ($serverId = $parameters['data']['server_id'])
						)
					));

					if ($server !== false) {
						$response['message']['text'] = 'Invalid server ID, please try again.';

						if (empty($server) === false) {
							$serverNodeData = array(
								'external_ip' => ($serverNodeIps[$serverNodeExternalIp] = $serverNodeExternalIp),
								'external_ip_version' => ($serverNodeExternalIpVersion = key($formattedServerNodeExternalIp)),
								'server_id' => $serverId
							);

							if (!empty($parameters['data']['internal_ip'])) {
								$response['message']['text'] = 'Invalid internal IP address, please try again.';
								$formattedServerNodeInternalIp = $this->_validateIps($parameters['data']['internal_ip'];

								if ($validServerNodeIps = (empty($formattedServerNodeInternalIp) === false)) {
									$serverNodeInternalIp = current(current($serverNodeInternalIp));
									$serverNodeData = array_merge($serverNodeData, array(
										'internal_ip' => ($serverNodeIps[$serverNodeInternalIp] = $serverNodeInternalIp),
										'internal_ip_version' => ($serverNodeInternalIpVersion = key($formattedServerNodeInternalIp))
									));
								}
							}

							if ($validServerNodeIps === true) {
								$response['message']['text'] = $defaultMessage;
								$existingParameters = array(
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
								$serverNodeIpKeys = array(
									'external_ip',
									'internal_ip'
								);

								foreach ($serverNodeIpKeys as $serverNodeIpKey) {
									$serverNodeIp = $serverNodeData[$serverNodeIpKey];
									$serverNodeIpVersion = $serverNodeData[$serverNodeIpKey . '_version'];

									if ($this->_validateIpType($serverNodeIp, $serverNodeIpVersion) === 'public') {
										$existingParameters['where']['OR'][1]['AND']['OR']['internal_ip'][] = $serverNodeIp;
									}
								}

								$existingParameters['in'] = 'server_nodes';
								$existingServerNodeCount = $this->count($existingParameters);
								unset($existingParameters['where']['OR'][0]);
								$existingParameters['in'] = 'servers';
								$existingParameters['where']['OR'][1]['AND']['id !='] = $serverId;
								$existingServerCount = $this->count($existingParameters);
								$validServerNodeIps = (
									intval($existingServerNodeCount) === true &&
									intval($existingServerCount) === true
								);

								if (
									$validServerNodeIps === true &&
									($validServerNodeIps = ((
										$existingServerNodeCount === 0 &&
										$existingServerCount === 0
									) === false))
								) {
									$response['message']['text'] = 'IPs already in use, please try again.';
								}
							}

							if ($validServerNodeIps === true) {
								$serverNodeData = array(
									array_merge($serverNodeData, $server)
								);
								$serverNodeDataSaved = $this->save(array(
									'data' => $serverNodeData,
									'to' => 'server_nodes'
								));

								if ($serverNodeDataSaved === true) {
									$response['message'] = array(
										'status' => 'success',
										'text' => 'Server node added successfully.'
									);
								}
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
				empty($parameters['data']['external_ip']) === false &&
				empty($parameters['where']['id']) === false
			) {
				$response['message']['text'] = 'Invalid external IP address, please try again.';
				$formattedServerNodeExternalIp = $this->_validateIps($parameters['data']['external_ip']);

				if ($validServerNodeIps = (empty($formattedServerNodeExternalIp) === false)) {
					$response['message']['text'] = $defaultMessage;
					$serverNodeExternalIp = current(current($formattedServerNodeExternalIp));
					$serverNodeData = array(
						'external_ip' => ($serverNodeIps[$serverNodeExternalIp] = $serverNodeExternalIp),
						'external_ip_version' => ($serverNodeExternalIpVersion = key($formattedServerNodeExternalIp)),
						'id' => ($serverNodeId = $parameters['where']['id']),
						'server_id' => ($serverId = $parameters['server_id'])
					);
					$serverNode = $this->fetch(array(
						'fields' => array(
							'external_ip',
							'id',
							'internal_ip'
						),
						'from' => 'server_nodes',
						'where' => array(
							'id' => $serverNodeId
						)
					));

					if ($serverNode !== false) {
						$response['message']['text'] = 'Invalid server node ID, please try again.';

						if (empty($serverNode) === false) {
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
