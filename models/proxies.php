<?php
	$extend = true;
	require_once($configuration->settings['base_path'] . '/models/main.php');

	class ProxiesModel extends MainModel {

		public function authenticate($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => ($defaultMessage = 'Error authenticating proxies, please try again.')
				)
			);

			if (
				(
					!empty($parameters['data']['password']) ||
					!empty($parameters['data']['username'])
				) &&
				(
					empty($parameters['data']['password']) ||
					empty($parameters['data']['username'])
				)
			) {
				$response['message']['text'] = 'Both username and password must be either set or empty, please try again.';
			} else {
				if (
					(
						!empty($parameters['data']['username']) &&
						(
							strlen($parameters['data']['username']) < 4 ||
							strlen($parameters['data']['username']) > 15
						)
					) ||
					(
						!empty($parameters['data']['password']) &&
						(
							strlen($parameters['data']['password']) < 4 ||
							strlen($parameters['data']['password']) > 15
						)
					)
				) {
					$response['message']['text'] = 'Both username and password must be between 4 and 15 characters, please try again.';
				} else {
					if (empty($parameters['data']['password'])) {
						$parameters['data']['password'] = null;
					}

					if (empty($parameters['data']['username'])) {
						$parameters['data']['username'] = null;
					}

					$existingUsername = $this->fetch(array(
						'fields' => array(
							'id'
						),
						'from' => 'proxies',
						'where' => array(
							'password !=' => $parameters['data']['password'],
							'username' => $parameters['data']['username'],
							'username !=' => null
						)
					));

					if (!empty($existingUsername['count'])) {
						$response['message']['text'] = 'Username [' . $parameters['data']['username'] . '] is already in use with a different password, please try a different username.';
					} else {
						$response['message']['text'] = $defaultMessage;

						if (!empty($parameters['items']['list_proxy_items']['count'])) {
							$validatedWhitelistedIps = (!empty($parameters['data']['whitelisted_ips']) ? $this->_validateIps($parameters['data']['whitelisted_ips'], true) : array());
							$proxyData = $whitelistedIps = array();

							if (!empty($validatedWhitelistedIps)) {
								foreach ($validatedWhitelistedIps as $validatedWhitelistedIpVersionIps) {
									$whitelistedIps += $validatedWhitelistedIpVersionIps;
								}
							}

							foreach ($parameters['items']['list_proxy_items']['data'] as $proxyId) {
								$proxy = $this->fetch(array(
									'fields' => array(
										'external_ip',
										'server_id'
									),
									'from' => 'proxies',
									'where' => array(
										'id' => $proxyId
									)
								));

								if (!empty($proxy['count'])) {
									$proxy = array(
										'id' => $proxyId,
										'password' => $parameters['data']['password'],
										'server_id' => ($serverId = $proxy['data'][0]['server_id']),
										'username' => $parameters['data']['username'],
										'whitelisted_ips' => implode("\n", array_diff($whitelistedIps, $proxy['data'][0]))
									);

									if (!empty($parameters['data']['generate_unique'])) {
										$proxyAuthentication = array_intersect_key($proxy, array(
											'password' => true,
											'username' => true
										));
										$proxy = array_merge($proxy, $this->_generateRandomAuthentication($proxyAuthentication));
									}

									if (!empty($parameters['data']['ignore_empty'])) {
										$proxy = array_filter($proxy);
									}

									$proxyData[] = $proxy;
								}
							}

							if ($this->save(array(
								'data' => $proxyData,
								'to' => 'proxies'
							))) {
								$response['message'] = array(
									'status' => 'success',
									'text' => 'Proxies authenticated successfully.'
								);
							}
						}
					}
				}
			}

			return $response;
		}

		public function download($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => 'Error downloading proxies, please try again.'
				)
			);

			if (!empty($parameters['items']['list_proxy_items']['data'])) {
				$formattedProxies = $proxyPorts = $serverProxyProcessPorts = array();
				$parameters['items']['list_proxy_items']['data'] = array_intersect_key($parameters['items']['list_proxy_items']['data'], array(
					$parameters['data']['results'] => true
				));
				$proxyItemParameters = array(
					'items' => array_intersect_key($parameters['items'], array(
						'list_proxy_items' => true
					))
				);

				if (!empty($parameters['search']['list_proxy_items'])) {
					$proxyItemParameters['search']['list_proxy_items'] = $parameters['search']['list_proxy_items'];
				}

				$proxyItems = $this->_decodeItems($proxyItemParameters, true);

				if (!empty($proxyItems['list_proxy_items']['data'])) {
					$proxyParameters = array(
						'fields' => array(
							'external_ip',
							'id',
							'internal_ip',
							'password',
							'server_id',
							'username'
						),
						'from' => 'proxies',
						'where' => array(
							'id' => $proxyItems['list_proxy_items']['data']
						)
					);

					if (!empty($proxyItems['list_proxy_items']['token']['parameters']['sort'])) {
						$proxyParameters['sort'] = $proxyItems['list_proxy_items']['token']['parameters']['sort'];
					}

					$proxies = $this->fetch($proxyParameters);
					$delimiters = array(
						!empty($parameters['data']['ipv4_delimiter1']) ? $parameters['data']['ipv4_delimiter1'] : '',
						!empty($parameters['data']['ipv4_delimiter2']) ? $parameters['data']['ipv4_delimiter2'] : '',
						!empty($parameters['data']['ipv4_delimiter3']) ? $parameters['data']['ipv4_delimiter3'] : '',
						''
					);
					$delimiterMask = implode('', array_unique($delimiters));

					if (!empty($proxies['data'])) {
						foreach ($proxies['data'] as $proxy) {
							$serverId = $proxy['server_id'];

							if (empty($serverProxyProcessPorts[$serverId])) {
								$serverProxyProcessPorts[$serverId] = $this->_call(array(
									'method_from' => 'server_proxy_processes',
									'method_name' => 'fetchServerProxyProcessPorts',
									'method_parameters' => array(
										$serverId
									)
								));
							}

							if (!empty($serverProxyProcessPorts[$serverId])) {
								foreach ($serverProxyProcessPorts[$serverId] as $serverProxyProcessPort) {
									$proxyPorts[$serverProxyProcessPort] = $serverProxyProcessPort;
								}
							}
						}

						$response['data'] = array(
							'proxy_port' => ($proxyPort = $parameters['data']['proxy_port']),
							'proxy_ports' => $proxyPorts
						);
						$separatorKey = $parameters['data']['separator'];
						$separators = array(
							'comma' => ',',
							'hyphen' => '-',
							'new_line' => "\n",
							'plus' => '+',
							'semicolon' => ';',
							'space' => ' ',
							'underscore' => '_'
						);

						if (
							empty($separatorKey) ||
							!array_key_exists($separatorKey, $separators)
						) {
							$separatorKey = 'new_line';
						}

						$separator = $separators[$separatorKey];

						foreach ($proxies['data'] as $proxyKey => $proxy) {
							$formattedProxy = '';
							$proxy['port'] = current($serverProxyProcessPorts[$proxy['server_id']]);

							if (in_array($proxyPort, $serverProxyProcessPorts[$proxy['server_id']])) {
								$proxy['port'] = $proxyPort;
							}

							for ($i = 1; $i < 5; $i++) {
								$column = $parameters['data']['ipv4_column' . $i];
								$formattedProxy .= ($proxy[$column] ? $proxy[$column] . $delimiters[($i - 1)] : '');
							}

							$formattedProxies[$proxyKey] = rtrim($formattedProxy, $delimiterMask);
						}

						if (!empty($formattedProxies)) {
							$response['data']['formatted_proxies'] = implode($separator, $formattedProxies);
							$response['message'] = array(
								'status' => 'success',
								'text' => 'Proxies downloaded successfully.'
							);
						}
					}
				}
			}

			return $response;
		}

		public function edit($parameters = array()) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => 'Error editing proxy, please try again.'
				)
			);

			if (
				isset($parameters['data']['enable_url_request_logs']) &&
				is_numeric($parameters['data']['enable_url_request_logs']) &&
				isset($parameters['data']['password']) &&
				is_string($parameters['data']['password']) &&
				isset($parameters['data']['username']) &&
				is_string($parameters['data']['username']) &&
				isset($parameters['data']['status']) &&
				is_numeric($parameters['data']['status']) &&
				isset($parameters['data']['whitelisted_ips']) &&
				is_string($parameters['data']['whitelisted_ips']) &&
				!empty($parameters['where']['id']) &&
				is_string($parameters['where']['id'])
			) {
				$proxy = $this->fetch(array(
					'fields' => array(
						'id',
						'server_id'
					),
					'from' => 'proxies',
					'where' => array_intersect_key($parameters['where'], array(
						'id' => true
					))
				));

				if (!empty($proxy['count'])) {
					$parameters['items']['list_proxy_items'] = array(
						'count' => 1,
						'data' => array(
							$parameters['where']['id']
						)
					);
					$authenticateProxy = $this->authenticate($parameters);

					if ($authenticateProxy['message']['status'] === 'error') {
						$response['message']['text'] = $authenticateProxy['message']['text'];
					} else {
						$proxyData = array(
							array_merge($proxy['data'][0], array(
								'enable_url_request_logs' => (boolean) $parameters['data']['enable_url_request_logs'],
								'status' => $parameters['data']['status'] ? 'active' : 'inactive'
							))
						);

						if ($this->save(array(
							'data' => $proxyData,
							'to' => 'proxies'
						))) {
							$response['message'] = array(
								'status' => 'success',
								'text' => 'Proxy edited successfully.'
							);
						}
					}
				}
			}

			return $response;
		}

		public function limit($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => 'Error limiting proxies, please try again.'
				)
			);

			if (!empty($parameters['items']['list_proxy_items']['count'])) {
				$formattedProxyServerIds = $proxyData = $proxyUrlRequestLimitationProxyData = array();

				if (
					($proxyUrlRequestLimitations = (
						!empty($parameters['items']['list_proxy_url_items']['data']) ||
						!empty($parameters['items']['list_proxy_url_request_limitation_items']['data'])
					)) ||
					(
						!empty($parameters['data']['block_all_urls']) ||
						(
							!empty($parameters['items']['list_proxy_url_items']['data']) &&
							!empty($parameters['data']['only_allow_urls'])
						)
					)
				) {
					if ($proxyUrlRequestLimitations === true) {
						$proxyServerIds = $this->fetch(array(
							'fields' => array(
								'id',
								'server_id'
							),
							'from' => 'proxies'
						));

						if (!empty($proxyServerIds['count'])) {
							foreach ($proxyServerIds['data'] as $proxyServerId) {
								$formattedProxyServerIds[$proxyServerId['id']] = $proxyServerId['server_id'];
							}
						}
					}

					foreach ($parameters['items']['list_proxy_items']['data'] as $proxyId) {
						if (
							!empty($parameters['data']['block_all_urls']) ||
							!empty($parameters['data']['only_allow_urls'])
						) {
							$proxyData[] = array(
								'block_all_urls' => (boolean) $parameters['data']['block_all_urls'],
								'id' => $proxyId,
								'only_allow_urls' => (boolean) $parameters['data']['only_allow_urls']
							);
						}

						if (
							!empty($parameters['items']['list_proxy_url_items']['data']) &&
							!empty($parameters['items']['list_proxy_url_request_limitation_items']['data'])
						) {
							foreach ($parameters['items']['list_proxy_url_items']['data'] as $proxyUrlId) {
								foreach ($parameters['items']['list_proxy_url_request_limitation_items']['data'] as $proxyUrlRequestLimitationId) {
									$proxyUrlRequestLimitationProxyData[] = array(
										'proxy_id' => $proxyId,
										'proxy_url_id' => $proxyUrlId,
										'proxy_url_request_limitation_id' => $proxyUrlRequestLimitationId,
										'server_id' => $formattedProxyServerIds[$proxyId]
									);
								}
							}
						}

						if (
							!empty($parameters['items']['list_proxy_url_items']['data']) &&
							empty($parameters['items']['list_proxy_url_request_limitation_items']['data'])
						) {
							$proxyUrlRequestLimitationProxyData[] = array(
								'proxy_id' => $proxyId,
								'proxy_url_id' => $proxyUrlId,
								'server_id' => $formattedProxyServerIds[$proxyId]
							);
						}

						if (
							empty($parameters['items']['list_proxy_url_items']['data']) &&
							!empty($parameters['items']['list_proxy_url_request_limitation_items']['data'])
						) {
							$proxyUrlRequestLimitationProxyData[] = array(
								'proxy_id' => $proxyId,
								'proxy_url_request_limitation_id' => $proxyUrlId,
								'server_id' => $formattedProxyServerIds[$proxyId]
							);
						}
					}
				}

				if (
					$this->delete(array(
						'from' => 'proxy_url_request_limitation_proxies',
						'where' => array(
							'proxy_id' => $parameters['items']['list_proxy_items']['data']
						)
					)) &&
					$this->save(array(
						'data' => $proxyData,
						'to' => 'proxies'
					)) &&
					$this->save(array(
						'data' => $proxyUrlRequestLimitationProxyData,
						'to' => 'proxy_url_request_limitation_proxies'
					))
				) {
					$response['message'] = array(
						'status' => 'success',
						'text' => 'Proxies limited successfully.'
					);
				}
			}

			return $response;
		}

		public function list($parameters) {
			return array();
		}

		public function search($parameters) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => 'Error searching proxies, please try again.'
				),
				'search' => array()
			);

			if (!empty($parameters['data']['broad_search'])) {
				$broadSearchFields = array(
					'password',
					'status',
					'username'
				);
				$broadSearchValues = array_filter(explode(' ', $parameters['data']['broad_search']));
				$response['search'] = array_map(function($broadSearchValue) use ($broadSearchFields) {
					$broadSearchFieldValues = array(
						'OR' => array()
					);

					foreach ($broadSearchFields as $broadSearchField) {
						$broadSearchFieldValues['OR'][$broadSearchField . ' LIKE'] = '%' . $broadSearchValue . '%';
					}

					return $broadSearchFieldValues;
				}, $broadSearchValues);
			}

			if (
				!empty($parameters['data']['granular_search']) &&
				($granularSearchIps = $this->_validateIps($parameters['data']['granular_search'], true, true))
			) {
				$response['search']['external_ip LIKE'] = $response['search']['internal_ip LIKE'] = array();

				foreach ($granularSearchIps as $ipVersion => $ips) {
					$formattedGranularSearchIps = array();

					foreach ($ips as $ipKey => $ip) {
						$formattedGranularSearchIps[] = $ip . '%';
					}

					$response['search']['external_ip LIKE'] += $formattedGranularSearchIps;
					$response['search']['internal_ip LIKE'] += $formattedGranularSearchIps;
				}
			}

			if (!empty($response['search'])) {
				$response['search'] = array(
					($parameters['data']['match_all_search'] ? 'AND' : 'OR') => $response['search']
				);

				unset($parameters['data']['id']);
			}

			$response = array_merge($response, array(
				'message' => array(
					'status' => 'success',
					'text' => 'Proxies searched successfully.'
				)
			));

			return $response;
		}

		public function view($parameters = array()) {
			$response = array(
				'message' => array(
					'status' => 'error',
					'text' => 'Error viewing server proxy process, please try again.'
				)
			);

			if (
				!empty($parameters['where']['id']) &&
				is_string($parameters['where']['id'])
			) {
				$proxy = $this->fetch(array(
					'fields' => array(
						'enable_url_request_logs',
						'external_ip',
						'id',
						'internal_ip',
						'password',
						'server_id',
						'status',
						'username',
						'whitelisted_ips'
					),
					'from' => 'proxies',
					'where' => array_intersect_key($parameters['where'], array(
						'id' => true
					))
				));
				$proxyId = $parameters['where']['id'];

				if (!empty($proxy['count'])) {
					$response = array(
						'data' => $proxy['data'][0],
						'message' => array(
							'status' => 'success',
							'text' => 'Proxy viewed successfully.'
						)
					);
				}
			}

			return $response;
		}

	}

	if (!empty($configuration->parameters)) {
		$proxiesModel = new ProxiesModel();
		$data = $proxiesModel->route($configuration->parameters);
	}
?>
