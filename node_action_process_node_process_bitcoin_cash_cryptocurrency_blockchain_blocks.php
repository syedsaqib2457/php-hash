<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _processNodeProcessBitcoinCashCryptocurrencyBlockchainBlocks($parameters, $response) {
		while (true) {
			$systemActionListNodeProcessCryptocurrencyBlockchainBlockProcessingLogsParameters = array(
				'action' => 'list_node_process_cryptocurrency_blockchain_block_processing_logs',
				'node_authentication_token' => $parameters['node_authentication_token'],
				'where' => array(
					'node_process_type' => 'bitcoin_cash_cryptocurrency_blockchain',
					'processed_status' => '0'
				)
			);
			$systemActionListNodeProcessCryptocurrencyBlockchainBlockProcessingLogsParameters = json_encode($systemActionListNodeProcessCryptocurrencyBlockchainBlockProcessingLogsParameters);
			shell_exec('sudo ' . $parameters['binary_files']['wget'] . ' -O /usr/local/nodecompute/system_action_list_node_process_bitcoin_cash_cryptocurrency_blockchain_block_processing_logs_response.json --no-dns-cache --post-data \'json=' . $systemActionListNodeProcessCryptocurrencyBlockchainBlockProcessingLogsParameters . '\' --timeout=10 ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php');
			$systemActionListNodeProcessCryptocurrencyBlockchainBlockProcessingLogsResponse = file_get_contents('/usr/local/nodecompute/system_action_list_node_process_bitcoin_cash_cryptocurrency_blockchain_block_processing_logs_response.json');
			$systemActionListNodeProcessCryptocurrencyBlockchainBlockProcessingLogsResponse = json_decode($systemActionListNodeProcessCryptocurrencyBlockchainBlockProcessingLogsResponse, true);

			if (empty($systemActionListNodeProcessCryptocurrencyBlockchainBlockProcessingLogsResponse['data']) === true) {
				sleep(1);
				continue;
			}

			foreach ($systemActionListNodeProcessCryptocurrencyBlockchainBlockProcessingLogsResponse['data'] as $nodeProcessCryptocurrencyBlockchainBlockProcessingLog) {
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactions = file_get_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_blockchain_block_' . $nodeProcessCryptocurrencyBlockchainBlockProcessingLog['block_height'] . '_transactions.json');
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactions = json_decode($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactions, true);

				if (empty($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactions) === true) {
					continue;
				}

				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockResponse = false;
				exec('sudo /usr/local/nodecompute/bitcoin_cash/bin/bitcoin-cli -conf=/usr/local/nodecompute/bitcoin_cash/bitcoin.conf submitblock \'' . $nodeProcessCryptocurrencyBlockchainBlockProcessingLog['block'] . $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount . $nodeProcessCryptocurrencyBlockchainBlockProcessingLog['block_reward_transaction'] . $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactions . '\' 2>&1', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockResponse);
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockResponse = implode('', $nodeProcessBitcoinCashCryptocurrencyBlockSubmissionResponse);
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockResponse = json_decode($nodeProcessBitcoinCashCryptocurrencyBlockSubmissionResponse, true);

				if (empty($nodeProcessBitcoinCashCryptocurrencyBlockSubmissionResponse) === true) {
					// todo
				} else {
					// todo
				}

				// todo: edit record in list_node_process_cryptocurrency_blockchain_block_processing_logs
			}
			
			$response['message'] = 'Node process Bitcoin Cash cryptocurrency blockchain block processed successfully.';
			return $response;
		}
	}

	if (($parameters['action'] === 'process_node_process_bitcoin_cash_cryptocurrency_blockchain_blocks') === true) {
		$response = _processNodeProcessBitcoinCashCryptocurrencyBlockchainBlocks($parameters, $response);
	}
?>
