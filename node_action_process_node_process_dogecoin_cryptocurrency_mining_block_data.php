<?php
	function _createLittleEndian($hexidecimalString) {
		$binaryString = hex2bin($hexidecimalString);
		$binaryString = strrev($binaryString);
		return bin2hex($binaryStringReversed);
	}

	function _processNodeProcessDogecoinCryptocurrencyMiningBlockData($parameters, $response) {
		$dogecoinCryptocurrencyBlockTemplateParameters = array(
			'capabilities' => array(
				'coinbase/append',
				'coinbasetxn',
				'coinbasevalue',
				'proposal',
				'workid'
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

		$dogecoinCryptocurrencyBlockHeader = array(
			'coinbase_output_value' => dechex($dogecoinCryptocurrencyBlockTemplate['coinbasevalue']),
			'next_block_height' => $dogecoinCryptocurrencyBlockTemplate['height'],
			'next_block_height_binary_string' => dechex($dogecoinCryptocurrencyBlockTemplate['height']),
			'nonce_range' => str_split($dogecoinCryptocurrencyBlockTemplate['noncerange'], 8),
			'previous_block_hash' => _createLittleEndian($dogecoinCryptocurrencyBlockTemplate['previousblockhash']),
			'target_hash' => $dogecoinCryptocurrencyBlockTemplate['target'],
			'target_hash_bits' => _createLittleEndian($dogecoinCryptocurrencyBlockTemplate['bits']),
			'timestamp' => (dechex($dogecoinCryptocurrencyBlockTemplate['curtime']) - 19),
			'version' => str_pad($dogecoinCryptocurrencyBlockTemplate['version'], 8, '0', STR_PAD_LEFT)
		);
		$dogecoinCryptocurrencyBlockHeader['next_block_height_binary_string'] = hex2bin($dogecoinCryptocurrencyBlockHeader['next_block_height_binary_string']);
		$dogecoinCryptocurrencyBlockHeader['version'] = _createLittleEndian($dogecoinCryptocurrencyBlockHeader['version']);
		$dogecoinCryptocurrencyTransactions = array(
			$dogecoinCryptocurrencyBlockHeader['version'],
			'01',
			'0000000000000000000000000000000000000000000000000000000000000000',
			'ffffffff',
			false,
			'0' . strlen($dogecoinCryptocurrencyBlockHeader['next_block_height_binary_string']) . _createLittleEndian($dogecoinCryptocurrencyBlockHeader['next_block_height']) . '67686f7374636f6d70757465', // todo: append extranonce based on user-input defined number of block headers to create
			'ffffffff',
			'01',
			_createLittleEndian($dogecoinCryptocurrencyBlockHeader['coinbase_output_value']),
			false,
			'mining_reward_public_key_goes_here', // todo: create pubKey script and verify string format as-is
			'00000000'
		);
		// todo: create API functions for simplifying wallet pubKey creation

		foreach (array(4, 9) as $dogecoinCryptocurrencyTransactionParameterLengthKey) {
			$dogecoinCryptocurrencyTransactions[$dogecoinCryptocurrencyTransactionParameterLengthKey] = hex2bin($dogecoinCryptocurrencyTransactions[($dogecoinCryptocurrencyTransactionParameterLengthKey + 1)]);
			$dogecoinCryptocurrencyTransactions[$dogecoinCryptocurrencyTransactionParameterLengthKey] = strlen($dogecoinCryptocurrencyTransactions[$dogecoinCryptocurrencyTransactionParameterLengthKey]);
			$dogecoinCryptocurrencyTransactions[$dogecoinCryptocurrencyTransactionParameterLengthKey] = dechex($dogecoinCryptocurrencyTransactions[$dogecoinCryptocurrencyTransactionParameterLengthKey]);
			$dogecoinCryptocurrencyTransactions[$dogecoinCryptocurrencyTransactionParameterLengthKey] = str_pad($dogecoinCryptocurrencyTransactions[$dogecoinCryptocurrencyTransactionParameterLengthKey], 2, '0', STR_PAD_LEFT);
		}

		$dogecoinCryptocurrencyTransactions = array(
			implode('', $dogecoinCryptocurrencyTransactions)
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

		$dogecoinCryptocurrencyMiningBlockData = array(
			$dogecoinCryptocurrencyBlockHeader['nonce_range'],
			$dogecoinCryptocurrencyBlockHeader['target_hash'],
			$dogecoinCryptocurrencyBlockHeader['target_hash_bits'],
			$dogecoinCryptocurrencyBlockHeader['version'] . $dogecoinCryptocurrencyBlockHeader['previous_block_hash'] . $dogecoinCryptocurrencyBlockHeader['merkle_root_hash']
		);
		$dogecoinCryptocurrencyBlockHeaderTimestampIncrements = range(0, 40);

		foreach ($dogecoinCryptocurrencyBlockHeaderTimestampIncrements as $dogecoinCryptocurrencyBlockHeaderTimestampIncrement) {
			$dogecoinCryptocurrencyMiningBlockData[4][] = _createLittleEndian($dogecoinCryptocurrencyBlockHeader['timestamp'] + $dogecoinCryptocurrencyBlockHeaderTimestampIncrement);
		}

		$dogecoinCryptocurrencyMiningBlockData = json_encode($dogecoinCryptocurrencyMiningBlockData);

		if (
			($dogecoinCryptocurrencyMiningBlockData === false) ||
			(file_put_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_data.json', $dogecoinCryptocurrencyMiningBlockData) === false)
		) {
			$response['message'] = 'Error adding node process Dogecoin cryptocurrency mining block data, please try again.';
			return $response;
		}

		if (file_exists('/etc/crontab') === false) {
			$response['message'] = 'Error listing crontab commands, please try again.';
			exit;
		}

		$crontabCommands = file_get_contents('/etc/crontab');

		if (empty($crontabCommands) === true) {
			$response['message'] = 'Error listing crontab commands, please try again.';
			exit;
		}

		$crontabCommands = explode("\n", $crontabCommands);
		$crontabCommandIndex = array_search('# ghostcompute_dogecoin_cryptocurrency_mining_proof_of_work', $crontabCommands);

		if (is_int($crontabCommandIndex) === true) {
			while (is_int($crontabCommandIndex) === true) {
				unset($crontabCommands[$crontabCommandIndex]);
				$crontabCommandIndex++;

				if (strpos($crontabCommands[$crontabCommandIndex], ' ghostcompute_dogecoin_cryptocurrency_mining_proof_of_work') === false) {
					$crontabCommandIndex = false;
				}
			}
		}

		$crontabCommands += array(
			'# ghostcompute_dogecoin_cryptocurrency_mining_proof_of_work',
			'* * * * * root sudo ' . $parameters['binary_files']['php'] . ' /usr/local/ghostcompute/node_action_process_node_process_dogecoin_cryptocurrency_mining_processes.php [lifetime_of_process_in_seconds] ghostcompute_dogecoin_cryptocurrency_mining_proof_of_work'
		);
		$crontabCommandIndexes = range(0, 2); // todo: make this based on user input + increment based on free resources
		$crontabCommandDelayIndexes = range(0, 55, 5);

		foreach ($crontabCommandIndexes as $crontabCommandIndex) {
			foreach ($crontabCommandDelayIndexes as $crontabCommandDelayIndex) {
				$crontabCommands[] = '* * * * * root sleep ' . $crontabCommandDelayIndex . ' && sudo ' . $parameters['binary_files']['php'] . ' /usr/local/ghostcompute/node_action_process_node_process_dogecoin_cryptocurrency_mining_proof_of_work.php ' . $dogecoinCryptocurrencyBlockHeader['next_block_height'] . ' ' . $crontabCommandIndex . '_' . $crontabCommandDelayIndex . ' ghostcompute_dogecoin_cryptocurrency_mining_proof_of_work';
			}
		}

		$crontabCommands = implode("\n", $crontabCommands);

		if (file_put_contents('/etc/crontab', $crontabCommands) === false) {
			echo 'Error adding crontab commands, please try again.' . "\n";
			exit;
		}

		$response['message'] = 'Node process Dogecoin cryptocurrency mining block data processed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'process_node_process_dogecoin_cryptocurrency_mining_block_data') === true) {
		$response = _processNodeProcessDogecoinCryptocurrencyMiningBlockData($parameters, $response);
		_output($response);
	}

	// todo: save block template to system API for GhostCompute nodes without dogecoin-cli installed
	// todo: add incremented nonce in mining process
	// todo: add crontab commands with static parameters
?>
