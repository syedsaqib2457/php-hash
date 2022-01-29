<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _processNodeProcessBitcoinCashCryptocurrencyBlockSubmission($parameters, $response) {
		exec('ps -h -o pid -o cmd $(pgrep php) | grep process_node_process_bitcoin_cash_cryptocurrency_block_submission | grep -v grep | awk \'{print $1}\'', $nodeProcessBitcoinCashCryptocurrencyBlockSubmissionProcessIds);

		if (empty($nodeProcessBitcoinCashCryptocurrencyBlockSubmissionProcessIds[1]) === false) {
			return $response;
		}

		while (true) {
			if (file_exists('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_block_header.dat') === true) {
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
			}

			sleep(1);
		}
	}

	if (($parameters['action'] === 'node_action_process_node_process_bitcoin_cash_cryptocurrency_block_submission') === true) {
		$response = _processNodeProcessBitcoinCashCryptocurrencyBlockSubmission($parameters, $response);
	}
?>
