<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_worker_block_headers'
	), $parameters['system_databases'], $response);

	function _editNodeProcessCryptocurrencyBlockchainWorkerBlockHeaders($parameters, $response) {
		if (isset($parameters['node_authentication_token']) === false) {
			return $response;
		}

		if (empty($_FILES['data']['tmp_name']) === true) {
			$response['message'] = 'Node process node user request logs must have a data file, please try again.';
			return $response;
		}

		$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders = file_get_contents($_FILES['data']['tmp_name']);
		$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders = json_decode($nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders, true);

		if (empty($nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders) === true) {
			$response['message'] = 'Invalid node process cryptocurrency blockchain worker block headers, please try again.';
			return $response;
		}

		foreach ($nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders as $nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderKey => $nodeProcessCryptocurrencyBlockchainWorkerBlockHeader) {
			$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders[$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderKey]['node_node_id'] = $parameters['node']['id'];
		}

		$response['data'] = _save(array(
			'data' => $nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders,
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
