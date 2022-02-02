<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_worker_settings',
		'nodes'
	), $parameters['system_databases'], $response);

	function _editNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response) {
		if (empty($parameters['where']['node_id']) === true) {
			$response['message'] = 'Node process cryptocurrency blockchain worker settings must have a node ID, please try again.';
			return $response;
		}

		$node = _list(array(
			'data' => array(
				'id',
				'node_id'
			),
			'in' => $parameters['system_databases']['nodes'],
			'where' => array(
				'id' => $parameters['where']['node_id']
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node ID, please try again.';
			return $response;
		}

		if (empty($node['node_id']) === false) {
			$parameters['where']['id'] = $node['node_id'];
		}

		// todo
		return $response;
	}

	if (($parameters['action'] === 'edit_node_process_cryptocurrency_blockchain_worker_settings') === true) {
		$response = _editNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response);
	}
?>
