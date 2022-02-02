<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_worker_settings',
		'node_process_cryptocurrency_blockchains',
		'nodes'
	), $parameters['system_databases'], $response);

	function _editNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response) {
		if (empty($parameters['where']['node_id']) === true) {
			$response['message'] = 'Node process cryptocurrency blockchain worker settings must have a node ID, please try again.';
			return $response;
		}

		if (empty($parameters['where']['node_node_id']) === true) {
			$response['message'] = 'Node process cryptocurrency blockchain worker settings must have a node node ID, please try again.';
			return $response;
		}

		if (empty($parameters['where']['node_process_type']) === true) {
			$response['message'] = 'Node process cryptocurrency blockchain worker settings must have a node process type, please try again.';
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
			$response['message'] = 'Invalid node process cryptocurrency blockchain worker settings node ID, please try again.';
			return $response;
		}

		if (empty($node['node_id']) === false) {
			$parameters['where']['id'] = $node['node_id'];
		}

		$nodeProcessCryptocurrencyBlockchain = _list(array(
			'data' => array(
				'block_download_progress_percentage'
			),
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchains'],
			'where' => array(
				'node_node_id' => $parameters['where']['node_node_id'],
				'node_process_type' => $parameters['where']['node_process_type']
			)
		), $response);
		$nodeProcessCryptocurrencyBlockchain = current($nodeProcessCryptocurrencyBlockchain);

		if (empty($nodeProcessCryptocurrencyBlockchain) === true) {
			$response['message'] = 'Invalid node process cryptocurrency blockchain worker settings node process cryptocurrency blockchain, please try again.';
			return $response;
		}

		if (($nodeProcessCryptocurrencyBlockchain['block_download_progress_percentage'] === '100') === false) {
			$response['message'] = 'Node process cryptocurrency blockchain worker settings must have a node process cryptocurrency blockchain block download progress percentage of 100, please try again.';
			return $response;
		}

		$nodeProcessCryptocurrencyBlockchainWorkerSettings = _list(array(
			'data' => array(
				'block_headers_per_node_process_count',
				'id'
			),
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_settings'],
			'where' => array(
				'node_id' => $parameters['where']['node_id'],
				'node_process_type' => $parameters['where']['node_process_type']
			)
		), $response);
		$nodeProcessCryptocurrencyBlockchainWorkerSettings = current($nodeProcessCryptocurrencyBlockchainWorkerSettings);

		if (empty($nodeProcessCryptocurrencyBlockchainWorkerSettings['block_headers_per_node_process_count']) === true) {
			$parameters['data']['block_headers_per_node_process_count'] = '1';
		}

		$parameters['data']['id'] = _createUniqueId();

		if (empty($nodeProcessCryptocurrencyBlockchainWorkerSettings['id']) === false) {
			$parameters['data']['id'] = $nodeProcessCryptocurrencyBlockchainWorkerSettings['id'];
		}

		$parameters['data']['node_id'] = $parameters['where']['node_id'];
		$parameters['data']['node_node_id'] = $parameters['where']['node_node_id'];
		$parameters['data']['node_process_type'] = $parameters['where']['node_process_type'];
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_settings']
		), $response);
		$response['message'] = 'Node process cryptocurrency blockchain worker settings edited successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'edit_node_process_cryptocurrency_blockchain_worker_settings') === true) {
		$response = _editNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response);
	}
?>
