<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_worker_block_headers'
	), $parameters['system_databases'], $response);

	function _listNodeProcessCryptocurrencyBlockchainWorkerBlockHeaders($parameters, $response) {
		$parameters['where']['node_node_id'] = $parameters['node']['id'];
		$response['data'] = _list(array(
			'data' => array(
				'id',
				'public_key_script'
			),
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_block_headers'],
			'where' => $parameters['where']
		), $response);
		$response['message'] = 'Node process cryptocurrency blockchain worker block headers listed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list_node_process_cryptocurrency_blockchain_worker_block_headers') === true) {
		$response = _listNodeProcessCryptocurrencyBlockchainWorkerBlockHeaders($parameters, $response);
	}
?>
