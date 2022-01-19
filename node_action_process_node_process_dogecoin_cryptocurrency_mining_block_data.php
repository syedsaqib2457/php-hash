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

		if (file_put_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_next_block_height.dat', $nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['height']) === false) {
			$response['message'] = 'Error adding node process Dogecoin cryptocurrency mining block template next block height, please try again.';
			return $response;
		}

		$nodeProcessDogecoinCryptocurrencyMiningBlockHeader = array(
			'coinbase_output_value' => dechex($nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['coinbasevalue']),
			'current_block_hash' => _createReverseByteOrderHexidecimalString($nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['previousblockhash']),
			'next_block_height' => dechex($nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['height']),
			'target_hash' => $nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['target'],
			'target_hash_bits' => _createReverseByteOrderHexidecimalString($nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['bits']),
			'timestamp' => $nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['mintime'],
			'version' => str_pad($nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['version'], 8, '0', STR_PAD_LEFT)
		);
		$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['next_block_height_binary_string'] = hex2bin($nodeProcessDogecoinCryptocurrencyMiningBlockHeader['next_block_height']);
		$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['version'] = _createReverseByteOrderHexidecimalString($nodeProcessDogecoinCryptocurrencyMiningBlockHeader['version']);
		$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount = 1;
		$nodeProcessDogecoinCryptocurrencyMiningBlockTransactions = array(
			$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['version'],
			'01',
			'0000000000000000000000000000000000000000000000000000000000000000',
			'ffffffff',
			false,
			'0' . strlen($nodeProcessDogecoinCryptocurrencyMiningBlockHeader['next_block_height_binary_string']) . _createReverseByteOrderHexidecimalString($nodeProcessDogecoinCryptocurrencyMiningBlockHeader['next_block_height']) . '67686f7374636f6d70757465', // todo: append extranonce as bin2hex('_' . $i)
			'00000000',
			'01',
			_createReverseByteOrderHexidecimalString($nodeProcessDogecoinCryptocurrencyMiningBlockHeader['coinbase_output_value']),
			false,
			'mining_reward_scriptPubKey_goes_here', // todo: create API functions for simplifying wallet pubKey creation
			'00000000'
		);
		// todo: create multiple merkle roots for extra nonce (number_of_instances * number_of_mining_pow_processes)
		// todo: make sure block passes "Block encoding failed" error for submitblock RPC

		foreach (array(4, 9) as $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionParameterSizeKey) {
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactions[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionParameterSizeKey] = hex2bin($nodeProcessDogecoinCryptocurrencyMiningBlockTransactions[($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionParameterSizeKey + 1)]);
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactions[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionParameterSizeKey] = strlen($nodeProcessDogecoinCryptocurrencyMiningBlockTransactions[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionParameterSizeKey]);
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactions[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionParameterSizeKey] = dechex($nodeProcessDogecoinCryptocurrencyMiningBlockTransactions[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionParameterSizeKey]);
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactions[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionParameterSizeKey] = str_pad($nodeProcessDogecoinCryptocurrencyMiningBlockTransactions[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionParameterSizeKey], 2, '0', STR_PAD_LEFT);
		}

		$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds = array();
		$nodeProcessDogecoinCryptocurrencyMiningBlockTransactions = implode('', $nodeProcessDogecoinCryptocurrencyMiningBlockTransactions);
		$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[0] = hex2bin($nodeProcessDogecoinCryptocurrencyMiningBlockTransactions);
		$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[0] = hash('sha256', $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[0], true);
		$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[0] = hash('sha256', $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[0], true);
		$compactSizeUnsignedIntegerHexidecimalPrefixes = array(
			4 => 'fd',
			5 => 'fe',
			6 => 'fe',
			7 => 'fe',
			8 => 'fe',
			9 => 'ff',
			10 => 'ff',
			11 => 'ff',
			12 => 'ff',
			13 => 'ff',
			14 => 'ff',
			15 => 'ff',
			16 => 'ff'
		);
		$nodeProcessDogecoinCryptocurrencyMiningBlockSize = 0;

		foreach ($nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['transactions'] as $nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransactionIndex => $nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransaction) {
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSize = hex2bin($nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransaction['data']);
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSize = strlen($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSize);
			$nodeProcessDogecoinCryptocurrencyMiningBlockSize += $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSize;
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSize = bin2hex($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSize);

			if (($nodeProcessDogecoinCryptocurrencyMiningBlockSize > 950000) === true) {
				break;
			}

			if (
				(empty($nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransaction['hash']) === true) ||
				(($nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransaction['hash'] === $nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransaction['txid']) === false)
			) {
				continue;
			}

			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionId = hex2bin($nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransaction['txid']);
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionId = strrev($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionId);

			if (empty($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionId) === true) {
				$response['message'] = 'Error listing node process Dogecoin cryptocurrency mining block template transactions, please try again.';
				return $response;
			}

			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[] = $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionId;

			if (($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSize < 253) === false) {
				$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSize = dechex($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSize);
				$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSizeLength = strlen($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSize);

				if ((($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSizeLength % 2) === 1) === true) {
					$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSize = '0' . $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSize;
					$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSizeLength++;
				}

				$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSize = $compactSizeUnsignedIntegerHexidecimalPrefixes[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSizeLength] . _createReverseByteOrderHexidecimalString($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSize);
			}

			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount++;
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactions .= $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionSize . $nodeProcessDogecoinCryptocurrencyMiningBlockTemplateTransaction['data'];
		}

		$nodeProcessDogecoinCryptocurrencyMiningBlockHeaderMerkleRootNodeCount = $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount;

		if (empty($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[1]) === true) {
			$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['merkle_root_hash'] = strrev($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[0]);
		} elseif ((($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount % 2) === 1) === true) {
			$nodeProcessDogecoinCryptocurrencyMiningBlockHeaderMerkleRootNodeCount++;
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount] = $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionId;
		}

		if (($nodeProcessDogecoinCryptocurrencyMiningBlockHeaderMerkleRootNodeCount === 2) === true) {
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndexes = array(
				0
			);
		} else {
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndexes = range(0, ($nodeProcessDogecoinCryptocurrencyMiningBlockHeaderMerkleRootNodeCount - 1), 2);
		}

		if (empty($nodeProcessDogecoinCryptocurrencyMiningBlockHeader['merkle_root_hash']) === true) {
			while (true) {
				$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionMerkleRootHashIndex = 0;

				foreach ($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndexes as $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex) {
					$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex] .= $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex + 1)];
					$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionId = hash('sha256', $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex], true);
					$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionId = hash('sha256', $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionId, true);
					unset($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex]);
					unset($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndex + 1)]);
					$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionMerkleRootHashIndex] = $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionId;
					$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionMerkleRootHashIndex++;
				}

				$nodeProcessDogecoinCryptocurrencyMiningBlockHeaderMerkleRootNodeCount = ($nodeProcessDogecoinCryptocurrencyMiningBlockHeaderMerkleRootNodeCount / 2);
				$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndexLength = ($nodeProcessDogecoinCryptocurrencyMiningBlockHeaderMerkleRootNodeCount / 2);

				if (($nodeProcessDogecoinCryptocurrencyMiningBlockHeaderMerkleRootNodeCount === 1) === true) {
					$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['merkle_root_hash'] = strrev($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionId);
					break;
				}

				if ((($nodeProcessDogecoinCryptocurrencyMiningBlockHeaderMerkleRootNodeCount % 2) === 1) === true) {
					$nodeProcessDogecoinCryptocurrencyMiningBlockHeaderMerkleRootNodeCount++;
					$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIds[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionMerkleRootHashIndex] = $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionId;
					$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndexLength++;
				}

				$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndexes = array_slice($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndexes, 0, $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionIndexLength, true);
			}
		}

		$nodeProcessDogecoinCryptocurrencyMiningBlockHeaderTimestamp = $nodeProcessDogecoinCryptocurrencyMiningBlockHeader['timestamp'];
		$nodeProcessDogecoinCryptocurrencyMiningBlockHeaderTimestampIncrements = range(0, (($nodeProcessDogecoinCryptocurrencyMiningBlockTemplate['curtime'] + 3000) - $nodeProcessDogecoinCryptocurrencyMiningBlockHeader['timestamp']));
		$nodeProcessDogecoinCryptocurrencyMiningBlockHeader = array(
			$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['target_hash'],
			$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['target_hash_bits'],
			$nodeProcessDogecoinCryptocurrencyMiningBlockHeader['version'] . $nodeProcessDogecoinCryptocurrencyMiningBlockHeader['current_block_hash'] . bin2hex($nodeProcessDogecoinCryptocurrencyMiningBlockHeader['merkle_root_hash'])
		);

		foreach ($nodeProcessDogecoinCryptocurrencyMiningBlockHeaderTimestampIncrements as $nodeProcessDogecoinCryptocurrencyMiningBlockHeaderTimestampIncrement) {
			$nodeProcessDogecoinCryptocurrencyMiningBlockHeader[3][$nodeProcessDogecoinCryptocurrencyMiningBlockHeaderTimestampIncrement] = decbin($nodeProcessDogecoinCryptocurrencyMiningBlockHeaderTimestamp + $nodeProcessDogecoinCryptocurrencyMiningBlockHeaderTimestampIncrement);
			$nodeProcessDogecoinCryptocurrencyMiningBlockHeader[3][$nodeProcessDogecoinCryptocurrencyMiningBlockHeaderTimestampIncrement] = strrev($nodeProcessDogecoinCryptocurrencyMiningBlockHeader[3][$nodeProcessDogecoinCryptocurrencyMiningBlockHeaderTimestampIncrement]);
		}

		$nodeProcessDogecoinCryptocurrencyMiningBlockHeader = json_encode($nodeProcessDogecoinCryptocurrencyMiningBlockHeader);

		if (($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount < 253) === false) {
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount = dechex($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount);
			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCountLength = strlen($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount);

			if ((($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCountLength % 2) === 1) === true) {
				$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount = '0' . $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount;
				$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCountLength++;
			}

			$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount = $compactSizeUnsignedIntegerHexidecimalPrefixes[$nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCountLength] . _createReverseByteOrderHexidecimalString($nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount);
		}

		$nodeProcessDogecoinCryptocurrencyMiningBlockTransactions = $nodeProcessDogecoinCryptocurrencyMiningBlockTransactionCount . $nodeProcessDogecoinCryptocurrencyMiningBlockTransactions;

		if (
			($nodeProcessDogecoinCryptocurrencyMiningBlockHeader === false) ||
			(file_put_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.json', $nodeProcessDogecoinCryptocurrencyMiningBlockHeader) === false)
		) {
			$response['message'] = 'Error adding node process Dogecoin cryptocurrency mining block header, please try again.';
			return $response;
		}

		if (file_put_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_transactions.dat', $nodeProcessDogecoinCryptocurrencyMiningBlockTransactions) === false) {
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
