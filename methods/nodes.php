<?php
	$extend = true;
	require_once($system->settings['base_path'] . '/methods/system.php');

	class NodeMethods extends SystemMethods {

		protected function _assignNodeInternalIp($existingNodeIps, $nodeIpVersion) {
			$response = false;
			$nodeInternalIpIncrement = 1;

			switch ($nodeIpVersion) {
				case 4:
					$nodeInternalIp = ip2long($this->settings['reserved_internal_ip'][4]);

					while ($response === false) {
						if (
							($nodeInternalIpIncrement === 1) &&
							($this->_detectIpType(long2ip($nodeInternalIp), 4) === 'public')
						) {
							$nodeInternalIpIncrement = -1;
						}

						$nodeInternalIp += $nodeInternalIpIncrement;

						if (in_array($nodeInternalIp, $existingNodeIps) === false) {
							$response = long2ip($nodeInternalIp);
						}
					}

					break;
				case 6:
					$nodeInternalIpBlock = substr($this->settings['reserved_internal_ip'][6], 0, -19);

					while ($response === false) {
						$nodeInternalIp = $nodeInternalIpBlock . implode(':', str_split(str_pad($nodeInternalIpIncrement, 16, '0', STR_PAD_LEFT), 4));
						$nodeInternalIpIncrement += 1;

						if (in_array($nodeInternalIp, $existingNodeIps) === false) {
							$response = $nodeInternalIp;
						}
					}

					break;
			}

			return $response;
		}

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
				$nodeNode = $this->fetch(array(
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
							'id' => $nodeNodeId,
							'internal_ip_version_4' => $nodeNodeId,
							'internal_ip_version_6' => $nodeNodeId
						)
					)
				));
				$response['status_valid'] = ($node !== false);

				if ($response['status_valid'] === false) {
					return $response;
				}

				$response['status_valid'] = (empty($nodeNode) === false);

				if ($response['status_valid'] === false) {
					$response['message'] = 'Invalid node ID, please try again.';
					return $response;
				}

				$parameters['data']['status_active'] = $nodeNode['status_active'];
				$parameters['data']['status_deployed'] = $nodeNode['status_deployed'];

				if (empty($nodeNode['node_id']) === false) {
					$nodeIdType = 'ID';

					if (is_numeric($parameters['data']['node_id']) === false) {
						$nodeIdType = 'IP';
					}

					$response['message'] = 'Node ' . $nodeIdType . ' ' . $parameters['data']['node_id'] . ' already belongs to node ID ' . $nodeNode['node_id'] . ', please try again.';
					return $response;
				}

				$nodeNodeId = $parameters['data']['node_id'] = $nodeNode['id'];
			}

			$nodeExternalIps = $nodeIpVersionExternalIps = array();
			$nodeIpVersions = array(
				4,
				6
			);

			foreach ($nodeIpVersions as $nodeIpVersion) {
				$nodeExternalIpKey = 'external_ip_version_' . $nodeIpVersion;

				if (empty($parameters['data'][$nodeExternalIpKey]) === false) {
					$nodeExternalIps[$nodeExternalIpKey] = $nodeIpVersionExternalIps[$nodeIpVersion][$parameters['data'][$nodeExternalIpKey]] = $parameters['data'][$nodeExternalIpKey];
				}
			}

			$response['status_valid'] = (empty($nodeExternalIps) === false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($nodeIpVersionExternalIps === $this->_sanitizeIps($nodeExternalIps));

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node external IPs, please try again.';
				return $response;
			}

			$nodeExternalIpTypes = array();

			foreach ($nodeIpVersionExternalIps as $nodeIpVersion => $nodeIpVersionExternalIp) {
				$nodeExternalIpTypes[$this->_detectIpType(current($nodeIpVersionExternalIp), $nodeIpVersion)] = true;

				if (empty($nodeExternalIpTypes['private']) === false) {
					unset($parameters['data']['internal_ip_version_' . $nodeIpVersion]);
				}
			}

			$response['status_valid'] = (count($nodeExternalIpTypes) === 1);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Node external IPs must be either private or public, please try again.';
				return $response;
			}

			$nodeInternalIps = $nodeIpVersionInternalIps = array();

			foreach ($nodeIpVersions as $nodeIpVersion) {
				$nodeInternalIpKey = 'internal_ip_version_' . $nodeIpVersion;

				if (empty($parameters['data'][$nodeInternalIpKey]) === false) {
					$nodeInternalIps[$nodeInternalIpKey] = $nodeIpVersionInternalIps[$nodeIpVersion][$parameters['data'][$serverNodeInternalIpKey]] = $parameters['data'][$serverNodeInternalIpKey];
				}
			}

			$response['status_valid'] = (
				(empty($nodeInternalIps) === true) ||
				($nodeIpVersionInternalIps === $this->_sanitizeIps($nodeInternalIps))
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node internal IPs, please try again.';
				return $response;
			}

			foreach ($nodeIpVersionInternalIps as $nodeIpVersion => $nodeIpVersionInternalIp) {
				$response['status_valid'] = ($this->_detectIpType(current($nodeIpVersionInternalIp), $nodeIpVersion) === 'private');

				if ($response['status_valid'] === false) {
					$response['message'] = 'Node internal IPs must be private, please try again.';
					return $response;
				}

				$response['status_valid'] = (empty($nodeIpVersionExternalIps[$nodeIpVersion]) === true);

				if ($response['status_valid'] === false) {
					$response['message'] = 'Node internal IPs must have a matching external IP, please try again.';
					return $response;
				}
			}

			$existingNodeParameters = array(
				'fields' => array(
					'external_ip_version_4',
					'external_ip_version_6',
					'internal_ip_version_4',
					'internal_ip_version_6'
				),
				'from' => 'nodes',
				'where' => array(
					'OR' => $nodeExternalIps
				)
			);

			if (empty($nodeNodeId) === false) {
				$existingNodeParameters['where']['OR'] = array(
					$existingNodeParameters['where'],
					array(
						'node_id' => $nodeNodeId,
						'OR' => ($nodeExternalIps + $nodeInternalIps)
					)
				);
			}

			$existingNode = $this->fetch($existingNodeParameters);
			$response['status_valid'] = ($existingNode !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (empty($existingNode) === true);

			if ($response['status_valid'] === false) {
				foreach ($existingNode as $existingNodeIp) {
					if (
						(in_array($existingNodeIp, $nodeExternalIps) === true) ||
						(in_array($existingNodeIp, $nodeInternalIps) === true)
					) {
						$response['message'] = 'Node IP ' . $existingNodeIp . ' already in use, please try again.';
						break;
					}
				}

				return $response;
			}

			$existingNodes = $this->fetch(array(
				'fields' => array(
					'external_ip_version_4',
					'external_ip_version_6',
					'id',
					'internal_ip_version_4',
					'internal_ip_version_6',
					'node_id',
					'reserved_internal_ip_version_4',
					'reserved_internal_ip_version_6'
				),
				'from' => 'nodes'
			));
			$response['status_valid'] = ($existingNodes !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$existingNodeIps = array();

			foreach ($existingNodes as $existingNode) {
				if (
					(empty($nodeNodeId) === true) ||
					(in_array($nodeNodeId, array(
						$existingNode['id'],
						$existingNode['node_id']
					)) === false)
				) {
					unset($existingNode['internal_ip_version_4']);
					unset($existingNode['internal_ip_version_6']);
					unset($existingNode['reserved_internal_ip_version_4']);
					unset($existingNode['reserved_internal_ip_version_6']);
				}

				unset($existingNode['id']);
				unset($existingNode['node_id']);

				foreach ($existingNode as $existingNodeIp) {
					$existingNodeIps[] = $existingNodeIp;
				}
			}

			foreach ($nodeIpVersions as $nodeIpVersion) {
				if (empty($nodeExternalIps['external_ip_version_' . $nodeIpVersion]) === false) {
					$existingNodeIps[] = $parameters['data']['reserved_internal_ip_version_' . $nodeIpVersion] = $this->_assignNodeInternalIp($existingNodeIps, $nodeIpVersion);
				}
			}

			if (empty($nodeNodeId) === false) {
				// todo: re-assign reserved internal ipv4 or ipv6 if internal ip conflicts
			}

			$nodesSaved = $this->save(array(
				'data' => array_intersect_key($parameters['data'], array(
					'external_ip_version_4' => true,
					'external_ip_version_6' => true,
					'internal_ip_version_4' => true,
					'internal_ip_version_6' => true,
					'node_id' => true,
					'reserved_internal_ip_version_4' => true,
					'reserved_internal_ip_version_6' => true,
					'status_active' => true,
					'status_deployed' => true
				)),
				'to' => 'nodes'
			));
			$response['status_valid'] = ($nodesSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$node = $this->fetch(array(
				'fields' => array(
					'id'
				),
				'from' => 'nodes',
				'where' => ($nodeExternalIps + $nodeInternalIps)
			));
			$response['status_valid'] = (
				($node !== false) &&
				(empty($node['id']) === false)
			);

			if ($response['status_valid'] === false) {
				$this->delete(array(
					'from' => 'nodes',
					'where' => ($nodeExternalIps + $nodeInternalIps)
				));
				return $response;
			}

			$nodeId = $node['id'];
			$nodeProcessData = $nodeProcessPortData = $nodeRecursiveDnsDestinationData = array();

			foreach ($this->settings['node_process_type_default_port_numbers'] as $nodeProcessType => $nodeProcessTypeDefaultPortNumber) {
				foreach (range(0, 9) as $nodeProcessPortNumberIndex) {
					$nodeProcessData[] = array(
						'node_id' => $nodeId,
						'port_number' => ($nodeProcessTypeDefaultPortNumber + $nodeProcessPortNumberIndex),
						'type' => $nodeProcessType
					);
					$nodeProcessPortData[] = array(
						'node_id' => $nodeId,
						'node_process_type' => $nodeProcessType,
						'number' => ($nodeProcessTypeDefaultPortNumber + $nodeProcessPortNumberIndex),
						'status_processed' => true
					);
				}

				if (strpos($nodeProcessType, 'proxy') === true) {
					foreach ($nodeIpVersions as $nodeIpVersion) {
						if (empty($nodeIpVersionExternalIps[$nodeIpVersion]) === false) {
							$existingNodeIps[] = $nodeRecursiveDnsDestinationData[$nodeProcessType]['listening_ip_version_' . $nodeIpVersion] = $this->_assignNodeInternalIp($existingNodeIps, $nodeIpVersion);
							$nodeRecursiveDnsDestinationData[$nodeProcessType]['listening_ip_version_' . $nodeIpVersion . '_node_id'] = $nodeRecursiveDnsDestinationData[$nodeProcessType]['node_id'] = $nodeId;
							$nodeRecursiveDnsDestinationData[$nodeProcessType]['listening_port_number_version_' . $nodeIpVersion] = $this->settings['node_process_type_default_port_numbers']['recursive_dns'];
							$nodeRecursiveDnsDestinationData[$nodeProcessType]['source_ip_version_' . $nodeIpVersion] = $nodeIpVersionExternalIps[$nodeIpVersion];
						}
					}

					$nodeRecursiveDnsDestinationData[$nodeProcessType]['node_id'] = $nodeId;
					$nodeRecursiveDnsDestinationData[$nodeProcessType]['node_process_type'] = $nodeProcessType;
				}
			}

			foreach ($nodeIpVersions as $nodeIpVersion) {
				if (empty($nodeIpVersionExternalIps[$nodeIpVersion]) === false) {
					$existingNodeIps[] = $nodeRecursiveDnsDestinationData['system']['listening_ip_version_' . $nodeIpVersion] = $this->_assignNodeInternalIp($existingNodeIps, $nodeIpVersion);
					$nodeRecursiveDnsDestinationData['system']['listening_ip_version_' . $nodeIpVersion . '_node_node_id'] = $nodeRecursiveDnsDestinationData['system']['node_node_id'] = $nodeId;
					$nodeRecursiveDnsDestinationData['system']['listening_port_number_version_' . $nodeIpVersion] = $this->settings['node_process_type_default_port_numbers']['recursive_dns'];
					$nodeRecursiveDnsDestinationData['system']['source_ip_version_' . $nodeIpVersion] = $nodeIpVersionExternalIps[$nodeIpVersion];
				}
			}

			$nodeRecursiveDnsDestinationData['system']['node_id'] = $nodeId;
			$nodeRecursiveDnsDestinationData['system']['node_process_type'] = 'system';
			$nodeProcessesSaved = $this->save(array(
				'data' => $nodeProcessData,
				'to' => 'node_processes'
			));
			$nodeProcessPortsSaved = $this->save(array(
				'data' => $nodeProcessPortData,
				'to' => 'node_process_ports'
			));
			$nodeRecursiveDnsDestinationsSaved = $this->save(array(
				'data' => $nodeRecursiveDnsDestinationData,
				'to' => 'node_recursive_dns_destinations'
			));
			$response['status_valid'] = (
				($nodeProcessesSaved !== false) &&
				($nodeProcessPortsSaved !== false) &&
				($nodeRecursiveDnsDestinationsSaved !== false)
			);

			if ($response['status_valid'] === false) {
				$this->delete(array(
					'from' => 'node_process_ports',
					'where' => array(
						'node_id' => $nodeId
					)
				));
				$this->delete(array(
					'from' => 'node_processes',
					'where' => array(
						'node_id' => $nodeId
					)
				));
				$this->delete(array(
					'from' => 'node_recursive_dns_destinations',
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

			$nodeIds = array();
			$$nodeIds[] = $nodeNodeId = $nodeId;

			if (empty($node['node_id']) === false) {
				$nodeIds[] = $nodeNodeId = $node['node_id'];
			}

			if (isset($parameters['data']['status_active']) === false) {
				$parameters['data']['status_active'] = boolval($parameters['data']['status_active']);

				if ($node['status_deployed'] === false) {
					$parameters['data']['status_active'] = false;
				}
			}

			$nodeExternalIps = $nodeIpVersionExternalIps = array();
			$nodeIpVersions = array(
				4,
				6
			);

			foreach ($nodeIpVersions as $nodeIpVersion) {
				$nodeExternalIpKey = 'external_ip_version_' . $nodeIpVersion;

				if (empty($parameters['data'][$nodeExternalIpKey]) === false) {
					$nodeExternalIps[$nodeExternalIpKey] = $nodeIpVersionExternalIps[$nodeIpVersion][$parameters['data'][$nodeExternalIpKey]] = $parameters['data'][$nodeExternalIpKey];
				}
			}

			$response['status_valid'] = (empty($nodeExternalIps) === false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($nodeIpVersionExternalIps === $this->_sanitizeIps($nodeExternalIps));

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node external IPs, please try again.';
				return $response;
			}

			$nodeExternalIpTypes = array();

			foreach ($nodeIpVersionExternalIps as $nodeIpVersion => $nodeIpVersionExternalIp) {
				$nodeExternalIpTypes[$this->_detectIpType(current($nodeIpVersionExternalIp), $nodeIpVersion)] = true;

				if (empty($nodeExternalIpTypes['private']) === false) {
					unset($parameters['data']['internal_ip_version_' . $nodeIpVersion]);
				}
			}

			$response['status_valid'] = (count($nodeExternalIpTypes) === 1);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Node external IPs must be either private or public, please try again.';
				return $response;
			}

			$nodeInternalIps = $nodeIpVersionInternalIps = array();

			foreach ($nodeIpVersions as $nodeIpVersion) {
				$nodeInternalIpKey = 'internal_ip_version_' . $nodeIpVersion;

				if (empty($parameters['data'][$nodeInternalIpKey]) === false) {
					$nodeInternalIps[$nodeInternalIpKey] = $nodeIpVersionInternalIps[$nodeIpVersion][$parameters['data'][$nodeInternalIpKey]] = $parameters['data'][$nodeInternalIpKey];
				}
			}

			$response['status_valid'] = (
				(empty($nodeInternalIps) === true) ||
				($nodeIpVersionInternalIps === $this->_sanitizeIps($nodeInternalIps))
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node internal IPs, please try again.';
				return $response;
			}

			foreach ($nodeIpVersionInternalIps as $nodeIpVersion => $nodeIpVersionInternalIp) {
				$response['status_valid'] = ($this->_detectIpType(current($nodeIpVersionInternalIp), $nodeIpVersion) === 'private');

				if ($response['status_valid'] === false) {
					$response['message'] = 'Node internal IPs must be private, please try again.';
					return $response;
				}
			}

			$existingNode = $this->fetch(array(
				'fields' => array(
					'external_ip_version_4',
					'external_ip_version_6',
					'id',
					'internal_ip_version_4',
					'internal_ip_version_6',
					'node_id',
					'reserved_internal_ip_version_4',
					'reserved_internal_ip_version_6'
				),
				'from' => 'nodes',
				'where' => array(
					'node_id' => $nodeId
				)
			));
			$response['status_valid'] = ($existingNode !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$existingNodeIps = array_intersect_key($existingNode, array(
				'external_ip_version_4' => true,
				'external_ip_version_6' => true,
				'internal_ip_version_4' => true,
				'internal_ip_version_6' => true
			));
			$nodeIps = array_merge($nodeExternalIps, $nodeInternalIps);

			foreach ($existingNodeIps as $existingNodeIpKey => $existingNodeIp) {
				if (
					(empty($nodeIps) === true) ||
					($existingNodeIp !== $nodeIps[$existingNodeIpKey])
				) {
					$nodeIpVersion = substr($existingNodeIpKey, -1);
					$existingNodeRecursiveDnsDestination = $this->fetch(array(
						'fields' => array(
							'node_process_type'
						),
						'from' => 'node_recursive_dns_destinations',
						'limit' => 1,
						'where' => array(
							'listening_ip_version_' . $nodeIpVersion . '_node_id' => $nodeId,
							'OR' => array(
								'listening_ip_version_' . $nodeIpVersion => $existingNodeIp,
								'source_ip_version_' . $nodeIpVersion => $existingNodeIp,
							)
						)
					));
					$response['status_valid'] = ($existingNodeRecursiveDnsDestination !== false);

					if ($response['status_valid'] === false) {
						return $response;
					}

					if (empty($existingNodeRecursiveDnsDestination) === false) {
						$existingNodeRecursiveDnsDestination['node_process_type'] = explode('_', $existingNodeRecursiveDnsDestination['node_process_type']);

						if (
							(empty($existingNodeRecursiveDnsDestination['node_process_type'][1]) === false) &&
							($existingNodeRecursiveDnsDestination['node_process_type'][1] === 'proxy')
						) {
							$existingNodeRecursiveDnsDestination['node_process_type'][0] = strtoupper($existingNodeRecursiveDnsDestination['node_process_type'][0]);
						}

						$existingNodeRecursiveDnsDestination['node_process_type'] = implode(' ', $existingNodeRecursiveDnsDestination['node_process_type']);
						$response['message'] = 'Existing ' . $existingNodeRecursiveDnsDestination['node_process_type'] . ' node recursive DNS destination must be changed for node IP ' . $existingNodeIp . ' before disabling node recursive DNS processes on this node, please try again.';
						return $response;
					}
				}
			}

			// todo: assign reserved internal ipv4 or ipv6 address if not assigned yet
                        // todo: re-assign reserved internal ipv4 or ipv6 if internal ip conflicts

			$existingNodeCountParameters = array(
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

			if (empty($node['node_id']) === false) {
				$existingNodeCountParameters['where']['OR'][] = array(
					'id' => $node['node_id'],
					'OR' => $nodeIps
				);
			}

			$existingNodeCount = $this->count($existingNodeCountParameters);
			$response['status_valid'] = (is_int($existingNodeCount) === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($existingNodeCount === 0);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Node IPs already in use, please try again.';
				return $response;
			}

			$existingNodeProcessPorts = $this->fetch(array(
				'fields' => array(
					'number'
				),
				'from' => 'node_process_ports',
				'where' => array(
					'node_id' => $nodeId
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

			$nodeRecursiveDnsDestinationData = array();

			foreach ($this->settings['node_process_type_default_port_numbers'] as $nodeProcessType => $nodeProcessTypeDefaultPortNumber) {
				$response['status_valid'] = (isset($parameters['data']['enable_' . $nodeProcessType . '_processes']) === true);

				if ($response['status_valid'] === false) {
					$response['message'] = 'Processes must be either enabled or disabled, please try again.';
					return $response;
				}

				if (
					(isset($parameters['data']['enable_' . $nodeProcessType . '_processes']) === false) ||
					($parameters['data']['enable_' . $nodeProcessType . '_processes'] === false)
				) {
					if ($nodeProcessType === 'recursive_dns') {
						foreach ($nodeIpVersions as $nodeIpVersion) {
							$existingNodeRecursiveDnsDestination = $this->fetch(array(
								'fields' => array(
									'listening_ip_version_' . $nodeIpVersion,
									'node_process_type',
									'source_ip_version_' . $nodeIpVersion
								),
								'from' => 'node_recursive_dns_destinations',
								'limit' => 1,
								'where' => array(
									'listening_ip_version_' . $nodeIpVersion . '_node_id' => $nodeId
								)
							));
							$response['status_valid'] = ($existingNodeRecursiveDnsDestination !== false);

							if ($response['status_valid'] === false) {
								return $response;
							}

							if (empty($existingNodeRecursiveDnsDestination) === false) {
								$existingNodeRecursiveDnsDestinationIp = $existingNodeRecursiveDnsDestination['listening_ip_version_' . $nodeIpVersion];

								if (empty($existingNodeRecursiveDnsDestination['source_ip_version_' . $nodeIpVersion]) === false) {
									$existingNodeRecursiveDnsDestinationIp = $existingNodeRecursiveDnsDestination['source_ip_version_' . $nodeIpVersion];
								}

								$existingNodeRecursiveDnsDestination['node_process_type'] = explode('_', $existingNodeRecursiveDnsDestination['node_process_type']);

								if (
									(empty($existingNodeRecursiveDnsDestination['node_process_type'][1]) === false) &&
									($existingNodeRecursiveDnsDestination['node_process_type'][1] === 'proxy')
								) {
									$existingNodeRecursiveDnsDestination['node_process_type'][0] = strtoupper($existingNodeRecursiveDnsDestination['node_process_type'][0]);
								}

								$existingNodeRecursiveDnsDestination['node_process_type'] = implode(' ', $existingNodeRecursiveDnsDestination['node_process_type']);
								$response['message'] = 'Existing ' . $existingNodeRecursiveDnsDestination['node_process_type'] . ' node recursive DNS destination must be changed for node IP ' . $existingNodeRecursiveDnsDestinationIp . ' before disabling node recursive DNS processes on this node, please try again.';
								return $response;
							}
						}
					}

					$nodePortsDeleted = $this->delete(array(
						'from' => 'node_process_ports',
						'where' => array(
							'node_id' => $nodeId,
							'node_process_type' => $nodeProcessType
						)
					));
					$nodeProcessesDeleted = $this->delete(array(
						'from' => 'node_processes',
						'where' => array(
							'node_id' => $nodeId,
							'type' => $nodeProcessType
						)
					));
					$nodeRecursiveDnsDestinationsDeleted = $this->delete(array(
						'from' => 'node_recursive_dns_destinations',
						'where' => array(
							'node_id' => $nodeId,
							'node_process_type' => $nodeProcessType
						)
					));
					$response['status_valid'] = (
						($nodePortsDeleted === true) &&
						($nodeProcessesDeleted === true) &&
						($nodeRecursiveDnsDestinationsDeleted === true)
					);

					if ($response['status_valid'] === false) {
						return $response;
					}
				} else {
					$nodeProcessPorts = $this->fetch(array(
						'fields' => array(
							'node_id',
							'node_process_type',
							'number',
							'status_allowing',
							'status_denying',
							'status_processed',
							'status_removed'
						),
						'from' => 'node_process_ports',
						'where' => array(
							'node_id' => $nodeId,
							'node_process_type' => $nodeProcessType
						)
					));
					$response['status_valid'] = ($nodeProcessPorts !== false);

					if ($response['status_valid'] === false) {
						return $response;
					}

					$existingNodeRecursiveDnsDestinationPortNumbers = $nodeProcessData = $nodeProcessPortData = array();

					foreach ($nodeProcessPorts as $nodeProcessPort) {
						$nodeProcessData[] = array(
							'node_id' => $nodeId,
							'port_number' => $nodeProcessPort['number'],
							'type' => $nodeProcessType
						);
						$nodeProcessPortData[$nodeProcessPort['number']] = $nodeProcessPort;

						if (
							($nodeProcessType === 'recursive_dns') &&
							(empty($nodeProcessPort['status_processed']) === true) &&
							(
								(empty($nodeProcessPort['status_denying']) === false) ||
								(
									(empty($nodeProcessPort['status_denying']) === true) &&
									(empty($nodeProcessPort['status_removed']) === false)
								)
							)
						) {
							$existingNodeRecursiveDnsDestinationPortNumbers[$nodeProcessPort['number']] = $nodeProcessPort['number'];
						}
					}

					if (empty($existingNodeRecursiveDnsDestinationPortNumbers) === false) {
						$existingNodeRecursiveDnsDestinationPortNumberParts = array_chunk($existingNodeRecursiveDnsDestinationPortNumbers, 1000);

						foreach ($existingNodeRecursiveDnsDestinationPortNumberParts as $existingNodeRecursiveDnsDestinationPortNumberPart) {
							foreach ($nodeIpVersions as $nodeIpVersion) {
								$existingNodeRecursiveDnsDestination = $this->fetch(array(
									'fields' => array(
										'listening_ip_version_' . $nodeIpVersion,
										'listening_port_version_' . $nodeIpVersion,
										'node_process_type',
										'source_ip_version_' . $nodeIpVersion
									),
									'from' => 'node_recursive_dns_destinations',
									'limit' => 1,
									'where' => array(
										'listening_ip_version_' . $nodeIpVersion . '_node_id' => $nodeId,
										'listening_port_version_' . $nodeIpVersion => $existingNodeRecursiveDnsDestinationPortNumberPart
									)
								));
								$response['status_valid'] = ($existingNodeRecursiveDnsDestination !== false);

								if ($response['status_valid'] === false) {
									return $response;
								}

								if (empty($existingNodeRecursiveDnsDestination) === false) {
									$existingNodeRecursiveDnsDestinationIp = $existingNodeRecursiveDnsDestination['listening_ip_version_' . $nodeIpVersion];

									if (empty($existingNodeRecursiveDnsDestination['source_ip_version_' . $nodeIpVersion]) === false) {
										$existingNodeRecursiveDnsDestinationIp = $existingNodeRecursiveDnsDestination['source_ip_version_' . $nodeIpVersion];
									}

									$existingNodeRecursiveDnsDestination['node_process_type'] = explode('_', $existingNodeRecursiveDnsDestination['node_process_type']);

									if (
										(empty($existingNodeRecursiveDnsDestination['node_process_type'][1]) === false) &&
										($existingNodeRecursiveDnsDestination['node_process_type'][1] === 'proxy')
									) {
										$existingNodeRecursiveDnsDestination['node_process_type'][0] = strtoupper($existingNodeRecursiveDnsDestination['node_process_type'][0]);
									}

									$existingNodeRecursiveDnsDestination['node_process_type'] = implode(' ', $existingNodeRecursiveDnsDestination['node_process_type']);
									$response['message'] = 'Existing ' . $existingNodeRecursiveDnsDestination['node_process_type'] . ' node recursive DNS destination must be changed for node IP ' . $existingNodeRecursiveDnsDestinationIp . ' before disabling node recursive DNS port ' . $existingNodeRecursiveDnsDestination['listening_port_version_' . $nodeIpVersion] . ' on this node, please try again.';
									return $response;
								}
							}
						}
					}

					if (empty($nodeProcessPort['status_allowing']) === false) {
						$nodeProcessesDeleted = $this->delete(array(
							'from' => 'node_processes',
							'where' => array(
								'node_id' => $nodeId,
								'type' => $nodeProcessType
							)
						));
						$nodeProcessPortsDeleted = $this->delete(array(
							'in' => 'node_process_ports',
							'where' => array(
								'node_id' => $nodeId,
								'type' => $nodeProcessType
							)
						));
						$nodeProcessesSaved = $this->save(array(
							'data' => $nodeProcessData,
							'to' => 'node_processes'
						));
						$nodeProcessPortsSaved = $this->save(array(
							'data' => $nodeProcessPortData,
							'to' => 'node_process_ports'
						));
						$response['status_valid'] = (
							($nodeProcessesDeleted === true) &&
							($nodeProcessPortsDeleted === true) &&
							($nodeProcessesSaved === true) &&
							($nodeProcessPortsSaved === true)
						);
					}

					if ($response['status_valid'] === false) {
						return $response;
					}

					if (empty($nodeProcessPort['status_denying']) === false) {
						$nodeProcessPortNumbers = array_keys($nodeProcessPortData);
						$nodeProcessesDeleted = $this->delete(array(
							'from' => 'node_processes',
							'where' => array(
								'node_id' => $nodeId,
								'port_number' => $nodeProcessPortNumbers,
								'type' => $nodeProcessType
							)
						));
						$nodeProcessPortsDeleted = $this->delete(array(
							'from' => 'node_process_ports',
							'where' => array(
								'node_id' => $nodeId,
								'number' => $nodeProcessPortNumbers,
								'node_process_type' => $nodeProcessType
							)
						));
						$nodeProcessPortsSaved = $this->save(array(
							'data' => $nodeProcessPortData,
							'to' => 'node_process_ports'
						));
						$response['status_valid'] = (
							($nodeProcessesDeleted === true) &&
							($nodeProcessPortsDeleted === true) &&
							($nodeProcessPortsSaved === true)
						);
					}

					if ($response['status_valid'] === false) {
						return $response;
					}

					$existingNodeRecursiveDnsDestination = $this->fetch(array(
						'fields' => array(
							'source_ip_version_4',
							'source_ip_version_6'
						),
						'from' => 'node_recursive_dns_destinations',
						'where' => array(
							'node_id' => $nodeId,
							'node_process_type' => $nodeProcessType
						)
					));
					$response['status_valid'] = ($existingNodeRecursiveDnsDestination !== false);

					if ($response['status_valid'] === false) {
						return $response;
					}

					$existingNodeRecursiveDnsDestinationProcess = array_filter($existingNodeRecursiveDnsDestination);

					foreach ($nodeIpVersions as $nodeIpVersion) {
						if (isset($parameters['data'][$nodeProcessType . '_recursive_dns_listening_ip_version_' . $nodeIpVersion]) === true) {
							$nodeRecursiveDnsDestinationIp = $this->_sanitizeIps(array($parameters['data'][$nodeProcessType . '_recursive_dns_destination_listening_ip_version_' . $nodeIpVersion]));
							$response['status_valid'] = (empty($nodeRecursiveDnsDestinationIp[$nodeIpVersion]) === false);

							if ($response['status_valid'] === false) {
								$response['message'] = 'Invalid proxy node process recursive DNS destination listening IP, please try again.';
								return $response;
							}

							$existingNodeCount = $this->count(array(
								'in' => 'nodes',
								'where' => array(
									'external_ip_version_' . $nodeIpVersion => $nodeRecursiveDnsDestinationIp[$nodeIpVersion],
									'OR' => array(
										'id' => $nodeIds,
										'node_id' => $nodeIds
									)
								)
							));
							$response['status_valid'] = (is_int($existingNodeCount) === true);

							if ($response['status_valid'] === false) {
								return $response;
							}

							$nodeRecursiveDnsDestinationData[$nodeProcessType]['listening_ip_version_' . $nodeIpVersion] = $nodeRecursiveDnsDestinationIp[$nodeIpVersion];
							$nodeRecursiveDnsDestinationData[$nodeProcessType]['listening_ip_version_' . $nodeIpVersion . '_node_node_id'] = null;
							$nodeRecursiveDnsDestinationData[$nodeProcessType]['listening_port_number_version_' . $nodeIpVersion] = $this->settings['node_process_type_default_port_numbers']['recursive_dns'];
							$nodeRecursiveDnsDestinationData[$nodeProcessType]['node_id'] = $nodeId;
							$nodeRecursiveDnsDestinationData[$nodeProcessType]['node_node_id'] = $nodeNodeId;
							$nodeRecursiveDnsDestinationData[$nodeProcessType]['node_process_type'] = $nodeProcessType;

							if (
								($existingNodeCount !== 0) &&
								(empty($existingNodeRecursiveDnsDestinationProcess) === true)
							) {
								if (empty($existingNodeIps) === true) {
									$existingNodeIps = $nodeIps;
									$existingNodes = $this->fetch(array(
										'fields' => array(
											'external_ip_version_4',
											'external_ip_version_6',
											'internal_ip_version_4',
											'internal_ip_version_6'
										),
										'from' => 'nodes',
										'where' => array(
											'OR' => array(
												'id' => $nodeIds,
												'node_id' => $nodeIds
											)
										)
									));
									$response['status_valid'] = (is_int($existingNodes) === true);

									if ($response['status_valid'] === false) {
										return $response;
									}

									foreach ($existingNodes as $existingNode) {
										foreach (array_filter($existingNode) as $existingNodeIp) {
											$existingNodeIps[] = $existingNodeIp;
										}
									}
								}

								$existingNodeIps[] = $nodeRecursiveDnsDestinationData[$nodeProcessType]['listening_ip_version_' . $nodeIpVersion] = $this->_assignNodeInternalIp($existingNodeIps, $nodeIpVersion);
								$nodeRecursiveDnsDestinationData[$nodeProcessType]['source_ip_version_' . $nodeIpVersion] = $nodeRecursiveDnsDestinationIp[$nodeIpVersion];
							}

							if ($existingNodeCount === 0) {
								$existingNode = $this->fetch(array(
									'fields' => array(
										'id',
										'node_id'
									),
									'from' => 'nodes',
									'where' => array(
										'external_ip_version_' . $nodeIpVersion => $nodeRecursiveDnsDestinationIp[$nodeIpVersion]
									)
								));
								$response['status_valid'] = ($existingNode !== false);

								if ($response['status_valid'] === false) {
									return $response;
								}

								if (empty($existingNode['id']) === false) {
									$nodeRecursiveDnsDestinationData[$nodeProcessType]['listening_ip_version_' . $nodeIpVersion . '_node_node_id'] = $existingNode['id'];

									if (empty($existingNode['node_id']) === false) {
										$nodeRecursiveDnsDestinationData[$nodeProcessType]['listening_ip_version_' . $nodeIpVersion . '_node_node_id'] = $existingNode['node_id'];
									}
								}

								if ($nodeNodeId !== $nodeRecursiveDnsDestinationData[$nodeProcessType]['listening_ip_version_' . $nodeIpVersion . '_node_node_id']) {
									$nodeRecursiveDnsDestinationData[$nodeProcessType]['source_ip_version_' . $nodeIpVersion] = null;
								}
							}

							if (empty($parameters['data'][$nodeProcessType . '_recursive_dns_destination_listening_port_number_version_' . $nodeIpVersion]) === false) {
								$response['status_valid'] = ($this->_validatePortNumber($parameters['data'][$nodeProcessType . '_recursive_dns_destination_listening_port_number_version_' . $nodeIpVersion]) === false);

								if ($response['status_valid'] === false) {
									$response['message'] = 'Invalid proxy node process recursive DNS destination listening port number, please try again.';
									return $response;
								}

								$nodeRecursiveDnsDestinationData[$nodeProcessType]['listening_port_number_version_' . $nodeIpVersion] = $parameters['data'][$nodeProcessType . '_recursive_dns_destination_listening_port_number_version_' . $nodeIpVersion];
							}

							$nodeRecursiveDnsDestination = $this->fetch(array(
								'fields' => array(
									'id'
								),
								'from' => 'node_recursive_dns_destinations',
								'where' => array(
									'node_id' => $nodeId,
									'node_process_type' => $nodeProcessType
								)
							));
							$response['status_valid'] = ($nodeRecursiveDnsDestination !== false);

							if ($response['status_valid'] === false) {
								return $response;
							}

							if (empty($nodeRecursiveDnsDestination['id']) === false) {
								$nodeRecursiveDnsDestinationData[$nodeProcessType]['id'] = $nodeRecursiveDnsDestination['id'];
							}
						}
					}

					if (empty($nodeRecursiveDnsDestinationData[$nodeProcessType]) === true) {
						$response['message'] = 'Invalid proxy node process recursive DNS destinations, please try again.';
						return $response;
					}
				}
			}

			// todo: refactor for node_process_forwarding_destinations table
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

			// todo: fetch existing node recursive dns record
			$nodeRecursiveDnsDestinationData['system'] = array(
				'node_id' => $nodeId,
				'node_process_type' => 'system'
			);

			foreach ($nodeIpVersions as $nodeIpVersion) {
				if (isset($parameters['data']['recursive_dns_ip_version_' . $nodeIpVersion]) === true) {
					$nodeRecursiveDnsDestinationIp = $this->_sanitizeIps(array($parameters['data']['recursive_dns_destination_ip_version_' . $nodeIpVersion]));
					$response['status_valid'] = (empty($nodeRecursiveDnsDestinationIp[$nodeIpVersion]) === false);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid node system recursive DNS destination IP, please try again.';
						return $response;
					}

					$nodeRecursiveDnsDestinationData['system']['ip_version_' . $nodeIpVersion] = $nodeRecursiveDnsDestinationIp[$nodeIpVersion];
					$nodeRecursiveDnsDestinationData['system']['ip_type_version_' . $nodeIpVersion] = $this->_detectIpType($nodeRecursiveDnsDestinationIp[$nodeIpVersion], $nodeIpVersion);
					// todo: if node recursive DNS ip exists on same node, assign internal IP with relational node_id to use as source_ip
					$nodeRecursiveDnsDestinationData['system']['port_number_version_' . $nodeIpVersion] = $this->settings['node_process_type_default_port_numbers']['recursive_dns'];

					if (empty($parameters['data']['recursive_dns_port_number_version_' . $nodeIpVersion]) === false) {
						$response['status_valid'] = ($this->_validatePortNumber($parameters['data']['recursive_dns_destination_port_number_version_' . $nodeIpVersion]) === false);

						if ($response['status_valid'] === false) {
							$response['message'] = 'Invalid node system recursive DNS port number, please try again.';
							return $response;
						}

						$nodeRecursiveDnsDestinationData['system']['port_number_version_' . $nodeIpVersion] = $parameters['data']['recursive_dns_destination_port_number_version_' . $nodeIpVersion];
					}

					$nodeRecursiveDnsDestination = $this->fetch(array(
						'fields' => array(
							'id'
						),
						'from' => 'node_recursive_dns_destinations',
						'where' => array(
							'node_id' => $nodeNodeId,
							'node_process_type' => 'system'
						)
					));
					$response['status_valid'] = ($nodeRecursiveDnsDestination !== false);

					if ($response['status_valid'] === false) {
						return $response;
					}

					if (empty($nodeRecursiveDnsDestination['id']) === false) {
						$nodeRecursiveDnsDestinationData['system']['id'] = $nodeRecursiveDnsDestination['id'];
					}
				}
			}

			if (empty($nodeRecursiveDnsDestinationData['system']) === true) {
				$response['message'] = 'Invalid node system recursive DNS destinations, please try again.';
				return $response;
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
					'reserved_internal_ip_version_4' => true,
					'reserved_internal_ip_version_6' => true,
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
						// todo: loop through proxy process counts with default ports using system setting values
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
			// todo: add reserved internal IP assignments for new ipset rules to allow unique process ports for each node
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

			$existingNodeProcessPorts = $this->fetch(array(
				'fields' => array(
					'node_process_type',
					'number',
					'status_allowing'
				),
				'from' => 'node_process_ports',
				'where' => array(
					'node_id' => $nodeId
					// ..
				)
			));
			$nodeCount = $this->count(array(
				'in' => 'nodes',
				'where' => array(
					'status_processed' => false,
					'OR' => array(
						'id' => $nodeId,
						'node_id' => $nodeId
					)
				)
			));
			$response['status_valid'] = (
				($existingNodeProcessPorts !== false) &&
				(is_int($nodeCount) === true) &&
				($nodeCount > 0)
			);

			$existingNodeProcessPortNumbers = $existingNodeProcessTypes = array();

			foreach ($existingNodeProcessPorts as $existingNodeProcessPort) {
				$existingNodeProcessTypes[$existingNodeProcessPort['node_process_type']] = $existingNodeProcessPortNumbers[$existingNodeProcessPort['number']] = $existingNodeProcessPort['status_allowing'];
			}

			foreach ($this->settings['node_process_type_default_port_numbers'] as $nodeProcessType => $nodeProcessTypeDefaultPortNumber) {
				if (empty($existingNodeProcessTypes[$nodeProcessType]) === true) {
					continue;
				}

				$nodeProcessCount = $this->count(array(
					'in' => 'node_processes',
					'where' => array(
						'node_id' => $nodeId,
						'type' => $nodeProcessType
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

				if (
					(is_int($nodeProcessCount) === false) ||
					($nodeProcessResourceUsageLogs === false)
				) {
					break;
				}

				if ($nodeProcessCount === 0) {
					continue;
				}

				$nodeProcessCountMaximum = min(100, max(ceil(($nodeProcessResourceUsageLogs[0]['cpu_capacity_cores'] * $nodeProcessResourceUsageLogs[0]['cpu_capacity_megahertz']) / 100)), ($nodeProcessCount + 5));

				if ($nodeProcessCount < $nodeProcessCountMaximum) {
					$nodeProcessData = $nodeProcessPortData = array();
					$nodeProcessResourceUsageLogCpuPercentage = $nodeProcessResourceUsageLogs[0]['cpu_percentage'] / $nodeProcessCount;

					if ($nodeProcessResourceUsageLogCpuPercentage > 0.5) {
						$nodeProcessPortNumber = $nodeProcessTypeDefaultPortNumber;

						foreach (range(1, min(5, $nodeProcessCountMaximum - $nodeProcessCount)) as $nodeProcessIndex) {
							while (
								($nodeProcessPortNumber <= 65535) &&
								(isset($existingNodeProcessPortNumbers[$nodeProcessPortNumber]) === true)
							) {
								$nodeProcessPortNumber++;
							}

							if (isset($existingNodeProcessPortNumbers[$nodeProcessPortNumber]) === true) {
								break;
							}

							$existingNodeProcessPortNumbers[$nodeProcessPortNumber] = false;
							$nodeProcessCount++;
							$nodeProcessData[] = array(
								'port_number' => $nodeProcessPortNumber,
								'type' => $nodeProcessType
							);
							$nodeProcessPortData[] = array(
								'number' => $nodeProcessPortNumber,
								'node_process_type' => $nodeProcessType
							);
						}

						if (empty($nodeProcessData) === false) {
							$nodeProcessesSaved = $this->save(array(
								'data' => $nodeProcessData,
								'to' => 'node_processes'
							));
							$nodeProcessPortsSaved = $this->save(array(
								'data' => $nodeProcessPortData,
								'to' => 'node_process_ports'
							));
							$response['status_valid'] = (
								($nodeProcessesSaved !== false) &&
								($nodeProcessPortsSaved !== false)
							);

							if ($response['status_valid'] === false) {
								return $response;
							}
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
			}

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
						$node['external_ip_version_' . $nodeIpVersion],
						$node['internal_ip_version_' . $nodeIpVersion]
					);

					foreach (array_filter($nodeIps) as $nodeIp) {
						$response['data']['node_ips'][$nodeIpVersion][$nodeIp] = $nodeIp;
					}
				}
			}

			$nodeIpVersions = array_values($nodeIpVersions);

			foreach ($nodeIpVersions as $nodeIpVersionKey => $nodeIpVersion) {
				if (empty($response['data']['node_ips'][$nodeIpVersion]) === true) {
					unset($response['data']['node_ip_versions'][(128 / 4) + (96 * $nodeIpVersionKey)]);
				}
			}

			foreach ($this->settings['node_process_type_default_port_numbers'] as $nodeProcessType => $nodeProcessTypeDefaultPortNumber) {
				$nodeProcesses = $this->fetch(array(
					'fields' => array(
						'node_id',
						'port_number'
					),
					'from' => 'node_processes',
					'where' => array(
						'node_id' => $nodeId,
						'type' => $nodeProcessType
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
					($nodeUsers !== false)
				);

				if ($response['status_valid'] === false) {
					return $response;
				}

				end($nodeProcesses);
				$nodeProcessParts = array_chunk($nodeProcesses, ((key($nodeProcesses) + 1) / 2));

				foreach ($nodeProcessParts as $nodeProcessPartKey => $nodeProcessPart) {
					foreach ($nodeProcessPart as $nodeProcess) {
						$response['data']['node_process_ports'][$nodeProcessType][$nodeProcess['id']] = $nodeProcess['port_number'];
						$response['data']['node_processes'][$nodeProcessType][$nodeProcessPartKey][$nodeProcess['id']] = $nodeProcess['port_number'];
					}
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
					'id',
					'listening_ip_version_4',
					'listening_ip_version_6',
					'listening_port_number_version_4',
					'listening_port_number_version_4',
					'node_id',
					'source_ip_version_4',
					'source_ip_version_6'
				),
				'from' => 'node_recursive_dns_destinations',
				'where' => array(
					'node_node_id' => $nodeId
				)
			));
			$response['status_valid'] = ($nodeRecursiveDnsDestinations !== false);

			if ($response['status_valid'] === false) {
                                return $response;
			}

			foreach ($nodeRecursiveDnsDestinations as $nodeRecursiveDnsDestination) {
				$response['data']['node_recursive_dns_destinations'][$nodeRecursiveDnsDestination['node_process_type']][$nodeRecursiveDnsDestination['node_id']] = $nodeRecursiveDnsDestination;

				foreach ($nodeIpVersions as $nodeIpVersion) {
					if (empty($nodeRecursiveDnsDestination['listening_ip_version_' . $nodeIpVersion]) === false) {
						$response['data']['node_ips'][$nodeIpVersion][$nodeRecursiveDnsDestination['listening_ip_version_' . $nodeIpVersion]] = $nodeRecursiveDnsDestination['listening_ip_version_' . $nodeIpVersion];
					}

					if (
						(empty($nodeRecursiveDnsDestination['source_ip_version_' . $nodeIpVersion]) === false) &&
						(empty($response['data']['nodes'][$nodeRecursiveDnsDestination['node_id']]['internal_ip_version_' . $nodeIpVersion]) === false)
					) {
						$response['data']['node_recursive_dns_destinations'][$nodeRecursiveDnsDestination['node_process_type']][$nodeRecursiveDnsDestination['node_id']]['source_ip_version_' . $nodeIpVersion] = $response['data']['nodes'][$nodeRecursiveDnsDestination['node_id']]['internal_ip_version_' . $nodeIpVersion];
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
