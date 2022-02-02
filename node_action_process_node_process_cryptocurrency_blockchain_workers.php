<?php
	// todo: add this file to /etc/crontab if block headers exist on node

	if (empty($parameters) === true) {
		exit;
	}

	function _processNodeProcessCryptocurrencyBlockchainWorkers($parameters, $response) {
		$systemParameters = array(
			'action' => 'list_node_process_cryptocurrency_blockchain_worker_block_headers',
			'node_authentication_token' => $parameters['node_authentication_token'],
			'where' => array()
		);
		$encodedSystemParameters = json_encode($systemParameters);

		if (empty($encodedSystemParameters) === false) {
			unlink('/usr/local/nodecompute/system_action_list_node_process_cryptocurrency_blockchain_worker_block_headers_response.json');
			shell_exec('sudo ' . $parameters['binary_files']['wget'] . ' -O /usr/local/nodecompute/system_action_list_node_process_cryptocurrency_blockchain_worker_block_headers_response.json --no-dns-cache --post-data \'json=' . $encodedSystemParameters . '\' --timeout=10 ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php');

			if (file_exists('/usr/local/nodecompute/system_action_list_node_process_cryptocurrency_blockchain_worker_block_headers_response.json') === false) {
				$response['message'] = 'Error listing node process cryptocurrency blockchain worker block headers, please try again.';
				return $response;
			}

			// todo
		}

		return $response;
	}

	if (($parameters['action'] === 'process_node_process_cryptocurrency_blockchain_workers') === true) {
		$response = _processNodeProcessCryptocurrencyBlockchainWorkers($parameters, $response);
	}
?>
