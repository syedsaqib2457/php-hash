<?php
	$extend = true;
	require_once($configuration->settings['base_path'] . '/models/main.php');

	class ProxyUrlRequestLogsModel extends MainModel {

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

		public function shellProcessProxyUrlRequestLogs($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => 'There aren\'t any new proxy URL request logs to process, please try again later.'
				)
			);
			// todo: clear proxy logs every 20 minutes
			// todo: create api for downloading proxy log files for elapsed 10 minute time period
			// ..
			return $response;
		}

	}

	if (!empty($configuration->parameters)) {
		$proxyUrlRequestLogsModel = new ProxyUrlRequestLogsModel();
		$data = $proxyUrlRequestLogsModel->route($configuration->parameters);
	}
?>
