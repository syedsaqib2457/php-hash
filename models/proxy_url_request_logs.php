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
				!empty($parameters['data']) &&
				!empty($parameters['user']['endpoint']) &&
				$this->save(array(
                                        'data' => $parameters['data'],
                                        'to' => 'proxy_url_request_logs'
                                ))
			) {
				$response['message'] = array(
					'status' => 'success',
					'text' => 'Proxy url request logs archived successfully.'
				);
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
			// Download proxy request logs from each proxy server instead of sending with wget --post-data
			return $response;
		}

	}

	if (!empty($configuration->parameters)) {
		$proxyUrlRequestLogsModel = new ProxyUrlRequestLogsModel();
		$data = $proxyUrlRequestLogsModel->route($configuration->parameters);
	}
?>
