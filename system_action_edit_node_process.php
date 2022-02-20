<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_processes'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_processes'] = $systemDatabasesConnections['node_processes'];
	require_once('/var/www/nodecompute/system_action_validate_port_number.php');

	function _editNodeProcess($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'Node process must have an ID, please try again.';
			return $response;
		}

		if (
			(empty($parameters['data']['port_number']) === false) &&
			(_validatePortNumber($parameters['data']['port_number']) === false)
		) {
			$response['message'] = 'Invalid node process port number, please try again.';
			return $response;
		}

		if (
			(empty($parameters['data']['type']) === false) &&
			(in_array($parameters['data']['type'], array(
				'bitcoin_cryptocurrency_blockchain',
				'bitcoin_cash_cryptocurrency_blockchain',
				'http_proxy',
				'load_balancer',
				'recursive_dns',
				'socks_proxy'
			)) === false)
		) {
			$response['message'] = 'Invalid node process type, please try again.';
			return $response;
		}

		$nodeProcess = _list(array(
			'data' => array(
				'node_id',
				'node_node_id',
				'port_number',
				'type'
			),
			'in' => $parameters['system_databases']['node_processes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$nodeProcess = current($nodeProcess);

		if (empty($nodeProcess) === true) {
			$response['message'] = 'Invalid node process ID, please try again.';
			return $response;
		}

		$existingNodeProcessCountParameters = array(
			'in' => $parameters['system_databases']['node_processes'],
			'where' => array(
				'id !=' => $parameters['where']['id'],
				'node_id' => $nodeProcess['node_id']
			)
		);

		if (empty($parameters['data']['port_number']) === false) {
			$existingNodeProcessCountParameters['where']['port_number'] = $parameters['data']['port_number'];
		}

		$existingNodeProcessCount = _count($existingNodeProcessCountParameters, $response);

		if (($existingNodeProcessCount === 0) === false) {
			$response['message'] = 'Node process already exists on the same node, please try again.';
			return $response;
		}

		if (empty($parameters['data']['node_id']) === true) {
			$parameters['data']['node_id'] = $nodeProcess['node_id'];
		}

		if (empty($parameters['data']['port_number']) === true) {
			$parameters['data']['port_number'] = $nodeProcess['port_number'];
		}

		if (empty($parameters['data']['type']) === true) {
			$parameters['data']['type'] = $nodeProcess['type'];
		}

		// todo: validate node processed_status = '1' for node_node_id
		// todo: set $parameters['data']['node_node_id'] from node[id]

		if (
			(($nodeProcess['node_id'] === $parameters['data']['node_id']) === false) ||
			(($nodeProcess['port_number'] === $parameters['data']['port_number']) === false) ||
			(($nodeProcess['type'] === $parameters['data']['type']) === false)
		) {
			if (
				(($nodeProcess['type'] === $parameters['data']['type']) === false) &&
				(
					((strpos($nodeProcess['type'], 'cryptocurrency_blockchain') === false) === false) ||
					((strpos($parameters['data']['type'], 'cryptocurrency_blockchain') === false) === false)
				)
			) {
				$response['message'] = 'Invalid node process type, please try again.';
				return $response;
			}

			if ((strpos($nodeProcess['type'], 'cryptocurrency_blockchain') === false) === false) {
				// todo: update node_process_cryptocurrency_blockchain_socks_proxy_destinations with $parameters['data']['node_id'] + $parameters['data']['port_number']
				// todo: update node_process_cryptocurrency_blockchains with $parameters['data']['node_id']
			} else {
				$systemDatabaseNames = array(
					'node_process_node_user_authentication_credentials',
					'node_process_node_user_authentication_sources',
					'node_process_node_user_request_destination_logs',
					'node_process_node_user_node_request_destinations',
					'node_process_node_user_node_request_limit_rules',
					'node_process_node_users',
				);

				foreach ($systemDatabaseNames as $systemDatabaseName) {
					_edit(array(
						'data' => array(
							'node_id' => $parameters['data']['node_id'],
							'node_node_id' => $parameters['data']['node_node_id'],
							'node_process_type' => $parameters['data']['type']
						),
						'in' => $parameters['system_databases'][$systemDatabaseName],
						'where' => array(
							'node_id' => $nodeProcess['node_id'],
							'node_process_type' => $nodeProcess['type']
						)
					), $response);
				}

				// todo: update node_process_* databases with $parameters['data']['node_id'] + $parameters['data']['type']
				// todo: update node_process_cryptocurrency_blockchain_socks_proxy_destinations $parameters['data']['node_id'] + $parameters['data']['port_number'] + $parameters['data']['type']
				// todo: update node_process_forwarding_destinations $parameters['data']['node_id'] + $parameters['data']['port_number'] + $parameters['data']['type']
				// todo: update node_process_recursive_dns_destinations $parameters['data']['node_id'] + $parameters['data']['port_number'] + $parameters['data']['type']
			}
		}

		_edit(array(
			'data' => array(
				'port_number' => $parameters['data']['port_number'],
				'type' => $parameters['data']['type']
			),
			'in' => $parameters['system_databases']['node_processes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$nodeProcess = _list(array(
			'in' => $parameters['system_databases']['node_processes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$nodeProcess = current($nodeProcess);
		$response['data'] = $nodeProcess;
		$response['message'] = 'Node process edited successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'edit_node_process') === true) {
		$response = _editNodeProcess($parameters, $response);
	}
?>
