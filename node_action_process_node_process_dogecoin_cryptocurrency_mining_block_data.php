<?php
	function _createReverseByteOrderHexidecimalString($hexidecimalString) {
		$binaryString = hex2bin($hexidecimalString);
		$binaryString = strrev($binaryString);
		return bin2hex($binaryString);
	}

	function _processNodeProcessDogecoinCryptocurrencyMiningBlockData($parameters, $response) {
		$nodeProcessDogecoinCryptocurrencyMiningProcessParameters = file_get_contents('/usr/local/ghostcompute/dogecoin/dogecoin.conf');
		$nodeProcessDogecoinCryptocurrencyMiningProcessParameters = explode("\n", $nodeProcessDogecoinCryptocurrencyMiningProcessParameters);
		$nodeProcessDogecoinCryptocurrencyMiningProcessParameters = '-' . implode(' -', $nodeProcessDogecoinCryptocurrencyMiningProcessParameters);
		exec('sudo dogecoin-cli ' . $nodeProcessDogecoinCryptocurrencyMiningProcessParameters . ' getblocktemplate 2>&1', $nodeProcessDogecoinCryptocurrencyMiningBlockTemplate);
		$nodeProcessDogecoinCryptocurrencyMiningBlockTemplate = implode('', $nodeProcessDogecoinCryptocurrencyMiningBlockTemplate);
		$nodeProcessDogecoinCryptocurrencyMiningBlockTemplate = json_decode($nodeProcessDogecoinCryptocurrencyMiningBlockTemplate, true);

		if (isset($nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['version']) === false) {
			$response['message'] = 'Error processing node process Dogecoin cryptocurrency mining block template, please try again.';
			return $response;
		}

		if (file_put_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_next_block_height.txt', $nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['height']) === false) {
			$response['message'] = 'Error adding node process Dogecoin cryptocurrency mining block template next block height, please try again.';
			return $response;
		}
		
		$internalByteOrder = exec($parameters['binary_files']['lscpu'] . ' | grep Endian | awk \'{print $3}\'', $internalByteOrder);
		$internalByteOrder = strtolower($internalByteOrder[0]);
		$nodeProcessDogecoinCryptocurrencyMiningBlockHeader = array(
			'coinbase_output_value' => dechex($nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['coinbasevalue']),
			'next_block_height' => dechex($nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['height']),
			'current_block_hash' => $nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['previousblockhash'],
			'target_hash' => $nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['target'],
			'target_hash_bits' => _createReverseByteOrderHexidecimalString($nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['bits']),
			'timestamp_difference' => ($nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['curtime'] - $nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['mintime']),
			'version' => str_pad($nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['version'], 8, '0', STR_PAD_LEFT)
		);

		if (($internalByteOrder === 'little') === true) {
			$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['current_block_hash'] = _createReverseByteOrderHexidecimalString($nodeProcessDogecoinCryptocurrencyMiningBlockHeader['current_block_hash']);
		}

		$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['next_block_height_binary_string'] = hex2bin($nodeProcessDogecoinCryptocurrencyMiningBlockHeader['next_block_height']);
		$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['version'] = _createReverseByteOrderHexidecimalString($nodeProcessDogecoinCryptocurrencyMiningBlockHeader['version']);
		$nodeProcessDogecoinCryptocurrencyMiningBlockTransactions = array(
			$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['version'],
			'01',
			'0000000000000000000000000000000000000000000000000000000000000000',
			'ffffffff',
			false,
			'0' . strlen($nodeProcessDogecoinCryptocurrencyMiningBlockHeader['next_block_height_binary_string']) . _createReverseByteOrderHexidecimalString($nodeProcessDogecoinCryptocurrencyMiningBlockHeader['next_block_height']) . '67686f7374636f6d70757465' . random_bytes(4),
			'ffffffff',
			'01',
			_createReverseByteOrderHexidecimalString($nodeProcessDogecoinCryptocurrencyMiningBlockHeader['coinbase_output_value']),
			false,
			'mining_reward_public_key_goes_here', // todo: create pubKey script and verify string format as-is
			'00000000'
		);
		// todo: make sure block passes "Block encoding failed" error for submitblock RPC + test internal byte order for block header values
		// todo: create API functions for simplifying wallet pubKey creation

		foreach (array(4, 9) as $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionParameterLengthKey) {
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactions[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionParameterLengthKey] = decbin($nodeProcessDogecoinCryptocurrencyMiningBlockTransactions[($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionParameterLengthKey + 1)]);
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactions[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionParameterLengthKey] = strlen($nodeProcessDogecoinCryptocurrencyMiningBlockTransactions[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionParameterLengthKey]);
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactions[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionParameterLengthKey] = dechex($nodeProcessDogecoinCryptocurrencyMiningBlockTransactions[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionParameterLengthKey]);
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactions[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionParameterLengthKey] = str_pad($nodeProcessDogecoinCryptocurrencyMiningBlockTransactions[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionParameterLengthKey], 2, '0', STR_PAD_LEFT);
		}

		$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds = array();
		$nodeProcessDogecoinCryptocurrencyMiningBlockTransactions = array(
			implode('', $nodeProcessDogecoinCryptocurrencyMiningBlockTransactions)
		);
		$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[0] = hex2bin($nodeProcessDogecoinCryptocurrencyMiningBlockTransactions[0]);
		$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[0] = hash('sha256', $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[0], true);
		$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[0] = hash('sha256', $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[0]);

		if (($internalByteOrder === 'little') === true) {
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[0] = _createReverseByteOrderHexidecimalString($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[0]);
		}
		
		$compactSizeUnsignedIntegerHexidecimalPrefixes = array(
			4 => 'fd',
			6 => 'fe',
			8 => 'fe',
			10 => 'ff',
			12 => 'ff',
			14 => 'ff',
			16 => 'ff'
		);
		$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSize = 0;

		foreach ($nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['transactions'] as $nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransactionIndex => $nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransaction) {
			$nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransactionSize = hex2bin($nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransaction['data']);
			$nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransactionSize = strlen($nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransactionSize);
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSize += $nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransactionSize;

			if (($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSize > 950000) === true) {
				break;
			}

			if (
				(empty($nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransaction['hash']) === true) ||
				(($nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransaction['hash'] === $nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransaction['txid']) === false)
			) {
				continue;
			}

			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionId = hex2bin($nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransaction['txid']);

			if (($internalByteOrder === 'big') === true) {
				$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionId = strrev($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionId);
			}

			if (empty($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionId) === true) {
				$response['message'] = 'Error listing node process Dogecoin cryptocurrency mining block template transactions, please try again.';
				return $response;
			}

			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[] = $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionId;
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransaction = $nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransaction['data'];
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionLength = strlen($nodeProcessDogecoinCryptocurrencyMiningBlockTransaction);
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionLength = dechex($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionLength);

			if ((($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionLength % 2) === 1) === true) {
				$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionLength = '0' . $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionLength;
			}

			if (($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionLength < 253) === false) {
				$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionLengthLength = strlen($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionLength);
				$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionLength = $compactSizeUnsignedIntegerHexidecimalPrefixes[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionLengthLength] . _createReverseByteOrderHexidecimalString($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionLength);
			}

			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactions[] = $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionLength . $nodeProcessDogecoinCryptocurrencyMiningBlockTransaction;
		}

		if (empty($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[1]) === true) {
			$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['merkle_root_hash'] = $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[0];
		} elseif (((($nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransactionIndex + 1) % 2) === 0) === true) {
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[($nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransactionIndex + 1)] = $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[$nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransactionIndex];
		}

		while (empty($nodeProcessDogecoinCryptocurrencyMiningBlockHeader['merkle_root_hash']) === true) {
			end($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds);
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex = (key($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds) - 1);
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndexIncrement = 2;

			if (($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex === 1) === true) {
				$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndexIncrement = 1;
			}

			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndexes = range(0, $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex, $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndexIncrement);
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionMerkleRootHashIndex = 0;

			foreach ($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndexes as $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex) {
				$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex] .= $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex + 1)];
				$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex] = hash('sha256', $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex], true);
				$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionMerkleRootHashIndex] = hash('sha256', $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex], true);

				if (($internalByteOrder === 'little') === true) {
					$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionMerkleRootHashIndex] = _createReverseByteOrderHexidecimalString($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionMerkleRootHashIndex]);
				}

				if (($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionMerkleRootHashIndex === 0) === false) {
					unset($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex]);
				}

				unset($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex + 1)]);
				$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionMerkleRootHashIndex++;
			}

			if (($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex === 0) === true) {
				$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['merkle_root_hash'] = $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex];
				$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['merkle_root_hash'] = bin2hex($nodeProcessDogecoinCryptocurrencyMiningBlockHeader['merkle_root_hash']);
			}
		}

		$nodeProcessDogecoinCryptocurrencyMiningBlockHeader = array(
			$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['target_hash'],
			$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['target_hash_bits'],
			$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['timestamp_difference'],
			$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['version'] . $nodeProcessDogecoinCryptocurrencyMiningBlockHeader['current_block_hash'] . $nodeProcessDogecoinCryptocurrencyMiningBlockHeader['merkle_root_hash']
		);
		$nodeProcessDogecoinCryptocurrencyMiningBlockHeader = json_encode($nodeProcessDogecoinCryptocurrencyMiningBlockHeader);
		end($nodeProcessDogecoinCryptocurrencyMiningBlockTransactions);
		$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount = (key($nodeProcessDogecoinCryptocurrencyMiningBlockTransactions) + 1);
		$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount = dechex($nodeProcessDogecoinCryptocurrencyMiningBlockTransactions);

		if ((($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount % 2) === 1) === true) {
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount = '0' . $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount;
		}

		if (($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount < 253) === false) {
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCountLength = strlen($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount);
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount = $compactSizeUnsignedIntegerHexidecimalPrefixes[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCountLength] . _createReverseByteOrderHexidecimalString($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount);
		}

		$nodeProcessDogecoinCryptocurrencyMiningBlockTransactions = $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount . implode('', $nodeProcessDogecoinCryptocurrencyMiningBlockTransactions);

		if (
			($nodeProcessDogecoinCryptocurrencyMiningBlockHeader === false) ||
			(file_put_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.json', $nodeProcessDogecoinCryptocurrencyMiningBlockHeader) === false)
		) {
			$response['message'] = 'Error adding node process Dogecoin cryptocurrency mining block header, please try again.';
			return $response;
		}

		if (file_put_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_transactions.txt', $nodeProcessDogecoinCryptocurrencyMiningBlockTransactions) === false) {
			$response['message'] = 'Error adding node process Dogecoin cryptocurrency mining block transactions, please try again.';
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
		$crontabCommandIndex = array_search('# ghostcompute_node_process_dogecoin_cryptocurrency_mining', $crontabCommands);

		if (is_int($crontabCommandIndex) === true) {
			while (is_int($crontabCommandIndex) === true) {
				unset($crontabCommands[$crontabCommandIndex]);
				$crontabCommandIndex++;

				if (strpos($crontabCommands[$crontabCommandIndex], ' ghostcompute_node_process_dogecoin_cryptocurrency_mining') === false) {
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
				$crontabCommands[] = '* * * * * root sleep ' . $crontabCommandDelayIndex . ' && sudo ' . $parameters['binary_files']['php'] . ' /usr/local/ghostcompute/node_action_process_node_process_dogecoin_cryptocurrency_mining_proof_of_work.php _' . $nodeProcessDogecoinCryptocurrencyBlockTemplate['height'] . ' ghostcompute_node_process_dogecoin_cryptocurrency_mining';
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

	// todo: save block template to system API for GhostCompute nodes without dogecoin-cli installed
?>
