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
			$systemRequestLogsSaved = $this->save(array(
				'data' => $systemRequestLogData,
				'to' => 'system_request_logs'
			));
			$response['status_valid'] = ($systemRequestLogsSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['message'] = 'System request logs added successfully.';
			return $response;
		}

		public function process($parameters) {
			$response = array(
				'message' => 'Error processing system request logs, please try again.',
				'status_valid' => false
			);
			$systemRequestLogsToProcess = $this->fetch(array(
				'fields' => array(
					'id',
					'source_ip'
				),
				'from' => 'system_request_logs',
				'where' => array(
					'status_processed' => false,
					// ..
				)
			));
			$response['status_valid'] = (empty($systemRequestLogsToProcess) === false);

			if ($response['status_valid'] === true) {
				$systemRequestLogsPath = $this->settings['base_path'] . '/request_logs/';

				if (is_dir($systemRequestLogsPath) === false) {
					mkdir($systemRequestLogsPath, 0755);
				}

				foreach ($systemRequestLogsToProcess as $systemRequestLogToProcess) {
					$systemRequestLogSourceIp = $this->_sanitizeIps($systemRequestLogToProcessPath['source_ip']);
					$systemRequestLogSourceIpDelimiter = '.';

					if (empty($systemRequestLogSourceIp[6]) === false) {
						$systemRequestLogSourceIpDelimiter = ':';
					}

					$systemRequestLogSourceIp = current($systemRequestLogSourceIp);
					$systemRequestLogToProcessPath = $systemRequestLogsPath . implode('/', explode($systemRequestLogSourceIpDelimiter, $systemRequestLogSourceIp)) . '/';

					if (is_dir($systemRequestLogToProcessPath) === false) {
						mkdir($systemRequestLogToProcessPath, 0755, true);
					}

					$systemRequestLogToProcessFile = $systemRequestLogToProcessPath . '.';

					if (filemtime($systemRequestLogToProcessFile) < strtotime('-1 hour')) {
						rmdir($systemRequestLogToProcessPath);
					}
				}

				$systemRequestLogsDeleted = $this->delete(array(
					'from' => 'system_request_logs',
					'where' => array(
						'modified <' => date('Y-m-d H:i:s', strtotime('-1 hour')),
						'node_user_id' => null
					)
				));
				$response['status_valid'] = ($systemRequestLogsDeleted === true);

				if ($response['status_valid'] === false) {
					return $response;
				}

				$response['message'] = 'System request logs processed successfully.';
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
