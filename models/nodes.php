<?php
	$extend = true;
	require_once($configuration->settings['base_path'] . '/models/main.php');

	class NodesModel extends MainModel {

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding node, please try again.',
				'status_valid' => false
			);

			if (empty($parameters['data']['type']) === false) {
				$response['status_valid'] = in_array($parameters['data']['type'], array(
					'nameserver',
					'proxy'
				));

				if ($response['status_valid'] === true) {
					$nodeData = array();

					if (empty($parameters['data']['node_id']) === false) {
						$node = $nodeData = $this->fetch(array(
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
						$response['status_valid'] = ($node !== false);

						if ($response['status_valid'] === true) {
							$response['status_valid'] = (empty($node) === false);

							if ($response['status_valid'] === false) {
								$response['message'] = 'Invalid node ID, please try again.';
							}
						}
					}
				}

				if ($response['status_valid'] === true) {
					$nodeExternalIpVersions = $nodeExternalIps = array();
					$nodeIpVersions = array(
						'4',
						'6'
					);
					$response['status_valid'] = false;

					foreach ($nodeIpVersions as $nodeIpVersion) {
						$nodeExternalIpKey = 'external_ip_version_' . $nodeIpVersion;

						if (empty($parameters['data'][$nodeExternalIpKey]) === false) {
							$nodeData[$nodeExternalIpKey] = $nodeExternalIps[$nodeExternalIpKey] = $nodeExternalIpVersions[$nodeIpVersion][] = $parameters['data'][$nodeExternalIpKey];
							$response['status_valid'] = true;
						}
					}
				}

				if ($response['status_valid'] === true) {
					$response['status_valid'] = (
						$nodeExternalIpVersions === $this->_sanitizeIps($nodeExternalIps) &&
						count(current($nodeExternalIpVersions)) === 1
					);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid node external IPs, please try again.';
					}
				}

				if ($response['status_valid'] === true) {
					$nodeExternalIpTypes = array();

					foreach ($nodeExternalIpVersions as $nodeExternalIpVersion => $nodeExternalIpVersionIps) {
						$nodeExternalIpTypes[$this->_fetchIpType(current($nodeExternalIpVersionIps), $nodeExternalIpVersion)] = true;

						if (empty($nodeExternalIpTypes['private']) === false) {
							unset($parameters['data']['internal_ip_version_' . $nodeExternalIpVersion]);
						}
					}

					if (count($nodeExternalIpTypes) !== 1) {
						$response = array(
							'message' => 'Node external IPs must be either private or public, please try again.',
							'status_valid' => false
						);
					}
				}

				if ($response['status_valid'] === true) {
					$nodeInternalIpVersions = $nodeInternalIps = array();

					if (empty($nodeInternalIpTypes['public']) === false) {
						foreach ($nodeIpVersions as $nodeIpVersion) {
							$nodeInternalIpKey = 'internal_ip_version_' . $nodeIpVersion;

							if (empty($parameters['data'][$nodeInternalIpKey]) === false) {
								$nodeData[$nodeInternalIpKey] = $nodeInternalIps[$nodeInternalIpKey] = $nodeInternalIpVersions[$nodeIpVersion][] = $parameters['data'][$serverNodeInternalIpKey];
							}
						}

						$response['status_valid'] = (
							$nodeInternalIpVersions === $this->_sanitizeIps($nodeInternalIps) &&
							count(current($nodeInternalIpVersions)) === 1
						);

						if ($response['status_valid'] === false) {
							$response['message'] = 'Invalid node internal IPs, please try again.';
						}
					}
				}

				if ($response['status_valid'] === true) {
					$nodeInternalIpTypes = array();

					foreach ($nodeExternalIpVersions as $nodeExternalIpVersion => $nodeExternalIpVersionIps) {
						if ($this->_fetchIpType(current($nodeExternalIpVersionIps), $nodeExternalIpVersion) !== 'private') {
							$response = array(
								'message' => 'Node internal IPs must be private, please try again.',
								'status_valid' => false
							);
							break;
						}
					}
				}

				if ($response['status_valid'] === true) {
					$conflictingNodeCountParameters = array(
						'in' => 'nodes',
						'where' => array(
							'OR' => $nodeExternalIps
						)
					));

					if (empty($nodeId) !== false) {
						$conflictingNodeCountParameters['where']['OR'] = array(
							$conflictingNodeCountParameters['where'],
							array(
								'node_id' => $nodeId,
								'OR' => ($nodeExternalIps + $nodeInternalIps)
							)
						);
					}

					$conflictingNodeCount = $this->count($conflictingNodeCountParameters);
					$response['status_valid'] = (is_int($conflictingNodeCount) === true);
				}

				if ($response['status_valid'] === true) {
					$response['status_valid'] = ($conflictingNodeCount === 0);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Node IPs already in use, please try again.';
					}
				}

				if ($response['status_valid'] === true) {
					$response['status_valid'] = false;
					$nodeData = array(
						$nodeData
					);
					$nodeDataSaved = $this->save(array(
						'data' => $nodeData,
						'to' => 'nodes'
					));

					if ($nodeDataSaved === true) {
						$response = array(
							'message' => 'Node added successfully.',
							'status_valid' => true
						);
					}
				}
			}

			return $response;
		}

		public function edit($parameters) {
			$response = array(
				'message' => 'Error editing node, please try again.',
				'status_valid' => false
			);

			if (empty($parameters['data']['id']) === false) {
				$node = $nodeData = $this->fetch(array(
					'fields' => array(
						'external_ip_version_4',
						'external_ip_version_6',
						'internal_ip_version_4',
						'internal_ip_version_6',
						'node_id'
					),
					'from' => 'nodes',
					'where' => array(
						'id' => ($nodeId = $parameters['data']['id'])
					)
				));
				$response['status_valid'] = ($node !== false);

				if ($response['status_valid'] === true) {
					$response['status_valid'] = (empty($node) === false);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid node ID, please try again.';
					}
				}

				if ($response['status_valid'] === true) {
					$response['status_valid'] = false;
					$nodeExternalIpVersions = $nodeExternalIps = array();
					$nodeIpVersions = array(
						'4',
						'6'
					);

					foreach ($nodeIpVersions as $nodeIpVersion) {
						$nodeExternalIpKey = 'external_ip_version_' . $nodeIpVersion;

						if (empty($parameters['data'][$nodeExternalIpKey]) === false) {
							$nodeData[$nodeExternalIpKey] = $nodeExternalIps[$nodeExternalIpKey] = $nodeExternalIpVersions[$nodeIpVersion][] = $parameters['data'][$nodeExternalIpKey];
							$response['status_valid'] = true;
						}
					}
				}

				if ($response['status_valid'] === true) {
					$response['status_valid'] = (
						$nodeExternalIpVersions === $this->_sanitizeIps($nodeExternalIps) &&
						count(current($nodeExternalIpVersions)) === 1
					);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid node external IPs, please try again.';
					}
				}

				if ($response['status_valid'] === true) {
					$nodeExternalIpTypes = array();

					foreach ($nodeExternalIpVersions as $nodeExternalIpVersion => $nodeExternalIpVersionIps) {
						$nodeExternalIpTypes[$this->_fetchIpType(current($nodeExternalIpVersionIps), $nodeExternalIpVersion)] = true;

						if (empty($nodeExternalIpTypes['private']) === false) {
							unset($parameters['data']['internal_ip_version_' . $nodeExternalIpVersion]);
						}
					}

					if (count($nodeExternalIpTypes) !== 1) {
						$response = array(
							'message' => 'Node external IPs must be either private or public, please try again.',
							'status_valid' => false
						);
					}
				}

				if ($response['status_valid'] === true) {
					$nodeInternalIpVersions = $nodeInternalIps = array();

					if (empty($nodeInternalIpTypes['public']) === false) {
						foreach ($nodeIpVersions as $nodeIpVersion) {
							$nodeInternalIpKey = 'internal_ip_version_' . $nodeIpVersion;

							if (empty($parameters['data'][$nodeInternalIpKey]) === false) {
								$nodeData[$nodeInternalIpKey] = $nodeInternalIps[$nodeInternalIpKey] = $nodeInternalIpVersions[$nodeIpVersion][] = $parameters['data'][$serverNodeInternalIpKey];
							}
						}

						$response['status_valid'] = (
							$nodeInternalIpVersions === $this->_sanitizeIps($nodeInternalIps) &&
							count(current($nodeInternalIpVersions)) === 1
						);

						if ($response['status_valid'] === false) {
							$response['message'] = 'Invalid node internal IPs, please try again.';
						}
					}
				}

				if ($response['status_valid'] === true) {
					$nodeInternalIpTypes = array();

					foreach ($nodeExternalIpVersions as $nodeExternalIpVersion => $nodeExternalIpVersionIps) {
						if ($this->_fetchIpType(current($nodeExternalIpVersionIps), $nodeExternalIpVersion) !== 'private') {
							$response = array(
								'message' => 'Node internal IPs must be private, please try again.',
								'status_valid' => false
							);
							break;
						}
					}
				}

				if ($response['status_valid'] === true) {
					$nodeInternalIpTypes = array();

					foreach ($nodeExternalIpVersions as $nodeExternalIpVersion => $nodeExternalIpVersionIps) {
						if ($this->_fetchIpType(current($nodeExternalIpVersionIps), $nodeExternalIpVersion) !== 'private') {
							$response = array(
								'message' => 'Node internal IPs must be private, please try again.',
								'status_valid' => false
							);
							break;
						}
					}
				}

				if ($response['status_valid'] === true) {
					$conflictingNodeCountParameters = array(
						'in' => 'nodes',
						'where' => array(
							'id' != $nodeId,
							'OR' => array(
								array(
									'node_id' => null,
									'OR' => $nodeExternalIps
								),
								array(
									'node_id' => $nodeId,
									'OR' => ($nodeIps = ($nodeExternalIps + $nodeInternalIps))
								)
							)
						)
					));

					if (empty($node['node_id']) === false) {
						$conflictingNodeCountParameters['where']['OR'][] = array(
							'id' => $node['node_id'],
							'OR' => $nodeIps
						);
					}

					$conflictingNodeCount = $this->count($conflictingNodeCountParameters);
					$response['status_valid'] = (is_int($conflictingNodeCount) === true);
				}

				if ($response['status_valid'] === true) {
					$response['status_valid'] = ($conflictingNodeCount === 0);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Node IPs already in use, please try again.';
					}
				}

				if ($response['status_valid'] === true) {
					$response['status_valid'] = false;
					$nodeData = array(
						$nodeData
					);
					$nodeDataUpdated = $this->update(array(
						'data' => $nodeData,
						'in' => 'nodes',
						'where' => array(
							'id' => $nodeId
						)
					));

					if ($response['status_valid'] === true) {
						$response = array(
							'message' => 'Node edited successfully.',
							'status_valid' => true
						);
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
