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

		_delete(array(
			'in' => $parameters['databases']['system_users'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);

		$databases = array(
			'system_user_authentication_token_scopes',
			'system_user_authentication_token_sources',
			'system_user_request_logs',
			'system_user_authentication_tokens'
		);

		foreach ($databases as $database) {
			_delete(array(
				'in' => $parameters['databases'][$database],
				'where' => array(
					'system_user_id' => $parameters['where']['id']
				)
			), $response);
		}

		$response['message'] = 'System user removed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'delete_system_user') === true) {
		$response = _deleteSystemUser($parameters, $response);
		_output($response);
	}
?>
