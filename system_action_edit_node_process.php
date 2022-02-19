<?php
	if (empty($parameters) === true) {
		exit;
	}

	// todo: delete + add node processes that aren't cryptocurrency_blockchain nodes instead of editing because foreign key is node_process_type + node_id instead of node_process_id
	$systemDatabasesConnections = _connect(array(
		'node_processes'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_processes'] = $systemDatabasesConnections['node_processes'];
	require_once('/var/www/nodecompute/system_action_add_node_process.php');
	require_once('/var/www/nodecompute/system_action_delete_node_process.php');
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
			// todo: update node_process_cryptocurrency_blockchain_socks_proxy_destinations port_number
			// todo: update node_process_forwarding_destinations port_number
			// todo: update node_process_recursive_dns_destinations port_number
		}

		if (
			(empty($parameters['data']['type']) === false) &&
			(strpos($parameters['data']['type'], 'cryptocurrency_blockchain') === false)
		) {
			$existingNodeProcessCountParameters['where'] = array(
				'either' => array(
					$existingNodeProcessCountParameters['where'],
					'type' => $parameters['data']['type']
				)
			);
		}

		$existingNodeProcessCount = _count($existingNodeProcessCountParameters, $response);

		if (($existingNodeProcessCount === 0) === false) {
			$response['message'] = 'Node process already exists on the same node, please try again.';
			return $response;
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
