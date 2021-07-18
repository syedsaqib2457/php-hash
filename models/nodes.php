<?php
	$extend = true;
	require_once($configuration->settings['base_path'] . '/models/main.php');

	class NodesModel extends MainModel {

		public function add($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error adding node, please try again.')
				)
			);

			if (empty($parameters['data']['type']) === false) {
				$validNode = in_array($parameters['data']['type'], array(
					'nameserver',
					'proxy'
				));

				if ($validNode === true) {
					$nodeData = array();

					if (empty($parameters['data']['node_id']) === false) {
						$node = $this->fetch(array(
							'fields' => array(
								'id',
								'removed',
								'status_active',
								'status_deployed'
							),
							'from' => 'nodes',
							'where' => array(
								'id' => ($nodeData['node_id'] = $nodeId = $parameters['data']['node_id'])
							)
						));
						$validNode = false;

						if ($node !== false) {
							$nodeData = $node;
							$validNode = (empty($node) === false);

							if ($validNode === false) {
								$response['message']['text'] = 'Invalid node ID, please try again.';
							}
						}
					}
				}

				if ($validNode === true) {
					$nodeExternalIpVersions = $nodeExternalIps = array();
					$nodeIpVersions = array(
						'4',
						'6'
					);
					$validNode = false;

					foreach ($nodeIpVersions as $nodeIpVersion) {
						$nodeExternalIpKey = 'external_ip_version_' . $nodeIpVersion;

						if (empty($parameters['data'][$nodeExternalIpKey]) === false) {
							$nodeData[$nodeExternalIpKey] = $nodeExternalIps[$nodeExternalIpKey] = $nodeExternalIpVersions[$nodeIpVersion][] = $parameters['data'][$nodeExternalIpKey];
							$validNode = true;
						}
					}
				}

				if ($validNode === true) {
					$validNode = (
						$nodeExternalIpVersions === $this->_sanitizeIps($nodeExternalIps) &&
						count(current($nodeExternalIpVersions)) === 1
					);

					if ($validNode === false) {
						$response['message']['text'] = 'Invalid node external IPs, please try again.';
					}
				}

				if ($validNode === true) {
					$nodeExternalIpTypes = array();

					foreach ($nodeExternalIpVersions as $nodeExternalIpVersion => $nodeExternalIpVersionIps) {
						$nodeExternalIpTypes[$this->_fetchIpType(current($nodeExternalIpVersionIps), $nodeExternalIpVersion)] = true;

						if (empty($nodeExternalIpTypes['private']) === false) {
							unset($parameters['data']['internal_ip_version_' . $nodeExternalIpVersion]);
						}
					}

					if (count($nodeExternalIpTypes) !== 1) {
						$response['message']['text'] = 'Node external IPs must be either private or public, please try again.';
						$validNode = false;
					}
				}

				if ($validNode === true) {
					$nodeInternalIpVersions = $nodeInternalIps = array();

					if (empty($nodeInternalIpTypes['public']) === false) {
						foreach ($nodeIpVersions as $nodeIpVersion) {
							$nodeInternalIpKey = 'internal_ip_version_' . $nodeIpVersion;

							if (empty($parameters['data'][$nodeInternalIpKey]) === false) {
								$nodeData[$nodeInternalIpKey] = $nodeInternalIps[$nodeInternalIpKey] = $nodeInternalIpVersions[$nodeIpVersion][] = $parameters['data'][$serverNodeInternalIpKey];
							}
						}

						$validNode = (
							$nodeInternalIpVersions === $this->_sanitizeIps($nodeInternalIps) &&
							count(current($nodeInternalIpVersions)) === 1
						);

						if ($validNode === false) {
							$response['message']['text'] = 'Invalid node internal IPs, please try again.';
						}
					}
				}

				if ($validNode === true) {
					$nodeInternalIpTypes = array();

					foreach ($nodeExternalIpVersions as $nodeExternalIpVersion => $nodeExternalIpVersionIps) {
						if ($this->_fetchIpType(current($nodeExternalIpVersionIps), $nodeExternalIpVersion) !== 'private') {
							$response['message']['text'] = 'Node internal IPs must be private, please try again.';
							$validNode = false;
							break;
						}
					}
				}

				if ($validNode === true) {
					$conflictingNodeCountParameters = array(
						'in' => 'nodes',
						'where' => array(
							'OR' => array(
								array(
									'node_id' => null,
									'OR' => $nodeExternalIps
								)
							)
						)
					));

					if (empty($nodeId) !== false) {
						$conflictingNodeCountParameters['where']['OR'][] = array(
							'node_id' => $nodeId,
							'OR' => ($nodeExternalIps + $nodeInternalIps)
						);
					}

					$conflictingNodeCount = $this->count($conflictingNodeCountParameters);
					$validNode = (is_int($conflictingNodeCount) === true);
				}

				if ($validNode === true) {
					$validNode = ($conflictingNodeCount === 0);

					if ($validNode === false) {
						$response['message']['text'] = 'Node IPs already in use, please try again.';
					}
				}

				if ($validNode === true) {
					$nodeData = array(
						$nodeData
					);
					$nodeDataSaved = $this->save(array(
						'data' => $nodeData,
						'to' => 'nodes'
					));

					if ($nodeDataSaved === true) {
						$response['message'] = array(
							'status' => 'success',
							'text' => 'Node added successfully.'
						);
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
					'text' => ($defaultMessage = 'Error removing server nodes, please try again.')
				)
			);

			// use same $validItem structure

			// if a server node id is tied to a server nameserver process, show error

			if (empty($parameters['items'][$parameters['item_list_name']]['data']) === false) {
				$serverNodeIds = $parameters['items'][$parameters['item_list_name']]['data'])
				//$serverIds = $serverNodeData = $serverNodeIps = array();
				$serverNodes = $this->fetch(array(
					'fields' => array(
						'id'
					),
					'from' => 'server_nodes',
					'where' => array(
						'id' => $serverNodeIds
					)
				));

				if ($serverNodes !== false) {
					$response['message']['text'] = 'Invalid server node IDs, please try again.';

					if (empty($serverNodes) === false) {
						$response['message']['text'] = $defaultMessage;
						$servers = $this->fetch(array(
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
							$servers !== false &&
							empty($servers) === false
						) {
							$validServerNodes = true;
							$serverNameserverProcessCount = $this->count(array(
								'in' => 'server_nameserver_processes',
								'where' => array(
									'server_node_id' => $serverNodeIds
								)
							));

							if ($serverNameserverProcessCount !== false) {
								$validServerNodes = ($serverNameserverProcessCount === 0);

								if ($validServerNodes === false) {
									$response['message']['text'] = 'The selected server nodes are connected to server nameserver processes, please try again.';
								}
							}

							if ($validServerNodes === true) {
								// delete server_node_users
								// update data
							}
						}
					}
				}

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
								$response['message']['text'] = 'Unable to delete server main IP, please try again.';
							} elseif (
								$this->save(array(
									'data' => $serverNodeData,
									'to' => 'server_nodes'
								))
							) {
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
