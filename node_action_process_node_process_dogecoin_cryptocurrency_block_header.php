<?php
	/*
		construct block based on Bitcoin documentation before testing
		list <1 mb of transactions in mempool
		create coinbase transaction with user input
		appent coinbase transaction to list of transactions
		Merkle root of list of transactions
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

	foreach ($dogecoinCryptocurrencyBlockTemplate['transactions'] as $dogecoinCryptocurrencyBlockTemplateTransactionIndex => $dogecoinCryptocurrencyBlockTemplateTransaction) {
		$dogecoinCryptocurrencyTransactionId = hex2bin($dogecoinCryptocurrencyBlockTemplateTransaction['txid']);

		if ($dogecoinCryptocurrencyTransactionId === false) {
			$response['message'] = 'Error listing Dogecoin cryptocurrency block template transactions, please try again.';
			return $response;
		}

		$dogecoinCryptocurrencyTransactionIds[] = $dogecoinCryptocurrencyTransactionId;
	}

	if (empty($dogecoinCryptocurrencyTransactionIds[1]) === true) {
		$dogecoinCryptocurrencyBlockHeader['merkle_root_hash'] = hash('sha256', $dogecoinCryptocurrencyTransactionIds[0], true);
		$dogecoinCryptocurrencyBlockHeader['merkle_root_hash'] = hash('sha256', $dogecoinCryptocurrencyBlockHeader['merkle_root_hash'], true);
		$dogecoinCryptocurrencyBlockHeader['merkle_root_hash'] = strrev($dogecoinCryptocurrencyBlockHeader['merkle_root_hash']);
		$dogecoinCryptocurrencyBlockHeader['merkle_root_hash'] = bin2hex($dogecoinCryptocurrencyBlockHeader['merkle_root_hash']);
	} elseif (((($dogecoinCryptocurrencyBlockTemplateTransactionIndex + 1) % 2) === 0) === true) {
		$dogecoinCryptocurrencyTransactionIds[($dogecoinCryptocurrencyBlockTemplateTransactionIndex + 1)] = $dogecoinCryptocurrencyTransactionIds[$dogecoinCryptocurrencyBlockTemplateTransactionIndex];
	}

	while (empty($dogecoinCryptocurrencyBlockHeader['merkle_root_hash']) === true) {
		end($dogecoinCryptocurrencyTransactionIds);
		$dogecoinCryptocurrencyTransactionIndex = (key($dogecoinCryptocurrencyTransactionIds) - 1);
		$dogecoinCryptocurrencyTransactionIndexes = range(0, $dogecoinCryptocurrencyTransactionIndex, 2);

		foreach ($dogecoinCryptocurrencyTransactionIndexes as $dogecoinCryptocurrencyTransactionIndex) {
			$dogecoinCryptocurrencyTransactionIds[$dogecoinCryptocurrencyTransactionIndex] += $dogecoinCryptocurrencyTransactionIds[($dogecoinCryptocurrencyTransactionIndex + 1)];
			$dogecoinCryptocurrencyTransactionIds[$dogecoinCryptocurrencyTransactionIndex] = hash('sha256', $dogecoinCryptocurrencyTransactionIds[$dogecoinCryptocurrencyTransactionIndex], true);
			$dogecoinCryptocurrencyTransactionIds[$dogecoinCryptocurrencyTransactionIndex] = hash('sha256', $dogecoinCryptocurrencyTransactionIds[$dogecoinCryptocurrencyTransactionIndex], true);
			unset($dogecoinCryptocurrencyTransactionIds[($dogecoinCryptocurrencyTransactionIndex + 1)]);
		}

		// todo
	}

	// todo
?>
