<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_block_processing_logs',
		'node_process_cryptocurrency_blockchain_worker_settings'
	), $parameters['system_databases'], $response);

	function _addNodeProcessCryptocurrencyBlockchainBlockProcessingLog($parameters, $response) {
		if (empty($parameters['node_authentication_token']) === true) {
			return $response;
		}

		$nodeProcessCryptocurrencyBlockchainBlockProcessingLogsCount = _count(array(
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_block_processing_logs'],
			'where' => array(
				'node_id' => $parameters['data']['node_id'],
				'node_node_id' => $parameters['data']['node_node_id'],
				'node_process_type' => $parameters['data']['node_process_type'],
				'processed_status' => '0'
			)
		), $response);
		$nodeProcessCryptocurrencyBlockchainWorkerSettingsCount = _count(array(
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_settings'],
			'where' => array(
				'node_id' => $parameters['data']['node_id'],
				'node_node_id' => $parameters['data']['node_node_id'],
				'node_process_type' => $parameters['data']['node_process_type']
			)
		), $response);

		if (
			(($nodeProcessCryptocurrencyBlockchainBlockProcessingLogsCount > 10) === true) ||
			(($nodeProcessCryptocurrencyBlockchainWorkerSettingsCount === '0') === true)
		) {
			$response['message'] = 'Invalid node process cryptocurrency blockchain block processing log, please try again.';
			return $response;
		}

		$parameters['data']['node_id'] = $parameters['node']['id'];
		$parameters['data']['processed_status'] = '0';
		// todo
	}

	if (($parameters['action'] === 'add_node_process_cryptocurrency_blockchain_block_processing_log') === true) {
		$response = _addNodeProcessCryptocurrencyBlockchainBlockProcessingLog($parameters, $response);
	}
?>
