<?php
	/*
		construct block based on Bitcoin documentation before testing
		list <1 mb of transactions in mempool
		create coinbase transaction with user input
		appent coinbase transaction to list of transactions
		Merkel root of list of transactions
		list previous block header, version, diff, etc
		create block header hash
		save block header hash to file for mining with multiple processes
	*/
	exec('sudo dogecoin-cli -rpcuser=ghostcompute -rpcpassword=ghostcompute getblocktemplate 2>&1', $dogecoinCryptocurrencyBlockTemplate);
	$dogecoinCryptocurrencyBlockTemplate = json_decode($dogecoinCryptocurrencyBlockTemplate, true);

	if (isset($dogecoinCryptocurrencyBlockTemplate['version']) === false) {
		$response['message'] = 'Error listing Dogecoin cryptocurrency block template, please try again.';
		return $response;
	}

	$dogecoinCryptocurrencyBlockHeader = array(
		'previous_block' => $dogecoinCryptocurrencyBlockTemplate['previousblockhash'],
		'timestamp' => $dogecoinCryptocurrencyBlockTemplate['curtime'],
		'version' => $dogecoinCryptocurrencyBlockTemplate['version']
	);

	// todo
?>
