<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'system_user_authentication_token_scopes',
		'system_user_authentication_token_sources',
		'system_user_authentication_tokens',
		'system_user_request_logs',
		'system_user_system_users',
		'system_users'
	), $parameters['system_databases'], $response);

	function _deleteSystemUser($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'System user must have an ID, please try again.';
			return $response;
		}

		if (is_string($parameters['where']['id']) === false) {
			$response['message'] = 'Invalid system user ID, please try again.';
			return $response;
		}

		$systemUserSystemUserCount = _count(array(
			'in' => $parameters['system_databases']['system_user_system_users'],
			'where' => array(
				'system_user_id' => $parameters['where']['id'],
				'system_user_system_user_id' => $parameters['system_user_id']
			)
		), $response);

		if (
			(($systemUserSystemUserCount > 0) === false) ||
			(($parameters['system_user_id'] === $parameters['where']['id']) === true)
		) {
			$response['message'] = 'Invalid permissions to delete system user, please try again.';
			return $response;
		}

		$systemUserSystemUserIdPartIndex = 0;
		$systemUserSystemUserIdParts = array(
			array(
				$parameters['where']['id']
			)
		);
		$systemUserSystemUsers = _list(array(
			'columns' => array(
				'system_user_id'
			),
			'in' => $parameters['system_databases']['system_user_system_users'],
			'where' => array(
				'system_user_system_user_id' => $parameters['where']['id']
		), $response);
		$systemUserSystemUsers = current($systemUserSystemUsers);

		foreach ($systemUserSystemUsers as $systemUserSystemUser) {
			if (empty($systemUserSystemUserIdParts[$systemUserSystemUserIdPartIndex][10]) === false) {
				$systemUserSystemUserIdPartIndex++;
			}

			$systemUserSystemUserIdParts[$systemUserSystemUserIdPartIndex][] = $systemUserSystemUser['system_user_id'];
		}

		$systemDatabaseNames = array(
			'system_user_authentication_token_scopes',
			'system_user_authentication_token_sources',
			'system_user_authentication_tokens',
			'system_user_request_logs'
		);

		foreach ($systemUserSystemUserIdParts as $systemUserSystemUserIdPart) {
			foreach ($systemDatabaseNames as $systemDatabaseName) {
				_delete(array(
					'in' => $parameters['system_databases'][$systemDatabaseName],
					'where' => array(
						'system_user_id' => $systemUserSystemUserIdPart
					)
				), $response);
			}

			_delete(array(
				'in' => $parameters['system_databases']['system_users'],
				'where' => array(
					'id' => $systemUserSystemUserIdPart
				)
			), $response);
		}

		_delete(array(
			'in' => $parameters['system_databases']['system_user_system_users'],
			'where' => array(
				'either' => array(
					'system_user_id' => $parameters['where']['id'],
					'system_user_system_user_id' => $parameters['where']['id']
				)
			)
		), $response);
		$response['message'] = 'System user deleted successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'delete_system_user') === true) {
		$response = _deleteSystemUser($parameters, $response);
		_output($response);
	}
?>
