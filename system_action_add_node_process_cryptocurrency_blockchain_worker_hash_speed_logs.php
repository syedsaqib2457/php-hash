<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_worker_block_headers',
		'node_process_cryptocurrency_blockchain_worker_hash_speed_logs'
	), $parameters['system_databases'], $response);

	function _addNodeProcessCryptocurrencyBlockchainWorkerHashSpeedLogs($parameters, $response) {
		if (empty($parameters['node_authentication_token']) === true) {
			return $response;
		}

		foreach ($parameters['data'] as $nodeProcessCryptocurrencyBlockchainWorkerHashSpeedLogKey => $nodeProcessCryptocurrencyBlockchainWorkerHashSpeedLog) {
			$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader = _list(array(
				'data' => array(
					'node_node_id'
				),
				'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_block_headers'],
				'limit' => 1,
				'sort' => array(
					'modified' => 'descending'
				),
				'where' => array(
					'node_id' => $parameters['node']['id'],
					'node_process_type' => $nodeProcessCryptocurrencyBlockchainWorkerHashSpeedLog['node_process_type']
				)
			), $response);
			$nodeProcessCryptocurrencyBlockchainWorkerBlockHeader = current($nodeProcessCryptocurrencyBlockchainWorkerBlockHeader);

			if (empty($nodeProcessCryptocurrencyBlockchainWorkerBlockHeader) === true) {
				unset($parameters['data'][$nodeProcessCryptocurrencyBlockchainWorkerHashSpeedLogKey]);
				continue;
			}

			$parameters['data'][$nodeProcessCryptocurrencyBlockchainWorkerHashSpeedLogKey]['id'] = _createUniqueId();
			$parameters['data'][$nodeProcessCryptocurrencyBlockchainWorkerHashSpeedLogKey]['node_id'] = $parameters['node']['id'];
			$parameters['data'][$nodeProcessCryptocurrencyBlockchainWorkerHashSpeedLogKey]['node_node_id'] = $nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['node_node_id'];
			unset($parameters['data'][$nodeProcessCryptocurrencyBlockchainWorkerHashSpeedLogKey]['created_timestamp']);
			unset($parameters['data'][$nodeProcessCryptocurrencyBlockchainWorkerHashSpeedLogKey]['modified_timestamp']);
		}

		// todo
	}

	if (($parameters['action'] === 'add_node_process_cryptocurrency_blockchain_worker_hash_speed_logs') === true) {
		$response = _addNodeProcessCryptocurrencyBlockchainWorkerHashSpeedLogs($parameters, $response);
	}
?>
