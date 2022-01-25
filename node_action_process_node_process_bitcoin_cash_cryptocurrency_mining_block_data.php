<?php
	function _createReverseByteOrderHexidecimalString($hexidecimalString) {
		$binaryString = hex2bin($hexidecimalString);
		$binaryString = strrev($binaryString);
		return bin2hex($binaryString);
	}

	function _processNodeProcessBitcoinCashCryptocurrencyMiningBlockData($parameters, $response) {
		$nodeProcessBitcoinCashCryptocurrencyMiningProcessParameters = file_get_contents('/usr/local/nodecompute/bitcoin_cash/bitcoin.conf');
		$nodeProcessBitcoinCashCryptocurrencyMiningProcessParameters = explode("\n", $nodeProcessBitcoinCashCryptocurrencyMiningProcessParameters);
		$nodeProcessBitcoinCashCryptocurrencyMiningProcessParameters = '-' . implode(' -', $nodeProcessBitcoinCashCryptocurrencyMiningProcessParameters);
		exec('sudo bitcoin-cli ' . $nodeProcessBitcoinCashCryptocurrencyMiningProcessParameters . ' getblocktemplate 2>&1', $nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplate);
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplate = implode('', $nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplate);
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplate = json_decode($nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplate, true);

		if (isset($nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplate['version']) === false) {
			$response['message'] = 'Error processing node process Bitcoin Cash cryptocurrency mining block template, please try again.';
			return $response;
		}

		if (file_put_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_mining_next_block_height.dat', $nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplate['height']) === false) {
			$response['message'] = 'Error adding node process Bitcoin Cash cryptocurrency mining block template next block height, please try again.';
			return $response;
		}

		$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader = array(
			'current_block_hash' => _createReverseByteOrderHexidecimalString($nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplate['previousblockhash']),
			'next_block_height' => dechex($nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplate['height']),
			'next_block_reward_amount' => sprintf('%08x', $nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplate['coinbasevalue']),
			'target_hash' => $nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplate['target'],
			'target_hash_bits' => _createReverseByteOrderHexidecimalString($nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplate['bits']),
			'version' => sprintf('%08x', $nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplate['version'])
		);
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['next_block_height_size'] = strlen($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['next_block_height']);

		if ((($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['next_block_height_size'] % 2) === 1) === true) {
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['next_block_height'] = '0' . $nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['next_block_height'];
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['next_block_height_size']++;
		}

		$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['next_block_height'] = _createReverseByteOrderHexidecimalString($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['next_block_height']);
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['next_block_height_size'] = ($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['next_block_height_size'] / 2);
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['next_block_reward_amount'] = _createReverseByteOrderHexidecimalString($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['next_block_reward_amount']);
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['version'] = _createReverseByteOrderHexidecimalString($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['version']);
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionCount = 1;
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockCoinbaseScript = '0' . $nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['next_block_height_size'] . $nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['next_block_height'] . '6e6f6465636f6d70757465';
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockCoinbaseScriptSize = (strlen($nodeProcessBitcoinCashCryptocurrencyMiningBlockCoinbaseScript + 1]) / 2);
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockCoinbaseScriptSize = sprintf('%02x', $nodeProcessBitcoinCashCryptocurrencyMiningBlockCoinbaseScriptSize);
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockRewardPublicKeyScript = '[mining_reward_scriptPubKey_goes_here]';
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockRewardPublicKeyScriptSize = (strlen($nodeProcessBitcoinCashCryptocurrencyMiningBlockRewardPublicKeyScript + 1]) / 2);
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockRewardPublicKeyScriptSize = sprintf('%02x', $nodeProcessBitcoinCashCryptocurrencyMiningBlockRewardPublicKeyScriptSize);
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactions = '01000000010000000000000000000000000000000000000000000000000000000000000000ffffffff' . $nodeProcessBitcoinCashCryptocurrencyMiningBlockCoinbaseScriptSize . $nodeProcessBitcoinCashCryptocurrencyMiningBlockCoinbaseScript . 'ffffffff01' . $nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['next_block_reward_amount'] . $nodeProcessBitcoinCashCryptocurrencyMiningBlockRewardPublicKeyScriptSize . $nodeProcessBitcoinCashCryptocurrencyMiningBlockRewardPublicKeyScript . '00000000';
		// todo: create multiple merkle roots for extra nonce (number_of_instances * number_of_mining_pow_processes)
		// todo: make sure block passes "Block encoding failed" error for submitblock RPC
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIds = array();
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIds[0] = hex2bin($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactions);
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIds[0] = hash('sha256', $nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIds[0], true);
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIds[0] = hash('sha256', $nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIds[0], true);
		$compactSizeUnsignedIntegerHexidecimalPrefixes = array(
			0 => '',
			1 => '',
			2 => '',
			3 => '',
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
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockSize = 0;

		foreach ($nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplate['transactions'] as $nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplateTransactionIndex => $nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplateTransaction) {
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionSize = hex2bin($nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplateTransaction['data']);
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionSize = strlen($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionSize);
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockSize += $nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionSize;

			if (($nodeProcessBitcoinCashCryptocurrencyMiningBlockSize > 950000) === true) {
				break;
			}

			if (
				(empty($nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplateTransaction['hash']) === true) ||
				(($nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplateTransaction['hash'] === $nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplateTransaction['txid']) === false)
			) {
				continue;
			}

			$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionId = hex2bin($nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplateTransaction['txid']);
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionId = strrev($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionId);

			if (empty($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionId) === true) {
				$response['message'] = 'Error listing node process Bitcoin Cash cryptocurrency mining block template transactions, please try again.';
				return $response;
			}

			$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionCount++;
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIds[] = $nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionId;
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactions .= $nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplateTransaction['data'];
		}

		$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderMerkleRootNodeCount = $nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionCount;

		if (empty($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIds[1]) === true) {
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['merkle_root_hash'] = bin2hex($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIds[0]);
		} elseif ((($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionCount % 2) === 1) === true) {
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderMerkleRootNodeCount++;
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionCount] = $nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionId;
		}

		if (($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderMerkleRootNodeCount === 2) === true) {
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIndexes = array(
				0
			);
		} else {
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIndexes = range(0, ($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderMerkleRootNodeCount - 1), 2);
		}

		if (empty($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['merkle_root_hash']) === true) {
			while (true) {
				$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionMerkleRootHashIndex = 0;

				foreach ($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIndexes as $nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIndex) {
					$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIndex] .= $nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIds[($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIndex + 1)];
					$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionId = hash('sha256', $nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIndex], true);
					$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionId = hash('sha256', $nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionId, true);
					unset($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIndex]);
					unset($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIds[($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIndex + 1)]);
					$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionMerkleRootHashIndex] = $nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionId;
					$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionMerkleRootHashIndex++;
				}

				$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderMerkleRootNodeCount = ($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderMerkleRootNodeCount / 2);
				$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIndexLength = ($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderMerkleRootNodeCount / 2);

				if (($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderMerkleRootNodeCount === 1) === true) {
					$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['merkle_root_hash'] = bin2hex($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionId);
					break;
				}

				if ((($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderMerkleRootNodeCount % 2) === 1) === true) {
					$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderMerkleRootNodeCount++;
					$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionMerkleRootHashIndex] = $nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionId;
					$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIndexLength++;
				}

				$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIndexes = array_slice($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIndexes, 0, $nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionIndexLength, true);
			}
		}

		$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader = array(
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['target_hash'],
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['target_hash_bits'],
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplate['mintime'],
			($nodeProcessBitcoinCashCryptocurrencyMiningBlockTemplate['curtime'] + 6000),
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['version'] . $nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['current_block_hash'] . $nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader['merkle_root_hash']
		);
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader = json_encode($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader);
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionCount = dechex($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionCount);
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionCountLength = strlen($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionCount);

		if ((($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionCountLength % 2) === 1) === true) {
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionCount = '0' . $nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionCount;
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionCountLength++;
		}

		$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionCount = $compactSizeUnsignedIntegerHexidecimalPrefixes[$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionCountLength] . _createReverseByteOrderHexidecimalString($nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionCount);
		$nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactions = $nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactionCount . $nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactions;

		if (
			($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader === false) ||
			(file_put_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_mining_block_header.json', $nodeProcessBitcoinCashCryptocurrencyMiningBlockHeader) === false)
		) {
			$response['message'] = 'Error adding node process Bitcoin Cash cryptocurrency mining block header, please try again.';
			return $response;
		}

		if (file_put_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_mining_block_transactions.dat', $nodeProcessBitcoinCashCryptocurrencyMiningBlockTransactions) === false) {
			$response['message'] = 'Error adding node process Bitcoin Cash cryptocurrency mining block transactions, please try again.';
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
		$crontabCommandIndex = array_search('# nodecompute_node_process_bitcoin_cash_cryptocurrency_mining', $crontabCommands);

		if (is_int($crontabCommandIndex) === true) {
			while (is_int($crontabCommandIndex) === true) {
				unset($crontabCommands[$crontabCommandIndex]);
				$crontabCommandIndex++;

				if (strpos($crontabCommands[$crontabCommandIndex], ' nodecompute_node_process_bitcoin_cash_cryptocurrency_mining') === false) {
					$crontabCommandIndex = false;
				}
			}
		}

		$crontabCommands += array(
			'# nodecompute_node_process_bitcoin_cash_cryptocurrency_mining',
			'* * * * * root sudo ' . $parameters['binary_files']['php'] . ' /usr/local/nodecompute/node_endpoint.php process_node_process_bitcoin_cash_cryptocurrency_mining_processes 5 nodecompute_node_process_bitcoin_cash_cryptocurrency_mining'
		);
		$crontabCommandIndexes = range(0, 2); // todo: make this based on user input + increment based on free resources
		$crontabCommandDelayIndexes = range(0, 55, 5);

		foreach ($crontabCommandIndexes as $crontabCommandIndex) {
			foreach ($crontabCommandDelayIndexes as $crontabCommandDelayIndex) {
				$crontabCommands[] = '* * * * * root sleep ' . $crontabCommandDelayIndex . ' && sudo ' . $parameters['binary_files']['php'] . ' /usr/local/nodecompute/node_action_process_node_process_bitcoin_cash_cryptocurrency_mining_block_header.php _' . $nodeProcessBitcoinCashCryptocurrencyBlockTemplate['height'] . ' nodecompute_node_process_bitcoin_cash_cryptocurrency_mining';
			}
		}

		$crontabCommands = implode("\n", $crontabCommands);

		if (file_put_contents('/etc/crontab', $crontabCommands) === false) {
			echo 'Error adding crontab commands, please try again.';
			return $response;
		}

		shell_exec('sudo ' . $parameters['binary_files']['crontab'] . ' /etc/crontab');
		$response['message'] = 'Node process Bitcoin Cash cryptocurrency mining block data processed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'process_node_process_bitcoin_cash_cryptocurrency_mining_block_data') === true) {
		$response = _processNodeProcessBitcoinCashCryptocurrencyMiningBlockData($parameters, $response);
	}

	// todo: save block template to system API for NodeCompute nodes without bitcoin-cli installed
?>
