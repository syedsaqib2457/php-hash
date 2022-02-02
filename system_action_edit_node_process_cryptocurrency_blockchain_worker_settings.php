<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_worker_settings',
		'nodes'
	), $parameters['system_databases'], $response);

	function _editNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response) {
		// todo
		return $response;
	}

	if (($parameters['action'] === 'edit_node_process_cryptocurrency_blockchain_worker_settings') === true) {
		$response = _editNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response);
	}
?>
