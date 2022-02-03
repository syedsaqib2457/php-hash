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

			$crontabCommands = file_get_contents('/etc/crontab');

			if (empty($crontabCommands) === true) {
				$response['message'] = 'Error listing crontab commands, please try again.';
				return $response;
			}

			$crontabCommands = explode("\n", $crontabCommands);

			if (empty($systemActionListNodeProcessCryptocurrencyBlockchainWorkerSettingsResponse['data']) === false) {
				foreach ($systemActionListNodeProcessCryptocurrencyBlockchainWorkerSettingsResponse['data'] as $systemActionListNodeProcessCryptocurrencyBlockchainWorkerSetting) {
					// todo: edit cryptocurrency crontab processes
					// node_action_process_node_process_cryptocurrency_blockchain_workers (frequent parsing of block header data to files for each worker process)
					// node_action_process_node_process_cryptocurrency_blockchain_worker_block_headers
						// todo: add blockchain worker count + worker process timeout seconds to $systemActionListNodeProcessCryptocurrencyBlockchainWorkerSettingsResponse
				}
			} else {
				// todo: delete cryptocurrency worker processes from crontab
			}

			$crontabCommands = implode("\n", $crontabCommands);

			if (file_put_contents('/etc/crontab', $crontabCommands) === false) {
				echo 'Error adding crontab commands, please try again.';
				return $response;
			}

			shell_exec('sudo ' . $parameters['binary_files']['crontab'] . ' /etc/crontab');
		}
	}

	if (($parameters['action'] === 'process_node_process_cryptocurrency_blockchain_worker_settings') === true) {
		$response = _processNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response);
	}
?>
