<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _processNodeProcessBitcoinCashCryptocurrencyBlockchainBlock($parameters, $response) {
		// todo: add to crontab from worker_settings
		// todo: list un-submitted block headers in node_process_cryptocurrency_blockchain_block_processing_logs with node_process_type bitcoin_cash_cryptocurrency_blockchain

		while (true) {
			/* if (file_exists('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_block_header.dat') === true) {
				$nodeProcessBitcoinCashCryptocurrencyBlockHeader = file_get_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_block_header.dat');
				$nodeProcessBitcoinCashCryptocurrencyBlockTransactions = file_get_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_block_transactions.dat');

				if (
					(empty($nodeProcessBitcoinCashCryptocurrencyBlockHeader) === false) &&
					(empty($nodeProcessBitcoinCashCryptocurrencyBlockTransactions) === false)
				) {
					exec('sudo bitcoin-cli -conf=/usr/local/nodecompute/bitcoin_cash/bitcoin.conf submitblock \'' . $nodeProcessBitcoinCashCryptocurrencyBlockHeader . $nodeProcessBitcoinCashCryptocurrencyBlockTransactions . '\' 2>&1', $nodeProcessBitcoinCashCryptocurrencyBlockSubmissionResponse);
					$nodeProcessBitcoinCashCryptocurrencyBlockSubmissionResponse = implode('', $nodeProcessBitcoinCashCryptocurrencyBlockSubmissionResponse);
					$nodeProcessBitcoinCashCryptocurrencyBlockSubmissionResponse = json_decode($nodeProcessBitcoinCashCryptocurrencyBlockSubmissionResponse, true);

					if (empty($nodeProcessBitcoinCashCryptocurrencyBlockSubmissionResponse) === true) {
						$response['message'] = 'Node process Bitcoin Cash cryptocurrency block submission processed successfully.';
					} else {
						// todo: return success message response if duplicate error is received
						$response['message'] = 'Error processing node process Bitcoin Cash cryptocurrency block submission, please try again.';
						unlink('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_block_header.dat');
						unlink('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_block_transactions.dat');
						return $response;
					}
				}
			} */

			sleep(1);
		}
	}

	if (($parameters['action'] === 'process_node_process_bitcoin_cash_cryptocurrency_blockchain_block') === true) {
		$response = _processNodeProcessBitcoinCashCryptocurrencyBlockchainBlock($parameters, $response);
	}
?>
