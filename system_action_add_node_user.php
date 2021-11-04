<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'node_users'
	), $parameters['databases'], $response);

	function _addNodeUser($parameters, $response) { 
		// 'node_request_logs_allowed_status'
		// 'strict_authentication_required_status'
		// 'tag'
		$parameters['data']['node_request_destinations_only_allowed_status'] = strval(intval(empty($parameters['data']['strict_authentication_required_status']) === false));
		$parameters['data']['id'] = random_bytes(10) . time() . random_bytes(10);
		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'id' => true,
				'node_request_destinations_only_allowed_status' => true,
				'node_request_logs_allowed_status' => true,
				'strict_authentication_required_status' => true,
				'tag' => true
			)),
			'in' => $parameters['databases']['node_users']
		), $response);
		$nodeUser = _list(array(
			'in' => $parameters['databases']['node_users'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeUser = current($nodeUser);
		$response['data'] = $nodeUser;
		$response['message'] = 'Node user added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_user') === true) {
		$response = _addNodeUser($parameters, $response);
		_output($response);
	}
?>
