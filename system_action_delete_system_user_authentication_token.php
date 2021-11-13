<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'system_user_authentication_token_scopes',
		'system_user_authentication_token_sources',
		'system_user_authentication_tokens',
		'system_user_system_users'
	), $parameters['databases'], $response);

	function _deleteSystemUserAuthenticationToken($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'System user authentication token must have an ID, please try again.';
			return $response;
		}

		if (is_string($parameters['where']['id']) === false) {
			$response['message'] = 'Invalid system user authentication token ID, please try again.';
			return $response;
		}

		$systemUserAuthenticationToken = _list(array(
			'columns' => array(
				'system_user_id'
			),
			'in' => $parameters['databases']['system_user_authentication_tokens'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$systemUserAuthenticationToken = current($systemUserAuthenticationToken);
		$systemUserSystemUserCount = _count(array(
			'in' => $parameters['databases']['system_user_system_users'],
			'where' => array(
				'system_user_id' => $systemUserAuthenticationToken['system_user_id'],
				'system_user_system_user_id' => $parameters['system_user_id']
			)
		), $response);

		if (
			(($systemUserSystemUserCount > 0) === false) &&
			(($parameters['system_user_id'] === $systemUserAuthenticationToken['system_user_id']) === false)
		) {
			$response['message'] = 'Invalid permissions to delete system user authentication token, please try again.';
			return $response;
		}

		// todo: validate minimum 1 authentication token
		// todo: delete token scopes and sources

		_delete(array(
			'in' => $parameters['databases']['system_user_authentication_token_sources'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$response['message'] = 'System user authentication token deleted successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'delete_system_user_authentication_token') === true) {
		$response = _deleteSystemUserAuthenticationToken($parameters, $response);
		_output($response);
	}
?>
