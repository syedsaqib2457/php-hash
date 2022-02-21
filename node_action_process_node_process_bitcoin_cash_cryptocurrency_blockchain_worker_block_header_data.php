<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _createReverseByteOrderHexidecimalString($hexidecimalString) {
		$binaryString = hex2bin($hexidecimalString);
		$binaryString = strrev($binaryString);
		return bin2hex($binaryString);
	}

	function _processNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderData($parameters, $response) {
		$systemActionListNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersParameters = array(
			'action' => 'list_node_process_cryptocurrency_blockchain_worker_block_headers',
			'node_authentication_token' => $parameters['node_authentication_token'],
			'where' => array(
				'node_process_type' => 'bitcoin_cash_cryptocurrency_blockchain'
			)
		);
		$systemActionListNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersParameters = json_encode($systemActionListNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersParameters);
		shell_exec('sudo ' . $parameters['binary_files']['wget'] . ' -O /usr/local/nodecompute/system_action_list_node_process_bitcoin_cash_cryptocurrency_blockchain_worker_block_headers_response.json --no-dns-cache --post-data \'json=' . $systemActionListNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersParameters . '\' --timeout=10 ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php');
		$systemActionListNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeadersResponse = file_get_contents('/usr/local/nodecompute/system_action_list_node_process_bitcoin_cash_cryptocurrency_blockchain_worker_block_headers_response.json');
		$systemActionListNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeadersResponse = json_decode($systemActionListNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeadersResponse, true);

		if (empty($systemActionListNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeadersResponse['data']) === true) {
			$response['message'] = 'Error listing node process Bitcoin Cash cryptocurrency blockchain worker block headers, please try again.';
			return $response;
		}

		while (true) {
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate = false;
			exec('sudo /usr/local/nodecompute/bitcoin_cash/bin/bitcoin-cli -conf=/usr/local/nodecompute/bitcoin_cash/bitcoin.conf getblocktemplate 2>&1', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate);
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate = implode('', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate);
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate = json_decode($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate, true);

			if (isset($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['version']) === false) {
				$response['message'] = 'Error processing node process Bitcoin Cash cryptocurrency blockchain block template, please try again.';
				return $response;
			}

			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockSize = 0;
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransactionIds = array();
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
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransaction['hash'] = hex2bin($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransaction['hash']);

				if ($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransaction['hash'] === false) {
					$response['message'] = 'Error listing node process Bitcoin Cash cryptocurrency blockchain block template transactions, please try again.';
					return $response;
				}

				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransaction['hash'] = strrev($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransaction['hash']);
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransactionIds[] = $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransaction['hash'];
			}

			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount = dechex($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount);
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCountLength = strlen($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount);

			if ((($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCountLength % 2) === 1) === true) {
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount = '0' . $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount;
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCountLength++;
			}

			$compactSizeUnsignedIntegerHexidecimalPrefixes = array(
				2 => '',
				4 => 'fd',
				6 => 'fe',
				8 => 'fe',
				10 => 'ff'
			);
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
			$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderData = array();

			foreach ($systemActionListNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeadersResponse['data'] as $systemActionListNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader) {
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockCoinbaseScript = '0' . $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_height_size'] . $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_height'] . '6e6f6465636f6d707574655f' . $systemActionListNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['id'] . $systemActionListNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['modified_timestamp'] . hrtime(true);
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockCoinbaseScriptSize = (strlen($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockCoinbaseScript) / 2);
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockCoinbaseScriptSize = sprintf('%02x', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockCoinbaseScriptSize);
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardPublicKeyScript = $systemActionListNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['public_key_script'];
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardPublicKeyScriptSize = (strlen($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardPublicKeyScript) / 2);
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardPublicKeyScriptSize = sprintf('%02x', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardPublicKeyScriptSize);
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardTransaction = '01000000010000000000000000000000000000000000000000000000000000000000000000ffffffff' . $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockCoinbaseScriptSize . $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockCoinbaseScript . 'ffffffff01' . $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_reward_amount'] . $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardPublicKeyScriptSize . $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardPublicKeyScript . '00000000';
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds = $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplateTransactionIds;
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardTransactionId = hex2bin($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardTransaction);

				if ($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardTransactionId === false) {
					$response['message'] = 'Error listing node process Bitcoin Cash cryptocurrency blockchain block reward transaction, please try again.';
					return $response;
				}

				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardTransactionId = hash('sha256', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardTransactionId, true);
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardTransactionId = hash('sha256', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardTransactionId, true);
				array_unshift($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds, $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardTransactionId);
				$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderMerkleRootNodeCount = $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount;

				if (empty($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[1]) === true) {
					$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['merkle_root_hash'] = bin2hex($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[0]);
				} elseif ((($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount % 2) === 1) === true) {
					$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderMerkleRootNodeCount++;
					$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds[$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount] = end($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionIds);
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

				$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderData[] = array(
					'created_timestamp' => $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['curtime'],
					'current_block_hash' => $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['current_block_hash'],
					'id' => $systemActionListNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['id'],
					'modified_timestamp' => $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['curtime'],
					'next_block_height' => $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_height'],
					'next_block_maximum_timestamp' => ($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['curtime'] + 6000),
					'next_block_merkle_root_hash' => $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['merkle_root_hash'],
					'next_block_minimum_timestamp' => $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTemplate['mintime'],
					'next_block_reward_transaction' => $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockRewardTransaction,
					'next_block_target_hash' => $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['target_hash'],
					'next_block_target_hash_bits' => $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['target_hash_bits'],
					'next_block_version' => $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['version'],
					'node_process_type' => 'bitcoin_cash_cryptocurrency_worker'
				);
			}

			$nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderData = json_encode($nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderData);

			if (file_put_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_blockchain_worker_block_headers_data.json', $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderData) === true) {
				$response['message'] = 'Error adding node process Bitcoin Cash cryptocurrency blockchain worker block headers, please try again.';
				return $response;
			}

			$systemActionEditNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersParameters = array(
				'action' => 'edit_node_process_cryptocurrency_blockchain_worker_block_headers',
				'node_authentication_token' => $parameters['node_authentication_token'],
			);
			$systemActionEditNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersParameters = json_encode($systemActionEditNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersParameters);
			exec('sudo curl -s --form "data=@/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_blockchain_worker_block_headers_data.json" --form-string \'json=' . $systemActionEditNodeProcessCryptocurrencyBlockchainWorkerBlockHeadersParameters . '\' ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php 2>&1', $systemActionEditNodeProcessBitcoinCashCryptocurrencyBlockchainWorkersResponse);
			$systemActionEditNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeadersResponse = current($systemActionEditNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeadersResponse);
			$systemActionEditNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeadersResponse = json_decode($systemActionEditNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeadersResponse, true);

			if (empty($systemActionEditNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeadersResponse['valid_status']) === true) {
				$response['message'] = 'Error editing node process Bitcoin Cash cryptocurrency blockchain worker block headers, please try again.';
				return $response;
			}

			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactions = array(
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactionCount,
				$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactions
			);
			$nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactions = json_encode($nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactions);

			if (file_put_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_blockchain_block_' . $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_height'] . '_transactions_data.json', $nodeProcessBitcoinCashCryptocurrencyBlockchainBlockTransactions) === false) {
				$response['message'] = 'Error adding node process Bitcoin Cash cryptocurrency blockchain block transactions, please try again.';
				return $response;
			}

			unlink('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_blockchain_block_' . ($nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_height'] - 3) . '_transactions_data.json');
			sleep(3);
		}
	}

	if (($parameters['action'] === 'process_node_process_bitcoin_cash_cryptocurrency_blockchain_worker_block_header_data') === true) {
		$response = _processNodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeaderData($parameters, $response);
	}
?>
