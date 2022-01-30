<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _createReverseByteOrderHexidecimalString($hexidecimalString) {
		$binaryString = hex2bin($hexidecimalString);
		$binaryString = strrev($binaryString);
		return bin2hex($binaryString);
	}

	function _processNodeProcessBitcoinCashCryptocurrencyBlockchainBlockData($parameters, $response) {
		// todo: list worker block header data with id + scriptpubkey
		$systemActionCountNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderParameters = array(
			'action' => 'count_node_process_cryptocurrency_blockchain_worker_block_headers',
			'node_authentication_token' => $parameters['node_authentication_token'],
			'where' => array(
				'process_type' => 'bitcoin_cash'
			)
		);
		$encodedSystemActionCountNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderParameters = json_encode($systemActionCountNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeadersParameters);
		shell_exec('sudo ' . $parameters['binary_files']['wget'] . ' -O /usr/local/nodecompute/system_action_count_node_process_bitcoin_cash_cryptocurrency_blockchain_worker_block_headers_response.json --no-dns-cache --post-data \'json=' . $encodedSystemActionCountNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeadersParameters . '\' --timeout=10 ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php');
		$systemActionCountNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderResponse = file_get_contents('/usr/local/nodecompute/system_action_count_node_process_bitcoin_cash_cryptocurrency_blockchain_worker_block_headers_response.json');
		$systemActionCountNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderResponse = json_decode($systemActionCountNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderResponse, true);

		if (empty($systemActionCountNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeadersResponse['data']) === true) {
			$response['message'] = 'Error counting node process Bitcoin Cash cryptocurrency blockchain worker block headers, please try again.';
			return $response;
		}

		exec('sudo bitcoin-cli -conf=/usr/local/nodecompute/bitcoin_cash/bitcoin.conf getblocktemplate 2>&1', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate = implode('', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate = json_decode($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate, true);

		if (isset($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['version']) === false) {
			$response['message'] = 'Error processing node process Bitcoin Cash cryptocurrency blockchain block template, please try again.';
			return $response;
		}

		if (file_put_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_blockchain_next_block_height.dat', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['height']) === false) {
			$response['message'] = 'Error adding node process Bitcoin Cash cryptocurrency blockchain block template next block height, please try again.';
			return $response;
		}

		$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockSize = 0;
		$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount = 1;
		$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactions = '';

		foreach ($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['transactions'] as $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransactionIndex => $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransaction) {
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionSize = hex2bin($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransaction['data']);
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionSize = strlen($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionSize);
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockSize += $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionSize;

			if (
				(($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockSize > 100000) === true) ||
				(($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount === 100) === true)
			) {
				break;
			}

			if (
				(empty($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransaction['hash']) === true) ||
				(($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransaction['hash'] === $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransaction['txid']) === false)
			) {
				continue;
			}

			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount++;
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactions .= $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransaction['data'];
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['transactions'][$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransactionIndex] = $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransaction['hash'];
		}
		
		$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount = dechex($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCountLength = strlen($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount);

		if ((($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCountLength % 2) === 1) === true) {
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount = '0' . $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount;
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCountLength++;
		}

		$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount = $compactSizeUnsignedIntegerHexidecimalPrefixes[$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCountLength] . _createReverseByteOrderHexidecimalString($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader = array(
			'current_block_hash' => _createReverseByteOrderHexidecimalString($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['previousblockhash']),
			'next_block_height' => dechex($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['height']),
			'next_block_reward_amount' => sprintf('%016x', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['coinbasevalue']),
			'target_hash' => $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['target'],
			'target_hash_bits' => _createReverseByteOrderHexidecimalString($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['bits']),
			'version' => sprintf('%08x', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['version'])
		);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_height_size'] = strlen($nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_height']);

		if ((($nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_height_size'] % 2) === 1) === true) {
			$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_height'] = '0' . $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_height'];
			$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_height_size']++;
		}

		$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_height'] = _createReverseByteOrderHexidecimalString($nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_height']);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_height_size'] = ($nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_height_size'] / 2);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_reward_amount'] = _createReverseByteOrderHexidecimalString($nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_reward_amount']);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['version'] = _createReverseByteOrderHexidecimalString($nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['version']);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerIndexes = range(1, $systemActionCountNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeadersResponse['data']);

		foreach ($nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerIndexes as $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerIndex) {
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockCoinbaseScript = '0' . $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_height_size'] . $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_height'] . '6e6f6465636f6d707574655f' . $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerIndex . hrtime(true);
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockCoinbaseScriptSize = (strlen($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockCoinbaseScript) / 2);
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockCoinbaseScriptSize = sprintf('%02x', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockCoinbaseScriptSize);
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardPublicKeyScript = '[block_reward_scriptPubKey_goes_here]';
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardPublicKeyScriptSize = (strlen($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardPublicKeyScript) / 2);
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardPublicKeyScriptSize = sprintf('%02x', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardPublicKeyScriptSize);
			$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderTransaction = '01000000010000000000000000000000000000000000000000000000000000000000000000ffffffff' . $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockCoinbaseScriptSize . $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockCoinbaseScript . 'ffffffff01' . $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_reward_amount'] . $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardPublicKeyScriptSize . $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardPublicKeyScript . '00000000';
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds = array();
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[0] = hex2bin($nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderTransactions);
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[0] = hash('sha256', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[0], true);
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[0] = hash('sha256', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[0], true);
			$compactSizeUnsignedIntegerHexidecimalPrefixes = array(
				2 => '',
				4 => 'fd',
				6 => 'fe',
				8 => 'fe',
				10 => 'ff'
			);

			foreach ($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['transactions'] as $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransaction) {
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionId = hex2bin($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransaction);
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionId = strrev($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionId);

				if (empty($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionId) === true) {
					$response['message'] = 'Error listing node process Bitcoin Cash cryptocurrency blockchain block template transactions, please try again.';
					return $response;
				}

				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[] = $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionId;
			}

			$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderMerkleRootNodeCount = $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount;

			if (empty($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[1]) === true) {
				$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['merkle_root_hash'] = bin2hex($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[0]);
			} elseif ((($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount % 2) === 1) === true) {
				$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderMerkleRootNodeCount++;
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount] = $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionId;
			}

			if (($nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderMerkleRootNodeCount === 2) === true) {
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIndexes = array(
					0
				);
			} else {
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIndexes = range(0, ($nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderMerkleRootNodeCount - 1), 2);
			}

			if (empty($nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['merkle_root_hash']) === true) {
				while (true) {
					$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionMerkleRootHashIndex = 0;

					foreach ($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIndexes as $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIndex) {
						$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIndex] .= $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIndex + 1)];
						$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionId = hash('sha256', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIndex], true);
						$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionId = hash('sha256', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionId, true);
						unset($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIndex]);
						unset($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIndex + 1)]);
						$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionMerkleRootHashIndex] = $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionId;
						$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionMerkleRootHashIndex++;
					}

					$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderMerkleRootNodeCount = ($nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderMerkleRootNodeCount / 2);
					$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIndexLength = ($nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderMerkleRootNodeCount / 2);

					if (($nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderMerkleRootNodeCount === 1) === true) {
						$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['merkle_root_hash'] = bin2hex($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionId);
						break;
					}

					if ((($nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderMerkleRootNodeCount % 2) === 1) === true) {
						$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderMerkleRootNodeCount++;
						$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionMerkleRootHashIndex] = $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionId;
						$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIndexLength++;
					}

					$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIndexes = array_slice($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIndexes, 0, $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIndexLength, true);
				}
			}

			$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader = array(
				$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['target_hash'],
				$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['target_hash_bits'],
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['mintime'],
				($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['curtime'] + 6000),
				$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['version'] . $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['current_block_hash'] . $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['merkle_root_hash']
			);
			$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader = json_encode($nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader);
			// todo: add block header transaction to data
			// todo: send blockchain worker block header data
		}

		$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactions = array(
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount,
			// block header transaction is added here before block submission
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactions
		);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactions = json_encode($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactions);

		if (file_put_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_blockchain_block_transactions.json', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactions) === false) {
			$response['message'] = 'Error adding node process Bitcoin Cash cryptocurrency blockchain block transactions, please try again.';
			return $response;
		}

		/* if (file_exists('/etc/crontab') === false) {
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
				$crontabCommands[] = '* * * * * root sleep ' . $crontabCommandDelayIndex . ' && sudo ' . $parameters['binary_files']['php'] . ' /usr/local/nodecompute/node_action_process_node_process_bitcoin_cash_cryptocurrency_blockchain_worker_block_header.php _' . $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['height'] . ' nodecompute_node_process_bitcoin_cash_cryptocurrency';
			}
		}

		$crontabCommands = implode("\n", $crontabCommands);

		if (file_put_contents('/etc/crontab', $crontabCommands) === false) {
			echo 'Error adding crontab commands, please try again.';
			return $response;
		}

		shell_exec('sudo ' . $parameters['binary_files']['crontab'] . ' /etc/crontab'); */
		$response['message'] = 'Node process Bitcoin Cash cryptocurrency blockchain block data processed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'process_node_process_bitcoin_cash_cryptocurrency_blockchain_block_data') === true) {
		$response = _processNodeProcessBitcoinCashCryptocurrencyBlockchainBlockData($parameters, $response);
	}

	// todo: save block template to system API for NodeCompute nodes without bitcoin-cli installed
?>
