<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'system_user_authentication_tokens',
		'system_users'
	), $parameters['system_databases'], $response);

	function _addSystemUserAuthenticationToken($parameters, $response) {
		if (empty($parameters['data']['system_user_id']) === true) {
			$response['message'] = 'System user authentication token must have a system user ID, please try again.';
			return $response;
		}

		$systemUserCount = _count(array(
			'in' => $parameters['system_databases']['system_users'],
			'where' => array(
				'id' => $parameters['data']['system_user_id']
			)
		), $response);

		if (($systemUserCount > 0) === false) {
			$response['message'] = 'Invalid system user ID, please try again.';
			return $response;
		}

		$systemUserSystemUserCount = _count(array(
			'in' => $parameters['system_databases']['system_user_system_users'],
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

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['system_user_authentication_tokens']
		), $response);
		$systemUserAuthenticationToken = _list(array(
			'in' => $parameters['system_databases']['system_user_authentication_tokens'],
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
	}
?>
