<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_worker_settings'
	), $parameters['system_databases'], $response);

	function _listNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response) {
		$parameters['where']['node_id'] = $parameters['node']['id'];
		$nodeProcessCryptocurrencyBlockchainWorkerSettings = _list(array(
			'data' => array(
				'node_process_type',
				'processing_interval_seconds'
			),
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_settings'],
			'where' => $parameters['where']
		), $response);

		foreach ($nodeProcessCryptocurrencyBlockchainWorkerSettings as $nodeProcessCryptocurrencyBlockchainWorkerSettingKey => $nodeProcessCryptocurrencyBlockchainWorkerSetting) {
			$response['data'][$nodeProcessCryptocurrencyBlockchainWorkerSetting['node_process_type']] = $nodeProcessCryptocurrencyBlockchainWorkerSetting['processing_interval_seconds'];
		}

		$response['message'] = 'Node process cryptocurrency blockchain worker settings listed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list_node_process_cryptocurrency_blockchain_worker_settings') === true) {
		$response = _listNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response);
	}
?>
