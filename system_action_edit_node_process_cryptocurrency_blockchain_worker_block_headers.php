<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_worker_block_headers'
	), $parameters['system_databases'], $response);

	function _editNodeProcessCryptocurrencyBlockchainWorkerBlockHeaders($parameters, $response) {
		foreach ($parameters['data'] as $nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderKey => $nodeProcessCryptocurrencyBlockchainWorkerBlockHeader) {
			$parameters['data'][$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderKey]['node_node_id'] = $parameters['node']['id'];
		}

		$response['data'] = _save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_block_headers']
		), $response);
		$response['message'] = 'Node process cryptocurrency blockchain worker block headers edited successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'edit_node_process_cryptocurrency_blockchain_worker_block_headers') === true) {
		$response = _editNodeProcessCryptocurrencyBlockchainWorkerBlockHeaders($parameters, $response);
	}
?>
