<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchains',
		'nodes'
	), $parameters['system_databases'], $response);

	function _addNodeProcessCryptocurrencyBlockchain($parameters, $response) {
		if (empty($parameters['system_user_authentication_token']) === true) {
			return $response;
		}

		if (empty($parameters['data']['node_process_type']) === true) {
			$response['message'] = 'Node process cryptocurrency blockchain must have a node process type, please try again.';
			return $response;
		}

		if (in_array($parameters['data']['node_process_type'], array(
			'bitcoin_cash_cryptocurrency_blockchain'
			// 'bitcoin_cryptocurrency_blockchain'
			// 'dogecoin_cryptocurrency_blockchain'
		)) === false) {
			$response['message'] = 'Invalid node process cryptocurrency blockchain node process type, please try again.';
			return $response;
		}

		$node = _list(array(
			'data' => array(
				'id',
				'node_id'
			),
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchains'],
			'where' => array(
				'either' => array(
					'id' => $parameters['data']['node_id'],
					'node_id' => $parameters['data']['node_id']
				)
			)
		));
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node process cryptocurrency blockchain node ID, please try again.';
			return $response;
		}

		$parameters['data']['node_node_id'] = $node['id'];

		if (empty($node['node_id']) === false) {
			$parameters['data']['node_node_id'] = $node['node_id'];
		}

		$existingNodeProcessCryptocurrencyBlockchainCount = _count(array(
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchains'],
			'where' => array(
				'node_node_id' => $parameters['data']['node_node_id'],
				'node_process_type' => $parameters['data']['node_process_type']
			)
		), $response);

		if (($existingNodeProcessCryptocurrencyBlockchainCount === '1') === true) {
			$response['message'] = 'Node process cryptocurrency blockchain node process type ' . $parameters['data']['node_process_type'] . ' already exists, please try again.';
			return $response;
		}

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
