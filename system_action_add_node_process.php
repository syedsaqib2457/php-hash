<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'node_processes'
	), $parameters['databases'], $response);
	require_once('/var/www/ghostcompute/system_action_validate_port_number.php');

	function _addNodeProcess($parameters, $response) {
		// todo: validate node process data
		$parameters['data']['id'] = random_bytes(10) . time() . random_bytes(10);
		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'id' => true
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
