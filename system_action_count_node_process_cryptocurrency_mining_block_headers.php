<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_processes'
	), $parameters['system_databases'], $response);

	function _countNodeProcessCryptocurrencyMiningBlockHeaders($parameters, $response) {
		// todo
	}

	if (($parameters['action'] === 'count_node_process_cryptocurrency_mining_block_headers') === true) {
		$response = _countNodeProcessCryptocurrencyMiningBlockHeaders($parameters, $response);
	}
?>
