<?php
	$extend = true;
	require_once($system->settings['base_path'] . '/methods/system.php');

	class SystemRequestLogMethods extends SystemMethods {

		public function process($parameters) {
			$response = array(
				'message' => 'Error processing system request logs, please try again.',
				'status_valid' => false
			);
			$systemRequestLogsToProcessParameters = array(
				'in' => 'system_request_logs',
				'where' => array(
					'status_processed' => false,
					'OR' => array(
						'modified >' => date('Y-m-d H:i:s', strtotime('-10 minutes')),
						'status_processing' => false
					)
				)
			);
			$systemRequestLogsToProcessCount = $this->count($systemRequestLogsToProcessParameters);
			$response['status_valid'] = (
				(is_int($systemRequestLogsToProcessCount) === true) &&
				($systemRequestLogsToProcessCount !== 0)
			);

			if ($response['status_valid'] === true) {
				$systemRequestLogsToProcessParameters['data'] = array(
					'status_processing' => true
				);
				$systemRequestLogsToProcessUpdated = $this->update($systemRequestLogsToProcessParameters);

				if ($systemRequestLogsToProcessUpdated === true) {
					$systemRequestLogData = array();
					$systemRequestLogsPath = $this->settings['base_path'] . '/request_logs/';

					if (is_dir($systemRequestLogsPath) === false) {
						mkdir($systemRequestLogsPath, 0755);
					}

					$systemRequestLogsToProcessIndex = 0;
					$systemRequestLogsToProcessParameters['fields'] = array(
						'id',
						'source_ip'
					);
					$systemRequestLogsToProcessParameters['from'] = 'system_request_logs';
					$systemRequestLogsToProcess = $this->fetch($systemRequestLogsToProcessParameters);

					foreach ($systemRequestLogsToProcess as $systemRequestLogToProcessKey => $systemRequestLogToProcess) {
						$systemRequestLogSourceIp = $this->_sanitizeIps($systemRequestLogToProcess['source_ip']);
						$systemRequestLogSourceIpDelimiter = '.';

						if (empty($systemRequestLogSourceIp[6]) === false) {
							// todo: block ipv6 prefixes based on the amount of IPs in a prefix making unauthorized requests unless nested file structure works for /128
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

						$systemRequestLogToProcess['status_processed'] = true;
						$systemRequestLogToProcess['status_processing'] = false;
						$systemRequestLogData[] = $systemRequestLogToProcess;
						$systemRequestLogsToProcessIndex++;

						if (
							($systemRequestLogsToProcessIndex === 10000) ||
							(empty($systemRequestLogsToProcess[($systemRequestLogToProcessKey + 1)]) === true)
						) {
							$systemRequestLogsToProcessIndex = 0;
							$this->save(array(
								'data' => $systemRequestLogData,
								'to' => 'system_request_logs'
							));
							$systemRequestLogData = array();
						}
					}
				}

				$systemRequestLogsDeleted = $this->delete(array(
					'from' => 'system_request_logs',
					'where' => array(
						'created <' => date('Y-m-d H:i:s', strtotime('-1 hour')),
						'status_authorized' => false,
						'status_processed' => false,
						'status_processing' => false
					)
				));
				$response['status_valid'] = ($systemRequestLogsDeleted === true);

				if ($response['status_valid'] === false) {
					return $response;
				}

				$response['message'] = 'System request logs processed successfully.';
				return $response;
			}

			return $response;
		}

	}

	if (empty($system->parameters) === false) {
		$systemRequestLogMethods = new SystemRequestLogMethods();
		$data = $systemRequestLogMethods->route($system->parameters);
	}
?>
