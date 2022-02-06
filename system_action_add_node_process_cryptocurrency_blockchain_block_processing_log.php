<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_block_processing_logs'
	), $parameters['system_databases'], $response);

	function _addNodeProcessCryptocurrencyBlockchainBlockProcessingLog($parameters, $response) {
		if (empty($parameters['node_authentication_token']) === true) {
			return $response;
		}

		$parameters['data']['node_id'] = $parameters['node']['id'];
		$parameters['data']['processed_status'] = '0';
		// todo: add node_node_id to request data
		// todo
	}

	if (($parameters['action'] === 'add_node_process_cryptocurrency_blockchain_block_processing_log') === true) {
		$response = _addNodeProcessCryptocurrencyBlockchainBlockProcessingLog($parameters, $response);
	}
?>
