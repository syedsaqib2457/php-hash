<?php
	function _processNodeProcessDogecoinCryptocurrencyMiningBlockSubmission($parameters, $response) {
		exec('ps -h -o pid -o cmd $(pgrep php) | grep process_node_process_dogecoin_cryptocurrency_mining_block_submission | grep -v grep | awk \'{print $1}\'', $nodeProcessDogecoinCryptocurrencyMiningBlockSubmissionProcessIds);

		if (empty($nodeProcessDogecoinCryptocurrencyMiningBlockSubmissionProcessIds[1]) === false) {
			return $response;
		}

		while (true) {
			if (file_exists('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.dat') === true) {
				$nodeProcessDogecoinCryptocurrencyMiningBlockHeader = file_get_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.dat');
				$nodeProcessDogecoinCryptocurrencyMiningBlockTransactions = file_get_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_transactions.dat');

				if (
					(empty($nodeProcessDogecoinCryptocurrencyMiningBlockHeader) === false) &&
					(empty($nodeProcessDogecoinCryptocurrencyMiningBlockTransactions) === false)
				) {
					$nodeProcessDogecoinCryptocurrencyMiningProcessParameters = file_get_contents('/usr/local/ghostcompute/dogecoin/dogecoin.conf');
					$nodeProcessDogecoinCryptocurrencyMiningProcessParameters = explode("\n", $nodeProcessDogecoinCryptocurrencyMiningProcessParameters);
					$nodeProcessDogecoinCryptocurrencyMiningProcessParameters = '-' . implode(' -', $nodeProcessDogecoinCryptocurrencyMiningProcessParameters);
					exec('sudo dogecoin-cli ' . $nodeProcessDogecoinCryptocurrencyMiningProcessParameters . ' submitblock \'' . $nodeProcessDogecoinCryptocurrencyMiningBlockHeader . $nodeProcessDogecoinCryptocurrencyMiningBlockTransactions . '\' 2>&1', $nodeProcessDogecoinCryptocurrencyMiningBlockSubmissionResponse); 
					$nodeProcessDogecoinCryptocurrencyMiningBlockSubmissionResponse = implode('', $nodeProcessDogecoinCryptocurrencyMiningBlockSubmissionResponse);
					$nodeProcessDogecoinCryptocurrencyMiningBlockSubmissionResponse = json_decode($nodeProcessDogecoinCryptocurrencyMiningBlockSubmissionResponse, true);

					if (empty($nodeProcessDogecoinCryptocurrencyMiningBlockSubmissionResponse) === true) {
						$response['message'] = 'Node process Dogecoin cryptocurrency mining block submission processed successfully.';
					} else {
						$response['message'] = 'Error processing node process Dogecoin cryptocurrency mining block submission, please try again.';
						// todo: log specific error code
					}

					unlink('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.dat');
					unlink('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_transactions.dat');
					return $response;
				}
			}

			sleep(1);
		}
	}

	if (($parameters['action'] === 'node_action_process_node_process_dogecoin_cryptocurrency_mining_block_submission') === true) {
		$response = _processNodeProcessDogecoinCryptocurrencyMiningBlockSubmission($parameters, $response);
		_output($response);
	}
?>
