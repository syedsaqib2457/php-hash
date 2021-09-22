<?php
	$extend = true;
	require_once($system->settings['base_path'] . '/methods/system.php');

	class NodeUserRequestLogMethods extends SystemMethods {

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding node user request logs, please try again.',
				'status_valid' => (
					(empty($_FILES['data']['tmp_name']) === false) &&
					(empty($parameters['user']['endpoint']) === false)
				)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (
				(empty($parameters['data']['node_id']) === false) &&
				(is_numeric($parameters['data']['node_id']) === true)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid request log node ID, please try again.';
			}

			// todo: verify $parameters['data']['node_id'] belongs to node with remote_addr to prevent poisoning from compromised node

			$response['status_valid'] = (
				(empty($parameters['data']['type']) === false) &&
				(in_array(array(
					'http_proxy',
					'recursive_dns',
					'socks_proxy'
				)) === true)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid request log type, please try again.';
				return $response;
			}

			$nodeUserRequestLogData = array();
			$nodeUserRequestLogKeys = array(
				'bytes_received',
				'bytes_sent',
				'created',
				'destination_hostname',
				'destination_ip',
				'response_code',
				'source_ip',
				'username'
			);
			$nodeUserRequestLogs = explode("\n", file_get_contents($_FILES['data']['tmp_name']));
			array_pop($nodeUserRequestLogs);

			foreach ($nodeUserRequestLogs as $nodeUserRequestLogKey => $nodeUserRequestLog) {
				$nodeUserRequestLog = explode(' _ ', $nodeUserRequestLog);
				$nodeUserRequestLogData[$nodeUserRequestLogKey] = array_combine($nodeUserRequestLogKeys, $nodeUserRequestLog);

				if (empty($nodeUserRequestLogData[$nodeUserRequestLogKey]) === false) {
					$nodeUserRequestLogData[$nodeUserRequestLogKey]['node_id'] = $parameters['data']['node_id'];
					$nodeUserRequestLogData[$nodeUserRequestLogKey]['type'] = $parameters['data']['type'];
				}
			}

			$nodeUserRequestLogsSaved = $this->save(array(
				'data' => $nodeUserRequestLogData,
				'to' => 'node_user_request_logs'
			));
			$response['status_valid'] = ($nodeUserRequestLogsSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['data']['most_recent_node_user_request_log'] = $requestLog;
			$response['message'] = 'Node user request logs added successfully.';
			return $response;
		}

		public function process($parameters) {
			$response = array(
				'message' => 'Error processing node user request logs, please try again.',
				'status_valid' => false
			);
			$nodeUserRequestLogsToProcessParameters = array(
				'in' => 'node_user_request_logs',
				'where' => array(
					'status_processed' => false,
					'OR' => array(
						'modified >' => date('Y-m-d H:i:s', strtotime('-10 minutes')),
						'status_processing' => false
					)
				)
			);
			$nodeUserRequestLogsToProcessCount = $this->count($nodeUserRequestLogsToProcessParameters);
			$response['status_valid'] = (
				(is_int($nodeUserRequestLogsToProcessCount) === true) &&
				($nodeUserRequestLogsToProcessCount !== 0)
			);

			if ($response['status_valid'] === true) {
				$nodeUserRequestLogsToProcessParameters['data'] = array(
					'status_processing' => true
				);
				$nodeUserRequestLogsToProcessUpdated = $this->update($nodeUserRequestLogsToProcessParameters);

				if ($nodeUserRequestLogsToProcessUpdated === true) {
					$nodeUserRequestLogData = $requestDestinationIds = array();
					$nodeUserRequestLogsToProcessIndex = 0;
					$nodeUserRequestLogsToProcessParameters['fields'] = array(
						'bytes_received',
						'bytes_sent',
						'created',
						'destination_hostname',
						'destination_ip',
						'id',
						'node_id',
						'node_user_id',
						'response_code',
						'source_ip',
						'username',
						'type'
					);
					$nodeUserRequestLogsToProcessParameters['from'] = 'node_user_request_logs';
					$nodeUserRequestLogsToProcess = $this->fetch($nodeUserRequestLogsToProcessParameters);

					foreach ($requestLogsToProcess as $requestLogToProcessKey => $nodeUserRequestLogToProcess) {
						$nodeUserRequestLogCreated = substr(date('Y-m-d H:i', $nodeUserRequestLogToProcess['created']), 0, 15) . '0:00';
						$nodeUserRequestLogData[$nodeUserRequestLogToProcess['id']] = array(
							'id' => $nodeUserRequestLogToProcess['id'],
							'status_processed' => true
						);

						// todo: track node_user_id bandwidth usage

						if (
							(isset($requestDestinationIds[$nodeUserRequestLogToProcess['destination_hostname']]) === false) &&
							($this->_validateHostname($nodeUserRequestLogToProcess['destination_hostname']) !== false)
						) {
							$requestDestination = $this->fetch(array(
								'fields' => array(
									'id'
								),
								'from' => 'request_destinations',
								'where' => array(
									'address' => $nodeUserRequestLogToProcess['destination_hostname']
								)
							));
							$response['status_valid'] = ($requestDestination !== false);

							if ($response['status_valid'] === false) {
								continue;
							}

							if (empty($requestDestination['id']) === false) {
								$requestDestinationIds[$nodeUserRequestLogToProcess['destination_hostname']] = $requestDestination['id'];
							}
						}

						if (empty($requestDestinationIds[$nodeUserRequestLogToProcess['destination_hostname']]) === false) {
							$requestLogData[$nodeUserRequestLogToProcess['id']]['destination_id'] = $requestDestinationIds[$nodeUserRequestLogToProcess['destination_hostname']];
						}

						$nodeUserRequestLogsToProcessIndex++;

						if (
							($nodeUserRequestLogsToProcessIndex === 10000) ||
							(empty($nodeUserRequestLogsToProcess[($nodeUserRequestLogToProcessKey + 1)]) === true)
						) {
							$nodeUserRequestLogsToProcessIndex = 0;
							$this->save(array(
								'data' => $nodeUserRequestLogData,
								'to' => 'node_user_request_logs'
							));
							$nodeUserRequestLogData = array();
						}
					}
				}

				// todo: support additional VMs for processing request logs if necessary for millions of logs per minute
			}

			return $response;
		}

	}

	if (empty($system->parameters) === false) {
		$nodeUserRequestLogMethods = new NodeUserRequestLogMethods();
		$data = $nodeUserRequestLogMethods->route($system->parameters);
	}
?>
