<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'node_request_destinations'
	), $parameters['databases'], $response);
	require_once('/var/www/ghostcompute/system_action_validate_request_destination_address.php');

	function _addNodeRequestDestination($parameters, $response) {
		if (empty($parameters['data']['address']) === true) {
			$response['message'] = 'Node request destination must have an address.';
			_output($response);
		}

		// todo: add validation with reference to _validateRequestDestinationAddress
		$parameters['data']['id'] = random_bytes(10) . time() . random_bytes(10);
		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'address' => true,
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
