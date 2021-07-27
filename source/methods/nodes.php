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
				$nodeDataUpdated = $this->update(array(
					'data' => array(
						'status_active' => true
					),
					'where' => array(
						'OR' => array(
							'id' => $nodeId,
							'node_id' => $nodeId
						)
					)
				));
				$response['status_valid'] = ($nodeDataUpdated === true);

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

			if (
				(empty($parameters['data']['enable_binding_to_existing_node']) === false) &&
				(empty($parameters['data']['node_id']) === false)
			) {
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
							'external_ip_version_4' => ($nodeId = $parameters['data']['node_id']),
							'external_ip_version_6' => $nodeId
							'id' => $nodeId
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

				$nodeId = $parameters['data']['node_id'] = $node['id'];

				if (empty($node['node_id']) === false) {
					$nodeId = $parameters['data']['node_id'] = $node['node_id'];
				}

				$parameters['data']['status_active'] = $node['status_active'];
				$parameters['data']['status_deployed'] = $node['status_deployed'];
			}

			$nodeExternalIps = $nodeExternalIpVersions = array();
			$nodeIpVersions = array(
				'4',
				'6'
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
				if ($this->_fetchIpType(current($nodeInternalIpVersionIps), $nodeInternalIpVersion) !== 'private') {
					$response = array(
						'message' => 'Node internal IPs must be private, please try again.',
						'status_valid' => false
					);
					return $response;
				}
			}

			$response['status_valid'] = (
				(empty($parameters['data']['enable_reverse_proxy_forwarding']) === true) ||
				(
					(
						empty($parameters['data']['destination_address_version_4']) === false &&
						empty($parameters['data']['destination_port_version_4']) === false
					) ||
					(
						empty($parameters['data']['destination_address_version_6']) === false &&
						empty($parameters['data']['destination_port_version_6']) === false
					)
				)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'IP version 4 or IP version 6 destination address and port are required for reverse proxy forwarding, please try again.';
			}

			if (empty($parameters['data']['enable_reverse_proxy_forwarding']) === false) {
				foreach ($nodeIpVersions as $nodeIpVersion) {
					$response['status_valid'] = (
						(empty($parameters['data']['destination_port_version_' . $nodeIpVersion]) === true) ||
						($this->_validatePort($parameters['data']['destination_port_version_' . $nodeIpVersion]) === false)
					);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid IP version ' . $nodeIpVersion . ' destination port, please try again.';
						return $response;
					}

					$response['status_valid'] = (
						(empty($parameters['data']['destination_address_version_' . $nodeIpVersion]) === true) ||
						($this->_validateHostname($parameters['data']['destination_address_version_' . $nodeIpVersion]) !== false)
					);

					if ($response['status_valid'] === false) {
						$nodeDestinationIp = $this->_sanitizeIps(array($parameters['data']['destination_address_version_' . $nodeIpVersion]))[];
						$response['status_valid'] = (empty($nodeDestinationIp[$nodeIpVersion]) === false);
					}

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid IP version ' . $nodeIpVersion . ' destination, please try again.';
						return $response;
					}
				}
			}

			if (empty($parameters['data']['node_user_id']) === false) {
				$userCount = $this->count(array(
					'in' => 'users',
					'where' => array(
						'id' => ($userIds = $parameters['data']['node_user_id'])
					)
				));
				$response['status_valid'] = (is_int($userCount) === true);

				if ($response['status_valid'] === false) {
					return $response;
				}

				$response['status_valid'] = (
					end($userIds) &&
					($userCount === (intval(key($userIds)) + 1))
				);

				if ($response['status_valid'] === false) {
					$response['message'] = 'Invalid node user IDs ,please try again';
					return $response;
				}
			}

			$conflictingNodeCountParameters = array(
				'in' => 'nodes',
				'where' => array(
					'OR' => $nodeExternalIps
				)
			));
			$conflictingNodeProcessCountParameters = array(
				'in' => 'node_processes',
				'where' => array(
					'OR' => $nodeExternalIps
				)
			);

			if (empty($nodeId) === false) {
				$conflictingNodeCountParameters['where']['OR'] = $conflictingNodeProcessCountParameters['where']['OR'] = array(
					$conflictingNodeCountParameters['where'],
					array(
						'node_id' => $nodeId,
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

			if (empty($nodeId) === true) {
				// todo: automatically create nameserver and proxy processes for primary nodes that haven't been deployed yet
			}

			$nodeDataSaved = $this->save(array(
				'data' => array_intersect_key($parameters['data'], array(
					'external_ip_version_4' => true,
					'external_ip_version_6' => true,
					'internal_ip_version_4' => true,
					'internal_ip_version_6' => true,
					'node_id' => true,
					'status_active' => true,
					'status_deployed' => true
				)),
				'data' => $nodeData,
				'to' => 'nodes'
			));
			$response['status_valid'] = ($nodeDataSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response = array(
				'message' => 'Node added successfully.',
				'status_valid' => true
			);
			return $response;
		}

		public function authenticate($parameters) {
			$response = array(
				'message' => 'Error authenticating nodes, please try again.',
				'status_valid' => (empty($parameters['ids']['nodes']) === false)
			);

			if ($response['status_valid'] === false)
				$response['message'] = 'Invalid node IDs, please try again.';
				return $response;
			}

			$response['status_valid'] = (
				(
					(empty($parameters['data']['authentication_password']) === false) ||
					(empty($parameters['data']['authentication_username']) === false)
				) &&
				(
					(empty($parameters['data']['authentication_password']) === true) ||
					(empty($parameters['data']['authentication_username']) === true)
				)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Authentication username and password must be either set or empty, please try again.';
				return $response;
			}

			if (empty($parameters['data']['authentication_password']) === true) {
				$parameters['data']['authentication_password'] = $parameters['data']['authentication_username'] = null;
			}

			$response['status_valid'] = (
				(empty($parameters['data']['authentication_username']) === true) ||
				(
					(strlen($parameters['data']['authentication_username']) > 10) &&
					(strlen($parameters['data']['authentication_username']) < 20) &&
					(strlen($parameters['data']['authentication_password']) > 10) &&
					(strlen($parameters['data']['authentication_password']) < 20)
				)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Authentication username and password must be between 10 and 20 characters, please try again.';
				return $response;
			}

			if (empty($parameters['data']['authentication_whitelist']) === false) {
				$authenticationWhitelist = array();
				$authenticationWhitelistSourceVersions = $this->_sanitizeIps($parameters['data']['authentication_whitelist'], true);

				if (!empty($authenticationWhitelistSourceVersions)) {
					foreach ($authenticationWhitelistSourceVersions as $authenticationWhitelistSources) {
						$authenticationWhitelist += $authenticationWhitelistSources;
					}
				}

				$parameters['data']['authentication_whitelist'] = implode("\n", $authenticationWhitelist);
			}

			$userParameters = array(
				'fields' => array(
					'id'
				),
				'from' => 'users',
				'where' => array_intersect_key($parameters['data'], array(
					'authentication_password',
					'authentication_username',
					'authentication_whitelist'
				))
			));
			$user = $this->fetch($userParameters);
			$response['status_valid'] = ($user !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			if (empty($userId) === false) {
				$parameters['data']['id'] = $userId = $user['id'];
			}

			$userDataSaved = $this->save(array(
				'data' => array(
					array_intersect_key($parameters['data'], array(
						'authentication_password',
						'authentication_username',
						'authentication_whitelist',
						'id'
					))
				),
				'to' => 'users'
			));
			$response['status_valid'] = ($userDataSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			if (empty($userId) === true) {
				$user = $this->fetch($userParameters);
				$response['status_valid'] = ($user !== false);

				if ($response['status_valid'] === false) {
					return $response;
				}

				$userId = $user['id'];
			}

			$nodeUsers = $this->fetch(array(
				'fields' => array(
					'id',
					'node_id',
					'user_id'
				),
				'from' => 'node_users',
				'where' => array(
					'node_id' => ($nodeIds = $parameters['ids']['nodes']),
					'user_id' => $userId
				)
			));
			$response['status_valid'] = ($nodeUsers !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$nodeUserData = array();

			foreach ($nodeUsers as $nodeUser) {
				$nodeUserData[$nodeUser['node_id']] = $nodeUser;
			}

			$nodeIds = array_diff($nodeIds, array_keys($nodeUserData));

			foreach ($nodeIds as $nodeId) {
				$nodeUserData[] = array(
					'node_id' => $nodeId,
					'user_id' => $user['id']
				);
			}

			$nodeUserDataSaved = $this->save(array(
				'data' => $nodeUserData,
				'to' => 'node_users'
			));
			$response['status_valid'] = ($nodeUserDataSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response = array(
				'message' => 'Nodes authenticated successfully.',
				'status_valid' => true
			);
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
				$nodeDataUpdated = $this->update(array(
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
				$response['status_valid'] = ($nodeDataUpdated === true);

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
			} elseif (empty($parameters['user']['endpoint']) === false) {
				$nodeDataUpdated = $this->update(array(
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
				$response['status_valid'] = ($nodeDataUpdated === true);

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

			// todo: combine authenticate and request_limit functions into edit() function

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
				'4',
				'6'
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
				if ($this->_fetchIpType(current($nodeInternalIpVersionIps), $nodeInternalIpVersion) !== 'private') {
					$response = array(
						'message' => 'Node internal IPs must be private, please try again.',
						'status_valid' => false
					);
					return $response;
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
							'OR' => ($nodeIps = ($nodeExternalIps + $nodeInternalIps))
						)
					)
				)
			));
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

			/*
			if (!empty($parameters['items']['list_proxy_items']['count'])) {
				$formattedProxyServerIds = $proxyData = $proxyUrlRequestLimitationProxyData = array();

				if (
					($proxyUrlRequestLimitations = (
						!empty($parameters['items']['list_proxy_url_items']['data']) ||
						!empty($parameters['items']['list_proxy_url_request_limitation_items']['data'])
					)) ||
					(
						!empty($parameters['data']['block_all_urls']) ||
						(
							!empty($parameters['items']['list_proxy_url_items']['data']) &&
							!empty($parameters['data']['only_allow_urls'])
						)
					)
				) {
					if ($proxyUrlRequestLimitations === true) {
						$proxyServerIds = $this->fetch(array(
							'fields' => array(
								'id',
								'server_id'
							),
							'from' => 'proxies'
						));

						if (!empty($proxyServerIds['count'])) {
							foreach ($proxyServerIds['data'] as $proxyServerId) {
								$formattedProxyServerIds[$proxyServerId['id']] = $proxyServerId['server_id'];
							}
						}
					}

					foreach ($parameters['items']['list_proxy_items']['data'] as $proxyId) {
						if (
							!empty($parameters['data']['block_all_urls']) ||
							!empty($parameters['data']['only_allow_urls'])
						) {
							$proxyData[] = array(
								'block_all_urls' => (boolean) $parameters['data']['block_all_urls'],
								'id' => $proxyId,
								'only_allow_urls' => (boolean) $parameters['data']['only_allow_urls']
							);
						}

						if (
							!empty($parameters['items']['list_proxy_url_items']['data']) &&
							!empty($parameters['items']['list_proxy_url_request_limitation_items']['data'])
						) {
							foreach ($parameters['items']['list_proxy_url_items']['data'] as $proxyUrlId) {
								foreach ($parameters['items']['list_proxy_url_request_limitation_items']['data'] as $proxyUrlRequestLimitationId) {
									$proxyUrlRequestLimitationProxyData[] = array(
										'proxy_id' => $proxyId,
										'proxy_url_id' => $proxyUrlId,
										'proxy_url_request_limitation_id' => $proxyUrlRequestLimitationId,
										'server_id' => $formattedProxyServerIds[$proxyId]
									);
								}
							}
						}

						if (
							!empty($parameters['items']['list_proxy_url_items']['data']) &&
							empty($parameters['items']['list_proxy_url_request_limitation_items']['data'])
						) {
							$proxyUrlRequestLimitationProxyData[] = array(
								'proxy_id' => $proxyId,
								'proxy_url_id' => $proxyUrlId,
								'server_id' => $formattedProxyServerIds[$proxyId]
							);
						}

						if (
							empty($parameters['items']['list_proxy_url_items']['data']) &&
							!empty($parameters['items']['list_proxy_url_request_limitation_items']['data'])
						) {
							$proxyUrlRequestLimitationProxyData[] = array(
								'proxy_id' => $proxyId,
								'proxy_url_request_limitation_id' => $proxyUrlId,
								'server_id' => $formattedProxyServerIds[$proxyId]
							);
						}
					}
				}

				if (
					$this->delete(array(
						'from' => 'proxy_url_request_limitation_proxies',
						'where' => array(
							'proxy_id' => $parameters['items']['list_proxy_items']['data']
						)
					)) &&
					$this->save(array(
						'data' => $proxyData,
						'to' => 'proxies'
					)) &&
					$this->save(array(
						'data' => $proxyUrlRequestLimitationProxyData,
						'to' => 'proxy_url_request_limitation_proxies'
					))
				) {
					$response['message'] = array(
						'status' => 'success',
						'text' => 'Proxies limited successfully.'
					);
				}
			}
			*/

			$nodeDataUpdated = $this->update(array(
				'data' => array_intersect_key($parameters['data'], array(
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
			$response['status_valid'] = ($nodeDataUpdated === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response = array(
				'message' => 'Node edited successfully.',
				'status_valid' => true
			);
			return $response;
		}

		public function list($parameters) {
			$response = array(
				'message' => 'Error listing nodes, please try again.',
				'status_valid' => false
			);
			// ..
			return array();
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

			$nodeDataDeleted = $this->delete(array(
				'from' => 'nodes',
				'where' => array(
					'id' => $nodeIds
				)
			));
			$nodeUserDataDeleted = $this->delete(array(
				'from' => 'node_users',
				'where' => array(
					'node_id' => $nodeIds
				)
			));
			$response['status_valid'] = (
				($nodeDataDeleted === true) &&
				($nodeUserDataDeleted === true)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response = array(
				'message' => 'Nodes removed successfully.',
				'status_valid' => true
			);
			return $response;
		}

		public function search($parameters) {
			$response = array(
				'message' => 'Error searching nodes, please try again.',
				'status_valid' => false
			);

			/*
			if (!empty($parameters['data']['broad_search'])) {
				$broadSearchFields = array(
					'password',
					'status',
					'username'
				);
				$broadSearchValues = array_filter(explode(' ', $parameters['data']['broad_search']));
				$response['search'] = array_map(function($broadSearchValue) use ($broadSearchFields) {
					$broadSearchFieldValues = array(
						'OR' => array()
					);

					foreach ($broadSearchFields as $broadSearchField) {
						$broadSearchFieldValues['OR'][$broadSearchField . ' LIKE'] = '%' . $broadSearchValue . '%';
					}

					return $broadSearchFieldValues;
				}, $broadSearchValues);
			}

			if (
				!empty($parameters['data']['granular_search']) &&
				($granularSearchIps = $this->_validateIps($parameters['data']['granular_search'], true, true))
			) {
				$response['search']['external_ip LIKE'] = $response['search']['internal_ip LIKE'] = array();

				foreach ($granularSearchIps as $ipVersion => $ips) {
					$formattedGranularSearchIps = array();

					foreach ($ips as $ipKey => $ip) {
						$formattedGranularSearchIps[] = $ip . '%';
					}

					$response['search']['external_ip LIKE'] += $formattedGranularSearchIps;
					$response['search']['internal_ip LIKE'] += $formattedGranularSearchIps;
				}
			}

			if (!empty($response['search'])) {
				$response['search'] = array(
					($parameters['data']['match_all_search'] ? 'AND' : 'OR') => $response['search']
				);

				unset($parameters['data']['id']);
			}
			*/

			return $response;
		}

		public function view($parameters = array()) {
			$response = array(
				'message' => 'Error viewing node, please try again.',
				'status_valid' => false
			);

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
