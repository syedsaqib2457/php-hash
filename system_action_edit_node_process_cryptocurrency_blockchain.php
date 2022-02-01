<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchains'
	), $parameters['system_databases'], $response);

	function _editNodeProcessCryptocurrencyBlockchain($parameters, $response) {
		// todo: editing from system API

		/*if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'Node process cryptocurrency blockchain must have an ID, please try again.';
			return $response;
		}

		$nodeProcessCryptocurrencyBlockchain = _list(array(
			'data' => array(
				'id',
				'node_id'
			),
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchains'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$nodeProcessCryptocurrencyBlockchain = current($nodeProcessCryptocurrencyBlockchain);*/

		_edit(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchains'],
			'where' => array(
				'node_node_id' => $parameters['node']['id']
			)
		), $response);
		$response['message'] = 'Node process cryptocurrency blockchain edited successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'edit_node_process_cryptocurrency_blockchain') === true) {
		$response = _editNodeProcessCryptocurrencyBlockchain($parameters, $response);
	}
?>
