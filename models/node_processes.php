<?php
	$extend = true;
	require_once($configuration->settings['base_path'] . '/models/main.php');

	class NodeProcessesModel extends MainModel {

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding node proxy process, please try again.',
				'status_valid' => false
			);

			if (empty($parameters['data']['transport_protocol']) === false) {
				$response['status_valid'] = (in_array($parameters['data']['transport_protocol'], array(
						'tcp',
						'udp'
					))
				);
			}

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid transport protocol, please try again.';
				return $response;
			}

			if (empty($parameters['data']['node_id']) === false) {
				$node = $nodeData = $this->fetch(array(
					'fields' => array(
						'id',
						'node_id',
						'type'
					),
					'from' => 'nodes',
					'where' => array(
						'id' => ($nodeProcessNodeId = $parameters['data']['node_id'])
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
				$response['message'] = 'Invalid application protocol, please try again.';
				return $response;
			}

			if (empty($parameters['data']['node_id']) === false) {
				$nodeProcessPort = $this->_validatePort($parameters['data']['port']);
				$response['status_valid'] = (is_int($nodeProcessPort) === true);
			}

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process port, please try again.';
				return $response;
			}

			$conflictingNodeProcessPortCount = $this->count(array(
				'in' => 'node_processes',
				'where' => array(
					'node_id' => array_filter($node)
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

			$nodeProcessDataSaved = $this->save(array(
				'data' => array(
					array(
						'node_id' => $nodeProcessNodeId,
						'port' => $nodeProcessPort
					)
				),
				'to' => 'nodes'
			));

			if ($nodeDataSaved === false) {
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
