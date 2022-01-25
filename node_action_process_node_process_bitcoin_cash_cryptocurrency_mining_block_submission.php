<?php
	function _processNodeProcessBitcoinCashCryptocurrencyMiningBlockSubmission($parameters, $response) {
		exec('ps -h -o pid -o cmd $(pgrep php) | grep process_node_process_bitcoin_cash_cryptocurrency_mining_block_submission | grep -v grep | awk \'{print $1}\'', $nodeProcessBitcoinCashCryptocurrencyMiningBlockSubmissionProcessIds);

		if (empty($nodeProcessBitcoinCashCryptocurrencyMiningBlockSubmissionProcessIds[1]) === false) {
			return $response;
		}

		while (true) {
			if (file_exists('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_mining_block_header.dat') === true) {
				$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader = file_get_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_mining_block_header.dat');
				$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactions = file_get_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_mining_block_transactions.dat');

				if (
					(empty($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader) === false) &&
					(empty($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactions) === false)
				) {
					exec('sudo bitcoin-cli -conf=/usr/local/nodecompute/bitcoin_cash/bitcoin.conf submitblock \'' . $nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader . $nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactions . '\' 2>&1', $nodeProcessBitcoinCashCryptocurrencyMiningBlockSubmissionResponse);
					$nodeProcessBitcoinCashCryptocurrencyMiningBlockSubmissionResponse = implode('', $nodeProcessBitcoinCashCryptocurrencyMiningBlockSubmissionResponse);
					$nodeProcessBitcoinCashCryptocurrencyMiningBlockSubmissionResponse = json_decode($nodeProcessBitcoinCashCryptocurrencyMiningBlockSubmissionResponse, true);

					if (empty($nodeProcessBitcoinCashCryptocurrencyMiningBlockSubmissionResponse) === true) {
						$response['message'] = 'Node process Bitcoin Cash cryptocurrency mining block submission processed successfully.';
					} else {
						$response['message'] = 'Error processing node process Bitcoin Cash cryptocurrency mining block submission, please try again.';
						// todo: log specific error code
					}

					unlink('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_mining_block_header.dat');
					unlink('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_mining_block_transactions.dat');
					return $response;
				}
			}

			sleep(1);
		}
	}

	if (($parameters['action'] === 'node_action_process_node_process_bitcoin_cash_cryptocurrency_mining_block_submission') === true) {
		$response = _processNodeProcessBitcoinCashCryptocurrencyMiningBlockSubmission($parameters, $response);
	}
?>
