<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_mining_block_headers'
	), $parameters['system_databases'], $response);

	function _countNodeProcessCryptocurrencyMiningBlockHeaders($parameters, $response) {
		$parameters['where']['node_node_id'] = $parameters['node']['id'];
		$response['data'] = _count(array(
			'in' => $parameters['system_databases']['node_process_cryptocurrency_mining_block_headers'],
			'where' => $parameters['where']
		), $response);
		$response['message'] = 'Node processes cryptocurrency mining block headers counted successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'count_node_process_cryptocurrency_mining_block_headers') === true) {
		$response = _countNodeProcessCryptocurrencyMiningBlockHeaders($parameters, $response);
	}
?>
