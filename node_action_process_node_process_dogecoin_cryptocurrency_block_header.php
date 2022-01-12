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

	// todo: convert values to little endian if getblocktemplate doesn't already
	$dogecoinCryptocurrencyBlockHeader = array(
		'previous_block_hash' => $dogecoinCryptocurrencyBlockTemplate['previousblockhash'],
		'target_hash_bits' => $dogecoinCryptocurrencyBlockTemplate['bits'],
		'timestamp' => $dogecoinCryptocurrencyBlockTemplate['curtime'],
		'version' => $dogecoinCryptocurrencyBlockTemplate['version']
	);
	$dogecoinCryptocurrencyTransactions = array();

	foreach ($dogecoinCryptocurrencyBlockTemplate['transactions'] as $dogecoinCryptocurrencyBlockTemplateTransaction) {
		$dogecoinCryptocurrencyTransaction = hex2bin($dogecoinCryptocurrencyBlockTemplateTransaction);

		if (($dogecoinCryptocurrencyTransaction === false) === false) {
			$dogecoinCryptocurrencyTransactions[] = $dogecoinCryptocurrencyTransaction;
		}
	}

	// todo
?>
