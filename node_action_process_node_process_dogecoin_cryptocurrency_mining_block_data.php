<?php
	function _createLittleEndian($hexidecimalString) {
		$binaryString = hex2bin($hexidecimalString);
		$binaryString = strrev($binaryString);
		return bin2hex($binaryString);
	}

	function _processNodeProcessDogecoinCryptocurrencyMiningBlockData($parameters, $response) {
		$nodeProcessDogecoinCryptocurrencyProcessParameters = file_get_contents('/usr/local/ghostcompute/dogecoin/dogecoin.conf');
		$nodeProcessDogecoinCryptocurrencyProcessParameters = explode("\n", $nodeProcessDogecoinCryptocurrencyProcessParameters);
		$nodeProcessDogecoinCryptocurrencyProcessParameters = '-' . implode(' -', $nodeProcessDogecoinCryptocurrencyProcessParameters);
		exec('sudo dogecoin-cli ' . $nodeProcessDogecoinCryptocurrencyProcessParameters . ' getblocktemplate 2>&1', $nodeProcessDogecoinCryptocurrencyBlockTemplate);
		$nodeProcessDogecoinCryptocurrencyBlockTemplate = implode('', $nodeProcessDogecoinCryptocurrencyBlockTemplate);
		$nodeProcessDogecoinCryptocurrencyBlockTemplate = json_decode($nodeProcessDogecoinCryptocurrencyBlockTemplate, true);

		if (isset($nodeProcessDogecoinCryptocurrencyBlockTemplate['version']) === false) {
			$response['message'] = 'Error processing node process Dogecoin cryptocurrency mining block template, please try again.';
			return $response;
		}

		if (file_put_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_next_block_height.txt', $nodeProcessDogecoinCryptocurrencyBlockTemplate['height']) === false) {
			$response['message'] = 'Error adding node process Dogecoin cryptocurrency mining block template next block height, please try again.';
			return $response;
		}

		$nodeProcessDogecoinCryptocurrencyBlockHeader = array(
			'coinbase_output_value' => dechex($nodeProcessDogecoinCryptocurrencyBlockTemplate['coinbasevalue']),
			'next_block_height' => dechex($nodeProcessDogecoinCryptocurrencyBlockTemplate['height']),
			'nonce_range' => str_split($nodeProcessDogecoinCryptocurrencyBlockTemplate['noncerange'], 8),
			'previous_block_hash' => _createLittleEndian($nodeProcessDogecoinCryptocurrencyBlockTemplate['previousblockhash']),
			'target_hash' => $nodeProcessDogecoinCryptocurrencyBlockTemplate['target'],
			'target_hash_bits' => _createLittleEndian($nodeProcessDogecoinCryptocurrencyBlockTemplate['bits']),
			'timestamp' => ($nodeProcessDogecoinCryptocurrencyBlockTemplate['curtime'] - 19),
			'version' => str_pad($nodeProcessDogecoinCryptocurrencyBlockTemplate['version'], 8, '0', STR_PAD_LEFT)
		);
		$nodeProcessDogecoinCryptocurrencyBlockHeader['next_block_height_binary_string'] = hex2bin($nodeProcessDogecoinCryptocurrencyBlockHeader['next_block_height']);
		$nodeProcessDogecoinCryptocurrencyBlockHeader['version'] = _createLittleEndian($nodeProcessDogecoinCryptocurrencyBlockHeader['version']);
		$nodeProcessDogecoinCryptocurrencyTransactions = array(
			$nodeProcessDogecoinCryptocurrencyBlockHeader['version'],
			'01',
			'0000000000000000000000000000000000000000000000000000000000000000',
			'ffffffff',
			false,
			'0' . strlen($nodeProcessDogecoinCryptocurrencyBlockHeader['next_block_height_binary_string']) . _createLittleEndian($nodeProcessDogecoinCryptocurrencyBlockHeader['next_block_height']) . '67686f7374636f6d70757465',
			'ffffffff',
			'01',
			_createLittleEndian($nodeProcessDogecoinCryptocurrencyBlockHeader['coinbase_output_value']),
			false,
			'mining_reward_public_key_goes_here', // todo: create pubKey script and verify string format as-is
			'00000000'
		);
		// todo: create API functions for simplifying wallet pubKey creation

		foreach (array(4, 9) as $nodeProcessDogecoinCryptocurrencyTransactionParameterLengthKey) {
			$nodeProcessDogecoinCryptocurrencyTransactions[$nodeProcessDogecoinCryptocurrencyTransactionParameterLengthKey] = decbin($nodeProcessDogecoinCryptocurrencyTransactions[($nodeProcessDogecoinCryptocurrencyTransactionParameterLengthKey + 1)]);
			$nodeProcessDogecoinCryptocurrencyTransactions[$nodeProcessDogecoinCryptocurrencyTransactionParameterLengthKey] = strlen($nodeProcessDogecoinCryptocurrencyTransactions[$nodeProcessDogecoinCryptocurrencyTransactionParameterLengthKey]);
			$nodeProcessDogecoinCryptocurrencyTransactions[$nodeProcessDogecoinCryptocurrencyTransactionParameterLengthKey] = dechex($nodeProcessDogecoinCryptocurrencyTransactions[$nodeProcessDogecoinCryptocurrencyTransactionParameterLengthKey]);
			$nodeProcessDogecoinCryptocurrencyTransactions[$nodeProcessDogecoinCryptocurrencyTransactionParameterLengthKey] = str_pad($nodeProcessDogecoinCryptocurrencyTransactions[$nodeProcessDogecoinCryptocurrencyTransactionParameterLengthKey], 2, '0', STR_PAD_LEFT);
		}

		$nodeProcessDogecoinCryptocurrencyTransactionCoinbase = implode('', $nodeProcessDogecoinCryptocurrencyTransactions);
		$nodeProcessDogecoinCryptocurrencyTransactionIds = array(
			$nodeProcessDogecoinCryptocurrencyTransactionCoinbase
		);
		$nodeProcessDogecoinCryptocurrencyTransactionIds[0] = hash('sha256', $nodeProcessDogecoinCryptocurrencyTransactionIds[0], true);
		$nodeProcessDogecoinCryptocurrencyTransactionIds[0] = hash('sha256', $nodeProcessDogecoinCryptocurrencyTransactionIds[0]);
		$nodeProcessDogecoinCryptocurrencyTransactionIds[0] = _createLittleEndian($nodeProcessDogecoinCryptocurrencyTransactionIds[0]);

		foreach ($nodeProcessDogecoinCryptocurrencyBlockTemplate['transactions'] as $nodeProcessDogecoinCryptocurrencyBlockTemplateTransactionIndex => $nodeProcessDogecoinCryptocurrencyBlockTemplateTransaction) {
			$nodeProcessDogecoinCryptocurrencyTransactionId = hex2bin($nodeProcessDogecoinCryptocurrencyBlockTemplateTransaction['txid']);

			if ($nodeProcessDogecoinCryptocurrencyTransactionId === false) {
				$response['message'] = 'Error listing node process Dogecoin cryptocurrency mining block template transactions, please try again.';
				return $response;
			}

			$nodeProcessDogecoinCryptocurrencyTransactionIds[] = $nodeProcessDogecoinCryptocurrencyTransactionId;
		}

		if (empty($nodeProcessDogecoinCryptocurrencyTransactionIds[1]) === true) {
			$nodeProcessDogecoinCryptocurrencyBlockHeader['merkle_root_hash'] = $nodeProcessDogecoinCryptocurrencyTransactionIds[0];
		} elseif (((($nodeProcessDogecoinCryptocurrencyBlockTemplateTransactionIndex + 1) % 2) === 0) === true) {
			$nodeProcessDogecoinCryptocurrencyTransactionIds[($nodeProcessDogecoinCryptocurrencyBlockTemplateTransactionIndex + 1)] = $nodeProcessDogecoinCryptocurrencyTransactionIds[$nodeProcessDogecoinCryptocurrencyBlockTemplateTransactionIndex];
		}

		while (empty($nodeProcessDogecoinCryptocurrencyBlockHeader['merkle_root_hash']) === true) {
			end($nodeProcessDogecoinCryptocurrencyTransactionIds);
			$nodeProcessDogecoinCryptocurrencyTransactionIndex = (key($nodeProcessDogecoinCryptocurrencyTransactionIds) - 1);
			$nodeProcessDogecoinCryptocurrencyTransactionIndexes = range(0, $nodeProcessDogecoinCryptocurrencyTransactionIndex, 2);
			$nodeProcessDogecoinCryptocurrencyTransactionMerkleRootHashIndex = 0;

			foreach ($nodeProcessDogecoinCryptocurrencyTransactionIndexes as $nodeProcessDogecoinCryptocurrencyTransactionIndex) {
				$nodeProcessDogecoinCryptocurrencyTransactionIds[$nodeProcessDogecoinCryptocurrencyTransactionIndex] .= $nodeProcessDogecoinCryptocurrencyTransactionIds[($nodeProcessDogecoinCryptocurrencyTransactionIndex + 1)];
				$nodeProcessDogecoinCryptocurrencyTransactionIds[$nodeProcessDogecoinCryptocurrencyTransactionIndex] = hash('sha256', $nodeProcessDogecoinCryptocurrencyTransactionIds[$nodeProcessDogecoinCryptocurrencyTransactionIndex], true);
				$nodeProcessDogecoinCryptocurrencyTransactionIds[$nodeProcessDogecoinCryptocurrencyTransactionMerkleRootHashIndex] = hash('sha256', $nodeProcessDogecoinCryptocurrencyTransactionIds[$nodeProcessDogecoinCryptocurrencyTransactionIndex], true);

				if (($nodeProcessDogecoinCryptocurrencyTransactionMerkleRootHashIndex === 0) === false) {
					unset($nodeProcessDogecoinCryptocurrencyTransactionIds[$nodeProcessDogecoinCryptocurrencyTransactionIndex]);
				}

				unset($nodeProcessDogecoinCryptocurrencyTransactionIds[($nodeProcessDogecoinCryptocurrencyTransactionIndex + 1)]);
				$nodeProcessDogecoinCryptocurrencyTransactionMerkleRootHashIndex++;
			}

			if (($nodeProcessDogecoinCryptocurrencyTransactionIndex === 0) === true) {
				$nodeProcessDogecoinCryptocurrencyBlockHeader['merkle_root_hash'] = strrev($nodeProcessDogecoinCryptocurrencyTransactionIds[$nodeProcessDogecoinCryptocurrencyTransactionIndex]);
				$nodeProcessDogecoinCryptocurrencyBlockHeader['merkle_root_hash'] = bin2hex($nodeProcessDogecoinCryptocurrencyBlockHeader['merkle_root_hash']);
			}
		}

		$nodeProcessDogecoinCryptocurrencyBlockHeaderTimestamp = time();
		$nodeProcessDogecoinCryptocurrencyBlockHeaderTimestampIncrements = range(0, 40);

		$nodeProcessDogecoinCryptocurrencyMiningBlockData = array(
			$nodeProcessDogecoinCryptocurrencyBlockHeader['nonce_range'],
			$nodeProcessDogecoinCryptocurrencyBlockHeader['target_hash'],
			$nodeProcessDogecoinCryptocurrencyBlockHeader['target_hash_bits'],
			$nodeProcessDogecoinCryptocurrencyBlockHeader['version'] . $nodeProcessDogecoinCryptocurrencyBlockHeader['previous_block_hash'] . $nodeProcessDogecoinCryptocurrencyBlockHeader['merkle_root_hash']
		);

		foreach ($nodeProcessDogecoinCryptocurrencyBlockHeaderTimestampIncrements as $nodeProcessDogecoinCryptocurrencyBlockHeaderTimestampIncrement) {
			$nodeProcessDogecoinCryptocurrencyBlockHeaderTimestamp = dechex($nodeProcessDogecoinCryptocurrencyBlockHeader['timestamp'] + $nodeProcessDogecoinCryptocurrencyBlockHeaderTimestampIncrement);
			$nodeProcessDogecoinCryptocurrencyMiningBlockData[4][] = _createLittleEndian($nodeProcessDogecoinCryptocurrencyBlockHeaderTimestamp);
		}

		$nodeProcessDogecoinCryptocurrencyMiningBlockData = json_encode($nodeProcessDogecoinCryptocurrencyMiningBlockData);

		if (
			($nodeProcessDogecoinCryptocurrencyMiningBlockData === false) ||
			(file_put_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.json', $nodeProcessDogecoinCryptocurrencyMiningBlockData) === false)
		) {
			$response['message'] = 'Error adding node process Dogecoin cryptocurrency mining block header, please try again.';
			return $response;
		}

		if (file_exists('/etc/crontab') === false) {
			$response['message'] = 'Error listing crontab commands, please try again.';
			return $response;
		}

		$crontabCommands = file_get_contents('/etc/crontab');

		if (empty($crontabCommands) === true) {
			$response['message'] = 'Error listing crontab commands, please try again.';
			return $response;
		}

		$crontabCommands = explode("\n", $crontabCommands);
		$crontabCommandIndex = array_search('# ghostcompute_dogecoin_cryptocurrency_mining', $crontabCommands);

		if (is_int($crontabCommandIndex) === true) {
			while (is_int($crontabCommandIndex) === true) {
				unset($crontabCommands[$crontabCommandIndex]);
				$crontabCommandIndex++;

				if (strpos($crontabCommands[$crontabCommandIndex], ' ghostcompute_dogecoin_cryptocurrency_mining') === false) {
					$crontabCommandIndex = false;
				}
			}
		}

		$crontabCommands += array(
			'# ghostcompute_node_process_dogecoin_cryptocurrency_mining',
			'* * * * * root sudo ' . $parameters['binary_files']['php'] . ' /usr/local/ghostcompute/node_endpoint.php process_node_process_dogecoin_cryptocurrency_mining_processes 5 ghostcompute_node_process_dogecoin_cryptocurrency_mining'
		);
		$crontabCommandIndexes = range(0, 2); // todo: make this based on user input + increment based on free resources
		$crontabCommandDelayIndexes = range(0, 55, 5);

		foreach ($crontabCommandIndexes as $crontabCommandIndex) {
			foreach ($crontabCommandDelayIndexes as $crontabCommandDelayIndex) {
				$crontabCommands[] = '* * * * * root sleep ' . $crontabCommandDelayIndex . ' && sudo ' . $parameters['binary_files']['php'] . ' /usr/local/ghostcompute/node_action_process_node_process_dogecoin_cryptocurrency_mining_proof_of_work.php _' . $nodeProcessDogecoinCryptocurrencyBlockTemplate['height'] . ' ' . $crontabCommandIndex . '_' . $crontabCommandDelayIndex . ' ghostcompute_node_process_dogecoin_cryptocurrency_mining';
			}
		}

		$crontabCommands = implode("\n", $crontabCommands);

		if (file_put_contents('/etc/crontab', $crontabCommands) === false) {
			echo 'Error adding crontab commands, please try again.';
			return $response;
		}

		shell_exec('sudo ' . $parameters['binary_files']['crontab'] . ' /etc/crontab');
		$response['message'] = 'Node process Dogecoin cryptocurrency mining block data processed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'process_node_process_dogecoin_cryptocurrency_mining_block_data') === true) {
		$response = _processNodeProcessDogecoinCryptocurrencyMiningBlockData($parameters, $response);
		_output($response);
	}

	// todo: save transaction data for block serialization
	// todo: save block template to system API for GhostCompute nodes without dogecoin-cli installed
?>
