<?php
	$extend = true;
	require_once($system->settings['base_path'] . '/methods/system.php');

	class RequestLogMethods extends SystemMethods {

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding request logs, please try again.',
				'status_valid' => (
					(empty($_FILES['data']['tmp_name']) === false) &&
					(empty($parameters['user']['endpoint']) === false)
				)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

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

			$requestLogData = array();
			$requestLogKeys = array(
				'bytes_received',
				'bytes_sent',
				'created',
				'destination_hostname',
				'destination_ip',
				'node_id',
				'node_user_id',
				'response_code',
				'source_ip',
				'username',
				'type'
			);
			$requestLogs = explode("\n", file_get_contents($_FILES['data']['tmp_name']));
			array_pop($requestLogs);

			foreach ($requestLogs as $requestLog) {
				$requestLogParts = explode(' _ ', $requestLog);

				if (empty($requestLogParts[0]) === false) {
					$requestLogParts[] = $parameters['data']['type'];
					$requestLogData[] = array_combine($requestLogKeys, $requestLogParts);
				}
			}

			$requestLogsSaved = $this->save(array(
				'data' => $requestLogData,
				'to' => 'request_logs'
			));
			$response['status_valid'] = ($requestLogsSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['data']['most_recent_request_log'] = $requestLog;
			$response['message'] = 'Request logs added successfully.';
			return $response;
		}

		public function processNodeRequestLogs($parameters) {
			$response = array(
				'message' => 'Error processing request logs, please try again.',
				'status_valid' => false
			);
			$requestLogsToProcessParameters = array(
				'in' => 'request_logs',
				'where' => array(
					'node_user_id !=' => null,
					'status_processed' => false,
					'OR' => array(
						'modified >' => date('Y-m-d H:i:s', strtotime('-10 minutes')),
						'status_processing' => false
					)
				)
			);
			$requestLogsToProcessCount = $this->count($requestLogsToProcessParameters);
			$response['status_valid'] = (
				(is_int($requestLogsToProcessCount) === true) &&
				($requestLogsToProcessCount !== 0)
			);

			if ($response['status_valid'] === true) {
				$requestLogsToProcessParameters['data'] = array(
					'status_processing' => true
				);
				$requestLogsToProcessUpdated = $this->update($requestLogsToProcessParameters);

				if ($requestLogsToProcessUpdated === true) {
					$nodeUserRequestLogData = $requestDestinationIds = $requestLogData = array();
					$requestLogsToProcessIndex = 0;
					$requestLogsToProcessParameters['fields'] = array(
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
					$requestLogsToProcessParameters['from'] = 'request_logs';
					$requestLogsToProcess = $this->fetch($requestLogsToProcessParameters);

					foreach ($requestLogsToProcess as $requestLogToProcessKey => $requestLogToProcess) {
						$nodeRequestLogCreated = substr(date('Y-m-d H:i', $requestLogToProcess['created']), 0, 15) . '0:00';
						$requestLogData[$requestLogToProcess['id']] = array(
							'id' => $requestLogToProcess['id'],
							'status_processed' => true
						);

						// todo: track node_user_id bandwidth usage

						if (
							(isset($requestDestinationIds[$requestLogToProcess['destination_hostname']]) === false) &&
							($this->_validateHostname($requestLogToProcess['destination_hostname']) !== false)
						) {
							$requestDestination = $this->fetch(array(
								'fields' => array(
									'id'
								),
								'from' => 'request_destinations',
								'where' => array(
									'address' => $requestLogToProcess['destination_hostname']
								)
							));
							$response['status_valid'] = ($requestDestination !== false);

							if ($response['status_valid'] === false) {
								continue;
							}

							if (empty($requestDestination['id']) === false) {
								$requestDestinationIds[$requestLogToProcess['destination_hostname']] = $requestDestination['id'];
							}
						}

						if (empty($requestDestinationIds[$requestLogToProcess['destination_hostname']]) === false) {
							$requestLogData[$requestLogToProcess['id']]['destination_id'] = $requestDestinationIds[$requestLogToProcess['destination_hostname']];
						}

						$requestLogsToProcessIndex++;

						if (
							($requestLogsToProcessIndex === 10000) ||
							(empty($requestLogsToProcess[($requestLogToProcessKey + 1)]) === true)
						) {
							$requestLogsToProcessIndex = 0;
							$this->save(array(
								'data' => $requestLogData,
								'to' => 'request_logs'
							));
							$requestLogData = array();
						}
					}
				}

				// todo: support additional VMs for processing request logs if necessary for millions of logs per minute
			}

			return $response;
		}

		public function processSystemRequestLogs($parameters) {
			$response = array(
				'message' => 'Error processing request logs, please try again.',
				'status_valid' => false
			);
			$requestLogsToProcess = $this->fetch(array(
				'fields' => array(
					'id',
					'source_ip'
				),
				'from' => 'request_logs',
				'where' => array(
					'node_user_id' => null,
					'response_code >=' => 10
				)
			));
			$response['status_valid'] = (empty($requestLogsToProcess) === false);

			if ($response['status_valid'] === true) {
				$requestLogsPath = $this->settings['base_path'] . '/request_logs/';

				if (is_dir($requestLogsPath) === false) {
					mkdir($requestLogsPath, 0755);
				}

				foreach ($requestLogsToProcess as $requestLogToProcess) {
					$requestLogToProcessPath = $requestLogsPath . implode('/', explode('.', $requestLogToProcessPath['source_ip'])) . '/';

					if (is_dir($requestLogToProcessPath) === false) {
						mkdir($requestLogToProcessPath, 0755, true);
					}

					$requestLogToProcessFile = $requestLogToProcessPath . '.';

					if (filemtime($requestLogToProcessFile) < strtotime('-1 hour')) {
						rmdir($requestLogToProcessPath);
					}
				}

				// todo: limit prefixes instead of addresses for ipv6

				$requestLogsDeleted = $this->delete(array(
					'from' => 'request_logs',
					'where' => array(
						'modified <' => date('Y-m-d H:i:s', strtotime('-1 hour')),
						'node_user_id' => null
					)
				));
				$response['status_valid'] = ($requestLogsDeleted === true);

				if ($response['status_valid'] === false) {
					return $response;
				}

				$response['message'] = 'Request logs processed successfully.';
				return $response;
			}

			// todo: clear proxy logs every 20 minutes
			// todo: create api for downloading proxy log files for elapsed 10 minute time period
			// ..
			return $response;
		}

	}

	if (empty($system->parameters) === false) {
		$requestLogMethods = new RequestLogMethods();
		$data = $requestLogMethods->route($system->parameters);
	}
?>
