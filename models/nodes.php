<?php
	$extend = true;
	require_once($configuration->settings['base_path'] . '/models/main.php');

	class NodesModel extends MainModel {

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

			$response = array(
				'data' => array(
					'command' => 'cd /tmp && rm -rf /etc/cloud/ /var/lib/cloud/ ; apt-get update ; DEBIAN_FRONTEND=noninteractive apt-get -y install sudo ; sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk \'{print $1}\') ; sudo $(whereis telinit | awk \'{print $2}\') u ; sudo rm -rf /etc/cloud/ /var/lib/cloud/ ; sudo dpkg --configure -a ; sudo apt-get update && sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php wget --fix-missing && sudo wget -O proxy.php --no-dns-cache --retry-connrefused --timeout=60 --tries=2 "' . ($url = $_SERVER['REQUEST_SCHEME'] . '://' . $this->settings['base_domain']) . '/assets/php/proxy.php?' . time() . '" && sudo php proxy.php ' . $nodeId . ' ' . $url,
				),
				'message' => 'Node is ready for activation.',
				'status_valid' => true
			);

			if ($node['status_active'] === true) {
				$response['message'] = 'Node is already activated.';
			} elseif ($parameters['user']['endpoint'] === false) {
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
			}

			return $response;
		}

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding node, please try again.',
				'status_valid' => (
					empty($parameters['data']['type']) === false &&
					in_array($parameters['data']['type'], array(
						'nameserver',
						'proxy'
					))
				)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node type, please try again.';
				return $response;
			}

			if (empty($parameters['data']['node_id']) === false) {
				$node = $this->fetch(array(
					'fields' => array(
						'status_active',
						'status_deployed'
					),
					'from' => 'nodes',
					'where' => array(
						'id' => ($nodeId = $parameters['data']['node_id'])
					)
				));
				$response['status_valid'] = ($node !== false);

				if ($response['status_valid'] === true) {
					$response['status_valid'] = (empty($node) === false);
					$parameters['data'] = array_merge($parameters['data'], $node);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid node ID, please try again.';
					}
				}
			}

			if ($response['status_valid'] === false) {
				return $response;
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
				count(current($nodeExternalIpVersions)) === 1
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
				empty($nodeInternalIps) === true ||
				(
					$nodeInternalIpVersions === $this->_sanitizeIps($nodeInternalIps) &&
					count(current($nodeInternalIpVersions)) === 1
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
					'OR' => $nodeExternalIps
				)
			));
			$conflictingNodeProcessCountParameters = array(
				'in' => 'node_processes',
				'where' => array(
					'OR' => $nodeExternalIps
				)
			);

			if (empty($nodeId) !== false) {
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
				is_int($conflictingNodeCount) === true &&
				is_int($conflictingNodeProcessCount) === true
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (
				$conflictingNodeCount === 0 &&
				$conflictingNodeProcessCount === 0
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Node IPs already in use, please try again.';
				return $response;
			}

			$nodeData = array(
				array_intersect_key($parameters['data'], array(
					'external_ip_version_4' => true,
					'external_ip_version_6' => true,
					'internal_ip_version_4' => true,
					'internal_ip_version_6' => true,
					'node_id' => true,
					'status_active' => true,
					'status_deployed' => true,
					'type' => true
				))
			);
			$nodeDataSaved = $this->save(array(
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
				'status_valid' => false
			);

			if (empty($parameters['ids']['nodes']) === true) {
				$response['message'] = 'Invalid node IDs, please try again.';
				return $response;
			}

			$response['status_valid'] = (
				(
					empty($parameters['data']['authentication_password']) === false ||
					empty($parameters['data']['authentication_username']) === false
				) &&
				(
					empty($parameters['data']['authentication_password']) === true ||
					empty($parameters['data']['authentication_username']) === true
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
				empty($parameters['data']['authentication_username']) === true ||
				(
					strlen($parameters['data']['authentication_username']) > 10 &&
					strlen($parameters['data']['authentication_username']) < 20 &&
					strlen($parameters['data']['authentication_password']) > 10 &&
					strlen($parameters['data']['authentication_password']) < 20
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
				'status_valid' => false
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

			$response = array(
				'message' => 'Node is ready for deactivation.',
				'status_valid' => true
			);

			if ($node['status_active'] === false) {
				$response['message'] = 'Node is already deactivated.';
			} elseif ($parameters['user']['endpoint'] === false) {
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
			}

			return $response;
		}

		public function deploy($parameters) {
			$response = array(
				'message' => 'Error deploying node, please try again.',
				'status_valid' => false
			);

			/*
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
			*/

			return $response;
		}

		public function download($parameters) {
			$response = array(
				'message' => 'Error downloading nodes, please try again.',
				'status_valid' => false
			);

			/*
			if (!empty($parameters['items']['list_proxy_items']['data'])) {
				$formattedProxies = $proxyPorts = $serverProxyProcessPorts = array();
				$parameters['items']['list_proxy_items']['data'] = array_intersect_key($parameters['items']['list_proxy_items']['data'], array(
					$parameters['data']['results'] => true
				));
				$proxyItemParameters = array(
					'items' => array_intersect_key($parameters['items'], array(
						'list_proxy_items' => true
					))
				);

				if (!empty($parameters['search']['list_proxy_items'])) {
					$proxyItemParameters['search']['list_proxy_items'] = $parameters['search']['list_proxy_items'];
				}

				$proxyItems = $this->_decodeItems($proxyItemParameters, true);

				if (!empty($proxyItems['list_proxy_items']['data'])) {
					$proxyParameters = array(
						'fields' => array(
							'external_ip',
							'id',
							'internal_ip',
							'password',
							'server_id',
							'username'
						),
						'from' => 'proxies',
						'where' => array(
							'id' => $proxyItems['list_proxy_items']['data']
						)
					);

					if (!empty($proxyItems['list_proxy_items']['token']['parameters']['sort'])) {
						$proxyParameters['sort'] = $proxyItems['list_proxy_items']['token']['parameters']['sort'];
					}

					$proxies = $this->fetch($proxyParameters);
					$delimiters = array(
						!empty($parameters['data']['ipv4_delimiter1']) ? $parameters['data']['ipv4_delimiter1'] : '',
						!empty($parameters['data']['ipv4_delimiter2']) ? $parameters['data']['ipv4_delimiter2'] : '',
						!empty($parameters['data']['ipv4_delimiter3']) ? $parameters['data']['ipv4_delimiter3'] : '',
						''
					);
					$delimiterMask = implode('', array_unique($delimiters));

					if (!empty($proxies['data'])) {
						foreach ($proxies['data'] as $proxy) {
							$serverId = $proxy['server_id'];

							if (empty($serverProxyProcessPorts[$serverId])) {
								$serverProxyProcessPorts[$serverId] = $this->_call(array(
									'method_from' => 'server_proxy_processes',
									'method_name' => 'fetchServerProxyProcessPorts',
									'method_parameters' => array(
										$serverId
									)
								));
							}

							if (!empty($serverProxyProcessPorts[$serverId])) {
								foreach ($serverProxyProcessPorts[$serverId] as $serverProxyProcessPort) {
									$proxyPorts[$serverProxyProcessPort] = $serverProxyProcessPort;
								}
							}
						}

						$response['data'] = array(
							'proxy_port' => ($proxyPort = $parameters['data']['proxy_port']),
							'proxy_ports' => $proxyPorts
						);
						$separatorKey = $parameters['data']['separator'];
						$separators = array(
							'comma' => ',',
							'hyphen' => '-',
							'new_line' => "\n",
							'plus' => '+',
							'semicolon' => ';',
							'space' => ' ',
							'underscore' => '_'
						);

						if (
							empty($separatorKey) ||
							!array_key_exists($separatorKey, $separators)
						) {
							$separatorKey = 'new_line';
						}

						$separator = $separators[$separatorKey];

						foreach ($proxies['data'] as $proxyKey => $proxy) {
							$formattedProxy = '';
							$proxy['port'] = current($serverProxyProcessPorts[$proxy['server_id']]);

							if (in_array($proxyPort, $serverProxyProcessPorts[$proxy['server_id']])) {
								$proxy['port'] = $proxyPort;
							}

							for ($i = 1; $i < 5; $i++) {
								$column = $parameters['data']['ipv4_column' . $i];
								$formattedProxy .= ($proxy[$column] ? $proxy[$column] . $delimiters[($i - 1)] : '');
							}

							$formattedProxies[$proxyKey] = rtrim($formattedProxy, $delimiterMask);
						}

						if (!empty($formattedProxies)) {
							$response['data']['formatted_proxies'] = implode($separator, $formattedProxies);
							$response['message'] = array(
								'status' => 'success',
								'text' => 'Proxies downloaded successfully.'
							);
						}
					}
				}
			}
			*/

			return $response;
		}

		public function edit($parameters) {
			$response = array(
				'message' => 'Error editing node, please try again.',
				'status_valid' => false
			);

			// todo: combine authenticate and request_limit functions into edit() function

			if (empty($parameters['data']['type']) === false) {
				$response['status_valid'] = in_array($parameters['data']['type'], array(
					'nameserver',
					'proxy'
				));

				if ($response['status_valid'] === false) {
					$response['message'] = 'Invalid node type, please try again.';
					return $response;
				}
			}

			if (empty($parameters['data']['id']) === false) {
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
			}

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
				$nodeExternalIpVersions === $this->_sanitizeIps($nodeExternalIps) &&
				count(current($nodeExternalIpVersions)) === 1
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
				empty($nodeInternalIps) === true ||
				(
					$nodeInternalIpVersions === $this->_sanitizeIps($nodeInternalIps) &&
					count(current($nodeInternalIpVersions)) === 1
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

			$nodeData = array_intersect_key($parameters['data'], array(
				'external_ip_version_4' => true,
				'external_ip_version_6' => true,
				'id' => true,
				'internal_ip_version_4' => true,
				'internal_ip_version_6' => true,
				'node_id' => true,
				'status_active' => true,
				'type' => true
			));
			$nodeDataUpdated = $this->update(array(
				'data' => $nodeData,
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
			// ..
			return array();
		}

		public function remove($parameters) {
			$response = array(
				'message' => 'Error removing nodes, please try again.',
				'status_valid' => false
			);

			if (empty($parameters['ids']['nodes']) === false) {
				$nodeIds = $parameters['ids']['nodes'];
				$nodeCount = $this->count(array(
					'in' => 'nodes',
					'where' => array(
						'id' => $nodeIds
					)
				));
				$response['status_valid'] = (is_int($nodeCount) === true);
			}

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
				$nodeDataDeleted === true &&
				$nodeUserDataDeleted === true
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

	if (!empty($configuration->parameters)) {
		$nodesModel = new NodesModel();
		$data = $nodesModel->route($configuration->parameters);
	}
?>
