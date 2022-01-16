<?php
	function _processNodeProcessDogecoinCryptocurrencyMiningBlockSubmission($parameters, $response) {
		exec('ps -h -o pid -o cmd $(pgrep php) | grep process_node_process_dogecoin_cryptocurrency_mining_block_submission | grep -v grep | awk \'{print $1}\'', $nodeProcessDogecoinCryptocurrencyMiningBlockSubmissionProcessIds);

		if (empty($nodeProcessDogecoinCryptocurrencyMiningBlockSubmissionProcessIds[1]) === false) {
			return $response;
		}

		while (true) {
			if (file_exists('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block.json') === true) {
				$nodeProcessDogecoinCryptocurrencyMiningBlock = file_get_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block.json');
				$nodeProcessDogecoinCryptocurrencyMiningBlock = json_decode($nodeProcessDogecoinCryptocurrencyMiningBlock, true);

				if (empty($nodeProcessDogecoinCryptocurrencyMiningBlock) === false) {
					$nodeProcessDogecoinCryptocurrencyProcessParameters = file_get_contents('/usr/local/ghostcompute/dogecoin/dogecoin.conf');
					$nodeProcessDogecoinCryptocurrencyProcessParameters = explode("\n", $nodeProcessDogecoinCryptocurrencyProcessParameters);
					$nodeProcessDogecoinCryptocurrencyProcessParameters = '-' . implode(' -', $nodeProcessDogecoinCryptocurrencyProcessParameters);
					exec('sudo dogecoin-cli ' . $nodeProcessDogecoinCryptocurrencyProcessParameters . ' submitblock \'' . $dogecoinCryptocurrencyBlockTemplateParameters . '\' 2>&1', $nodeProcessDogecoinCryptocurrencyMiningBlockSubmissionResponse); // todo: add secondary parameters if required by block template 
					$nodeProcessDogecoinCryptocurrencyMiningBlockSubmissionResponse = implode('', $nodeProcessDogecoinCryptocurrencyMiningBlockSubmissionResponse);
					$nodeProcessDogecoinCryptocurrencyMiningBlockSubmissionResponse = json_decode($dogecoinCryptocurrencyBlockTemplate, true);

					if (empty($nodeProcessDogecoinCryptocurrencyMiningBlockSubmissionResponse) === true) {
						$response['message'] = 'Node process Dogecoin cryptocurrency mining block submission processed successfully.';
					} else {
						$response['message'] = 'Error processing node process Dogecoin cryptocurrency mining block submission, please try again.';
						// todo: log specific error code
					}

					unlink('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block.json');
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
