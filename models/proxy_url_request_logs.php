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
				!empty($parameters['user']['endpoint']) &&
				!empty($parameters['where']['id'])
			) {
				// ..
			}

			return $response;
		}

	}

	if (!empty($configuration->parameters)) {
		$proxyUrlRequestLogsModel = new ProxyUrlRequestLogsModel();
		$data = $proxyUrlRequestLogsModel->route($configuration->parameters);
	}
?>
