<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _processNodeProcessBitcoinCashCryptocurrencyBlockchainBlockData($parameters, $response) {
		while (true) {
			if (file_exists('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_blockchain_block_data.json') === true) {
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockData = file_get_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_blockchain_block_data.json');
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockData = json_decode($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockData, true);

				if ($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockData === false) {
					continue;
				}

				$systemActionAddNodeProcessCryptocurrencyBlockchainBlockProcessingLogsParameters = array(
					'action' => 'add_node_process_cryptocurrency_blockchain_block_processing_logs',
					'data' => array(
						'block' => $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockData[0],
						'block_reward_transaction' => $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockData[1],
						'node_process_type' => 'bitcoin_cash_cryptocurrency_blockchain'
					),
					'node_authentication_token' => $parameters['node_authentication_token']
				);
				$systemActionAddNodeProcessCryptocurrencyBlockchainBlockProcessingLogsParameters = json_encode($systemActionAddNodeProcessCryptocurrencyBlockchainBlockProcessingLogsParameters);
				unlink('/usr/local/nodecompute/system_action_add_node_process_bitcoin_cash_cryptocurrency_blockchain_block_processing_logs_response.json');
				shell_exec('sudo ' . $parameters['binary_files']['wget'] . ' -O /usr/local/nodecompute/system_action_add_node_process_bitcoin_cash_cryptocurrency_blockchain_block_processing_logs_response.json --no-dns-cache --post-data \'json=' . $systemActionAddNodeProcessCryptocurrencyBlockchainBlockProcessingLogsParameters . '\' --timeout=10 ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php');

				if (file_exists('/usr/local/nodecompute/system_action_add_node_process_bitcoin_cash_cryptocurrency_blockchain_block_processing_logs_response.json') === false) {
					continue;
				}

				$systemActionAddNodeProcessCryptocurrencyBlockchainBlockProcessingLogsResponse = file_get_contents('/usr/local/nodecompute/system_action_list_node_process_bitcoin_cash_cryptocurrency_blockchain_block_processing_logs_response.json');
				$systemActionAddNodeProcessCryptocurrencyBlockchainBlockProcessingLogsResponse = json_decode($systemActionAddNodeProcessCryptocurrencyBlockchainBlockProcessingLogsResponse, true);

				if ($systemActionListNodeProcessCryptocurrencyBlockchainWorkerSettingsResponse === false) {
					continue;
				}
			}

			sleep(1);
		}
	}

	if (($parameters['action'] === 'process_node_process_bitcoin_cash_cryptocurrency_blockchain_block_data') === true) {
		$response = _processNodeProcessBitcoinCashCryptocurrencyBlockchainBlockData($parameters, $response);
	}
?>
