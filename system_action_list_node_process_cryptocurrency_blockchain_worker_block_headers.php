<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_worker_block_headers',
		'node_process_cryptocurrency_blockchain_worker_settings'
	), $parameters['system_databases'], $response);

	function _listNodeProcessCryptocurrencyBlockchainWorkerBlockHeaders($parameters, $response) {
		if (empty($parameters['node_authentication_token']) === true) {
			return $response;
		}

		if (empty($parameters['where']['node_process_type']) === true) {
			$parameters['where']['node_id'] = $parameters['node']['id'];
		} else {
			$parameters['where']['node_node_id'] = $parameters['node']['id'];
		}

		$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders = _list(array(
			'data' => array(
				'current_block_hash',
				'id',
				'next_block_height',
				'next_block_maximum_timestamp',
				'next_block_merkle_root_hash',
				'next_block_minimum_timestamp',
				'next_block_reward_transaction',
				'next_block_target_hash',
				'next_block_target_hash_bits',
				'next_block_version',
				'node_process_type',
				'public_key_script'
			),
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_block_headers'],
			'where' => $parameters['where']
		), $response);
		$response['message'] = 'Node process cryptocurrency blockchain worker block headers listed successfully.';
		$response['valid_status'] = '1';

		if (empty($parameters['where']['node_process_type']) === false) {
			$response['data'] = $nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders;
			return $response;
		}

		$nodeProcessCryptocurrencyBlockchainWorkerSettings = _list(array(
			'data' => array(
				'count',
				'node_process_type'
			),
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_settings'],
			'where' => $parameters['where']
		), $response);
		$nodeProcessCryptocurrencyBlockchainWorkerIndexes = array();

		foreach ($nodeProcessCryptocurrencyBlockchainWorkerSettings as $nodeProcessCryptocurrencyBlockchainWorkerSetting) {
			$nodeProcessCryptocurrencyBlockchainWorkerIndexes[[$nodeProcessCryptocurrencyBlockchainWorkerSetting['node_process_type']] = 0;
			$nodeProcessCryptocurrencyBlockchainWorkerSettings[$nodeProcessCryptocurrencyBlockchainWorkerSetting['node_process_type']] = $nodeProcessCryptocurrencyBlockchainWorkerSetting;
		}

		foreach ($nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders as $nodeProcessCryptocurrencyBlockchainWorkerBlockHeader) {
			$nodeProcessCryptocurrencyBlockchainWorkerIndexes[$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']]++;

			if (($nodeProcessCryptocurrencyBlockchainWorkerIndexes[$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']] === $nodeProcessCryptocurrencyBlockchainWorkerSettings['count']) === true) {
				$nodeProcessCryptocurrencyBlockchainWorkerIndexes[$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']] = 0;
			}

			$response['data'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']]['next_block_header_parts'][$nodeProcessCryptocurrencyBlockchainWorkerIndexes[$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']]][] = $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_version'] . $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['current_block_hash'] . $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_merkle_root_hash'];

			if (empty($response['data'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']]['next_block_target_hash']) === true) {
				$response['data'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']]['next_block_target_hash'] = $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_target_hash'];
				$response['data'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']]['next_block_target_hash_bits'] = $nodeProcessBitcoinCashCryptocurrencyBlockchainWorkerBlockHeader['next_block_target_hash_bits'];
				$response['data'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']]['next_block_timestamps'] = range($nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['next_block_minimum_timestamp'], $nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['next_block_maximum_timestamp']);

				foreach ($response['data'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']]['next_block_timestamps'] as $nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderTimestampKey => $nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderTimestamp) {
					$response['data'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']]['next_block_timestamps'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderTimestampKey] = sprintf('%08x', $nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderTimestamp);
					$response['data'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']]['next_block_timestamps'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderTimestampKey] = hex2bin($response['data'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']]['timestamps'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderTimestampKey]);
					$response['data'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']]['next_block_timestamps'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderTimestampKey] = strrev($response['data'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']]['timestamps'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderTimestampKey]);
					$response['data'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']]['next_block_timestamps'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderTimestampKey] = bin2hex($response['data'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']]['timestamps'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderTimestampKey]);
				}

				$response['data'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']]['next_block_reward_transaction'] = $nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['next_block_reward_transaction'];
			}
		}

		return $response;
	}

	if (($parameters['action'] === 'list_node_process_cryptocurrency_blockchain_worker_block_headers') === true) {
		$response = _listNodeProcessCryptocurrencyBlockchainWorkerBlockHeaders($parameters, $response);
	}
?>
