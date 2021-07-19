<?php
	$extend = true;
	require_once($configuration->settings['base_path'] . '/models/main.php');

	class NodeProcessesModel extends MainModel {

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding node process, please try again.',
				'status_valid' => false
			);

			if (empty($parameters['data']['transport_protocol']) === false) {
				$response['status_valid'] = (in_array($parameters['data']['transport_protocol'], array(
					'tcp',
					'udp'
				));
			}

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process transport protocol, please try again.';
				return $response;
			}

			if (empty($parameters['data']['node_id']) === false) {
				$nodeParameters = array(
					'fields' => array(
						'id',
						'node_id'
					),
					'from' => 'nodes',
					'where' => array(
						'id' => $parameters['data']['node_id']
					)
				);
				$node = $this->fetch($nodeParameters);
				$response['status_valid'] = ($node !== false);

				if ($response['status_valid'] === true) {
					$response['status_valid'] = (empty($node) === false);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid node ID, please try again.';
					}
				}
			}

			if ($response['status_valid'] === false) {
				return $response;
			}

			if (empty($node['node_id']) === false) {
				$nodeParameters['where']['id'] = $node['node_id'];
				$node = $this->fetch($nodeParameters);
				$response['status_valid'] = ($node !== false);

				if ($response['status_valid'] === true) {
					$response['status_valid'] = (empty($node) === false);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid node ID, please try again.';
					}
				}
			}

			$nodeProcessNodeId = $parameters['data']['node_id'] = $node['id'];

			if ($response['status_valid'] === false) {
				return $response;
			}

			if ($node['type'] === 'nameserver') {
				unset($parameters['data']['application_protocol']);
			}

			if (empty($parameters['data']['application_protocol']) === false) {
				$response['status_valid'] = in_array($parameters['data']['application_protocol'], array(
					'http',
					'socks'
				));

				if ($parameters['data']['application_protocol'] === 'http') {
					$parameters['data']['transport_protocol'] = 'tcp';
				}
			}

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process application protocol, please try again.';
				return $response;
			}

			if (empty($parameters['data']['port']) === false) {
				$nodeProcessPort = $this->_validatePort($parameters['data']['port']);
				$response['status_valid'] = (is_int($nodeProcessPort) === true);
			}

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process port, please try again.';
				return $response;
			}

			$nodeProcessExternalIps = $nodeProcessIps = $nodeProcessIpVersions = array();
			$nodeProcessIpTypes = array(
				'external' => 'public',
				'internal' => 'private'
			);
			$nodeProcessIpVersions = array(
				'4',
				'6'
			);

			foreach ($nodeProcessIpTypes as $nodeProcessIpInterface => $nodeProcessIpType) {
				foreach ($nodeProcessIpVersions as $nodeProcessIpVersion) {
					$nodeProcessIpKey = $nodeProcessIpInterface . '_ip_version_' . $nodeProcessIpVersion;

					if (empty($parameters['data'][$nodeIpKey]) === false) {
						$nodeProcessIp = $nodeProcessIps[$nodeProcessIpKey] = $nodeProcessIpVersions[$nodeProcessIpVersion][] = $parameters['data'][$nodeProcessIpKey];

						if (empty($node['external_ip_version_' . $nodeProcessIpVersion]) === true) {
							$response = array(
								'message' => 'Node must have an external IP version ' . $nodeProcessIpVersion . ' before adding a node process ' . $nodeProcessIpInterface . ' IP version ' . $nodeProcessIpVersion . ', please try again.',
								'status_valid' => false
							);
							return $response;
						}

						if ($nodeProcessIpInterface === 'external') {
							$nodeProcessExternalIps[$nodeProcessIpKey] = $nodeProcessIp;
						}

						if ($nodeProcessIpType !== $this->_fetchIpType($nodeProcessIp, $nodeProcessIpVersion)) {
							$response = array(
								'message' => 'Node process ' . $nodeProcessIpInterface . ' IPs must be ' . $nodeProcessIpType . ', please try again.',
								'status_valid' => false
							);
							return $response;
						}

						if ($nodeProcessIpVersion !== key($this->_sanitizeIps(array($nodeProcessIp)))) {
							$response = array(
								'message' => 'Invalid node process ' . $nodeProcessIpInterface . ' IP version ' . $nodeProcessIpVersion . ', please try again.',
								'status_valid' => false
							);
							return $response;
						}
					}
				}
			}

			if ($response['status_valid'] === false) {
				return $response;
			}

			if (empty($nodeIps) === false) {
				$conflictingNodeIpCountParameters = array(
					'in' => 'nodes',
					'where' => array(
						'OR' => array(
							array(
								'id' => $nodeProcessNodeId,
								'OR' => $nodeProcessIps
							),
							array(
								'node_id' => $nodeProcessNodeId,
								'OR' => $nodeProcessIps
							)
						)
					)
				));
				$conflictingNodeProcessIpCountParameters = array(
					'in' => 'node_processes',
					'where' => array(
						'node_id' => $nodeProcessNodeId,
						'OR' => $nodeProcessIps
					)
				);

				if (empty($nodeProcessExternalIps) === false) {
					$conflictingNodeIpCountParameters['where']['OR'][] = array(
						'OR' => $nodeProcessExternalIps
					);
					$conflictingNodeProcessIpCountParameters['where']['OR'] = array(
						$conflictingNodeProcessIpCountParameters['where'],
						array(
							'OR' => $nodeProcessExternalIps
						)
					);
				}

				$conflictingNodeIpCount = $this->count($conflictingNodeIpCountParameters);
				$conflictingNodeProcessIpCount = $this->count($conflictingNodeProcessIpCountParameters);
				$response['status_valid'] = (
					is_int($conflictingNodeIpCount) === true &&
					is_int($conflictingNodeProcessIpCount) === true
				);

				if ($response['status_valid'] === false) {
					return $response;
				}

				$response['status_valid'] = (
					$conflictingNodeIpCount === 0 &&
					$conflictingNodeProcessIpCount === 0
				);

				if ($response['status_valid'] === false) {
					$response['message'] = 'Node process IP already in use, please try again.';
					return $response;
				}
			}

			$conflictingNodeProcessPortCount = $this->count(array(
				'in' => 'node_processes',
				'where' => array(
					'node_id' => $nodeProcessId,
					'port' => $nodeProcessPort
				)
			));
			$response['status_valid'] = (is_int($conflictingNodeProcessPortCount) === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($conflictingNodeProcessPortCount === 0);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Node process port already in use, please try again.';
				return $response;
			}

			$nodeProcessData = array(
				array_intersect_key($parameters['data'], array(
					'application_protocol' => true,
					'external_ip_version_4' => true,
					'external_ip_version_6' => true,
					'internal_ip_version_4' => true,
					'internal_ip_version_6' => true,
					'node_id' => true,
					'port' => true,
					'transport_protocol' => true
				))
			);
			$nodeProcessDataSaved = $this->save(array(
				'data' => $nodeProcessData,
				'to' => 'nodes'
			));

			if ($nodeProcessDataSaved === false) {
				$response['status_valid'] = false;
				return $response;
			}

			$response = array(
				'message' => 'Node process added successfully.',
				'status_valid' => true
			);
			return $response;
		}

		public function edit($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error editing server proxy process, please try again.')
				)
			);

			if (
				empty($parameters['data']['port']) === false &&
				empty($parameters['where']['id']) === false
			) {
				$response['message']['text'] = 'Invalid server proxy process port, please try again.';
				$serverProxyProcessPort = $this->_validatePort($parameters['data']['port']);

                                if (is_int($serverProxyProcessPort) === true) {
					$response['message']['text'] = $defaultMessage;
					$serverProxyProcess = $this->fetch(array(
						'fields' => array(
							'id',
							'server_id'
						),
						'from' => 'server_proxy_processes',
						'where' => array(
							'id' => $parameters['where']['id']
						)
					));

					if ($serverProxyProcess !== false) {
						$response['message']['text'] = 'Invalid server proxy process ID, please try again.';

						if (empty($serverProxyProcess) === false) {
							$response['message']['text'] = 'Server proxy process port already in use on this server, please try again.';
							$serverId = $serverProxyProcess['server_id'];
							$serverProcessPorts = $this->_call(array(
								'method_from' => 'servers',
								'method_name' => 'fetchServerProcessPorts',
								'method_parameters' => array(
									$serverId
								)
							));

							if (in_array($serverProxyProcessPort, $serverProcessPorts) === false) {
								$response['message']['text'] = $defaultMessage;
								$serverProxyProcessData = array(
									array(
										'id' => $parameters['where']['id'],
										'port' => $serverProxyProcessPort,
										'server_id' => $serverId
									)
								);
								$serverProxyProcessDataSaved = $this->save(array(
									'data' => $serverProxyProcessData,
									'to' => 'server_proxy_processes'
								));

								if ($serverProxyProcessDataSaved === true) {
									$response = array(
										'data' => current($serverProxyProcessData),
										'message' => array(
											'status' => 'success',
											'text' => 'Server proxy process edited successfully.'
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
					'text' => ($defaultMessage = 'Error removing server proxy processes, please try again.')
				)
			);

			if (empty($parameters['items'][$parameters['item_list_name']]['data']) === false) {
				$selectedServerProxyProcessParameters = $serverProxyProcessParameters = array(
					'fields' => array(
						'id'
					),
					'in' => 'server_proxy_processes',
					'where' => array(
						'server_id' => ($serverId = $parameters['server_id'])
					)
				);
				$serverProxyProcessIds = $selectedServerProxyProcessParameters['where']['id'] = $parameters['items'][$parameters['item_list_name']]['data'];
				$selectedServerProxyProcessCount = $this->count($selectedServerProxyProcessParameters);
				$serverProxyProcessCount = $this->count($serverProxyProcessParameters);
				$selectedServerProxyProcessParameters['from'] = $serverProxyProcessParameters['from'] = 'server_proxy_processes';
				$selectedServerProxyProcesses = $this->fetch($selectedServerProxyProcessParameters);
				$serverProxyProcesses = $this->fetch($serverProxyProcessParameters);

				if (
					$selectedServerProxyProcessCount !== false &&
					$serverProxyProcessCount !== false &&
					$selectedServerProxyProcesses !== false &&
					$serverProxyProcesses !== false
				) {
					$response['message']['text'] = 'Invalid server proxy process IDs, please try again.';

					if (
						$selectedServerProxyProcessCount > 0 &&
						$serverProxyProcessCount > 0
						empty($serverProxyProcesses) === false &&
						empty($selectedServerProxyProcesses) === false
					) {
						$response['message']['text'] = 'There is a minimum requirement of 10 server proxy processes, please try again.';
						$remainingServerProxyProcessCount = ($serverProxyProcessCount - $selectedServerProxyProcessCount);

						if ($remainingServerProxyProcessCount >= 10) {
							$response['message']['text'] = $defaultMessage;
							$serverProxyProcessData = array();

							foreach ($serverProxyProcessIds as $serverProxyProcessId) {
								$serverProxyProcessData[] = array(
									'id' => $serverProxyProcessId,
									'removed' => true
								);
							}

							$serverProxyProcessDataSaved = $this->save(array(
								'data' => $serverProxyProcessData,
								'to' => 'server_proxy_processes'
							));

							if ($serverProxyProcessDataSaved === true) {
								$response['message'] = array(
									'status' => 'success',
									'text' => 'Server proxy processes removed successfully.'
								);
							}
						}
					}
				}
			}

			return $response;
		}

		public function view($parameters = array()) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => 'Error viewing server proxy process, please try again.'
				)
			);

			if (empty($parameters['where']['id']) === false) {
				$serverProxyProcess = $this->fetch(array(
					'fields' => array(
						'id',
						'port',
						'server_id'
					),
					'from' => 'server_proxy_processes',
					'where' => array(
						'id' => $parameters['where']['id']
					)
				));

				if ($serverProxyProcess !== false) {
					$response['message']['text'] = 'Invalid server proxy process ID, please try again';

					if (empty($serverProxyProcess) === false) {
						$response = array(
							'data' => $serverProxyProcess,
							'message' => array(
								'status' => 'success',
								'text' => 'Server proxy process viewed successfully.'
							)
						);
					}
				}
			}

			return $response;
		}

	}

	if (!empty($configuration->parameters)) {
		$serverProxyProcessesModel = new ServerProxyProcessesModel();
		$data = $serverProxyProcessesModel->route($configuration->parameters);
	}
?>
