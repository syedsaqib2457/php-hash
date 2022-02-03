<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_worker_block_headers',
		'node_process_cryptocurrency_blockchain_worker_settings'
	), $parameters['system_databases'], $response);

	function _listNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response) {
		$parameters['where']['node_id'] = $parameters['node']['id'];
		$nodeProcessCryptocurrencyBlockchainWorkerSettings = _list(array(
			'data' => array(
				'block_header_part_count',
				'node_process_type'
			),
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_settings'],
			'where' => $parameters['where']
		), $response);

		foreach ($nodeProcessCryptocurrencyBlockchainWorkerSettings as $nodeProcessCryptocurrencyBlockchainWorkerSettingKey => $nodeProcessCryptocurrencyBlockchainWorkerSetting) {
			$nodeProcessCryptocurrencyBlockchainWorkerSetting['node_process_count'] = _count(array(
				'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_block_headers'],
				'where' => array(
					'node_id' => $parameters['node']['id'],
					'node_process_type' => $nodeProcessCryptocurrencyBlockchainWorkerSetting['node_process_type']
				)
			), $response);
			$response['data'][$nodeProcessCryptocurrencyBlockchainWorkerSetting['node_process_type']] = ceil($nodeProcessCryptocurrencyBlockchainWorkerSetting['node_process_count'] / $nodeProcessCryptocurrencyBlockchainWorkerSetting['block_headers_per_node_process_count']);
		}

		$response['message'] = 'Node process cryptocurrency blockchain worker settings listed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list_node_process_cryptocurrency_blockchain_worker_settings') === true) {
		$response = _listNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response);
	}
?>
