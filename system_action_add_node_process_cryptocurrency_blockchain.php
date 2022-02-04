<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchains'
	), $parameters['system_databases'], $response);

	function _addNodeProcessCryptocurrencyBlockchain($parameters, $response) {
		if (empty($parameters['system_user_authentication_token']) === true) {
			return $response;
		}

		// todo
		unset($parameters['data']['created_timestamp']);
		unset($parameters['data']['modified_timestamp']);
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchains']
		), $response);
		$response['message'] = 'Node process cryptocurrency blockchain added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_process_cryptocurrency_blockchain') === true) {
		$response = _addNodeProcessCryptocurrencyBlockchain($parameters, $response);
	}
?>
