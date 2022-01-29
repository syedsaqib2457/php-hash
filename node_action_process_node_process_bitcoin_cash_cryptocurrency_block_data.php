<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _createReverseByteOrderHexidecimalString($hexidecimalString) {
		$binaryString = hex2bin($hexidecimalString);
		$binaryString = strrev($binaryString);
		return bin2hex($binaryString);
	}

	function _processNodeProcessBitcoinCashCryptocurrencyBlockData($parameters, $response) {
		$systemActionCountNodeProcessBitcoinCashCryptocurrencyBlockHeadersParameters = array(
			'action' => 'count_node_process_cryptocurrency_block_headers',
			'node_authentication_token' => $parameters['node_authentication_token'],
			'where' => array(
				'process_type' => 'bitcoin_cash'
			)
		);
		$encodedSystemActionCountNodeProcessBitcoinCashCryptocurrencyBlockHeadersParameters = json_encode($systemActionCountNodeProcessBitcoinCashCryptocurrencyBlockHeadersParameters);
		shell_exec('sudo ' . $parameters['binary_files']['wget'] . ' -O /usr/local/nodecompute/system_action_count_node_process_bitcoin_cash_cryptocurrency_block_headers_response.json --no-dns-cache --post-data \'json=' . $encodedSystemActionCountNodeProcessBitcoinCashCryptocurrencyBlockHeadersParameters . '\' --timeout=10 ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php');
		$systemActionCountNodeProcessBitcoinCashCryptocurrencyBlockHeadersResponse = file_get_contents('/usr/local/nodecompute/system_action_count_node_process_bitcoin_cash_cryptocurrency_block_headers_response.json');
		$systemActionCountNodeProcessBitcoinCashCryptocurrencyBlockHeadersResponse = json_decode($systemActionCountNodeProcessBitcoinCashCryptocurrencyBlockHeadersResponse, true);

		if (empty($systemActionCountNodeProcessBitcoinCashCryptocurrencyBlockHeadersResponse['data']) === true) {
			$response['message'] = 'Error counting node process Bitcoin Cash cryptocurrency block headers, please try again.';
			return $response;
		}

		// todo: create multiple merkle roots for extra nonce based on system_action_count_node_process_cryptocurrency_block_headers_response
		exec('sudo bitcoin-cli -conf=/usr/local/nodecompute/bitcoin_cash/bitcoin.conf getblocktemplate 2>&1', $nodeProcessBitcoinCashCryptocurrencyBlockTemplate);
		$nodeProcessBitcoinCashCryptocurrencyBlockTemplate = implode('', $nodeProcessBitcoinCashCryptocurrencyBlockTemplate);
		$nodeProcessBitcoinCashCryptocurrencyBlockTemplate = json_decode($nodeProcessBitcoinCashCryptocurrencyBlockTemplate, true);

		if (isset($nodeProcessBitcoinCashCryptocurrencyBlockTemplate['version']) === false) {
			$response['message'] = 'Error processing node process Bitcoin Cash cryptocurrency block template, please try again.';
			return $response;
		}

		if (file_put_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_next_block_height.dat', $nodeProcessBitcoinCashCryptocurrencyBlockTemplate['height']) === false) {
			$response['message'] = 'Error adding node process Bitcoin Cash cryptocurrency block template next block height, please try again.';
			return $response;
		}

		$nodeProcessBitcoinCashCryptocurrencyBlockHeader = array(
			'current_block_hash' => _createReverseByteOrderHexidecimalString($nodeProcessBitcoinCashCryptocurrencyBlockTemplate['previousblockhash']),
			'next_block_height' => dechex($nodeProcessBitcoinCashCryptocurrencyBlockTemplate['height']),
			'next_block_reward_amount' => sprintf('%016x', $nodeProcessBitcoinCashCryptocurrencyBlockTemplate['coinbasevalue']),
			'target_hash' => $nodeProcessBitcoinCashCryptocurrencyBlockTemplate['target'],
			'target_hash_bits' => _createReverseByteOrderHexidecimalString($nodeProcessBitcoinCashCryptocurrencyBlockTemplate['bits']),
			'version' => sprintf('%08x', $nodeProcessBitcoinCashCryptocurrencyBlockTemplate['version'])
		);
		$nodeProcessBitcoinCashCryptocurrencyBlockHeader['next_block_height_size'] = strlen($nodeProcessBitcoinCashCryptocurrencyBlockHeader['next_block_height']);

		if ((($nodeProcessBitcoinCashCryptocurrencyBlockHeader['next_block_height_size'] % 2) === 1) === true) {
			$nodeProcessBitcoinCashCryptocurrencyBlockHeader['next_block_height'] = '0' . $nodeProcessBitcoinCashCryptocurrencyBlockHeader['next_block_height'];
			$nodeProcessBitcoinCashCryptocurrencyBlockHeader['next_block_height_size']++;
		}

		$nodeProcessBitcoinCashCryptocurrencyBlockHeader['next_block_height'] = _createReverseByteOrderHexidecimalString($nodeProcessBitcoinCashCryptocurrencyBlockHeader['next_block_height']);
		$nodeProcessBitcoinCashCryptocurrencyBlockHeader['next_block_height_size'] = ($nodeProcessBitcoinCashCryptocurrencyBlockHeader['next_block_height_size'] / 2);
		$nodeProcessBitcoinCashCryptocurrencyBlockHeader['next_block_reward_amount'] = _createReverseByteOrderHexidecimalString($nodeProcessBitcoinCashCryptocurrencyBlockHeader['next_block_reward_amount']);
		$nodeProcessBitcoinCashCryptocurrencyBlockHeader['version'] = _createReverseByteOrderHexidecimalString($nodeProcessBitcoinCashCryptocurrencyBlockHeader['version']);
		$nodeProcessBitcoinCashCryptocurrencyBlockTransactionCount = 1;
		$nodeProcessBitcoinCashCryptocurrencyBlockCoinbaseScript = '0' . $nodeProcessBitcoinCashCryptocurrencyBlockHeader['next_block_height_size'] . $nodeProcessBitcoinCashCryptocurrencyBlockHeader['next_block_height'] . '6e6f6465636f6d70757465';
		$nodeProcessBitcoinCashCryptocurrencyBlockCoinbaseScriptSize = (strlen($nodeProcessBitcoinCashCryptocurrencyBlockCoinbaseScript) / 2);
		$nodeProcessBitcoinCashCryptocurrencyBlockCoinbaseScriptSize = sprintf('%02x', $nodeProcessBitcoinCashCryptocurrencyBlockCoinbaseScriptSize);
		$nodeProcessBitcoinCashCryptocurrencyBlockRewardPublicKeyScript = '[block_reward_scriptPubKey_goes_here]';
		$nodeProcessBitcoinCashCryptocurrencyBlockRewardPublicKeyScriptSize = (strlen($nodeProcessBitcoinCashCryptocurrencyBlockRewardPublicKeyScript) / 2);
		$nodeProcessBitcoinCashCryptocurrencyBlockRewardPublicKeyScriptSize = sprintf('%02x', $nodeProcessBitcoinCashCryptocurrencyBlockRewardPublicKeyScriptSize);
		$nodeProcessBitcoinCashCryptocurrencyBlockTransactions = '01000000010000000000000000000000000000000000000000000000000000000000000000ffffffff' . $nodeProcessBitcoinCashCryptocurrencyBlockCoinbaseScriptSize . $nodeProcessBitcoinCashCryptocurrencyBlockCoinbaseScript . 'ffffffff01' . $nodeProcessBitcoinCashCryptocurrencyBlockHeader['next_block_reward_amount'] . $nodeProcessBitcoinCashCryptocurrencyBlockRewardPublicKeyScriptSize . $nodeProcessBitcoinCashCryptocurrencyBlockRewardPublicKeyScript . '00000000';
		$nodeProcessBitcoinCashCryptocurrencyBlockTransactionIds = array();
		$nodeProcessBitcoinCashCryptocurrencyBlockTransactionIds[0] = hex2bin($nodeProcessBitcoinCashCryptocurrencyBlockTransactions);
		$nodeProcessBitcoinCashCryptocurrencyBlockTransactionIds[0] = hash('sha256', $nodeProcessBitcoinCashCryptocurrencyBlockTransactionIds[0], true);
		$nodeProcessBitcoinCashCryptocurrencyBlockTransactionIds[0] = hash('sha256', $nodeProcessBitcoinCashCryptocurrencyBlockTransactionIds[0], true);
		$compactSizeUnsignedIntegerHexidecimalPrefixes = array(
			2 => '',
			4 => 'fd',
			6 => 'fe',
			8 => 'fe',
			10 => 'ff'
		);
		$nodeProcessBitcoinCashCryptocurrencyBlockSize = 0;

		foreach ($nodeProcessBitcoinCashCryptocurrencyBlockTemplate['transactions'] as $nodeProcessBitcoinCashCryptocurrencyBlockTemplateTransactionIndex => $nodeProcessBitcoinCashCryptocurrencyBlockTemplateTransaction) {
			$nodeProcessBitcoinCashCryptocurrencyBlockTransactionSize = hex2bin($nodeProcessBitcoinCashCryptocurrencyBlockTemplateTransaction['data']);
			$nodeProcessBitcoinCashCryptocurrencyBlockTransactionSize = strlen($nodeProcessBitcoinCashCryptocurrencyBlockTransactionSize);
			$nodeProcessBitcoinCashCryptocurrencyBlockSize += $nodeProcessBitcoinCashCryptocurrencyBlockTransactionSize;

			if (
				(($nodeProcessBitcoinCashCryptocurrencyBlockSize > 950000) === true) ||
				(($nodeProcessBitcoinCashCryptocurrencyBlockTransactionCount === 100) === true)
			) {
				break;
			}

			if (
				(empty($nodeProcessBitcoinCashCryptocurrencyBlockTemplateTransaction['hash']) === true) ||
				(($nodeProcessBitcoinCashCryptocurrencyBlockTemplateTransaction['hash'] === $nodeProcessBitcoinCashCryptocurrencyBlockTemplateTransaction['txid']) === false)
			) {
				continue;
			}

			$nodeProcessBitcoinCashCryptocurrencyBlockTransactionId = hex2bin($nodeProcessBitcoinCashCryptocurrencyBlockTemplateTransaction['txid']);
			$nodeProcessBitcoinCashCryptocurrencyBlockTransactionId = strrev($nodeProcessBitcoinCashCryptocurrencyBlockTransactionId);

			if (empty($nodeProcessBitcoinCashCryptocurrencyBlockTransactionId) === true) {
				$response['message'] = 'Error listing node process Bitcoin Cash cryptocurrency block template transactions, please try again.';
				return $response;
			}

			$nodeProcessBitcoinCashCryptocurrencyBlockTransactionCount++;
			$nodeProcessBitcoinCashCryptocurrencyBlockTransactionIds[] = $nodeProcessBitcoinCashCryptocurrencyBlockTransactionId;
			$nodeProcessBitcoinCashCryptocurrencyBlockTransactions .= $nodeProcessBitcoinCashCryptocurrencyBlockTemplateTransaction['data'];
		}

		$nodeProcessBitcoinCashCryptocurrencyBlockHeaderMerkleRootNodeCount = $nodeProcessBitcoinCashCryptocurrencyBlockTransactionCount;

		if (empty($nodeProcessBitcoinCashCryptocurrencyBlockTransactionIds[1]) === true) {
			$nodeProcessBitcoinCashCryptocurrencyBlockHeader['merkle_root_hash'] = bin2hex($nodeProcessBitcoinCashCryptocurrencyBlockTransactionIds[0]);
		} elseif ((($nodeProcessBitcoinCashCryptocurrencyBlockTransactionCount % 2) === 1) === true) {
			$nodeProcessBitcoinCashCryptocurrencyBlockHeaderMerkleRootNodeCount++;
			$nodeProcessBitcoinCashCryptocurrencyBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyBlockTransactionCount] = $nodeProcessBitcoinCashCryptocurrencyBlockTransactionId;
		}

		if (($nodeProcessBitcoinCashCryptocurrencyBlockHeaderMerkleRootNodeCount === 2) === true) {
			$nodeProcessBitcoinCashCryptocurrencyBlockTransactionIndexes = array(
				0
			);
		} else {
			$nodeProcessBitcoinCashCryptocurrencyBlockTransactionIndexes = range(0, ($nodeProcessBitcoinCashCryptocurrencyBlockHeaderMerkleRootNodeCount - 1), 2);
		}

		if (empty($nodeProcessBitcoinCashCryptocurrencyBlockHeader['merkle_root_hash']) === true) {
			while (true) {
				$nodeProcessBitcoinCashCryptocurrencyBlockTransactionMerkleRootHashIndex = 0;

				foreach ($nodeProcessBitcoinCashCryptocurrencyBlockTransactionIndexes as $nodeProcessBitcoinCashCryptocurrencyBlockTransactionIndex) {
					$nodeProcessBitcoinCashCryptocurrencyBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyBlockTransactionIndex] .= $nodeProcessBitcoinCashCryptocurrencyBlockTransactionIds[($nodeProcessBitcoinCashCryptocurrencyBlockTransactionIndex + 1)];
					$nodeProcessBitcoinCashCryptocurrencyBlockTransactionId = hash('sha256', $nodeProcessBitcoinCashCryptocurrencyBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyBlockTransactionIndex], true);
					$nodeProcessBitcoinCashCryptocurrencyBlockTransactionId = hash('sha256', $nodeProcessBitcoinCashCryptocurrencyBlockTransactionId, true);
					unset($nodeProcessBitcoinCashCryptocurrencyBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyBlockTransactionIndex]);
					unset($nodeProcessBitcoinCashCryptocurrencyBlockTransactionIds[($nodeProcessBitcoinCashCryptocurrencyBlockTransactionIndex + 1)]);
					$nodeProcessBitcoinCashCryptocurrencyBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyBlockTransactionMerkleRootHashIndex] = $nodeProcessBitcoinCashCryptocurrencyBlockTransactionId;
					$nodeProcessBitcoinCashCryptocurrencyBlockTransactionMerkleRootHashIndex++;
				}

				$nodeProcessBitcoinCashCryptocurrencyBlockHeaderMerkleRootNodeCount = ($nodeProcessBitcoinCashCryptocurrencyBlockHeaderMerkleRootNodeCount / 2);
				$nodeProcessBitcoinCashCryptocurrencyBlockTransactionIndexLength = ($nodeProcessBitcoinCashCryptocurrencyBlockHeaderMerkleRootNodeCount / 2);

				if (($nodeProcessBitcoinCashCryptocurrencyBlockHeaderMerkleRootNodeCount === 1) === true) {
					$nodeProcessBitcoinCashCryptocurrencyBlockHeader['merkle_root_hash'] = bin2hex($nodeProcessBitcoinCashCryptocurrencyBlockTransactionId);
					break;
				}

				if ((($nodeProcessBitcoinCashCryptocurrencyBlockHeaderMerkleRootNodeCount % 2) === 1) === true) {
					$nodeProcessBitcoinCashCryptocurrencyBlockHeaderMerkleRootNodeCount++;
					$nodeProcessBitcoinCashCryptocurrencyBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyBlockTransactionMerkleRootHashIndex] = $nodeProcessBitcoinCashCryptocurrencyBlockTransactionId;
					$nodeProcessBitcoinCashCryptocurrencyBlockTransactionIndexLength++;
				}

				$nodeProcessBitcoinCashCryptocurrencyBlockTransactionIndexes = array_slice($nodeProcessBitcoinCashCryptocurrencyBlockTransactionIndexes, 0, $nodeProcessBitcoinCashCryptocurrencyBlockTransactionIndexLength, true);
			}
		}

		$nodeProcessBitcoinCashCryptocurrencyBlockHeader = array(
			$nodeProcessBitcoinCashCryptocurrencyBlockHeader['target_hash'],
			$nodeProcessBitcoinCashCryptocurrencyBlockHeader['target_hash_bits'],
			$nodeProcessBitcoinCashCryptocurrencyBlockTemplate['mintime'],
			($nodeProcessBitcoinCashCryptocurrencyBlockTemplate['curtime'] + 6000),
			$nodeProcessBitcoinCashCryptocurrencyBlockHeader['version'] . $nodeProcessBitcoinCashCryptocurrencyBlockHeader['current_block_hash'] . $nodeProcessBitcoinCashCryptocuBlockHeader['merkle_root_hash']
		);
		$nodeProcessBitcoinCashCryptocurrencyBlockHeader = json_encode($nodeProcessBitcoinCashCryptocurrencyBlockHeader);
		$nodeProcessBitcoinCashCryptocurrencyBlockTransactionCount = dechex($nodeProcessBitcoinCashCryptocurrencyBlockTransactionCount);
		$nodeProcessBitcoinCashCryptocurrencyBlockTransactionCountLength = strlen($nodeProcessBitcoinCashCryptocurrencyBlockTransactionCount);

		if ((($nodeProcessBitcoinCashCryptocurrencyBlockTransactionCountLength % 2) === 1) === true) {
			$nodeProcessBitcoinCashCryptocurrencyBlockTransactionCount = '0' . $nodeProcessBitcoinCashCryptocurrencyBlockTransactionCount;
			$nodeProcessBitcoinCashCryptocurrencyBlockTransactionCountLength++;
		}

		$nodeProcessBitcoinCashCryptocurrencyBlockTransactionCount = $compactSizeUnsignedIntegerHexidecimalPrefixes[$nodeProcessBitcoinCashCryptocurrencyBlockTransactionCountLength] . _createReverseByteOrderHexidecimalString($nodeProcessBitcoinCashCryptocurrencyBlockTransactionCount);
		$nodeProcessBitcoinCashCryptocurrencyBlockTransactions = $nodeProcessBitcoinCashCryptocurrencyBlockTransactionCount . $nodeProcessBitcoinCashCryptocurrencyBlockTransactions;

		if (
			($nodeProcessBitcoinCashCryptocurrencyBlockHeader === false) ||
			(file_put_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_block_header.json', $nodeProcessBitcoinCashCryptocurrencyBlockHeader) === false)
		) {
			$response['message'] = 'Error adding node process Bitcoin Cash cryptocurrency block header, please try again.';
			return $response;
		}

		if (file_put_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_block_transactions.dat', $nodeProcessBitcoinCashCryptocurrencyBlockTransactions) === false) {
			$response['message'] = 'Error adding node process Bitcoin Cash cryptocurrency block transactions, please try again.';
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
		$crontabCommandIndex = array_search('# nodecompute_node_process_bitcoin_cash_cryptocurrency', $crontabCommands);

		if (is_int($crontabCommandIndex) === true) {
			while (is_int($crontabCommandIndex) === true) {
				unset($crontabCommands[$crontabCommandIndex]);
				$crontabCommandIndex++;

				if (strpos($crontabCommands[$crontabCommandIndex], ' nodecompute_node_process_bitcoin_cash_cryptocurrency') === false) {
					$crontabCommandIndex = false;
				}
			}
		}

		$crontabCommands += array(
			'# nodecompute_node_process_bitcoin_cash_cryptocurrency',
			'* * * * * root sudo ' . $parameters['binary_files']['php'] . ' /usr/local/nodecompute/node_endpoint.php process_node_process_bitcoin_cash_cryptocurrency_processes nodecompute_node_process_bitcoin_cash_cryptocurrency'
		);
		$crontabCommandIndexes = range(0, 2); // todo: make this based on user input + increment based on free resources
		$crontabCommandDelayIndexes = range(0, 55, 5);

		foreach ($crontabCommandIndexes as $crontabCommandIndex) {
			foreach ($crontabCommandDelayIndexes as $crontabCommandDelayIndex) {
				$crontabCommands[] = '* * * * * root sleep ' . $crontabCommandDelayIndex . ' && sudo ' . $parameters['binary_files']['php'] . ' /usr/local/nodecompute/node_action_process_node_process_bitcoin_cash_cryptocurrency_block_header.php _' . $nodeProcessBitcoinCashCryptocurrencyBlockTemplate['height'] . ' nodecompute_node_process_bitcoin_cash_cryptocurrency';
			}
		}

		$crontabCommands = implode("\n", $crontabCommands);

		if (file_put_contents('/etc/crontab', $crontabCommands) === false) {
			echo 'Error adding crontab commands, please try again.';
			return $response;
		}

		shell_exec('sudo ' . $parameters['binary_files']['crontab'] . ' /etc/crontab');
		$response['message'] = 'Node process Bitcoin Cash cryptocurrency block data processed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'process_node_process_bitcoin_cash_cryptocurrency_block_data') === true) {
		$response = _processNodeProcessBitcoinCashCryptocurrencyBlockData($parameters, $response);
	}

	// todo: save block template to system API for NodeCompute nodes without bitcoin-cli installed
?>
