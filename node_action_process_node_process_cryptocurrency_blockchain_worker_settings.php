<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _processNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response) {
		$systemActionListNodeProcessCryptocurrencyBlockchainWorkerSettingsParameters = array(
			'action' => 'list_node_process_cryptocurrency_blockchain_worker_settings',
			'node_authentication_token' => $parameters['node_authentication_token']
		);
		$systemActionListNodeProcessCryptocurrencyBlockchainWorkerSettingsParameters = json_encode($systemActionListNodeProcessCryptocurrencyBlockchainWorkerSettingsParameters);

		if (empty($systemActionListNodeProcessCryptocurrencyBlockchainWorkerSettingsParameters) === false) {
			unlink('/usr/local/nodecompute/system_action_list_node_process_cryptocurrency_blockchain_worker_settings_response.json');
			shell_exec('sudo ' . $parameters['binary_files']['wget'] . ' -O /usr/local/nodecompute/system_action_list_node_process_cryptocurrency_blockchain_worker_settings_response.json --no-dns-cache --post-data \'json=' . $encodedSystemActionListNodeProcessCryptocurrencyBlockchainWorkerSettingsParameters . '\' --timeout=10 ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php');

			if (file_exists('/usr/local/nodecompute/system_action_list_node_process_cryptocurrency_blockchain_worker_settings_response.json') === false) {
				$response['message'] = 'Error listing node process cryptocurrency blockchain worker settings, please try again.';
				return $response;
			}

			$systemActionListNodeProcessCryptocurrencyBlockchainWorkerSettingsResponse = file_get_contents('/usr/local/nodecompute/system_action_list_node_process_cryptocurrency_blockchain_worker_settings_response.json');
			$systemActionListNodeProcessCryptocurrencyBlockchainWorkerSettingsResponse = json_decode($systemActionListNodeProcessCryptocurrencyBlockchainWorkerSettingsResponse, true);

			if ($systemActionListNodeProcessCryptocurrencyBlockchainWorkerSettingsResponse === false) {
				$response['message'] = 'Error listing node process cryptocurrency blockchain worker settings, please try again.';
				return $response;
			}

			if (
				(empty($systemActionListNodeProcessCryptocurrencyBlockchainWorkerSettingsResponse['data']) === true) &&
				(file_exists('/usr/local/nodecompute/node_process_cryptocurrency_blockchain_worker_settings_data.json') === false)
			) {
				$response['message'] = 'Node process cryptocurrency blockchain worker settings processed successfully.';
				$response['valid_status'] = '1';
				return $response;
			}

			$crontabCommands = file_get_contents('/etc/crontab');

			if (empty($crontabCommands) === true) {
				$response['message'] = 'Error listing crontab commands, please try again.';
				return $response;
			}

			$crontabCommands = explode("\n", $crontabCommands);
			$crontabCommandIndex = array_search('# nodecompute_cryptocurrency_blockchain_worker_processes', $crontabCommands);

			if (is_int($crontabCommandIndex) === true) {
				while (is_int($crontabCommandIndex) === true) {
					unset($crontabCommands[$crontabCommandIndex]);
					$crontabCommandIndex++;

					if (strpos($crontabCommands[$crontabCommandIndex], ' nodecompute_cryptocurrency_blockchain_worker_processes') === false) {
						$crontabCommandIndex = false;
					}
				}
			}

			if (empty($systemActionListNodeProcessCryptocurrencyBlockchainWorkerSettingsResponse['data']) === false) {
				$nodeProcessCryptocurrencyBlockchainWorkerSettings = json_encode($systemActionListNodeProcessCryptocurrencyBlockchainWorkerSettingsResponse['data']);

				if (
					(($nodeProcessCryptocurrencyBlockchainWorkerSettings === false) === false) &&
					((file_put_contents('/usr/local/nodecompute/node_process_cryptocurrency_blockchain_worker_settings_data.json', $nodeProcessCryptocurrencyBlockchainWorkerSettings) === false) === false)
				) {
					$crontabCommands[] = '# nodecompute_cryptocurrency_blockchain_worker_processes';
					$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderDelays = range(1, 56, 5);
					$nodeProcessCryptocurrencyBlockchainWorkerDelays = range(8, 58, 10);
					$nodeProcessCryptocurrencyBlockchainWorkerSettings = $systemActionListNodeProcessCryptocurrencyBlockchainWorkerSettingsResponse['data'];

					foreach ($nodeProcessCryptocurrencyBlockchainWorkerDelays as $nodeProcessCryptocurrencyBlockchainWorkerDelay) {
						$crontabCommands[] = '* * * * * root sudo ' . $parameters['binary_files']['sleep'] . ' ' . $nodeProcessCryptocurrencyBlockchainWorkerDelay . ' && sudo ' . $parameters['binary_files']['php'] . ' /usr/local/nodecompute/node_endpoint.php process_node_process_cryptocurrency_blockchain_workers nodecompute_cryptocurrency_blockchain_worker_processes';
					}

					foreach ($nodeProcessCryptocurrencyBlockchainWorkerSettings as $nodeProcessCryptocurrencyBlockchainWorkerSettingNodeProcessType => $nodeProcessCryptocurrencyBlockchainWorkerSetting) {
						$crontabCommands[] = '* * * * * root sudo ' . $parameters['binary_files']['timeout'] . ' 58 ' . $parameters['binary_files']['php'] . ' /usr/local/nodecompute/node_endpoint.php process_node_process_' . $nodeProcessCryptocurrencyBlockchainWorkerSettingNodeProcessType . '_block_data nodecompute_cryptocurrency_blockchain_worker_processes';
						$nodeProcessCryptocurrencyBlockchainWorkerIndexes = range(1, $nodeProcessCryptocurrencyBlockchainWorkerSetting['count']);

						foreach ($nodeProcessCryptocurrencyBlockchainWorkerIndexes as $nodeProcessCryptocurrencyBlockchainWorkerIndex) {
							foreach ($nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderDelays as $nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderDelay) {
								$crontabCommands[] = '* * * * * root sudo ' . $parameters['binary_files']['sleep'] . ' ' . $nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderDelay . ' && sudo ' . $parameters['binary_files']['timeout'] . ' ' . $nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderDelay . ' ' . $parameters['binary_files']['php'] . ' /usr/local/nodecompute/node_endpoint.php process_node_process_' . $nodeProcessCryptocurrencyBlockchainWorkerSettingNodeProcessType . '_worker_block_headers ' . $nodeProcessCryptocurrencyBlockchainWorkerIndex . ' nodecompute_cryptocurrency_blockchain_worker_processes';
							}
						}

						$crontabCommands[] = '*/9 * * * * root sudo ' . $parameters['binary_files']['php'] . ' /usr/local/nodecompute/node_endpoint.php process_node_process_' . $nodeProcessCryptocurrencyBlockchainWorkerSettingNodeProcessType . '_worker_block_headers_hash_speed_logs nodecompute_cryptocurrency_blockchain_worker_processes';
					}
				}
			} else {
				unlink('/usr/local/nodecompute/node_process_cryptocurrency_blockchain_worker_settings_data.json');
			}

			$crontabCommands = implode("\n", $crontabCommands);

			if (file_put_contents('/etc/crontab', $crontabCommands) === false) {
				echo 'Error adding crontab commands, please try again.';
				return $response;
			}

			shell_exec('sudo ' . $parameters['binary_files']['crontab'] . ' /etc/crontab');
		}

		$response['message'] = 'Node process cryptocurrency blockchain worker settings processed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'process_node_process_cryptocurrency_blockchain_worker_settings') === true) {
		$response = _processNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response);
	}
?>
