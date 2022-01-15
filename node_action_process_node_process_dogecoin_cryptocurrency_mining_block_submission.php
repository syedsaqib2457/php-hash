<?php
	function _processNodeProcessDogecoinCryptocurrencyMiningBlockSubmission($parameters, $response) {
		while (true) {
			if (file_exists('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block.json') === true) {
				// todo
				unlink('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block.json');
			}

			sleep(1);
		}
	}

	if (($parameters['action'] === 'node_action_process_node_process_dogecoin_cryptocurrency_mining_block_submission') === true) {
		$response = _processNodeProcessDogecoinCryptocurrencyMiningBlockSubmission($parameters, $response);
		_output($response);
	}
?>
