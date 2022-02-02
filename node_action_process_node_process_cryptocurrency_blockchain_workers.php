<?php
	// todo: add this file to /etc/crontab if block headers exist on node

	if (empty($parameters) === true) {
		exit;
	}

	function _processNodeProcessCryptocurrencyBlockchainWorkers($parameters, $response) {
		$systemActionListNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersParameters = array(
			'action' => 'list_node_process_cryptocurrency_blockchain_worker_block_headers',
			'node_authentication_token' => $parameters['node_authentication_token']
		);
		$encodedSystemActionListNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersParameters = json_encode($systemActionListNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersParameters);

		if (empty($encodedSystemActionListNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersParameters) === false) {
			unlink('/usr/local/nodecompute/system_action_list_node_process_cryptocurrency_blockchain_worker_block_headers_response.json');
			shell_exec('sudo ' . $parameters['binary_files']['wget'] . ' -O /usr/local/nodecompute/system_action_list_node_process_cryptocurrency_blockchain_worker_block_headers_response.json --no-dns-cache --post-data \'json=' . $encodedSystemActionListNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersParameters . '\' --timeout=10 ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php');

			if (file_exists('/usr/local/nodecompute/system_action_list_node_process_cryptocurrency_blockchain_worker_block_headers_response.json') === false) {
				$response['message'] = 'Error listing node process cryptocurrency blockchain worker block headers, please try again.';
				return $response;
			}

			$systemActionListNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersResponse = file_get_contents('/usr/local/nodecompute/system_action_list_node_process_cryptocurrency_blockchain_worker_block_headers_response.json');
			$systemActionListNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersResponse = json_decode($systemActionListNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersResponse, true);

			if ($systemActionListNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersResponse === false) {
				$response['message'] = 'Error listing node process cryptocurrency blockchain worker block headers, please try again.';
				return $response;
			}

			if (empty($systemActionListNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersResponse['data']) === true) {
				// todo: delete this file from /etc/crontab
				$response['message'] = 'Node process cryptocurrency blockchain workers processed successfully.';
				$response['valid_status'] = '1';
				return $response;
			}

			$nodeProcessCryptocurrencyBlockchainWorkers = $systemActionListNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersResponse['data'];

			foreach ($nodeProcessCryptocurrencyBlockchainWorkers as $nodeProcessCryptocurrencyBlockchainWorkerIndex => $nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders) {
				$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders = json_encode($nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders);
				file_put_contents('/usr/local/nodecompute/system_action_list_node_process_cryptocurrency_blockchain_worker' . $nodeProcessCryptocurrencyBlockchainWorkerIndex . '_block_headers.json', $nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders);
			}
		}

		return $response;
	}

	if (($parameters['action'] === 'process_node_process_cryptocurrency_blockchain_workers') === true) {
		$response = _processNodeProcessCryptocurrencyBlockchainWorkers($parameters, $response);
	}
?>
