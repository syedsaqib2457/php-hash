<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_worker_block_headers',
		'node_process_cryptocurrency_blockchain_worker_settings'
	), $parameters['system_databases'], $response);

	function _listNodeProcessCryptocurrencyBlockchainWorkerBlockHeaders($parameters, $response) {
		$parameters['where']['node_id'] = $parameters['node']['id'];
		$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders = _list(array(
			'data' => array(
				'current_block_hash',
				'id',
				'modified_timestamp',
				'next_block_height',
				'next_block_maximum_timestamp',
				'next_block_merkle_root_hash',
				'next_block_minimum_timestamp',
				'next_block_target_hash',
				'next_block_target_hash_bits',
				'next_block_transaction',
				'next_block_version',
				'node_process_type',
				'public_key_script'
			),
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_block_headers'],
			'where' => $parameters['where']
		), $response);
		$nodeProcessCryptocurrencyBlockchainWorkerSettings = _list(array(
			'data' => array(
				'block_headers_per_node_process_count',
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
			if (empty($response['data'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']][$nodeProcessCryptocurrencyBlockchainWorkerIndexes[$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']][($nodeProcessCryptocurrencyBlockchainWorkerSettings[$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']]['block_headers_per_node_process_count'] - 1)]) === false) {
				$nodeProcessCryptocurrencyBlockchainWorkerIndexes[$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']]++;
			}

			$response['data'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']][$nodeProcessCryptocurrencyBlockchainWorkerIndexes[$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_process_type']][] = $nodeProcessCryptocurrencyBlockchainWorkerBlockHeader;
			// todo: parse for node_action_process_node_process_bitcoin_cash_cryptocurrency_block_header
		}

		$response['message'] = 'Node process cryptocurrency blockchain worker block headers listed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list_node_process_cryptocurrency_blockchain_worker_block_headers') === true) {
		$response = _listNodeProcessCryptocurrencyBlockchainWorkerBlockHeaders($parameters, $response);
	}
?>
