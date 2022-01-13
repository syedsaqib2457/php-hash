<?php
	/*
		construct block based on Bitcoin documentation before testing
		create coinbase transaction with user input
		appent coinbase transaction to list of transactions
		list previous block header, version, diff, etc
		create block header hash
		save block header hash to file for mining with multiple processes
	*/

	function _createLittleEndian($hexidecimalString) {
		$binaryString = hex2bin($hexidecimalString);
		$binaryString = strrev($binaryString);
		return bin2hex($binaryStringReversed);
	}

	$dogecoinCryptocurrencyBlockTemplateParameters = array(
		'capabilities' => array(
			'coinbasetxn',
			'coinbasevalue',
			'proposal'
		),
		'mode' => 'proposal',
		'rules' => array(
			'segwit'
		)
	);
	$dogecoinCryptocurrencyBlockTemplateParameters = json_encode($dogecoinCryptocurrencyBlockTemplateParameters);

	if ($dogecoinCryptocurrencyBlockTemplateParameters === false) {
		$response['message'] = 'Error listing node process Dogecoin cryptocurrency block template parameters, please try again.';
		return $response;
	}

	exec('sudo dogecoin-cli -rpcuser=ghostcompute -rpcpassword=ghostcompute getblocktemplate \'' . $dogecoinCryptocurrencyBlockTemplateParameters . '\' 2>&1', $dogecoinCryptocurrencyBlockTemplate);
	$dogecoinCryptocurrencyBlockTemplate = json_decode($dogecoinCryptocurrencyBlockTemplate, true);

	if (isset($dogecoinCryptocurrencyBlockTemplate['version']) === false) {
		$response['message'] = 'Error processing node process Dogecoin cryptocurrency block header, please try again.';
		return $response;
	}

	// todo: convert values to little endian if getblocktemplate doesn't already
	$dogecoinCryptocurrencyBlockHeader = array(
		'previous_block_hash' => $dogecoinCryptocurrencyBlockTemplate['previousblockhash'],
		'target_hash_bits' => $dogecoinCryptocurrencyBlockTemplate['bits'],
		'timestamp' => dechex($dogecoinCryptocurrencyBlockTemplate['curtime']),
		'version' => str_pad($dogecoinCryptocurrencyBlockTemplate['version'], 8, '0', STR_PAD_LEFT)
	);
	$dogecoinCryptocurrencyBlockHeader['timestamp'] = _createLittleEndian($dogecoinCryptocurrencyBlockHeader['timestamp']);
	$dogecoinCryptocurrencyTransactions = array(
		// todo: coinbase tx
	);

	foreach ($dogecoinCryptocurrencyBlockTemplate['transactions'] as $dogecoinCryptocurrencyBlockTemplateTransactionIndex => $dogecoinCryptocurrencyBlockTemplateTransaction) {
		$dogecoinCryptocurrencyTransactionId = hex2bin($dogecoinCryptocurrencyBlockTemplateTransaction['txid']);

		if ($dogecoinCryptocurrencyTransactionId === false) {
			$response['message'] = 'Error listing node process Dogecoin cryptocurrency block template transactions, please try again.';
			return $response;
		}

		$dogecoinCryptocurrencyTransactionIds[] = $dogecoinCryptocurrencyTransactionId;
	}

	if (empty($dogecoinCryptocurrencyTransactionIds[1]) === true) {
		$dogecoinCryptocurrencyBlockHeader['merkle_root_hash'] = hash('sha256', $dogecoinCryptocurrencyTransactionIds[0], true);
		$dogecoinCryptocurrencyBlockHeader['merkle_root_hash'] = hash('sha256', $dogecoinCryptocurrencyBlockHeader['merkle_root_hash']);
		$dogecoinCryptocurrencyBlockHeader['merkle_root_hash'] = _createLittleEndian($dogecoinCryptocurrencyBlockHeader['merkle_root_hash']);
	} elseif (((($dogecoinCryptocurrencyBlockTemplateTransactionIndex + 1) % 2) === 0) === true) {
		$dogecoinCryptocurrencyTransactionIds[($dogecoinCryptocurrencyBlockTemplateTransactionIndex + 1)] = $dogecoinCryptocurrencyTransactionIds[$dogecoinCryptocurrencyBlockTemplateTransactionIndex];
	}

	while (empty($dogecoinCryptocurrencyBlockHeader['merkle_root_hash']) === true) {
		end($dogecoinCryptocurrencyTransactionIds);
		$dogecoinCryptocurrencyTransactionIndex = (key($dogecoinCryptocurrencyTransactionIds) - 1);
		$dogecoinCryptocurrencyTransactionIndexes = range(0, $dogecoinCryptocurrencyTransactionIndex, 2);
		$dogecoinCryptocurrencyTransactionMerkleRootHashIndex = 0;

		foreach ($dogecoinCryptocurrencyTransactionIndexes as $dogecoinCryptocurrencyTransactionIndex) {
			$dogecoinCryptocurrencyTransactionIds[$dogecoinCryptocurrencyTransactionIndex] += $dogecoinCryptocurrencyTransactionIds[($dogecoinCryptocurrencyTransactionIndex + 1)];
			$dogecoinCryptocurrencyTransactionIds[$dogecoinCryptocurrencyTransactionIndex] = hash('sha256', $dogecoinCryptocurrencyTransactionIds[$dogecoinCryptocurrencyTransactionIndex], true);
			$dogecoinCryptocurrencyTransactionIds[$dogecoinCryptocurrencyTransactionMerkleRootHashIndex] = hash('sha256', $dogecoinCryptocurrencyTransactionIds[$dogecoinCryptocurrencyTransactionIndex], true);

			if (($dogecoinCryptocurrencyTransactionMerkleRootHashIndex === 0) === false) {
				unset($dogecoinCryptocurrencyTransactionIds[$dogecoinCryptocurrencyTransactionIndex]);
			}

			unset($dogecoinCryptocurrencyTransactionIds[($dogecoinCryptocurrencyTransactionIndex + 1)]);
			$dogecoinCryptocurrencyTransactionMerkleRootHashIndex++;
		}

		if (($dogecoinCryptocurrencyTransactionIndex === 0) === true) {
			$dogecoinCryptocurrencyBlockHeader['merkle_root_hash'] = strrev($dogecoinCryptocurrencyTransactionIds[$dogecoinCryptocurrencyTransactionIndex]);
			$dogecoinCryptocurrencyBlockHeader['merkle_root_hash'] = bin2hex($dogecoinCryptocurrencyBlockHeader['merkle_root_hash']);
		}
	}

	$dogecoinCryptocurrencyBlockHeaderString = $dogecoinCryptocurrencyBlockHeader['version'] . $dogecoinCryptocurrencyBlockHeader['previous_block_hash'] . $dogecoinCryptocurrencyBlockHeader['merkle_root_hash'] . $dogecoinCryptocurrencyBlockHeader['timestamp'] . $dogecoinCryptocurrencyBlockHeader['bits'];
	// todo
?>
