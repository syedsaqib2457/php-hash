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
		$parameters['data']['string'] = time() . random_bytes(mt_rand(10, 25)) . uniqid();

		if (empty($parameters['data']['system_user_id']) === true) {
			$response['message'] = 'System user authentication token must have a system user ID, please try again.';
			return $response;
		}

		$systemUserCount = _count(array(
			'in' => $parameters['databases']['system_users'],
			'where' => array(
				'id' => $parameters['data']['system_user_id']
			)
		), $response);

		if (($systemUserCount > 0) === false) {
			$response['message'] = 'Invalid system user ID, please try again.';
			return $response;
		}

		$systemUserSystemUserCount = _count(array(
			'in' => $parameters['databases']['system_user_system_users'],
			'where' => array(
				'system_user_id' => $parameters['data']['system_user_id'],
				'system_user_system_user_id' => $parameters['system_user_id']
			)
		), $response);

		if (
			(($systemUserSystemUserCount > 0) === false) &&
			(($parameters['system_user_id'] === $parameters['data']['system_user_id']) === false)
		) {
			$response['message'] = 'Invalid permissions to add system user authentication token, please try again.';
			return $response;
		}

		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'id' => true,
				'string' => true,
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
