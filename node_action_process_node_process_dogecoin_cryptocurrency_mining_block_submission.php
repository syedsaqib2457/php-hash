<?php
	function _processNodeProcessDogecoinCryptocurrencyMiningBlockSubmission($parameters, $response) {
		// todo: scan block file repeatedly for fast submission with RPC address
	}

	if (($parameters['action'] === 'node_action_process_node_process_dogecoin_cryptocurrency_mining_block_submission') === true) {
		$response = _processNodeProcessDogecoinCryptocurrencyMiningBlockSubmission($parameters, $response);
		_output($response);
	}
?>
