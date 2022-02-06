<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'list_node_process_cryptocurrency_blockchain_block_processing_logs'
	), $parameters['system_databases'], $response);

	function _listNodeProcessCryptocurrencyBlockchainBlockProcessingLogs($parameters, $response) {
		if (empty($parameters['pagination']['results_page_number']) === true) {
			$parameters['pagination']['results_per_page_number'] = 1;
		}

		if (empty($parameters['pagination']['results_per_page_count']) === true) {
			$parameters['pagination']['results_per_page_count'] = 100;
		}

		$parameters['pagination']['results_total_count'] = _count(array(
			'in' => $parameters['system_databases']['list_node_process_cryptocurrency_blockchain_block_processing_logs'],
			'where' => $parameters['where']
		), $response);

		if (empty($parameters['node_authentication_token']) === false) {
			$parameters['where']['node_node_id'] = $parameters['node']['id'];
		}

		$nodeProcessCryptocurrencyBlockchainBlockProcessingLogs = _list(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['list_node_process_cryptocurrency_blockchain_block_processing_logs'],
			'limit' => $parameters['pagination']['results_per_page_count'],
			'offset' => (($parameters['pagination']['results_per_page_number'] - 1) * $parameters['pagination']['results_per_page_count']),
			'sort' => $parameters['sort'],
			'where' => $parameters['where']
		), $response);
		$response['data'] = $nodeProcessCryptocurrencyBlockchainBlockProcessingLogs;
		$response['message'] = 'Node process cryptocurrency blockchain block processing logs successfully.';
		$response['pagination'] = $parameters['pagination'];
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list_node_process_cryptocurrency_blockchain_block_processing_logs') === true) {
		$response = _listNodeProcessCryptocurrencyBlockchainBlockProcessingLogs($parameters, $response);
	}
?>
