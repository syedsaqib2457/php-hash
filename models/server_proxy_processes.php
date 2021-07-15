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

					if ($serverProxyProcess !== false) {
						$response['message']['text'] = 'Invalid server proxy process ID, please try again.';

						if (empty($serverProxyProcess) === false) {
							$response['message']['text'] = 'Port already in use on this server, please try again.';
							$serverId = $serverProxyProcess['server_id'];
							$serverProcessPorts = $this->_call(array(
								'method_from' => 'servers',
								'method_name' => 'fetchServerProcessPorts',
								'method_parameters' => array(
									$serverId
								)
							));

							if (in_array($validServerProxyProcessPort, $serverProcessPorts) === false) {
								$response['message']['text'] = $defaultMessage;
								$serverProxyProcessData = array(
									array(
										'id' => $parameters['where']['id'],
										'port' => $validServerProxyProcessPort,
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
