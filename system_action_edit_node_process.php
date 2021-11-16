<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'node_processes'
	), $parameters['databases'], $response);
	require_once('/var/www/ghostcompute/system_action_validate_port_number.php');

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

		if (empty($parameters['data']['type']) === true) {
			$response['message'] = 'Node process must have a type, please try again.';
			return $response;
		}

		if (in_array(strval($parameters['data']['type']), array(
			'http_proxy',
			'load_balancer',
			'recursive_dns',
			'socks_proxy'
		)) === false) {
			$response['message'] = 'Invalid node process type, please try again.';
			return $response;
		}

		_update(array(
			'data' => array_intersect_key($parameters['data'], array(
				'port_number' => true,
				'type' => true
			)),
			'in' => $parameters['databases']['node_processes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$nodeProcess = _list(array(
			'in' => $parameters['databases']['node_processes'],
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
		_output($response);
	}
?>
