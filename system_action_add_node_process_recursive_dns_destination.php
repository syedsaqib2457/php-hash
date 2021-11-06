<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'node_process_recursive_dns_destinations'
	), $parameters['databases'], $response);

	function _addNodeProcessRecursiveDnsDestination($parameters, $response) {
		$parameters['data']['id'] = random_bytes(10) . time() . random_bytes(10);
		// todo: validate + save data
		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'id' => true
			)),
			'in' => $parameters['databases']['node_process_recursive_dns_destinations']
		), $response);
		$nodeProcessRecursiveDnsDestination = _list(array(
			'in' => $parameters['databases']['node_process_recursive_dns_destinations'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeProcessRecursiveDnsDestination = current($nodeProcessRecursiveDnsDestination);
		$response['data'] = $nodeProcessRecursiveDnsDestination;
		$response['message'] = 'Node process recursive DNS destination added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_process_recursive_dns_destination') === true) {
		$response = _addNodeProcessRecursiveDnsDestination($parameters, $response);
		_output($response);
	}
?>
