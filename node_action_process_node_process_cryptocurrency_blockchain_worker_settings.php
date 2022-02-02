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

			// todo
		}
	}

	if (($parameters['action'] === 'process_node_process_cryptocurrency_blockchain_worker_settings') === true) {
		$response = _processNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response);
	}
?>
