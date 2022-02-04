<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchains'
	), $parameters['system_databases'], $response);

	function _editNodeProcessCryptocurrencyBlockchain($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'Node process cryptocurrency blockchain must have an ID, please try again.';
			return $response;
		}

		$nodeProcessCryptocurrencyBlockchainCount = _count(array(
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchains'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);

		if (($nodeProcessCryptocurrencyBlockchainCount === '0') === true) {
			$response['message'] = 'Invalid node process cryptocurrency blockchain ID, please try again.';
			return $response;
		}

		if (empty($parameters['node_authentication_token']) === true) {
			unset($parameters['data']['block_download_progress_percentage']);
			unset($parameters['data']['created_timestamp']);
			unset($parameters['data']['node_node_id']);
			unset($parameters['data']['node_process_type']);
			unset($parameters['data']['modified_timestamp']);

			if (empty($parameters['data']['node_id']) === false) {
				$nodeProcessCryptocurrencyBlockchainNodeCount = _count(array(
					'in' => $parameters['system_databases']['nodes'],
					'where' => array(
						'node_id' => $parameters['where']['node_id']
					)
				), $response);

				if (($nodeProcessCryptocurrencyBlockchainNodeCount === '0') === true) {
					$response['message'] = 'Invalid node process cryptocurrency blockchain node ID, please try again.';
					return $response;
				}
			}
		}

		_edit(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchains'],
			'where' => array(
				'id' => $parameters['where']['id']
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
