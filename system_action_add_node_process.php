<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_processes',
		'nodes'
	), $parameters['system_databases'], $response);
	require_once('/var/www/ghostcompute/system_action_validate_port_number.php');

	function _addNodeProcess($parameters, $response) {
		if (empty($parameters['data']['node_id']) === true) {
			$response['message'] = 'Node process must have a node ID, please try again.';
			return $response;
		}

		if (empty($parameters['data']['port_number']) === true) {
			$response['message'] = 'Node process must have a port number, please try again.';
			return $response;
		}

		if (_validatePortNumber($parameters['data']['port_number']) === false) {
			$response['message'] = 'Invalid node process port number, please try again.';
			return $response;
		}

		if (
			(empty($parameters['data']['type']) === true) ||
			(is_string($parameters['data']['type']) === false)
		) {
			$response['message'] = 'Node process must have a type, please try again.';
			return $response;
		}

		if (in_array($parameters['data']['type'], array(
			'bitcoin_cryptocurrency',
			'http_proxy',
			'load_balancer',
			'monero_cryptocurrency',
			'recursive_dns',
			'socks_proxy'
		)) === false) {
			$response['message'] = 'Invalid node process type, please try again.';
			return $response;
		}

		if ((strpos($parameters['data']['type'], 'cryptocurrency') === false) === false) {
			// todo
		}

		$node = _list(array(
			'data' => array(
				'id',
				'node_id'
			),
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'id' => $parameters['data']['node_id']
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node process node ID, please try again.';
			return $response;
		}

		$parameters['data']['node_node_id'] = $node['node_id'];
		$existingNodeProcessCount = _count(array(
			'in' => $parameters['databases']['node_processes'],
			'where' => array_intersect_key($parameters['data'], array(
				'node_id' => true,
				'port_number' => true
			))
		), $response);

		if (($existingNodeProcessCount > 0) === true) {
			$response['message'] = 'Node process already exists, please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'id' => true,
				'node_id' => true,
				'node_node_id' => true,
				'port_number' => true,
				'type' => true
			)),
			'in' => $parameters['databases']['node_processes']
		), $response);
		$nodeProcess = _list(array(
			'in' => $parameters['databases']['node_processes'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeProcess = current($nodeProcess);
		$response['data'] = $nodeProcess;
		$response['message'] = 'Node process added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_process') === true) {
		$response = _addNodeProcess($parameters, $response);
		_output($response);
	}
?>
