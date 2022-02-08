<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_worker_hash_speed_logs'
	), $parameters['system_databases'], $response);

	function _addNodeProcessCryptocurrencyBlockchainWorkerHashSpeedLogs($parameters, $response) {
		if (empty($parameters['node_authentication_token']) === true) {
			return $response;
		}

		foreach ($parameters['data'] as $nodeProcessCryptocurrencyBlockchainWorkerHashSpeedLog) {
			$nodeProcessCryptocurrencyBlockchainWorkerHashSpeedLog['node_id'] = $parameters['node']['id'];
		}

		// todo
	}

	if (($parameters['action'] === 'add_node_process_cryptocurrency_blockchain_worker_hash_speed_logs') === true) {
		$response = _addNodeProcessCryptocurrencyBlockchainWorkerHashSpeedLogs($parameters, $response);
	}
?>
