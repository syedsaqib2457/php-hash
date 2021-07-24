<?php
	$extend = true;
	require_once($system->settings['base_path'] . '/methods/system.php');

	class RequestLogMethods extends SystemMethods {

		public function archive($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => 'Error archiving proxy URL request logs, please try again.'
				)
			);

			if (
				!empty($_FILES['data']['tmp_name']) &&
				!empty($parameters['user']['endpoint'])
			) {
				$proxyUrlRequestLogData = array();
				$proxyUrlRequestLogKeys = array(
					'bytes_received',
					'bytes_sent',
					'client_ip',
					'code',
					'created',
					'proxy_id',
					'server_id',
					'target_ip',
					'target_url',
					'username'
				);
				$proxyUrlRequestLogs = explode("\n", file_get_contents($_FILES['data']['tmp_name']));
				array_pop($proxyUrlRequestLogs);

				foreach ($proxyUrlRequestLogs as $proxyUrlRequestLog) {
					$proxyUrlRequestLogParts = explode(' _ ', $proxyUrlRequestLog);

					if (!empty($proxyUrlRequestLogParts[0])) {
						$proxyUrlRequestLogData[] = array_combine($proxyUrlRequestLogKeys, $proxyUrlRequestLogParts);
					}
				}

				if ($this->save(array(
					 'data' => $proxyUrlRequestLogData,
					'to' => 'proxy_url_request_logs'
				))) {
					$response = array(
						'data' => array(
							'most_recent_proxy_url_request_log' => $proxyUrlRequestLog
						),
						'message' => array(
							'status' => 'success',
							'text' => 'Proxy url request logs archived successfully.'
						)
					);
                        	}
			}

			return $response;
		}

		public function processRequestLogs($parameters) {
			// todo: limit prefixes instead of addresses for ipv6
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

			if (empty($requestLogsToProcess) === false) {
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

				// ..

				$this->delete(array(
					'from' => 'request_logs',
					'where' => array(
						'modified <' => date('Y-m-d H:i:s', strtotime('-1 hour')),
						'node_user_id' => null
					)
				));
				$response = array(
					'message' => array(
						'status' => 'success',
						'text' => 'Request logs processed successfully.'
					)
				);
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
