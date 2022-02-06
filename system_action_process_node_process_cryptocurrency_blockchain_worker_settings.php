<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_worker_settings'
	), $parameters['system_databases'], $response);

	function _processNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response) {
		if (empty($_SERVER['argv'][1]) === true) {
			return $response;
		}

		// crontab every 10 mins + timeout 590s
		while (true) {
			$nodeProcessCryptocurrencyBlockchainWorkerSettings = _list(array(
				'data' => array(
					'count',
					'cpu_usage_maximum_percentage',
					'gpu_usage_maximum_percentage',
					'id',
					'node_id',
					'memory_usage_maximum_percentage',
					'node_process_type'
				),
				'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_settings'],
				'sort' => array(
					'modified_timestamp' => 'ascending'
				),
				'where' => array(
					'modified_timestamp <' => (time() - 600)
				)
			), $response);

			if (empty($nodeProcessCryptocurrencyBlockchainWorkerSettings) === true) {
				$response['message'] = 'Node process cryptocurrency blockchain worker settings processed successfully.';
				$response['valid_status'] = '1';
				return $response;
			}

			foreach ($nodeProcessCryptocurrencyBlockchainWorkerSettings as $nodeProcessCryptocurrencyBlockchainWorkerSetting) {
				// todo
			}
		}
	}

	if (($parameters['action'] === 'process_node_process_cryptocurrency_blockchain_worker_settings') === true) {
		$response = _processNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response);
	}
?>
