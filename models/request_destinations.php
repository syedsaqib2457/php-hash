<?php
	$extend = true;
	require_once($configuration->settings['base_path'] . '/models/system.php');

	class RequestDestinationsModel extends SystemModel {

		public function add($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error adding proxy URL, please try again.')
				)
			);

			if (!empty($parameters['data']['url'])) {
				$response['message']['text'] = 'URL must be a root domain or subdomain (e.g. example.com), please try again.';
				$url = rtrim($parameters['data']['url'], '/');

				if (strpos(str_replace('://', '', $url), '/') === false) {
					$response['message']['text'] = 'URL already in use, please try again.';
					$existingProxyUrlParameters = array(
						'fields' => array(
							'url'
						),
						'from' => 'proxy_urls',
						'where' => array(
							'url' => $url
						)
					);
					$existingProxyUrl = $this->fetch($existingProxyUrlParameters);

					if (empty($existingProxyUrl['count'])) {
						$response['message']['text'] = $defaultMessage;

						if ($this->save(array(
							'data' => array(
								array(
									'url' => $url
								)
							),
							'to' => 'proxy_urls'
						))) {
							$response['message'] = array(
								'status' => 'success',
								'text' => 'Proxy URL added successfully.'
							);
						}
					}
				}
			}

			return $response;
		}

		public function edit($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error editing proxy URL, please try again.')
				)
			);

			// ..

			return $response;
		}

		public function remove($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => 'Error removing proxy URLs, please try again.'
				)
			);

			if (
				!empty($parameters['items'][$parameters['item_list_name']]['data']) &&
				($proxyUrlIds = $parameters['items'][$parameters['item_list_name']]['data'])
			) {
				$proxyUrlData = array();

				foreach ($proxyUrlIds as $proxyUrlId) {
					$proxyUrlData[] = array(
						'id' => $proxyUrlId,
						'removed' => true
					);
				}

				if (
					$this->delete(array(
						'from' => 'proxy_url_request_limitation_proxies',
						'where' => array(
							'proxy_url_id' => $proxyUrlIds
						)
					)) &&
					$this->save(array(
						'data' => $proxyUrlData,
						'to' => 'proxy_urls'
					))
				) {
					$response['message'] = array(
						'status' => 'success',
						'text' => 'Proxy URLs removed successfully.'
					);
				}
			}

			return $response;
		}

	}

	if (!empty($configuration->parameters)) {
		$requestDestinationsModel = new RequestDestinationsModel();
		$data = $requestDestinationsModel->route($configuration->parameters);
	}
?>
