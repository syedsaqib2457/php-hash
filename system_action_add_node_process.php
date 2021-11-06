<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'node_processes'
	), $parameters['databases'], $response);
	require_once('/var/www/ghostcompute/system_action_validate_port_number.php');

	function _addNodeProcess($parameters, $response) {
		$parameters['data']['id'] = random_bytes(10) . time() . random_bytes(10);

		if (empty($parameters['data']['node_id']) === true) {
			$response['message'] = 'Node process must have a node ID, please try again.';
			_output($response);
		}

		if (empty($parameters['data']['port_number']) === true) {
			$response['message'] = 'Node process must have a port number, please try again.';
			_output($response);
		}

		$node = _list(array(
			'columns' => array(
				'node_id',
				'node_node_id'
			),
			'in' => $parameters['databases']['node_processes'],
			'where' => array(
				'node_id' => $parameters['data']['node_id']
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node process node ID, please try again.';
			return $response;
		}

		$nodeIds = array_filter($node);

		// todo: validate node process data

		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'id' => true,
				'node_id' => true
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
