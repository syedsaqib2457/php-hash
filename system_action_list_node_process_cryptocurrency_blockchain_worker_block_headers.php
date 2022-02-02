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
				'cpu_usage_maximum_percentage',
				'gpu_usage_maximum_percentage',
				'memory_usage_maximum_percentage',
				'node_process_type'
			),
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_settings'],
			'where' => $parameters['where']
		), $response);
		$nodeProcessCryptocurrencyBlockchainWorkerSettings = current($nodeProcessCryptocurrencyBlockchainWorkerSettings);
		end($nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders);
		$nodeProcessCryptocurrencyBlockchainWorkerIndex = ((key($nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders) + 1) / $nodeProcessCryptocurrencyBlockchainWorkerSettings['block_headers_per_node_process_count']);
		$nodeProcessCryptocurrencyBlockchainWorkerIndex = ceil($nodeProcessCryptocurrencyBlockchainWorkerIndex);
		$nodeProcessCryptocurrencyBlockchainWorkerIndexes = range(1, $nodeProcessCryptocurrencyBlockchainWorkerIndex);
		$response['data'] = array();

		foreach ($nodeProcessCryptocurrencyBlockchainWorkerIndexes as $nodeProcessCryptocurrencyBlockchainWorkerIndex) {
			$response['data'][] = array_splice($nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders, 0, $nodeProcessCryptocurrencyBlockchainWorkerSettings['block_headers_per_node_process_count']);
		}

		$response['message'] = 'Node process cryptocurrency blockchain worker block headers listed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list_node_process_cryptocurrency_blockchain_worker_block_headers') === true) {
		$response = _listNodeProcessCryptocurrencyBlockchainWorkerBlockHeaders($parameters, $response);
	}
?>
