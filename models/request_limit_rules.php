<?php
	$extend = true;
	require_once($configuration->settings['base_path'] . '/models/system.php');

	class RequestLimitRulesModel extends SystemModel {

		public function add($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error adding proxy URL request limitation, please try again.')
				)
			);

			if (
				isset($parameters['data']['proxy_url_block_interval_type']) &&
				isset($parameters['data']['proxy_url_block_interval_value']) &&
				isset($parameters['data']['proxy_url_request_interval_type']) &&
				isset($parameters['data']['proxy_url_request_interval_value']) &&
				isset($parameters['data']['proxy_url_request_number'])
			) {
				$response['message']['text'] = 'Request intervals must be valid numbers greater than 0, please try again.';
				$validIntervalTypes = array(
					'minute',
					'hour',
					'day',
					'month'
				);
				$validIntervalValues = array_product(array(
					$parameters['data']['proxy_url_block_interval_value'],
					$parameters['data']['proxy_url_request_interval_value'],
					$parameters['data']['proxy_url_request_number']
				));

				if (!empty($validIntervalValues)) {
					$response['message']['text'] = 'Request intervals must be set in minutes, hours, days or months, please try again.';

					if (
						in_array($parameters['data']['proxy_url_block_interval_type'], $validIntervalTypes) &&
						in_array($parameters['data']['proxy_url_request_interval_type'], $validIntervalTypes)
					) {
						$response['message']['text'] = 'Proxy URL request limitation already exists, please try again.';
						$proxyUrlRequestLimitationData = array(
							array_intersect_key($parameters['data'], array(
								'proxy_url_block_interval_type' => true,
								'proxy_url_block_interval_value' => true,
								'proxy_url_request_interval_type' => true,
								'proxy_url_request_interval_value' => true,
								'proxy_url_request_number' => true
							))
						);
						$existingProxyUrlRequestLimitation = $this->fetch(array(
							'fields' => array(
								'id'
							),
							'from' => 'proxy_url_request_limitations',
							'where' => $proxyUrlRequestLimitationData[0]
						));

						if (empty($existingProxyUrlRequestLimitation['count'])) {
							$response['message']['text'] = $defaultMessage;

							if ($this->save(array(
								'data' => $proxyUrlRequestLimitationData,
								'to' => 'proxy_url_request_limitations'
							))) {
								$response['message'] = array(
									'status' => 'success',
									'text' => 'Proxy URL request limitation added successfully.'
								);
							}
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
					'text' => 'Error editing proxy URL request limitation, please try again.'
				)
			);

			// ..

			return $response;
		}

		public function remove($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => 'Error removing proxy URL request limitations, please try again.'
				)
			);

			if (
				!empty($parameters['items'][$parameters['item_list_name']]['data']) &&
				($proxyUrlRequestLimitationIds = $parameters['items'][$parameters['item_list_name']]['data'])
			) {
				$proxyUrlRequestLimitationData = array();

				foreach ($proxyUrlRequestLimitationIds as $proxyUrlRequestLimitationId) {
					$proxyUrlRequestLimitationData[] = array(
						'id' => $proxyUrlRequestLimitationId,
						'removed' => true
					);
				}

				if (
					$this->delete(array(
						'from' => 'proxy_url_request_limitation_proxies',
						'where' => array(
							'proxy_url_request_limitation_id' => $proxyUrlRequestLimitationIds
						)
					)) &&
					$this->save(array(
						'data' => $proxyUrlRequestLimitationData,
						'to' => 'proxy_url_request_limitations'
					))
				) {
					$response['message'] = array(
						'status' => 'success',
						'text' => 'Proxy URL request limitations removed successfully.'
					);
				}
			}

			return $response;
		}

		public function view($parameters = array()) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => 'Error viewing proxy URL request limitation, please try again.'
				)
			);

			if (
				!empty($parameters['data']['id']) &&
				is_string($parameters['data']['id'])
			) {
				// ..
			}

			return $response;
		}

	}

	if (!empty($configuration->parameters)) {
		$requestLimitRulesModel = new RequestLimitRulesModel();
		$data = $requestLimitRulesModel->route($configuration->parameters);
	}
?>
