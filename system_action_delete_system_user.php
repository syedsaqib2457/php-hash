<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'system_user_authentication_token_scopes',
		'system_user_authentication_token_sources',
		'system_user_authentication_tokens',
		'system_user_request_logs',
		'system_users'
	), $parameters['databases'], $response);

	function _deleteSystemUser($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'System user must have an ID, please try again.';
			return $response;
		}

		if (is_string($parameters['where']['id']) === false) {
			$response['message'] = 'Invalid system user ID, please try again.';
			return $response;
		}

		$systemUserAuthenticationToken = _list(array(
			'columns' => array(
				'system_user_id'
			),
			'in' => $parameters['databases']['system_user_authentication_tokens'],
			'where' => array(
				'string' => $parameters['authentication_token']
			)
		), $response);
		$systemUserAuthenticationToken = current($systemUserAuthenticationToken);
		$systemUserCount = _count(array(
			'in' => $parameters['databases']['system_users'],
			'where' => array(
				'id' => $parameters['where']['id'],
				'system_user_id' => $systemUserAuthenticationToken['system_user_id']
			)
		), $response);

		if (($systemUserCount > 0) === false) {
			$response['message'] = 'System user must belong to system user ID ' . $systemUserAuthenticationToken['system_user_id'] . ', please try again.';
			return $response;
		}

		// todo: delete all system users created by system user
		_delete(array(
			'in' => $parameters['databases']['system_users'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);

		$databases = array(
			'system_user_authentication_token_scopes',
			'system_user_authentication_token_sources',
			'system_user_authentication_tokens',
			'system_user_request_logs'
		);

		foreach ($databases as $database) {
			_delete(array(
				'in' => $parameters['databases'][$database],
				'where' => array(
					'system_user_id' => $parameters['where']['id']
				)
			), $response);
		}

		$response['message'] = 'System user deleted successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'delete_system_user') === true) {
		$response = _deleteSystemUser($parameters, $response);
		_output($response);
	}
?>
