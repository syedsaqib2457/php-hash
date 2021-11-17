<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'node_process_node_user_request_logs'
	), $parameters['databases'], $response);

	function _addNodeProcessNodeUserRequestLogs($parameters, $response) {
		// todo
		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'activated_status' => true,
				'authentication_token' => true,
				'deployed_status' => true,
				'external_ip_address_version_4' => true,
				'external_ip_address_version_4_type' => true,
				'external_ip_address_version_6' => true,
				'external_ip_address_version_6_type' => true,
				'id' => true,
				'internal_ip_address_version_4' => true,
				'internal_ip_address_version_4_type' => true,
				'internal_ip_address_version_6' => true,
				'internal_ip_address_version_6_type' => true,
				'node_id' => true
			)),
			'in' => $parameters['databases']['nodes']
		), $response);
		$response['message'] = 'Node process node user request logs added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_process_node_user_request_logs') === true) {
		$response = _addNodeProcessNodeUserRequestLogs($parameters, $response);
		_output($response);
	}
?>
