<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _processNodeProcessBitcoinCashCryptocurrencyBlockchainBlockData($parameters, $response) {
		while (true) {
			if (file_exists('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_blockchain_block_data.json') === true) {
				// todo: save to system database for block processing
			}
		}
	}

	if (($parameters['action'] === 'process_node_process_bitcoin_cash_cryptocurrency_blockchain_block_data') === true) {
		$response = _processNodeProcessBitcoinCashCryptocurrencyBlockchainBlockData($parameters, $response);
	}
?>
