<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'system_user_authentication_tokens',
		'system_users'
	), $parameters['databases'], $response);

	function _addSystemUserAuthenticationToken($parameters, $response) {
		$parameters['data']['id'] = random_bytes(10) . time() . random_bytes(10);
		// todo: random string value

		if (empty($parameters['data']['system_user_id']) === false) {
			$systemUser = _list(array(
				'columns' => array(
					'id'
				),
				'in' => $parameters['databases']['system_users'],
				'where' => array(
					'id' => $parameters['data']['system_user_id']
				)
			), $response);
			$systemUser = current($systemUser);

			if (empty($systemUser) === true) {
				$response['message'] = 'Invalid system user ID, please try again.';
				return $response;
			}
		}

		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'id' => true,
				'system_user_id' => true
			)),
			'in' => $parameters['databases']['system_user_authentication_tokens']
		), $response);
		$systemUserAuthenticationToken = _list(array(
			'in' => $parameters['databases']['system_user_authentication_tokens'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$systemUserAuthenticationToken = current($systemUserAuthenticationToken);
		$response['data'] = $systemUserAuthenticationToken;
		$response['message'] = 'System user authentication token added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_system_user_authentication_token') === true) {
		$response = _addSystemUserAuthenticationToken($parameters, $response);
		_output($response);
	}
?>
