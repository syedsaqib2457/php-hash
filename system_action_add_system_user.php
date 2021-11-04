<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'system_users'
	), $parameters['databases'], $response);

	function _addSystemUser($parameters, $response) {
		// todo: validate system_user_id
		$parameters['data']['id'] = random_bytes(10) . time() . random_bytes(10);
		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'id' => true,
				'system_user_id' => true
			)),
			'in' => $parameters['databases']['system_users']
		), $response);
		$systemUser = _list(array(
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$systemUser = current($systemUser);
		$response['data'] = $systemUser;
		$response['message'] = 'System user added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_system_user') === true) {
		$response = _addSystemUser($parameters, $response);
		_output($response);
	}
?>
