<?php
	$extend = true;
	require_once($system->settings['base_path'] . '/methods/system.php');

	class SystemRequestLogMethods extends SystemMethods {

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding request logs, please try again.',
				'status_valid' => (
					// ..
				)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			// ..
			$systemRequestLogData = array();
			$systemRequestLogDataSaved = $this->save(array(
				'data' => $systemRequestLogData,
				'to' => 'system_request_logs'
			));
			$response['status_valid'] = ($systemRequestLogDataSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['message'] = 'System request logs added successfully.';
			return $response;
		}

		public function process($parameters) {
			$response = array(
				'message' => 'Error processing request logs, please try again.',
				'status_valid' => false
			);
			$systemRequestLogsToProcess = $this->fetch(array(
				'fields' => array(
					'id',
					'source_ip'
				),
				'from' => 'request_logs',
				'where' => array(
					'status_processed' => false,
					// ..
				)
			));
			$response['status_valid'] = (empty($requestLogsToProcess) === false);

			if ($response['status_valid'] === true) {
				$requestLogsPath = $this->settings['base_path'] . '/request_logs/';

				if (is_dir($requestLogsPath) === false) {
					mkdir($requestLogsPath, 0755);
				}

				foreach ($requestLogsToProcess as $requestLogToProcess) {
					$requestLogSourceIp = $this->_sanitizeIps($requestLogToProcessPath['source_ip']);
					$requestLogSourceIpDelimiter = '.';

					if (empty($requestLogSourceIp[6]) === false) {
						$requestLogSourceIpDelimiter = ':';
					}

					$requestLogSourceIp = current($requestLogSourceIp);
					$requestLogToProcessPath = $requestLogsPath . implode('/', explode($requestLogSourceIpDelimiter, $requestLogSourceIp)) . '/';

					if (is_dir($requestLogToProcessPath) === false) {
						mkdir($requestLogToProcessPath, 0755, true);
					}

					$requestLogToProcessFile = $requestLogToProcessPath . '.';

					if (filemtime($requestLogToProcessFile) < strtotime('-1 hour')) {
						rmdir($requestLogToProcessPath);
					}
				}

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
		$systemRequestLogMethods = new SystemRequestLogMethods();
		$data = $systemRequestLogMethods->route($system->parameters);
	}
?>
