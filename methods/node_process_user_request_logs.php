<?php
	$extend = true;
	require_once($system->settings['base_path'] . '/methods/system.php');

	class NodeProcessUserRequestLogMethods extends SystemMethods {

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding node process user request logs, please try again.',
				'status_valid' => (
					(empty($parameters['data']['node_id']) === false) &&
					(is_numeric($parameters['data']['node_id']) === true)
				)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process user request log node ID, please try again.';
				return $response;
			}

			// todo: add node_process_id to each log file if 2+ processes don't share the same log files for either proxy or recursive_dns process types

			$response['status_valid'] = (
				(empty($parameters['data']['node_process_type']) === false) &&
				(in_array(array(
					'http_proxy',
					'recursive_dns',
					'socks_proxy'
				)) === true)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process user request log node process type, please try again.';
				return $response;
			}

			$response['status_valid'] = (
				(empty($parameters['data']['node_user_id']) === false) &&
				(is_numeric($parameters['data']['node_user_id']) === true)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process user request log node user ID, please try again.';
				return $response;
			}

			// todo: verify $parameters['data']['node_id'] belongs to node with remote_addr to prevent poisoning from compromised node
			// todo: verify node_user_id belongs to node_id

			$response['status_valid'] = (
				(empty($_FILES['data']['tmp_name']) === false) &&
				(empty($parameters['user']['endpoint']) === false)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$nodeProcessUserRequestLogs = explode("\n", file_get_contents($_FILES['data']['tmp_name']));
			$response['status_valid'] = (empty($nodeProcessUserRequestLogs) === false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$nodeProcessUserRequestLogData = array();

			switch ($parameters['data']['node_process_type']) {
				case 'http_proxy':
				case 'socks_proxy':
					array_pop($nodeProcessUserRequestLogs);

					foreach ($nodeProcessUserRequestLogs as $nodeProcessUserRequestLog) {
						$nodeProcessUserRequestLog = explode(' _ ', $nodeProcessUserRequestLog);
						$nodeProcessUserRequestLogData[] = array(
							'bytes_received' => $nodeProcessUserRequestLog[1],
							'bytes_sent' => $nodeProcessUserRequestLog[2],
							'created' => $nodeProcessUserRequestLog[3],
							'destination_hostname' => '$nodeProcessUserRequestLog[4]',
							'destination_ip' => $nodeProcessUserRequestLog[5],
							'node_id' => $parameters['data']['node_id'],
							'node_process_type' => $parameters['data']['node_process_type'],
							'node_user_id' => $parameters['data']['node_user_id'],
							'response_code' => $nodeProcessUserRequestLog[6],
							'source_ip' => $nodeProcessUserRequestLog[7],
							'username' => $nodeProcessUserRequestLog[8]
						);
					}

					break;
				case 'recursive_dns':
					// todo: format recursive_dns request logs for node_process_user_request_logs
					break;
			}

			$nodeProcessUserRequestLogsSaved = $this->save(array(
				'data' => $nodeProcessUserRequestLogData,
				'to' => 'node_process_user_request_logs'
			));
			$response['status_valid'] = ($nodeProcessUserRequestLogsSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['data']['most_recent_node_process_user_request_log'] = $nodeProcessUserRequestLog;
			$response['message'] = 'Node process user request logs added successfully.';
			return $response;
		}

		public function process($parameters) {
			$response = array(
				'message' => 'Error processing node process user request logs, please try again.',
				'status_valid' => false
			);
			$nodeProcessUserRequestLogsToProcessParameters = array(
				'in' => 'node_process_user_request_logs',
				'where' => array(
					'status_processed' => false,
					'OR' => array(
						'modified >' => date('Y-m-d H:i:s', strtotime('-10 minutes')),
						'status_processing' => false
					)
				)
			);
			$nodeProcessUserRequestLogsToProcessCount = $this->count($nodeProcessUserRequestLogsToProcessParameters);
			$response['status_valid'] = (
				(is_int($nodeProcessUserRequestLogsToProcessCount) === true) &&
				($nodeProcessUserRequestLogsToProcessCount !== 0)
			);

			if ($response['status_valid'] === true) {
				$nodeProcessUserRequestLogsToProcessParameters['data'] = array(
					'status_processing' => true
				);
				$nodeProcessUserRequestLogsToProcessUpdated = $this->update($nodeProcessUserRequestLogsToProcessParameters);

				if ($nodeProcessUserRequestLogsToProcessUpdated === true) {
					$nodeProcessUserRequestLogData = $requestDestinationIds = array();
					$nodeProcessUserRequestLogsToProcessIndex = 0;
					$nodeProcessUserRequestLogsToProcessParameters['fields'] = array(
						'bytes_received',
						'bytes_sent',
						'created',
						'destination_hostname',
						'destination_ip',
						'id',
						'node_id',
						'node_process_type',
						'node_user_id',
						'response_code',
						'source_ip',
						'username'
					);
					$nodeProcessUserRequestLogsToProcessParameters['from'] = 'node_process_user_request_logs';
					$nodeProcessUserRequestLogsToProcess = $this->fetch($nodeProcessUserRequestLogsToProcessParameters);

					foreach ($nodeProcessUserRequestLogsToProcess as $nodeProcessUserRequestLogToProcessKey => $nodeProcessUserRequestLogToProcess) {
						$nodeProcessUserRequestLogCreated = substr(date('Y-m-d H:i', $nodeProcessUserRequestLogToProcess['created']), 0, 15) . '0:00';
						$nodeProcessUserRequestLogData[$nodeProcessUserRequestLogToProcess['id']] = array(
							'id' => $nodeProcessUserRequestLogToProcess['id'],
							'status_processed' => true
						);

						// todo: track node_user_id bandwidth usage

						if (
							(isset($requestDestinationIds[$nodeProcessUserRequestLogToProcess['destination_hostname']]) === false) &&
							($this->_validateHostname($nodeProcessUserRequestLogToProcess['destination_hostname']) !== false)
						) {
							$requestDestination = $this->fetch(array(
								'fields' => array(
									'id'
								),
								'from' => 'request_destinations',
								'where' => array(
									'address' => $nodeProcessUserRequestLogToProcess['destination_hostname']
								)
							));
							$response['status_valid'] = ($requestDestination !== false);

							if ($response['status_valid'] === false) {
								continue;
							}

							if (empty($requestDestination['id']) === false) {
								$requestDestinationIds[$nodeProcessUserRequestLogToProcess['destination_hostname']] = $requestDestination['id'];
							}
						}

						if (empty($requestDestinationIds[$nodeProcessUserRequestLogToProcess['destination_hostname']]) === false) {
							$nodeProcessUserRequestLogData[$nodeProcessUserRequestLogToProcess['id']]['destination_id'] = $requestDestinationIds[$nodeProcessUserRequestLogToProcess['destination_hostname']];
						}

						$nodeProcessUserRequestLogsToProcessIndex++;

						if (
							($nodeProcessUserRequestLogsToProcessIndex === 10000) ||
							(empty($nodeProcessUserRequestLogsToProcess[($nodeProcessUserRequestLogToProcessKey + 1)]) === true)
						) {
							$nodeProcessUserRequestLogsToProcessIndex = 0;
							$this->save(array(
								'data' => $nodeProcessUserRequestLogData,
								'to' => 'node_process_user_request_logs'
							));
							$nodeProcessUserRequestLogData = array();
						}
					}
				}

				// todo: support additional VMs for processing request logs if necessary for millions of logs per minute
			}

			return $response;
		}

	}

	if (empty($system->parameters) === false) {
		$nodeProcessUserRequestLogMethods = new NodeProcessUserRequestLogMethods();
		$data = $nodeProcessUserRequestLogMethods->route($system->parameters);
	}
?>
