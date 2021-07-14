<?php
	$extend = true;
	require_once($configuration->settings['base_path'] . '/models/main.php');

	class ServerProxyProcessesModel extends MainModel {

		public function add($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error adding server proxy process, please try again.')
				)
			);

			if (
				empty($parameters['data']['port']) === false &&
				empty($parameters['data']['server_id']) === false
			) {
				$response['message']['text'] = 'Invalid port, please try again.';
				$validServerProxyProcessPort = $this->_validatePort($parameters['data']['port']);

				if (is_int($validServerProxyProcessPort) === true) {
					$response['message']['text'] = $defaultMessage;
					$server = $this->fetch(array(
						'fields' => array(
							'id'
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
							$serverProcessPorts = $this->_call(array(
								'method_from' => 'servers',
								'method_name' => 'fetchServerProcessPorts',
								'method_parameters' => array(
									$serverId
								)
							));

							if ($serverProcessPorts !== false) {
								$response['message']['text'] = 'Port already in use on this server, please try again.';

								if (in_array($validServerProxyProcessPort, $serverProcessPorts) === false) {
									$response['message']['text'] = $defaultMessage;
									$serverProxyProcessDataSaved = $this->save(array(
										'data' => array(
											array(
												'port' => $validServerProxyProcessPort,
												'server_id' => $serverId
											)
										),
										'to' => 'server_proxy_processes'
									));

									if ($serverProxyProcessDataSaved === true) {
										$response['message'] = array(
											'status' => 'success',
											'text' => 'Server proxy process added successfully.'
										);
									}
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
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error editing server proxy process, please try again.')
				)
			);

			if (
				empty($parameters['data']['port']) === false &&
				empty($parameters['where']['id']) === false
			) {
				$response['message']['text'] = 'Invalid port, please try again.';
				$validServerProxyProcessPort = $this->_validatePort($parameters['data']['port']);

                                if (is_int($validServerProxyProcessPort) === true) {
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

					// ..

					if (!empty($serverProxyProcess['count'])) {
						$response['message']['text'] = 'Port already in use on this server, please try again.';
						$serverId = $serverProxyProcess['data'][0]['server_id'];
						$serverProxyProcessPorts = $this->fetchServerProxyProcessPorts($serverId);

						if (!in_array($validServerProxyProcessPort, $serverProxyProcessPorts)) {
							$response['message']['text'] = $defaultMessage;
							$serverProxyProcessData = array(
								array(
									'id' => $parameters['where']['id'],
									'port' => $validServerProxyProcessPort,
									'server_id' => $serverId
								)
							);

							if (
								$this->save(array(
									'data' => $serverProxyProcessData,
									'to' => 'server_proxy_processes'
								))
							) {
								$response = array(
									'data' => $serverProxyProcessData[0],
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

			return $response;
		}

		public function fetchServerProcessPorts($serverId, $serverProcessType = false) { // todo: add ports to server_nodes for both DNS and proxies, move to servers.php, pass second optional parameter for serverProcessPortType
			$response = array();
			$serverProcessPortParameters = array(
				'fields' => array(
					'port'
				),
				'where' => array(
					'server_id' => $serverId
				)
			);
			$serverProcessTypes = array(
				'nameserver',
				'proxy'
			);

			if (is_string($serverProcessType) === true) {
				$serverProcessTypes = array_intersect($serverProcessTypes, array(
					$serverProcessType
				));
			}

			if (empty($serverProcessTypes) === false) {
				foreach ($serverProcessPortTypes as $serverProcessPortType) {
					$serverProcessPortParameters['from'] = 'server_' . $serverProcessPortType . '_processes';
					$serverProcessPorts = $this->fetch($serverProcessPortParameters);

					if ($serverProcessPorts === false) {
						return false;
					}

					if (empty($serverProcessPorts) === false) {
						foreach ($serverProcessPorts as $serverProcessPort) {
							$response[$serverProcessPort] = $serverProcessPort;
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
			if (!empty($parameters['items'][$parameters['item_list_name']]['data'])) {
				$serverProxyProcessParameters = array(
					'fields' => array(
						'id'
					),
					'from' => 'server_proxy_processes',
					'where' => array(
						'server_id' => ($serverId = $parameters['server_id'])
					)
				);
				$serverProxyProcesses = $this->fetch($serverProxyProcessParameters);
				$serverProxyProcessIds = $serverProxyProcessParameters['where']['id'] = $parameters['items'][$parameters['item_list_name']]['data'];
				$selectedServerProxyProcesses = $this->fetch($serverProxyProcessParameters);

				if (
					!empty($serverProxyProcesses['count']) &&
					!empty($selectedServerProxyProcesses['count'])
				) {
					$response['message']['text'] = 'Cannot remove the selected processes past the minimum count, please try again.';
					$validServerProxyProcessCount = true;

					if (
						($remainingServerProxyProcessCount = ($serverProxyProcesses['count'] - $selectedServerProxyProcesses['count'])) &&
						$remainingServerProxyProcessCount < 10
					) {
						$validServerProxyProcessCount = false;
					}

					if ($validServerProxyProcessCount === true) {
						$response['message']['text'] = $defaultMessage;
						$serverProxyProcessData = array();

						foreach ($serverProxyProcessIds as $serverProxyProcessId) {
							$serverProxyProcessData[] = array(
								'id' => $serverProxyProcessId,
								'removed' => true
							);
						}

						if (
							$this->save(array(
								'data' => $serverProxyProcessData,
								'to' => 'server_proxy_processes'
							))
						) {
							$response['message'] = array(
								'status' => 'success',
								'text' => 'Server proxy processes removed successfully.'
							);
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

			if (
				!empty($parameters['where']['id']) &&
				is_string($parameters['where']['id'])
			) {
				$serverProxyProcess = $this->fetch(array(
					'fields' => array(
						'id',
						'port',
						'server_id'
					),
					'from' => 'server_proxy_processes',
					'where' => array_intersect_key($parameters['where'], array(
						'id' => true
					))
				));

				if (!empty($serverProxyProcess['count'])) {
					$response = array(
						'data' => $serverProxyProcess['data'][0],
						'message' => array(
							'status' => 'success',
							'text' => 'Server proxy process viewed successfully.'
						)
					);
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
