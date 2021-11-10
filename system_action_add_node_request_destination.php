<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'node_request_destinations'
	), $parameters['databases'], $response);
	require_once('/var/www/ghostcompute/system_action_validate_hostname.php');

	function _addNodeRequestDestination($parameters, $response) {
		if (empty($parameters['data']['hostname']) === true) {
			$response['message'] = 'Node request destination must have a hostname, please try again.';
			return $response;
		}

		if (_validateHostname($parameters['data']['hostname'], true) === false) {
			$response['message'] = 'Invalid node request destination hostname, please try again.';
			return $response;
		}

		$parameters['data']['id'] = random_bytes(10) . time() . random_bytes(10);
		// todo: existing node request destination validation
		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'hostname' => true,
				'id' => true
			)),
			'in' => $parameters['databases']['node_request_destinations']
		), $response);
		$nodeRequestDestination = _list(array(
			'in' => $parameters['databases']['node_request_destinations'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeRequestDestination = current($nodeRequestDestination);
		$response['data'] = $nodeRequestDestination;
		$response['message'] = 'Node request destination added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_request_destination') === true) {
		$response = _addNodeRequestDestination($parameters, $response);
		_output($response);
	}
?>
