<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_block_processing_logs'
	), $parameters['system_databases'], $response);

	function _processNodeProcessCryptocurrencyBlockchainBlockProcessingLogs($parameters, $response) {
		if (empty($parameters['node_authentication_token']) === true) {
			return $response;
		}

		foreach ($parameters['data'] as $nodeProcessCryptocurrencyBlockchainBlockProcessingLogKey => $nodeProcessCryptocurrencyBlockchainBlockProcessingLog) {
			$existingNodeProcessCryptocurrencyBlockchainBlockProcessingLogCount = _count(array(
				'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_block_processing_logs'],
				'where' => array(
					'id' => $nodeProcessCryptocurrencyBlockchainBlockProcessingLog['id'],
					'processed_status' => '0'
				)
			), $response);

			if (($existingNodeProcessCryptocurrencyBlockchainBlockProcessingLogCount === 0) === true) {
				unset($parameters['data'][$nodeProcessCryptocurrencyBlockchainBlockProcessingLogKey]);
				continue;
			}

			if (empty($nodeProcessCryptocurrencyBlockchainBlockProcessingLog['response']) === true) {
				$nodeProcessCryptocurrencyBlockchainBlockProcessingLog['response'] = null;
			}

			$parameters['data'][$nodeProcessCryptocurrencyBlockchainBlockProcessingLogKey] = array(
				'block' => $nodeProcessCryptocurrencyBlockchainBlockProcessingLog['block'],
				'id' => $nodeProcessCryptocurrencyBlockchainBlockProcessingLog['id'],
				'processed_status' => '1',
				'response' => $nodeProcessCryptocurrencyBlockchainBlockProcessingLog['response']
			);
		}

		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_block_processing_logs']
		), $response);
		$response['message'] = 'Node process cryptocurrency blockchain block processing logs processed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'process_node_process_cryptocurrency_blockchain_block_processing_logs') === true) {
		$response = _processNodeProcessCryptocurrencyBlockchainBlockProcessingLogs($parameters, $response);
	}
?>
