<?php
	$extend = true;
	require_once($system->settings['base_path'] . '/methods/system.php');

	class NodeMethods extends SystemMethods {

		public function activate($parameters) {
			$response = array(
				'message' => 'Error activating node, please try again.',
				'status_valid' => (empty($parameters['where']['id']) === false)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$node = $this->fetch(array(
				'fields' => array(
					'node_id',
					'status_active'
				),
				'from' => 'nodes',
				'where' => array(
					'id' => ($nodeId = $parameters['where']['id'])
				)
			));
			$response['status_valid'] = ($node !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (empty($node) === false);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node ID, please try again.';
				return $response;
			}

			if (empty($node['node_id']) !== false) {
				$nodeId = $node['node_id'];
			}

			$response['data'] = array(
				'command' => 'cd /tmp && rm -rf /etc/cloud/ /var/lib/cloud/ ; apt-get update ; DEBIAN_FRONTEND=noninteractive apt-get -y install sudo ; sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk \'{print $1}\') ; sudo $(whereis telinit | awk \'{print $2}\') u ; sudo rm -rf /etc/cloud/ /var/lib/cloud/ ; sudo dpkg --configure -a ; sudo apt-get update && sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php wget --fix-missing && sudo wget -O proxy.php --no-dns-cache --retry-connrefused --timeout=60 --tries=2 "' . ($url = $_SERVER['REQUEST_SCHEME'] . '://' . $this->settings['base_domain']) . '/assets/php/proxy.php?' . time() . '" && sudo php proxy.php ' . $nodeId . ' ' . $url,
			);

			if ($node['status_active'] === true) {
				$response['message'] = 'Node is already activated.';
				return $response;
			} elseif (empty($parameters['user']['endpoint']) === false) {
				$nodesUpdated = $this->update(array(
					'data' => array(
						'status_active' => true
					),
					'in' => 'nodes',
					'where' => array(
						'OR' => array(
							'id' => $nodeId,
							'node_id' => $nodeId
						)
					)
				));
				$response['status_valid'] = ($nodesUpdated === true);

				if ($response['status_valid'] === false) {
					return $response;
				}

				$response['message'] = 'Node activated successfully.';
				return $response;
			}

			$response['message'] = 'Node is ready for activation.';
			return $response;
		}

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding node, please try again.',
				'status_valid' => false
			);

			if (empty($parameters['data']['node_id']) === false) {
				$node = $this->fetch(array(
					'fields' => array(
						'id',
						'node_id',
						'status_active',
						'status_deployed'
					),
					'from' => 'nodes',
					'where' => array(
						'OR' => array(
							'external_ip_version_4' => ($nodeNodeId = $parameters['data']['node_id']),
							'external_ip_version_6' => $nodeNodeId,
							'id' => $nodeNodeId
						)
					)
				));
				$response['status_valid'] = ($node !== false);

				if ($response['status_valid'] === false) {
					return $response;
				}

				$response['status_valid'] = (empty($node) === false);

				if ($response['status_valid'] === false) {
					$response['message'] = 'Invalid node ID, please try again.';
					return $response;
				}

				$nodeNodeId = $parameters['data']['node_id'] = $node['id'];

				if (empty($node['node_id']) === false) {
					$nodeNodeId = $parameters['data']['node_id'] = $node['node_id'];
				}

				$parameters['data']['status_active'] = $node['status_active'];
				$parameters['data']['status_deployed'] = $node['status_deployed'];
			}

			$nodeExternalIps = $nodeExternalIpVersions = array();
			$nodeIpVersions = array(
				4,
				6
			);

			foreach ($nodeIpVersions as $nodeIpVersion) {
				$nodeExternalIpKey = 'external_ip_version_' . $nodeIpVersion;

				if (empty($parameters['data'][$nodeExternalIpKey]) === false) {
					$nodeExternalIps[$nodeExternalIpKey] = $nodeExternalIpVersions[$nodeIpVersion][] = $parameters['data'][$nodeExternalIpKey];
				}
			}

			$response['status_valid'] = (empty($nodeExternalIps) === false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (
				$nodeExternalIpVersions === $this->_sanitizeIps($nodeExternalIps) &&
				(count(current($nodeExternalIpVersions)) === 1)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node external IPs, please try again.';
				return $response;
			}

			$nodeExternalIpTypes = array();

			foreach ($nodeExternalIpVersions as $nodeExternalIpVersion => $nodeExternalIpVersionIps) {
				$nodeExternalIpTypes[$this->_detectIpType(current($nodeExternalIpVersionIps), $nodeExternalIpVersion)] = true;

				if (empty($nodeExternalIpTypes['private']) === false) {
					unset($parameters['data']['internal_ip_version_' . $nodeExternalIpVersion]);
				}
			}

			$response['status_valid'] = (count($nodeExternalIpTypes) === 1);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Node external IPs must be either private or public, please try again.';
				return $response;
			}

			$nodeInternalIps = $nodeInternalIpVersions = array();

			foreach ($nodeIpVersions as $nodeIpVersion) {
				$nodeInternalIpKey = 'internal_ip_version_' . $nodeIpVersion;

				if (empty($parameters['data'][$nodeInternalIpKey]) === false) {
					$nodeInternalIps[$nodeInternalIpKey] = $nodeInternalIpVersions[$nodeIpVersion][] = $parameters['data'][$serverNodeInternalIpKey];
				}
			}

			$response['status_valid'] = (
				(empty($nodeInternalIps) === true) ||
				(
					($nodeInternalIpVersions === $this->_sanitizeIps($nodeInternalIps)) &&
					(count(current($nodeInternalIpVersions)) === 1)
				)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node internal IPs, please try again.';
				return $response;
			}

			foreach ($nodeInternalIpVersions as $nodeInternalIpVersion => $nodeInternalIpVersionIps) {
				$response['status_valid'] = ($this->_detectIpType(current($nodeInternalIpVersionIps), $nodeInternalIpVersion) === 'private');

				if ($response['status_valid'] === false) {
					$response['message'] = 'Node internal IPs must be private, please try again.';
					return $response;
				}
			}

			$conflictingNodeCountParameters = array(
				'in' => 'nodes',
				'where' => array(
					'OR' => $nodeExternalIps
				)
			);
			$conflictingNodeProcessCountParameters = array(
				'in' => 'node_processes',
				'where' => array(
					'OR' => $nodeExternalIps
				)
			);

			if (empty($nodeNodeId) === false) {
				$conflictingNodeCountParameters['where']['OR'] = $conflictingNodeProcessCountParameters['where']['OR'] = array(
					$conflictingNodeCountParameters['where'],
					array(
						'node_id' => $nodeNodeId,
						'OR' => ($nodeExternalIps + $nodeInternalIps)
					)
				);
			}

			$conflictingNodeCount = $this->count($conflictingNodeCountParameters);
			$conflictingNodeProcessCount = $this->count($conflictingNodeProcessCountParameters);
			$response['status_valid'] = (
				(is_int($conflictingNodeCount) === true) &&
				(is_int($conflictingNodeProcessCount) === true)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (
				($conflictingNodeCount === 0) &&
				($conflictingNodeProcessCount === 0)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Node IPs already in use, please try again.';
				return $response;
			}

			$nodesSaved = $this->save(array(
				'data' => array_intersect_key($parameters['data'], array(
					'external_ip_version_4' => true,
					'external_ip_version_6' => true,
					'internal_ip_version_4' => true,
					'internal_ip_version_6' => true,
					'node_id' => true,
					'status_active' => true,
					'status_deployed' => true
				)),
				'to' => 'nodes'
			));
			$response['status_valid'] = ($nodesSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			if (empty($nodeNodeId) === true) {
				$node = $this->fetch(array(
					'fields' => array(
						'id'
					),
					'from' => 'nodes',
					'where' => ($nodeIps = ($nodeExternalIps + $nodeInternalIps))
				));
				$response['status_valid'] = (
					($node !== false) &&
					(empty($node['id']) === false)
				);

				if ($response['status_valid'] === false) {
					$this->delete(array(
						'from' => 'nodes',
						'where' => $nodeIps
					));
					return $response;
				}

				$nodeId = $node['id'];

				foreach ($this->settings['node_process_type_default_port_numbers'] as $nodeProcessType => $nodeProcessTypeDefaultPortNumber) {
					$nodeProcessData = array();

					foreach (range(0, 9) as $processPortNumberIndex) {
						$nodeProcessData[] = array(
							'node_id' => $nodeId,
							'port_number' => ($nodeProcessTypeDefaultPortNumber + $processPortNumberIndex),
							'type' => $nodeProcessType
						);
					}

					$nodeProcessesSaved = $this->save(array(
						'data' => $nodeProcessData,
						'to' => 'node_processes'
					));
					$response['status_valid'] = ($nodeProcessesSaved !== false);

					if ($response['status_valid'] === false) {
						$this->delete(array(
							'from' => 'node_processes',
							'where' => array(
								'node_id' => $nodeId
							)
						));
						$this->delete(array(
							'from' => 'nodes',
							'where' => array(
								'id' => $nodeId
							)
						));
						return $response;
					}
				}
			}

			$response['message'] = 'Node added successfully.';
			return $response;
		}

		public function deactivate($parameters) {
			$response = array(
				'message' => 'Error deactivating node, please try again.',
				'status_valid' => (empty($parameters['where']['id']) === false)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$node = $this->fetch(array(
				'fields' => array(
					'node_id',
					'status_active'
				),
				'from' => 'nodes',
				'where' => array(
					'id' => ($nodeId = $parameters['where']['id'])
				)
			));
			$response['status_valid'] = ($node !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (empty($node) === false);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node ID, please try again.';
				return $response;
			}

			if (empty($node['node_id']) !== false) {
				$nodeId = $node['node_id'];
			}

			if ($node['status_active'] === false) {
				$response['message'] = 'Node is already deactivated.';
				return $response;
			} elseif (empty($parameters['user']['endpoint']) === false) {
				$nodesUpdated = $this->update(array(
					'data' => array(
						'status_active' => false
					),
					'where' => array(
						'OR' => array(
							'id' => $nodeId,
							'node_id' => $nodeId
						)
					)
				));
				$response['status_valid'] = ($nodesUpdated === true);

				if ($response['status_valid'] === false) {
					return $response;
				}

				$response['message'] = 'Node deactivated successfully.';
				return $response;
			}

			$response['message'] = 'Node is ready for deactivation.';
			return $response;
		}

		public function deploy($parameters) {
			$response = array(
				'message' => 'Error deploying node, please try again.',
				'status_valid' => (empty($parameters['where']['id']) === false)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$node = $this->fetch(array(
				'fields' => array(
					'node_id',
					'status_active',
					'status_deployed'
				),
				'from' => 'nodes',
				'where' => array(
					'id' => ($nodeId = $parameters['where']['id'])
				)
			));
			$response['status_valid'] = ($node !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (empty($node) === false);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node ID, please try again.';
				return $response;
			}

			if (empty($node['node_id']) !== false) {
				$nodeId = $node['node_id'];
			}

			$response['status_valid'] = (empty($node['status_active']) === false);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Node activation required before deployment, please try again.';
				return $response;
			}

			if ($node['status_deployed'] === true) {
				$response['message'] = 'Node is already deployed.';
				return $response;
			} elseif (
				(empty($parameters['user']['node_id']) === false) &&
				($nodeId === $parameters['user']['node_id'])
			) {
				$nodesUpdated = $this->update(array(
					'data' => array(
						'status_deployed' => true
					),
					'where' => array(
						'OR' => array(
							'id' => $nodeId,
							'node_id' => $nodeId
						)
					)
				));
				$response['status_valid'] = ($nodesUpdated === true);

				if ($response['status_valid'] === false) {
					return $response;
				}

				$response['message'] = 'Node deployed successfully.';
				return $response;
			}

			$response['message'] = 'Node is ready for deployment.';
			return $response;
		}

		public function edit($parameters) {
			$response = array(
				'message' => 'Error editing node, please try again.',
				'status_valid' => (empty($parameters['data']['id']) === false)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$node = $this->fetch(array(
				'fields' => array(
					'node_id',
					'status_deployed'
				),
				'from' => 'nodes',
				'where' => array(
					'id' => ($nodeId = $parameters['data']['id'])
				)
			));
			$response['status_valid'] = ($node !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (empty($node) === false);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node ID, please try again.';
				return $response;
			}

			if (isset($parameters['data']['status_active']) === false) {
				$parameters['data']['status_active'] = boolval($parameters['data']['status_active']);

				if ($node['status_deployed'] === false) {
					$parameters['data']['status_active'] = false;
				}
			}

			$nodeExternalIps = $nodeExternalIpVersions = array();
			$nodeIpVersions = array(
				4,
				6
			);

			foreach ($nodeIpVersions as $nodeIpVersion) {
				$nodeExternalIpKey = 'external_ip_version_' . $nodeIpVersion;

				if (empty($parameters['data'][$nodeExternalIpKey]) === false) {
					$nodeExternalIps[$nodeExternalIpKey] = $nodeExternalIpVersions[$nodeIpVersion][] = $parameters['data'][$nodeExternalIpKey];
				}
			}

			$response['status_valid'] = (empty($nodeExternalIps) === false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (
				($nodeExternalIpVersions === $this->_sanitizeIps($nodeExternalIps)) &&
				(count(current($nodeExternalIpVersions)) === 1)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node external IPs, please try again.';
				return $response;
			}

			$nodeExternalIpTypes = array();

			foreach ($nodeExternalIpVersions as $nodeExternalIpVersion => $nodeExternalIpVersionIps) {
				$nodeExternalIpTypes[$this->_detectIpType(current($nodeExternalIpVersionIps), $nodeExternalIpVersion)] = true;

				if (empty($nodeExternalIpTypes['private']) === false) {
					unset($parameters['data']['internal_ip_version_' . $nodeExternalIpVersion]);
				}
			}

			$response['status_valid'] = (count($nodeExternalIpTypes) === 1);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Node external IPs must be either private or public, please try again.';
				return $response;
			}

			$nodeInternalIps = $nodeInternalIpVersions = array();

			foreach ($nodeIpVersions as $nodeIpVersion) {
				$nodeInternalIpKey = 'internal_ip_version_' . $nodeIpVersion;

				if (empty($parameters['data'][$nodeInternalIpKey]) === false) {
					$nodeInternalIps[$nodeInternalIpKey] = $nodeInternalIpVersions[$nodeIpVersion][] = $parameters['data'][$serverNodeInternalIpKey];
				}
			}

			$response['status_valid'] = (
				(empty($nodeInternalIps) === true) ||
				(
					($nodeInternalIpVersions === $this->_sanitizeIps($nodeInternalIps)) &&
					(count(current($nodeInternalIpVersions)) === 1)
				)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node internal IPs, please try again.';
				return $response;
			}

			foreach ($nodeInternalIpVersions as $nodeInternalIpVersion => $nodeInternalIpVersionIps) {
				$response['status_valid'] = ($this->_detectIpType(current($nodeInternalIpVersionIps), $nodeInternalIpVersion) === 'private');

				if ($response['status_valid'] === false) {
					$response['message'] = 'Node internal IPs must be private, please try again.';
					return $response;
				}
			}

			$existingNodeProcessPorts = $this->fetch(array(
				'fields' => array(
					'number'
				),
				'from' => 'node_process_ports',
				'where' => array(
					'node_id' => ($nodeIds = array(
						$nodeId,
						$node['node_id']
					))
				)
			));
			$response['status_valid'] = ($existingNodeProcessPorts !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$existingNodeProcessPortNumbers = array();

			foreach ($existingNodeProcessPorts as $existingNodeProcessPort) {
				$existingNodeProcessPortNumbers[$existingNodeProcessPort['number']] = $existingNodeProcessPort['number'];
			}

			$nodeIps = $nodeExternalIps + $nodeInternalIps;

			foreach ($this->settings['node_process_type_default_port_numbers'] as $nodeProcessType => $nodeProcessTypeDefaultPortNumber) {
				$response['status_valid'] = (isset($parameters['data']['enable_' . $nodeProcessType . '_processes']) === true);

				if ($response['status_valid'] === false) {
					$response['message'] = 'Processes must be either enabled or disabled, please try again.';
					return $response;
				}

				if ($parameters['data']['enable_' . $nodeProcessType . '_processes'] === false) {
					$nodePortsDeleted = $this->delete(array(
						'from' => 'node_ports',
						'where' => array(
							'node_id' => $nodeIds,
							'node_process_type' => $nodeProcessType
						)
					));
					$nodeProcessesDeleted = $this->delete(array(
						'from' => 'node_processes',
						'where' => array(
							'node_id' => $nodeIds,
							'type' => $nodeProcessType
						)
					));
					$response['status_valid'] = (
						($nodePortsDeleted === true) &&
						($nodeProcessesDeleted === true)
					);

					if ($response['status_valid'] === false) {
						return $response;
					}
				} else {
					$nodeProcessPorts = $this->fetch(array(
						'fields' => array(
							'id',
							'number',
							'status_allowing',
							'status_denying'
						),
						'from' => 'node_process_ports',
						'where' => array(
							'node_id' => $nodeIds,
							'node_process_type' => $nodeProcessType
						)
					));
					$response['status_valid'] = ($nodeProcessPorts !== false);

					if ($response['status_valid'] === false) {
						return $response;
					}

					$nodeProcessPortNumbers = $nodeProcessPortStatusAllowingPortNumbers = $nodeProcessPortStatusDenyingPortNumbers = array();

					foreach ($nodeProcessPorts as $nodeProcessPort) {
						if ($nodePort['status_allowing'] === true) {
							$nodeProcessPortStatusAllowingPortNumbers[$nodeProcessPort['number']] = $nodeProcessPort['number'];
						}

						if ($nodePort['status_denying'] === true) {
							$nodeProcessPortStatusDenyingPortNumbers[$nodeProcessPort['number']] = $nodeProcessPort['number'];
						}

						$nodeProcessPortNumbers[$nodeProcessPort['number']] = $nodeProcessPort['number'];
					}

					if (empty($nodeProcessPortStatusAllowingPortNumbers) === false) {
						$nodeProcessesDeleted = $this->delete(array(
							'from' => 'node_processes',
							'where' => array(
								'node_id' => $nodeIds,
								'port_number !=' => $nodeProcessPortStatusAllowingPortNumbers,
								'type' => $nodeProcessType
							)
						));
						$response['status_valid'] = ($nodeProcessesDeleted === true);
					}

					if ($response['status_valid'] === false) {
						return $response;
					}

					if (empty($nodeProcessPortStatusDenyingPortNumbers) === false) {
						$nodeProcessesDeleted = $this->delete(array(
							'from' => 'node_processes',
							'where' => array(
								'node_id' => $nodeIds,
								'port_number' => $nodeProcessPortStatusDenyingPortNumbers,
								'type' => $nodeProcessType
							)
						));
						$response['status_valid'] = ($nodeProcessesDeleted === true);
					}

					if ($response['status_valid'] === false) {
						return $response;
					}

					$nodeProcessPortCount = $this->count(array(
						'in' => 'node_process_ports',
						'where' => array(
							'node_process_type' => $nodeProcessType,
							'status_denying' => false
						)
					));
					$response['status_valid'] = (is_int($nodeProcessPortCount) === true);

					if ($response['status_valid'] === false) {
						return $response;
					}

					if (empty($nodeProcessPortStatusAllowingPortNumbers) === false) {
						$nodeProcessPortNumbers = $nodeProcessPortStatusAllowingPortNumbers;
					}

					$nodeProcessPortNumbers = array_diff($nodeProcessPortNumbers, $existingNodeProcessPortNumbers, $nodeProcessPortStatusDenyingPortNumbers);
					$nodeProcessData = array();

					foreach ($nodeProcessPortNumbers as $nodeProcessPortNumber) {
						$nodeProcessData[] = array(
							'port_number' => $nodeProcessPortNumber,
							'type' => $nodeProcessType
						);
					}

					$existingNodeProcessPortNumbers = array_merge($existingNodeProcessPortNumbers, $nodeProcessPortNumbers);
					$nodeProcessPortCount = (count($nodeProcessPortNumbers) + $nodeProcessPortCount);

					if (
						(empty($nodeProcessPortStatusAllowingPortNumbers) === true) &&
						($nodeProcessPortCount < 10)
					) {
						
						$nodeProcessPortNumber = $nodeProcess['port_number'];
						$nodeProcessPortData = array();

						foreach (range($nodeProcessPortCount, 10) as $nodeProcessIndex) {
							while (
								($nodeProcessPortNumber <= 65535) &&
								(in_array($nodeProcessPortNumber, $existingNodeProcessPortNumbers) === true)
							) {
								$nodeProcessPortNumber++;
							}

							if (in_array($nodeProcessPortNumber, $existingNodeProcessPortNumbers) === true) {
								break;
							}

							$existingNodeProcessPortNumbers[] = $nodeProcessPortNumber;
							$nodeProcessData[] = array(
								'port_number' => $nodeProcessPortNumber,
								'type' => $nodeProcessType
							);
							$nodeProcessPortData[] = array(
								'node_process_type' => $nodeProcessType,
								'number' => $nodeProcessPortNumber
							);
						}

						$nodeProcessPortsSaved = $this->save(array(
							'data' => $nodeProcessPortData,
							'to' => 'node_process_ports'
						));
						$response['status_valid'] = ($nodeProcessPortsSaved === true);

						if ($response['status_valid'] === false) {
							return $response;
						}
					}

					$nodeProcessPortsUpdated = $this->update(array(
						'data' => array(
							'status_processed' => true
						),
						'in' => 'node_process_ports',
						'where' => array(
							'node_id' => $nodeIds
						)
					));
					$nodeProcessesSaved = $this->save(array(
						'data' => $nodeProcessData,
						'to' => 'node_processes'
					));
					$response['status_valid'] = (
						($nodePortsUpdated === true) &&
						($nodeProcessesSaved === true)
					);

					if ($response['status_valid'] === false) {
						return $response;
					}
				}
			}

			foreach ($nodeIpVersions as $nodeIpVersion) {
				$response['status_valid'] = (
					(
						(empty($parameters['data']['destination_address_version_' . $nodeIpVersion]) === false) ||
						(empty($parameters['data']['destination_port_number_version_' . $nodeIpVersion]) === false)
					) &&
					(
						(empty($parameters['data']['destination_address_version_' . $nodeIpVersion]) === true) ||
						(empty($parameters['data']['destination_port_number_version_' . $nodeIpVersion]) === true)
					)
				);

				if ($response['status_valid'] === false) {
					$response['message'] = 'Both destination address and port number are required for reverse proxy forwarding, please try again.';
					return $response;
				}

				$nodeDestinationAddress = $parameters['data']['destination_address_version_' . $nodeIpVersion];
				$nodeDestinationPort = $parameters['data']['destination_port_number_version_' . $nodeIpVersion];

				if (
					(empty($nodeDestinationAddress) === false) &&
					(empty($nodeDestinationPortNumber) === false)
				) {
					$response['status_valid'] = (
						(empty($nodeDestinationPortNumber) === true) ||
						($this->_validatePortNumber($nodeDestinationPortNumber) === false)
					);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid IP version ' . $nodeIpVersion . ' destination port number, please try again.';
						return $response;
					}

					$response['status_valid'] = (
						(empty($nodeDestinationAddress) === true) ||
						($this->_validateHostname($nodeDestinationAddress) !== false)
					);

					if ($response['status_valid'] === false) {
						$nodeDestinationIp = $this->_sanitizeIps(array($nodeDestinationAddress));
						$response['status_valid'] = (empty($nodeDestinationIp[$nodeIpVersion]) === false);
					}

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid IP version ' . $nodeIpVersion . ' destination address, please try again.';
						return $response;
					}
				} else {
					unset($parameters['data']['destination_address_version_' . $nodeIpVersion]);
					unset($parameters['data']['destination_port_number_version_' . $nodeIpVersion]);
				}
			}

			$conflictingNodeCountParameters = array(
				'in' => 'nodes',
				'where' => array(
					'id' != $nodeId,
					'OR' => array(
						array(
							'OR' => $nodeExternalIps
						),
						array(
							'node_id' => $nodeId,
							'OR' => $nodeIps
						)
					)
				)
			);
			$conflictingNodeProcessCountParameters = array(
				'in' => 'node_processes',
				'where' => array(
					'OR' => array(
						array(
							'OR' => $nodeExternalIps
						),
						array(
							'node_id' => $nodeId,
							'OR' => $nodeIps
						)
					)
				)
			);

			if (empty($node['node_id']) === false) {
				$conflictingNodeCountParameters['where']['OR'][] = array(
					'id' => $node['node_id'],
					'OR' => $nodeIps
				);
				$conflictingNodeProcessCountParameters['where']['OR'][] = array(
					'node_id' => $node['node_id'],
					'OR' => $nodeIps
				);
			}

			$conflictingNodeCount = $this->count($conflictingNodeCountParameters);
			$response['status_valid'] = (is_int($conflictingNodeCount) === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($conflictingNodeCount === 0);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Node IPs already in use, please try again.';
				return $response;
			}

			$nodeRecursiveDnsDestinationData = array(
				'node_id' => $nodeId
			);

			foreach ($nodeIpVersions as $nodeIpVersion) {
				if (isset($parameters['data']['recursive_dns_ip_version_' . $nodeIpVersion]) === true) {
					$nodeRecursiveDnsDestinationIp = $this->_sanitizeIps(array($parameters['data']['recursive_dns_destination_ip_version_' . $nodeIpVersion]));
					$response['status_valid'] = (empty($nodeRecursiveDnsDestinationIp[$nodeIpVersion]) === false);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid node recursive DNS destination IP, please try again.';
						return $response;
					}

					$nodeRecursiveDnsData['ip_version_' . $nodeIpVersion] = $nodeRecursiveDnsDestinationIp[$nodeIpVersion];
				}

				if (empty($parameters['data']['recursive_dns_port_number_version_' . $nodeIpVersion]) === false) {
					$response['status_valid'] = ($this->_validatePortNumber($parameters['data']['recursive_dns_destination_port_number_version_' . $nodeIpVersion]) === false);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid node recursive DNS port number, please try again.';
						return $response;
					}

					$nodeRecursiveDnsDestinationData['port_version_' . $nodeIpVersion] = $parameters['data']['recursive_dns_destination_port_number_version_' . $nodeIpVersion];
				}
			}

			$nodesUpdated = $this->update(array(
				'data' => array_intersect_key($parameters['data'], array(
					'destination_address_version_4' => true,
					'destination_address_version_6' => true,
					'destination_port_number_version_4' => true,
					'destination_port_number_version_6' => true,
					'external_ip_version_4' => true,
					'external_ip_version_6' => true,
					'id' => true,
					'internal_ip_version_4' => true,
					'internal_ip_version_6' => true,
					'node_id' => true,
					'status_active' => true
				)),
				'in' => 'nodes',
				'where' => array(
					'id' => $nodeId
				)
			));
			$nodeRecursiveDnsDestinationsUpdated = $this->update(array(
				'data' => $nodeRecursiveDnsDestinationData,
				'in' => 'node_recursive_dns_destinations',
				'where' => array(
					'node_id' => $nodeId
				)
			));
			$nodeUsersDeleted = $this->delete(array(
				'in' => 'node_users',
				'where' => array(
					'status_removed' => true,
					'node_id' => $nodeId
				)
			));
			$nodeUsersUpdated = $this->update(array(
				'data' => array(
					'status_processed' => true
				),
				'in' => 'node_users',
				'where' => array(
					'status_removed' => false,
					'node_id' => $nodeId
				)
			));
			$response['status_valid'] = (
				($nodesUpdated === true) &&
				($nodeRecursiveDnsDestinationsUpdated === true) &&
				($nodeUsersDeleted === true) &&
				($nodeUsersUpdated === true)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['message'] = 'Node edited successfully.';
			return $response;
		}

		public function list($parameters) {
			$response = array(
				'message' => 'Error listing nodes, please try again.',
				'status_valid' => (
					(empty($parameters['where']['id']) === true) ||
					(is_int($parameters['where']['id']) === true)
				)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$nodeParameters = array(
				'fields' => array(
					'id',
					'destination_address_version_4',
					'destination_address_version_6',
					'destination_port_number_version_4',
					'destination_port_number_version_6',
					'external_ip_version_4',
					'external_ip_version_6',
					'internal_ip_version_4',
					'internal_ip_version_6',
					'node_id',
					'status_activated',
					'status_deployed',
					'status_processed'
				),
				'from' => 'nodes',
				'where' => array(
					'node_id' => null
				)
			);

			if (empty($parameters['where']['id']) === false) {
				$nodeParameters['where'] = array(
					'id' => $parameters['where']['id'],
					'node_id' => $parameters['where']['id']
				);
			}

			if (empty($parameters['limit']) === false) {
				$nodeParameters['limit'] = $parameters['limit'];
				$response['status_valid'] = (is_int($parameters['limit']) === true);
			}

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node list limit, please try again.';
				return $response;
			}

			if (empty($parameters['offset']) === false) {
				$nodeParameters['offset'] = $parameters['offset'];
				$response['status_valid'] = (is_int($parameters['offset']) === true);
			}

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node list limit, please try again.';
				return $response;
			}

			$response['status_valid'] = (
				(
					(empty($parameters['resource_usage_log_interval_hours']) === true) &&
					($parameters['resource_usage_log_interval_hours'] = 1)
				) ||
				(is_int($parameters['resource_usage_log_interval_hours']) === true)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node resource usage log interval, please try again.';
				return $response;
			}

			$nodes = $this->fetch($nodeParameters);
			$response['status_valid'] = ($nodes !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$nodeIds = array();

			foreach ($nodes as $node) {
				$nodeIds[] = $node['id'];
				$response['data']['nodes'][$node['id']] = $node;
			}

			// todo: add separate $nodeResourceUsageLogs and $nodeProcessResourceUsageLogs with peak values for log interval hours

			/*
			if (empty($nodeIds) === false) {
				$nodeResourceUsageLogs = $this->fetch(array(
					'fields' => array(
						'bytes_received',
						'bytes_sent',
						'cpu_capacity_cores',
						'cpu_capacity_megahertz',
						'cpu_percentage',
						'memory_capacity_megabytes',
						'memory_percentage',
						'node_id',
						'requests',
						'storage_capacity_megabytes',
						'storage_percentage'
					),
					'from' => 'node_resource_usage_logs',
					'where' => array(
						'created >' => date('Y-m-d H:i:s', strtotime('-' . $parameters['resource_usage_log_interval_hours'] . ' hours')),
						'node_id' => $nodeIds
					)
				));
				$response['status_valid'] = ($nodeResourceUsageLogs !== false);

				if ($response['status_valid'] === false) {
					return $response;
				}

				foreach ($nodeIds as $nodeId) {
					if (empty($parameters['where']['id']) === true) {
						$nodeCount = $this->count(array(
							'in' => 'nodes',
							'where' => array(
								'id' => $nodeId,
								'node_id' => $nodeId
							)
						));
						$nodeHttpProxyProcessCount = $this->count(array(
							'in' => 'node_processes',
							'where' => array(
								'node_id' => $nodeId,
								'type' => 'http_proxy'
							)
						));
						$nodeRecursiveDnsProcessCount = $this->count(array(
							'in' => 'node_processes',
							'where' => array(
								'node_id' => $nodeId,
								'type' => 'recursive_dns'
							)
						));
						$nodeSocksProxyProcessCount = $this->count(array(
							'in' => 'node_processes',
							'where' => array(
								'node_id' => $nodeId,
								'type' => 'socks_proxy'
							)
						));
						$response['status_valid'] = (
							($nodeCount !== false) &&
							($nodeHttpProxyProcessCount !== false) &&
							($nodeRecursiveDnsProcessCount !== false) &&
							($nodeSocksProxyProcessCount !== false)
						);

						if ($response['status_valid'] === false) {
							return $response;
						}

						$response['data']['nodes'][$nodeId]['node_count'] = $nodeNodeCount;
						$response['data']['nodes'][$nodeId]['node_http_proxy_process_count'] = $nodeHttpProxyProcessCount;
						$response['data']['nodes'][$nodeId]['node_recursive_dns_process_count'] = $nodeRecursiveDnsProcessCount;
						$response['data']['nodes'][$nodeId]['node_socks_proxy_process_count'] = nodeSocksProxyProcessCount;
					}
				}

				$nodeResourceUsageLogAverageKeys = array(
					'cpu_capacity_cores',
					'cpu_capacity_megahertz',
					'cpu_percentage_node_processing',
					'cpu_percentage_node_usage',
					'memory_capacity_megabytes',
					'memory_percentage_node_processing',
					'memory_percentage_node_usage',
					'memory_percentage_tcp',
					'memory_percentage_udp',
					'storage_capacity_megabytes',
					'storage_percentage'
				);

				foreach ($nodeResourceUsageLogs as $nodeResourceUsageLog) {
					$nodeResourceUsageLogNodeId = $nodeResourceUsageLog['node_id'];
					unset($response['data']['nodes'][$nodeResourceUsageLogNodeId]['resource_usage_logs']['node_id']);

					if (empty($response['data']['nodes'][$nodeResourceUsageLogNodeId]['resource_usage_logs']) === true) {
						$nodeResourceUsageLog['count'] = 1;
						$response['data']['nodes'][$nodeResourceUsageLogNodeId]['resource_usage_logs'] = $nodeResourceUsageLog;
					} else {
						foreach ($response['data']['nodes'][$nodeResourceUsageLogNodeId] as $nodeResourceUsageLogKey => $nodeResourceUsageLogValue) {
							$response['data']['nodes'][$nodeResourceUsageLogNodeId][$nodeResourceUsageLogKey] += $nodeResourceUsageLog[$nodeResourceUsageLogKey];
						}

						$response['data']['nodes'][$nodeResourceUsageLogNodeId]['resource_usage_logs']['count']++;
					}
				}

				foreach ($nodeIds as $nodeId) {
					if (empty($response['data']['nodes'][$nodeId]['resource_usage_logs']['count']) === false) {
						foreach ($nodeResourceUsageLogAverageKeys as $nodeResourceUsageLogAverageKey) {
							$response['data']['nodes'][$nodeId][$nodeResourceUsageLogAverageKey] = ceil($response['data']['nodes'][$nodeId][$nodeResourceUsageLogAverageKey] / $response['data']['nodes'][$nodeId]['resource_usage_logs']['count']);
						}

						unset($response['data']['nodes'][$nodeId]['resource_usage_logs']['count']);
					}
				}
			}*/

			return $response;
		}

		public function process() {
			$response = array(
				'data' => array(
					'node_ip_versions' => ($nodeIpVersions = array(
						32 => 4,
						128 => 6
					)),
					'private_network' => $this->settings['private_network'],
					'version' => $this->settings['version']
				),
				'message' => 'Error processing nodes, please try again.',
				'status_valid' => (empty($parameters['where']['id']) === false)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$nodeId = $parameters['where']['id'];

			if (isset($parameters['data']['processed']) === true) {
				$nodeDataUpdated = $this->update(array(
					'data' => array(
						'status_processed' => boolval($parameters['data']['processed'])
					),
					'in' => 'nodes',
					'where' => array(
						'id' => $nodeId,
						'node_id' => $nodeId
					)
				));
				$response['status_valid'] = $nodeDataUpdated;

				if ($response['status_valid'] === false) {
					return $response;
				}

				$response['message'] = 'Nodes updated for processing successfully.';

				if (empty($parameters['data']['processed']) === false) {
					$response['message'] = 'Nodes processed successfully.';
				}

				return $response;
			}

			$nodeCount = $this->count(array(
				'in' => 'nodes',
				'where' => array(
					'id' => $nodeId,
					'node_id' => $nodeId,
					'status_processed' => false
				)
			));
			$response['status_valid'] = (
				(is_int($nodeCount) === true) &&
				($nodeCount > 0)
			);

			// todo: verify if processes need to be scaled before exiting

			if ($response['status_valid'] === false) {
				return $response;
			}

			$nodes = $this->fetch(array(
				'fields' => array(
					'destination_address_version_4',
					'destination_address_version_6',
					'destination_port_number_version_4',
					'destination_port_number_version_6',
					'external_ip_version_4',
					'external_ip_version_6',
					'id',
					'internal_ip_version_4',
					'internal_ip_version_6',
					'node_id',
					'status_active',
					'status_deployed'
				),
				'from' => 'nodes',
				'where' => array(
					'id' => $nodeId,
					'node_id' => $nodeId
				)
			));
			$response['status_valid'] = ($nodes !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (empty($nodes) === false);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node ID, please try again.';
				return $response;
			}

			foreach ($nodes as $node) {
				$response['data']['nodes'][$node['id']] = $node;

				foreach ($nodeIpVersions as $nodeIpVersion) {
					$nodeIps = array(
						$node['id'] . '_' . $nodeIpVersion => $node['external_ip_version_' . $nodeIpVersion],
						$node['external_ip_version_' . $nodeIpVersion] => $node['internal_ip_version_' . $nodeIpVersion]
					);

					foreach (array_filter($nodeIps) as $nodeIpKey => $nodeIp) {
						$response['data']['node_ips'][$nodeIpVersion][$nodeIpKey] = $nodeIp;
					}
				}
			}

			$nodeIpVersions = array_values($nodeIpVersions);

			foreach ($nodeIpVersions as $nodeIpVersionKey => $nodeIpVersion) {
				if (empty($response['data']['node_ips'][$nodeIpVersion]) === true) {
					unset($response['data']['node_ip_versions'][(128 / 4) + (96 * $nodeIpVersionKey)]);
				}
			}

			$existingNodeProcessPorts = $this->fetch(array(
				'fields' => array(
					'number',
					'status_allowing',
					'status_denying'
				),
				'from' => 'node_process_ports',
				'where' => array(
					'node_id' => $nodeId
					// ..
				)
			));
			$response['status_valid'] = ($existingNodePorts !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$existingNodeProcessPortNumbers = array();

			foreach ($existingNodeProcessPorts as $existingNodeProcessPort) {
				$existingNodeProcessPortNumbers[$existingNodeProcessPort['number']] = $existingNodeProcessPort['number'];
			}

			foreach ($this->settings['node_process_type_default_port_numbers'] as $nodeProcessType => $nodeProcessTypeDefaultPortNumber) {
				$nodeProcesses = $this->fetch(array(
					'fields' => array(
						'node_id',
						'port_id'
					),
					'from' => 'node_processes',
					'where' => array(
						'node_id' => $nodeId,
						'type' => $nodeProcessType
					)
				));
				$nodeProcessPorts = $this->fetch(array(
					'fields' => array(
						'number',
						'status_allowing',
						'status_denying'
					),
					'from' => 'node_process_ports',
					'where' => array(
						'node_id' => $nodeId,
						'node_process_type' => $nodeProcessType,
						// ..
					)
				));
				$nodeProcessResourceUsageLogs = $this->fetch(array(
					'fields' => array(
						'cpu_capacity_cores',
						'cpu_capacity_megahertz',
						'cpu_percentage'
					),
					'from' => 'node_process_resource_usage_logs',
					'where' => array(
						'created >' => date('Y-m-d H:i:s', strtotime('-1 hour')),
						'node_id' => $nodeId,
						'node_process_type' => $nodeProcessType
					)
				));
				$nodeUsers = $this->fetch(array(
					'fields' => array(
						'node_id',
						'user_id'
					),
					'from' => 'node_users',
					'where' => array(
						'node_id' => $nodeId,
						'status_removed' => false,
						'type' => $nodeProcessType
					)
				));
				$response['status_valid'] = (
					($nodeProcesses !== false) &&
					($nodeProcessPorts !== false) &&
					($nodeProcessResourceUsageLogs !== false) &&
					($nodeUsers !== false)
				);

				if ($response['status_valid'] === false) {
					return $response;
				}

				if (empty($nodeProcesses) === false) {
					end($nodeProcesses);
					$nodeProcessCount = key($nodeProcesses) + 1;
					$nodeProcessCountMaximum = min(100, ceil(($nodeProcessResourceUsageLogs[0]['cpu_capacity_cores'] * $nodeProcessResourceUsageLogs[0]['cpu_capacity_megahertz']) / 100));

					if (
						($nodeProcessPorts[0]['status_allowing'] === false) &&
						($nodeProcessCount < $nodeProcessCountMaximum)
					) {
						$nodeProcessResourceUsageLogCpuPercentage = $nodeProcessResourceUsageLogs[0]['cpu_percentage'] / $nodeProcessCount;

						if ($nodeProcessResourceUsageLogCpuPercentage > 0.5) {
							$nodeProcessPortNumber = $nodeProcessTypeDefaultPortNumber;

							foreach (range(1, min(5, $nodeProcessCountMaximum - $nodeProcessCount)) as $nodeProcessIndex) {
								while (
									($nodeProcessPortNumber <= 65535) &&
									(in_array($nodeProcessPortNumber, $existingNodeProcessPortNumbers) === true)
								) {
									$nodeProcessPortNumber++;
								}

								if (in_array($nodeProcessPortNumber, $existingNodeProcessPortNumbers) === true) {
									break;
								}

								$nodeProcessPortNumbers[] = $nodeProcessPortNumber;
								$nodeProcessCount++;
								$nodeProcessData[] = array(
									'port_number' => $nodeProcessPortNumber,
									'type' => $nodeProcessType
								);
							}
						}
					} elseif ($nodeProcessCount !== 10) {
						foreach ($nodeProcessResourceUsageLogs as $nodeProcessResourceUsageLog) {
							if (($nodeProcessResourceUsageLog['cpu_percentage'] / $nodeProcessCount) < 0.25) {
								$nodeProcessPortNumberIndex = 0;
								$nodeProcessPortNumbersToDelete = array();

								while (
									(count($nodeProcessPortNumbersToDelete) < 5) &&
									($nodeProcessCount > 10)
								) {
									if ($nodeProcesses[$nodeProcessPortNumberIndex]['number'] !== $nodeProcessTypeDefaultPortNumber) {
										$nodeProcessCount--;
										$nodeProcessPortNumbersToDelete[] = $nodeProcesses[$nodeProcessPortNumberIndex]['number'];
									}

									$nodeProcessPortNumberIndex++;
								}

								$nodeProcessesDeleted = $this->delete(array(
									'in' => 'node_processes',
									'where' => array(
										'node_id' => $nodeId,
										'number' => $nodeProcessPortNumbersToDelete,
										// ..
									)
								));
								$nodeProcessPortsDeleted = $this->delete(array(
									'in' => 'node_process_ports',
									'where' => array(
										'node_id' => $nodeId,
										'number' => $nodeProcessPortNumbersToDelete,
										// ..
									)
								));
								$response['status_valid'] = (
									($nodeProcessesDeleted !== false) &&
									($nodeProcessPortsDeleted !== false)
								);

								if ($response['status_valid'] === false) {
									return $response;
								}

								break;
							}
						}
					}

					if ($nodeProcessCount !== key($nodeProcesses) + 1) {
						$nodeProcesses = $this->fetch(array(
							'fields' => array(
								'node_id',
								'port_id'
							),
							'from' => 'node_processes',
							'where' => array(
								'node_id' => $nodeId,
								'type' => $nodeProcessType
							)
						));
						$response['status_valid'] = ($nodeProcesses !== false);

						if ($response['status_valid'] === false) {
							return $response;
						}
					}

					$response['data']['node_processes'][$nodeProcessType] = array_chunk($nodeProcesses, ($nodeProcessCount / 2));
				}

				if (empty($nodeUsers) === false) {
					$userIds = array();

					foreach ($nodeUsers as $nodeUser) {
						$response['data']['node_users'][$nodeProcessType][$nodeUser['node_id']][$nodeUser['user_id']] = $nodeUser['user_id'];
						$userIds[$nodeUser['user_id']] = $nodeUser['user_id'];
					}

					$userRequestDestinations = $this->fetch(array(
						'fields' => array(
							'request_destination_id',
							'user_id'
						),
						'from' => 'user_request_destinations',
						'where' => array(
							'status_removed' => false,
							'user_id' => $userIds
						)
					));
					$userRequestLimitRules = $this->fetch(array(
						'fields' => array(
							'request_limit_rule_id',
							'status_limit_exceeded_destination_only'
						),
						'from' => 'user_request_limit_rules',
						'where' => array(
							'limit_until !=' => null,
							'status_removed' => false,
							'user_id' => $userIds
						)
					));
					// todo: add limiting for status_limit_exceeded_destination_only based on node_user_request_destination_logs
					$users = $this->fetch(array(
						'fields' => array(
							'authentication_password',
							'authentication_username',
							'authentication_whitelist',
							'id',
							'status_allowing_request_destinations_only',
							'status_allowing_request_logs',
							'status_requiring_strict_authentication'
						),
						'from' => 'users',
						'where' => array(
							'id' => $userIds
						)
					));
					$response['status_valid'] = (
						($userRequestDestinations !== false) &&
						($userRequestLimitRules !== false) &&
						($users !== false)
					);

					if ($response['status_valid'] === false) {
						return $response;
					}

					if (empty($users) === false) {
						foreach ($users as $user) {
							$response['data']['users'][$nodeProcessType][$user['id']] = $user;
						}

						if (empty($userRequestDestinations) === false) {
							$requestDestinationIds = array();

							foreach ($userRequestDestinations as $userRequestDestination) {
								$requestDestinationIds[$userRequestDestination['request_destination_id']] = $response['data']['users'][$nodeProcessType][$user['id']]['request_destination_id'][] = $userRequestDestination['request_destination_id'];
							}

							$requestDestinations = $this->fetch(array(
								'fields' => array(
									'address',
									'id'
								),
								'from' => 'request_destinations',
								'where' => array(
									'id' => $requestDestinationIds
								)
							));
							$response['status_valid'] = ($requestDestinations !== false);

							if ($response['status_valid'] === false) {
								return $response;
							}

							foreach ($requestDestinations as $requestDestination) {
								$response['data']['request_destinations'][$nodeProcessType][$requestDestination['id']] = $requestDestination['address'];
							}
						}

						if (empty($userRequestLimitRules) === false) {
							$requestLimitUserIds = array();

							foreach ($userRequestLimitRules as $userRequestLimitRule) {
								if (empty($response['data']['node_users'][$nodeProcessType][$userRequestLimitRule['user_id']]['request_destination_id']) === true) {
									unset($response['data']['node_users'][$nodeProcessType][$userRequestLimitRule['user_id']]);
								}
							}
						}
					}
				}
			}

			$nodeRecursiveDnsDestinations = $this->fetch(array(
				'fields' => array(
					'ip_version_4',
					'ip_version_6',
					'node_id',
					'port_number_version_4',
					'port_number_version_6'
				),
				'from' => 'node_recursive_dns_destinations',
				'where' => array(
					'node_id' => array_keys($response['data']['nodes'])
				)
			));
			$response['status_valid'] = ($nodeRecursiveDnsDestinations !== false);

			if ($response['status_valid'] === false) {
                                return $response;
			}

			foreach ($nodeRecursiveDnsDestinations as $nodeRecursiveDnsDestination) {
				foreach ($nodeIpVersions as $nodeIpVersion) {
					$nodeRecursiveDnsProcess = (empty($response['data']['node_ips'][$nodeIpVersion][$nodeRecursiveDnsDestination['node_id'] . '_' . $nodeIpVersion]) === false);

					if (empty($response['data']['node_ips'][$nodeIpVersion][$nodeRecursiveDnsDestination['ip_version_' . $nodeIpVersion]]) === false) {
						$nodeRecursiveDnsDestination['ip_version_' . $nodeIpVersion] = $response['data']['node_ips'][$nodeIpVersion][$nodeRecursiveDnsDestination['ip_version_' . $nodeIpVersion]];
						$nodeRecursiveDnsProcess = true;
					}

					if (
						(empty($response['data']['node_system_recursive_dns_destinations'][$nodeIpVersion]) === true) ||
						($nodeRecursiveDnsProcess === true)
					) {
						$response['data']['node_system_recursive_dns_destinations'][$nodeIpVersion] = 'nameserver [' . $nodeRecursiveDnsDestination['ip_version_' . $nodeIpVersion] . ']:' . $nodeRecursiveDnsDestination['port_number_version_' . $nodeIpVersion];
					}
				}
			}

			$response['message'] = 'Nodes processed successfully.';
			return $response;
		}

		public function remove($parameters) {
			$response = array(
				'message' => 'Error removing nodes, please try again.',
				'status_valid' => (empty($parameters['where']['id']) === false)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$nodeCount = $this->count(array(
				'in' => 'nodes',
				'where' => array(
					'id' => ($nodeIds = $parameters['where']['id'])
				)
			));
			$response['status_valid'] = (is_int($nodeCount) === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($nodeCount === count($nodeIds));

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node IDs, please try again.';
				return $response;
			}

			$nodesDeleted = $this->delete(array(
				'from' => 'nodes',
				'where' => array(
					'id' => $nodeIds
				)
			));
			$nodeUsersDeleted = $this->delete(array(
				'from' => 'node_users',
				'where' => array(
					'node_id' => $nodeIds
				)
			));
			$response['status_valid'] = (
				($nodesDeleted === true) &&
				($nodeUsersDeleted === true)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['message'] = 'Nodes removed successfully.';
			return $response;
		}

		public function view($parameters = array()) {
			$response = array(
				'message' => 'Error viewing node, please try again.',
				'status_valid' => (empty($parameters['where']['id']) === false)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			/*
			if (
				(
					empty($parameters['id']) === true ||
					is_numeric($parameters['id']) === false
				) &&
				empty($parameters['where']['id']) === false
			) {
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
			*/

			return $response;
		}

	}

	if (empty($system->parameters) === false) {
		$nodeMethods = new NodeMethods();
		$data = $nodeMethods->route($system->parameters);
	}
?>
