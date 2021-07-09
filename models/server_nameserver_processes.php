<?php
	$extend = true;
	require_once($configuration->settings['base_path'] . '/models/main.php');

	class ServerNameserverProcessesModel extends MainModel {

		public function add($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error adding server nameserver process, please try again.')
				)
			);

			if (
				isset($parameters['data']['create_internal_process']) &&
				isset($parameters['data']['external_source_ip']) &&
				isset($parameters['data']['listening_ip'])
			) {
				$serverNameserverProcessExternalSourceIp = $parameters['data']['external_source_ip'];
				$serverNameserverProcessListeningIp = $parameters['data']['listening_ip'];

				if (
					($validServerNameserverProcessListeningIp = current(current($this->_validateIps($serverNameserverProcessListeningIp)))) &&
					empty($validServerNameserverProcessListeningIp)
				) {
					$response['message']['text'] = 'Invalid listening IP' . ($serverNameserverProcessListeningIp ? ' ' . $serverNameserverProcessListeningIp : '') . ', please try again.';
				}

				$usePublicServerNameserverSourceIp = empty($parameters['data']['create_internal_process']);
				$serverNameserverProcessExternalSourceIp = $usePublicServerNameserverSourceIp ? $serverNameserverProcessListeningIp : $serverNameserverProcessExternalSourceIp;

				if (
					($validServerNameserverProcessSourceIp = current(current($this->_validateIps($serverNameserverProcessExternalSourceIp)))) &&
					empty($validServerNameserverProcessSourceIp)
				) {
					$response['message']['text'] = 'Invalid source IP' . ($serverNameserverProcessExternalSourceIp ? ' ' . $serverNameserverProcessExternalSourceIp : '') . ', please try again.';
				}

				if (
					$validServerNameserverProcessListeningIp &&
					$validServerNameserverProcessSourceIp
				) {
					$serverNameserverListeningIpData = $serverNameserverProcessData = $serverProxyProcessData = array_intersect_key($parameters['where'], array(
						'server_id' => true
					));
					$serverNodeIpParameters = array(
						'fields' => array(
							'external_ip',
							'id',
							'internal_ip'
						),
						'from' => 'server_nodes',
						'where' => array_merge($serverNameserverProcessData, array(
							'OR' => array(
								'external_ip' => $serverNameserverProcessListeningIp,
								'internal_ip' => $serverNameserverProcessListeningIp
							)
						))
					);
					$serverNodeIp = $this->fetch($serverNodeIpParameters);
					$validServerNameserverProcessIps = true;

					if (
						$usePublicServerNameserverSourceIp &&
						!empty($serverNodeIp['count'])
					) {
						$response['message']['text'] = 'Invalid public listening IP ' . $validNameserverProcessListeningIp . ', please try again.';
						$validServerNameserverProcessIps = false;
					} elseif (
						!empty($serverNodeIp['data'][0]['internal_ip']) &&
						$validServerNameserverProcessListeningIp != $serverNodeIp['data'][0]['internal_ip']
					) {
						$validServerNameserverProcessListeningIp = $serverNodeIp['data'][0]['internal_ip'];
					}

					if (
						$validServerNameserverProcessIps === true &&
						$validServerNameserverProcessListeningIp !== $validServerNameserverProcessSourceIp
					) {
						$serverNodeIpParameters['where']['OR'] = array(
							'external_ip' => $validServerNameserverProcessSourceIp,
							'internal_ip' => $validServerNameserverProcessSourceIp
						);
						$serverNodeIp = $this->fetch($serverNodeIpParameters);

						if (empty($serverNodeIp['count'])) {
							$response['message']['text'] = 'Source IP not found on this server, please try again.';
							$validServerNameserverProcessIps = false;
						} elseif (!empty($serverNodeIp['data'][0]['internal_ip'])) {
							$serverNameserverProcessData['internal_source_ip'] = $serverNodeIp['data'][0]['internal_ip'];
							$validServerNameserverProcessSourceIp = $serverNodeIp['data'][0]['external_ip'];
						}
					}

					if ($validServerNameserverProcessIps === true) {
						$response['message']['text'] = $defaultMessage;
						$existingServerNameserverProcessParameters = array(
							'fields' => array(
								'id'
							),
							'from' => 'server_nameserver_processes',
							'where' => array_merge($serverNameserverProcessData, array(
								'listening_ip' => $validServerNameserverProcessListeningIp,
								'external_source_ip' => $validServerNameserverProcessSourceIp
							))
						);
						$existingServerNameserverProcess = $this->fetch($existingServerNameserverProcessParameters);
						$serverNameserverProcessData = array(
							array_merge($serverNameserverProcessData, array(
								'listening_ip' => $serverNameserverProcessListeningIp,
								'local' => !$usePublicServerNameserverSourceIp,
								'external_source_ip' => $validServerNameserverProcessSourceIp
							))
						);
						$existingServerNameserverProcessParameters['fields'][] = 'source_ip_count';
						$existingServerNameserverProcessParameters['from'] = 'server_nameserver_listening_ips';
						$existingServerNameserverProcessParameters['where']['listening_ip'] = $serverNameserverListeningIpData['listening_ip'] = $validServerNameserverProcessListeningIp;
						unset($existingServerNameserverProcessParameters['where']['external_source_ip']);
						$serverNameserverListeningIps = $this->fetch($existingServerNameserverProcessParameters);

						if ($usePublicServerNameserverSourceIp) {
							$serverNameserverListeningIpData['source_ip_count'] = 0;
						}

						if (!empty($serverNameserverListeningIps['count'])) {
							$serverNameserverListeningIps['data'][0]['source_ip_count']++;
							$serverNameserverListeningIpData = array_merge($serverNameserverListeningIpData, $serverNameserverListeningIps['data'][0]);
						}

						$serverNameserverListeningIpData = array(
							$serverNameserverListeningIpData
						);
						unset($existingServerNameserverProcessParameters['where']['internal_source_ip']);
						$existingServerNameserverListeningIp = $this->fetch(array(
							'fields' => array(
								'external_source_ip'
							),
							'from' => 'server_nameserver_processes',
							'where' => $existingServerNameserverProcessParameters['where']
						));
						$existingServerNameserverLoadBalanceSourceIp = false;

						if ($existingServerNameserverListeningIp['count']) {
							unset($existingServerNameserverProcessParameters['where']['listening_ip']);
							$existingServerNameserverProcessParameters['where']['external_source_ip'] = $existingServerNameserverListeningIp['data'][0];
							$existingServerNameserverListeningIps = $this->fetch(array(
								'fields' => array(
									'listening_ip'
								),
								'from' => 'server_nameserver_processes',
								'where' => $existingServerNameserverProcessParameters['where']
							));

							if ($existingServerNameserverListeningIps['count'] > 1) {
								$existingServerNameserverLoadBalanceSourceIp = true;
							}
						}

						if ($existingServerNameserverProcess['count']) {
							$response['message']['text'] = 'IP already in use on this server, please try again.';
						} elseif ($existingServerNameserverLoadBalanceSourceIp === true) {
							$response['message']['text'] = 'Listening IP already in use as a load balancer on this server, please try again.';
						} elseif (
							$this->save(array(
								'data' => $serverNameserverListeningIpData,
								'to' => 'server_nameserver_listening_ips'
							)) &&
							$this->save(array(
								'data' => $serverNameserverProcessData,
								'to' => 'server_nameserver_processes'
							))
						) {
							$response['message'] = array(
								'status' => 'success',
								'text' => 'Server nameserver process added successfully.'
							);
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
					'text' => 'Error removing server nameserver processes, please try again.'
				)
			);

			if (
				!empty($parameters['items'][$parameters['item_list_name']]['data']) &&
				!empty($parameters['server_id'])
			) {
				$serverNameserverListeningIpSourceIpCounts = $serverNameserverProcessIds = array();
				$serverNameserverProcessData = array(
					'server_id' => $parameters['server_id']
				);
				$serverNameserverProcessParameters = array(
					'fields' => array(
						'id',
						'listening_ip'
					),
					'from' => 'server_nameserver_processes',
					'where' => array_merge(array(
						'id' => $parameters['items'][$parameters['item_list_name']]['data']
					), $serverNameserverProcessData)
				);
				$serverNameserverProcesses = $this->fetch($serverNameserverProcessParameters);

				if (!empty($serverNameserverProcesses['count'])) {
					foreach ($serverNameserverProcesses['data'] as $serverNameserverProcess) {
						$serverNameserverListeningIpSourceIpCounts[$serverNameserverProcess['listening_ip']] += 1;
						$serverNameserverProcessIds[$serverNameserverProcess['id']] = $serverNameserverProcess['id'];
					}

					$serverNameserverListeningIpParameters = array(
						'fields' => array(
							'id',
							'listening_ip',
							'server_id',
							'source_ip_count'
						),
						'from' => 'server_nameserver_listening_ips',
						'where' => array_merge(array(
							'listening_ip' => array_keys($serverNameserverListeningIpSourceIpCounts)
						), $serverNameserverProcessData)
					);
					$serverNameserverListeningIps = $this->fetch($serverNameserverListeningIpParameters);

					if (!empty($serverNameserverListeningIps['count'])) {
						$serverNameserverListeningIpIdsToDelete = array();

						foreach ($serverNameserverListeningIps['data'] as $serverNameserverListeningIpKey => $serverNameserverListeningIp) {
							$serverNameserverListeningIpSourceIpCount = max(0, $serverNameserverListeningIp['source_ip_count'] - $serverNameserverListeningIpSourceIpCounts[$serverNameserverListeningIp['listening_ip']]);

							if ($serverNameserverListeningIpSourceIpCount === 0) {
								$serverNameserverListeningIpIdsToDelete[] = $serverNameserverListeningIp['id'];
								unset($serverNameserverListeningIps['data'][$serverNameserverListeningIpKey]);
							} else {
								$serverNameserverListeningIps['data'][$serverNameserverListeningIpKey]['source_ip_count'] = $serverNameserverListeningIpSourceIpCount;
							}
						}

						$serverNameserverProcessIds = array_filter(array_values($serverNameserverProcessIds));
						$serverNameserverProcessData = array();

						foreach ($serverNameserverProcessIds as $serverNameserverProcessId) {
							$serverNameserverProcessData[] = array(
								'id' => $serverNameserverProcessId,
								'removed' => true
							);
						}

						if (
							(
								empty($serverNameserverListeningIpIdsToDelete) ||
								(
									$this->delete(array(
										'from' => 'server_nameserver_listening_ips',
										'where' => array(
											'id' => $serverNameserverListeningIpIdsToDelete
										)
									)) &&
									$this->delete(array(
										'from' => 'server_proxy_process_nameserver_processes',
										'where' => array(
											'server_nameserver_listening_ip_id' => $serverNameserverListeningIpIdsToDelete
										)
									))
								)
							) &&
							$this->save(array(
								'data' => $serverNameserverListeningIps['data'],
								'to' => 'server_nameserver_listening_ips'
							)) &&
							$this->save(array(
								'data' => $serverNameserverProcessData,
								'to' => 'server_nameserver_processes'
							))
						) {
							$response['message'] = array(
								'status' => 'success',
								'text' => 'Server nameserver processes removed successfully.'
							);
						}
					}
				}
			}

			return $response;
		}

	}

	if (!empty($configuration->parameters)) {
		$serverNameserverProcessesModel = new ServerNameserverProcessesModel();
		$data = $serverNameserverProcessesModel->route($configuration->parameters);
	}
?>
