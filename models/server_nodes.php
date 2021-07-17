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

			if (empty($parameters['data']['server_id']) === false) {
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
						$response['message']['text'] = $defaultMessage;
						$formattedServerNodeExternalIps = $serverNodeExternalIps = array();
						$serverNodeData = array_merge($server, array(
							'server_id' => $serverId
						));
						$serverNodeIpVersions = array(
							'4',
							'6'
						);
						$validServerNodeIps = false;

						foreach ($serverNodeIpVersions as $serverNodeIpVersion) {
							$serverNodeExternalIpKey = 'external_ip_version_' . $serverNodeIpVersion;

							if (empty($parameters['data'][$serverNodeExternalIpKey]) === false) {
								$formattedServerNodeExternalIps[$serverNodeIpVersion][] = $serverNodeData[$serverNodeExternalIpKey] = $serverNodeExternalIps[$serverNodeExternalIpKey] = $parameters['data'][$serverNodeExternalIpKey];
								$validServerNodeIps = true;
							}
						}

						if ($validServerNodeIps === true) {
							$validServerNodeIps = (
								$formattedServerNodeExternalIps === $this->_validateIps($serverNodeExternalIps) &&
								count(current($formattedServerNodeExternalIps)) === 1
							);

							if ($validServerNodeIps === false) {
								$response['message']['text'] = 'Invalid server node external IPs, please try again.';
							}
						}

						if ($validServerNodeIps === true) {
							$serverNodeExternalIpTypes = $serverNodeInternalIps = array();

							foreach ($formattedServerNodeExternalIps as $serverNodeExternalIpVersion => $serverNodeExternalIpVersionIps) {
								$formattedServerNodeExternalIps[$serverNodeExternalIpVersion] = current($serverNodeExternalIpVersionIps);
								$serverNodeExternalIpTypes[$this->_validateIpType(current($formattedServerNodeExternalIps[$serverNodeExternalIpVersion]), $serverNodeExternalIpVersion)] = true;

								if (empty($serverNodeExternalIpTypes['private']) === false) {
									unset($parameters['data']['internal_ip_version_' . $serverNodeExternalIpVersion]);
								}
							}

							if (count($serverNodeExternalIpTypes) !== 1) {
								$response['message']['text'] = 'Server node external IPs must be either private or public, please try again.';
								$validServerNodeIps = false;
							}
						}

						if (
							$validServerNodeIps === true &&
							empty($serverNodeExternalIpTypes['public']) === false
						) {
							$formattedServerNodeInternalIps = array();

							foreach ($serverNodeIpVersions as $serverNodeIpVersion) {
								$serverNodeInternalIpKey = 'internal_ip_version_' . $serverNodeIpVersion;

								if (empty($parameters['data'][$serverNodeInternalIpKey]) === false) {
									$formattedServerNodeInternalIps[$serverNodeIpVersion][] = $serverNodeData[$serverNodeInternalIpKey] = $serverNodeInternalIps[$serverNodeInternalIpKey] = $parameters['data'][$serverNodeInternalIpKey];
								}
							}

							$validServerNodeIps = (
								$formattedServerNodeInternalIps === $this->_validateIps($serverNodeInternalIps) &&
								count(current($formattedServerNodeInternalIps)) === 1
							);

							if ($validServerNodeIps === false) {
								$response['message']['text'] = 'Invalid server node internal IPs, please try again.';
							}
						}

						if ($validServerNodeIps === true) {
							$conflictingServerNodeCount = $this->count(array(
								'in' => 'server_nodes',
								'where' => array(
									'OR' => array(
										array(
											'server_id' => $serverId,
											'OR' => ($serverNodeExternalIps + $serverNodeInternalIps)
										),
										array(
											'server_id !=' => $serverId,
											'OR' => $serverNodeExternalIps
										)
									)
								)
							));
							$conflictingServerCount = $this->count(array(
								'in' => 'servers',
								'where' => array(
									'OR' => array_combine(array(
										'main_ip_version_4',
										'main_ip_version_6'
									), $serverNodeExternalIps)
								)
							));
							$validServerNodeIps = (
								is_int($conflictingServerNodeCount) === true &&
								is_int($conflictingServerCount) === true
							);

							if ($validServerNodeIps === true) {
								$validServerNodeIps = (
									$conflictingServerNodeCount === 0 &&
									$conflictingServerCount === 0
								);

								if ($validServerNodeIps === false) {
									$response['message']['text'] = 'Server node IPs already in use, please try again.';
								}
							}
						}

						if ($validServerNodeIps === true) {
							$serverNodeData = array(
								$serverNodeData
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

			// Add ipv6 support to edit method, repeating validation logic from add because server_nodes edit will include node type, authentication, etc

			if (empty($parameters['data']['id']) === false) {
				$serverNode = $this->fetch(array(
					'fields' => array(
						'external_ip_version_4',
						'external_ip_version_6',
						'internal_ip_version_4',
						'internal_ip_version_6',
						'server_id'
					),
					'from' => 'server_nodes',
					'where' => array(
						'id' => ($serverNodeId = $parameters['data']['id'])
					)
				));

				if ($serverNode !== false) {
					$response['message']['text'] = 'Invalid server node ID, please try again.';

					if (empty($serverNode) === false) {
						$response['message']['text'] = $defaultMessage;
						$server = $this->fetch(array(
							'fields' => array(
								'main_ip_version_4',
								'main_ip_version_6'
							),
							'from' => 'servers',
							'where' => array(
								'id' => ($serverId = $parameters['where']['id'])
							)
						));

						if (
							$server !== false &&
							empty($server) === false
						) {
							$formattedServerNodeExternalIps = $serverNodeData = $serverNodeExternalIps = array();
							$serverNodeIpVersions = array(
								'4',
								'6'
							);
							$validServerNodeIps = false;

							foreach ($serverNodeIpVersions as $serverNodeIpVersion) {
								$serverNodeExternalIpKey = 'external_ip_version_' . $serverNodeIpVersion;

								if (empty($parameters['data'][$serverNodeExternalIpKey]) === false) {
									$formattedServerNodeExternalIps[$serverNodeIpVersion][] = $serverNodeData[$serverNodeExternalIpKey] = $serverNodeExternalIps[$serverNodeExternalIpKey] = $parameters['data'][$serverNodeExternalIpKey];
									$validServerNodeIps = true;
								}
							}

							if ($validServerNodeIps === true) {
								$validServerNodeIps = (
									$formattedServerNodeExternalIps === $this->_validateIps($serverNodeExternalIps) &&
									count(current($formattedServerNodeExternalIps)) === 1
								);

								if ($validServerNodeIps === false) {
									$response['message']['text'] = 'Invalid server node external IPs, please try again.';
								}
							}

							if ($validServerNodeIps === true) {
								$serverNodeExternalIpTypes = $serverNodeInternalIps = array();

								foreach ($formattedServerNodeExternalIps as $serverNodeExternalIpVersion => $serverNodeExternalIpVersionIps) {
									$formattedServerNodeExternalIps[$serverNodeExternalIpVersion] = current($serverNodeExternalIpVersionIps);
									$serverNodeExternalIpTypes[$this->_validateIpType(current($formattedServerNodeExternalIps[$serverNodeExternalIpVersion]), $serverNodeExternalIpVersion)] = true;

									if (empty($serverNodeExternalIpTypes['private']) === false) {
										unset($parameters['data']['internal_ip_version_' . $serverNodeExternalIpVersion]);
									}
								}

								if (count($serverNodeExternalIpTypes) !== 1) {
									$response['message']['text'] = 'Server node external IPs must be either private or public, please try again.';
									$validServerNodeIps = false;
								}
							}

							if (
								$validServerNodeIps === true &&
								empty($serverNodeExternalIpTypes['public']) === false
							) {
								$formattedServerNodeInternalIps = array();

								foreach ($serverNodeIpVersions as $serverNodeIpVersion) {
									$serverNodeInternalIpKey = 'internal_ip_version_' . $serverNodeIpVersion;

									if (empty($parameters['data'][$serverNodeInternalIpKey]) === false) {
										$formattedServerNodeInternalIps[$serverNodeIpVersion][] = $serverNodeData[$serverNodeInternalIpKey] = $serverNodeInternalIps[$serverNodeInternalIpKey] = $parameters['data'][$serverNodeInternalIpKey];
									}
								}

								$validServerNodeIps = (
									$formattedServerNodeInternalIps === $this->_validateIps($serverNodeInternalIps) &&
									count(current($formattedServerNodeInternalIps)) === 1
								);

								if ($validServerNodeIps === false) {
									$response['message']['text'] = 'Invalid server node internal IPs, please try again.';
								}
							}

							if ($validServerNodeIps === true) {
								$conflictingServerNodeCount = $this->count(array(
									'in' => 'server_nodes',
									'where' => array(
										'id !=' => $serverNodeId,
										'OR' => array(
											array(
												'server_id' => $serverId,
												'OR' => ($serverNodeExternalIps + $serverNodeInternalIps)
											),
											array(
												'server_id !=' => $serverId,
												'OR' => $serverNodeExternalIps
											)
										)
									)
								));
								$conflictingServerCount = $this->count(array(
									'in' => 'servers',
									'where' => array(
										'id !=' => $serverNodeId,
										'OR' => array_combine(array(
											'main_ip_version_4',
											'main_ip_version_6'
										), $serverNodeExternalIps)
									)
								));
								$validServerNodeIps = (
									is_int($conflictingServerNodeCount) === true &&
									is_int($conflictingServerCount) === true
								);

								if ($validServerNodeIps === true) {
									$validServerNodeIps = (
										$conflictingServerNodeCount === 0 &&
										$conflictingServerCount === 0
									);

									if ($validServerNodeIps === false) {
										$response['message']['text'] = 'Server node IPs already in use, please try again.';
									}
								}
							}
						}

						if ($validServerNodeIps === true) {
							$serverData = array();

							foreach ($formattedServerNodeExternalIps as $serverNodeIpVersion => $serverNodeExternalIp) {
								$serverMainIpKey = 'main_ip_version_' . $serverNodeIpVersion;
								$serverMainIp = $server[$serverMainIpKey];

								if (
									$serverMainIp === $serverNode['external_ip_version_' . $serverNodeIpVersion] &&
									$serverMainIp !== $serverNodeExternalIp
								) {
									$serverData[$serverMainIpKey] = $serverNodeExternalIp;
								}
							}
						}

						if ($validServerNodeIps === true) {
							$conflictingServerNameserverProcesses = $this->fetch(array(
								'fields' => array(
									// ..
								),
								'from' => 'server_nameserver_processes',
								'where' => array(
									// ..
								)
							));
							$serverNameserverProcessData = array();
							// ..

							$serverData = array(
								$serverData
							);
							$serverNodeData = array(
								$serverNodeData
							);
							$serverDataUpdated = $this->update(array(
								'data' => $serverData,
								'in' => 'servers',
								'where' => array(
									'id' => $serverId
								)
							));
							$serverNodeDataUpdated = $this->update(array(
								'data' => $serverNodeData,
								'in' => 'server_nodes',
								'where' => array(
									'id' => $serverNodeId
								)
							));

							if (
								$serverDataUpdated === true &&
								$serverNodeDataUpdated === true
							) {
								$response = array(
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
