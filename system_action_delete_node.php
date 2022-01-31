<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_block_submission_logs',
		'node_process_cryptocurrency_blockchain_socks_proxy_destinations',
		'node_process_cryptocurrency_blockchain_wallet_payment_requests',
		'node_process_cryptocurrency_blockchain_wallet_transaction_logs',
		'node_process_cryptocurrency_blockchain_wallets',
		'node_process_cryptocurrency_blockchain_worker_block_headers',
		'node_process_cryptocurrency_blockchains',
		'node_process_forwarding_destinations',
		'node_process_node_user_authentication_credentials',
		'node_process_node_user_authentication_sources',
		'node_process_node_user_request_destination_logs',
		'node_process_node_user_node_request_destinations',
		'node_process_node_user_node_request_limit_rules',
		'node_process_node_user_request_logs',
		'node_process_node_user_resource_usage_logs',
		'node_process_node_users',
		'node_process_recursive_dns_destinations',
		'node_process_resource_usage_logs',
		'node_processes',
		'node_reserved_internal_destinations',
		'node_resource_usage_logs',
		'nodes'
	), $parameters['system_databases'], $response);

	function _deleteNode($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'Node must have an ID, please try again.';
			return $response;
		}

		if (is_string($parameters['where']['id']) === false) {
			$response['message'] = 'Invalid node ID, please try again.';
			return $response;
		}

		_delete(array(
			'in' => $parameters['system_databases']['nodes'],
			'where' => array(
				'either' => array(
					'id' => $parameters['where']['id'],
					'node_id' => $parameters['where']['id']
				)
			)
		), $response);
		_update(array(
			'data' => array(
				'added_status' => '0',
				'processed_status' => '0'
			),
			'in' => $parameters['system_databases']['node_reserved_internal_destinations'],
			'where' => array(
				'either' => array(
					'node_id' => $parameters['where']['id'],
					'node_node_id' => $parameters['where']['id']
				)
			)
		), $response);
		$systemDatabaseNames = array(
			'node_process_cryptocurrency_blockchain_block_submission_logs',
			'node_process_cryptocurrency_blockchain_socks_proxy_destinations',
			'node_process_cryptocurrency_blockchain_wallet_payment_requests',
			'node_process_cryptocurrency_blockchain_wallet_transaction_logs',
			'node_process_cryptocurrency_blockchain_wallets',
			'node_process_cryptocurrency_blockchain_worker_block_headers',
			'node_process_cryptocurrency_blockchains',
			'node_process_forwarding_destinations',
			'node_process_node_user_authentication_credentials',
			'node_process_node_user_authentication_sources',
			'node_process_node_user_request_destination_logs',
			'node_process_node_user_node_request_destinations',
			'node_process_node_user_node_request_limit_rules',
			'node_process_node_user_request_logs',
			'node_process_node_user_resource_usage_logs',
			'node_process_node_users',
			'node_process_recursive_dns_destinations',
			'node_process_resource_usage_logs',
			'node_processes',
			'node_resource_usage_logs'
		);
		$nodeCount = _count(array(
			'in' => $parameters['system_databases']['nodes'],
			'where' => array(
				'either' => array(
					'id' => $parameters['where']['id'],
					'node_id' => $parameters['where']['id']
				)
			)
		), $response);

		if ($nodeCount === 0) {
			// reset added_status instead of deleting node_reserved_internal_destinations
			$databases[] = 'node_reserved_internal_destinations';
		}

		foreach ($systemDatabaseNames as $systemDatabaseName) {
			_delete(array(
				'in' => $parameters['system_databases'][$systemDatabaseName],
				'where' => array(
					'either' => array(
						'node_id' => $parameters['where']['id'],
						'node_node_id' => $parameters['where']['id']
					)
				)
			), $response);
		}

		$response['message'] = 'Node deleted successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'delete_node') === true) {
		$response = _deleteNode($parameters, $response);
	}
?>
