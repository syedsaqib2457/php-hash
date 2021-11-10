<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'system_user_authentication_token_scopes',
		'system_user_authentication_tokens',
		'system_users'
	), $parameters['databases'], $response);

	function _deleteSystemUserAuthenticationTokenScope($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'System user authentication token scope must have an ID, please try again.';
			return $response;
		}

		if (is_string($parameters['where']['id']) === false) {
			$response['message'] = 'Invalid system user authentication token scope ID, please try again.';
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
		$systemUserAuthenticationTokenScope = _list(array(
			'columns' => array(
				'system_user_id'
			),
			'in' => $parameters['databases']['system_user_authentication_token_scopes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$systemUserAuthenticationTokenScope = current($systemUserAuthenticationTokenScope);
		$systemUser = _list(array(
			'columns' => array(
				'id'
			),
			'in' => $parameters['databases']['system_users'],
			'where' => array(
				'either' => array(
					array(
						'id' => $systemUserAuthenticationToken['system_user_id'],
						'system_user_id' => null
					),
					array(
						'id' => $systemUserAuthenticationTokenScope['system_user_id'],
						'system_user_id' => $systemUserAuthenticationToken['system_user_id']
					)
				)
			)
		), $response);
		$systemUser = current($systemUser);

		if (empty($systemUser) === true) {
			$response['message'] = 'Invalid permissions to delete system user authentication token scope, please try again.';
			return $response;
		}

		_delete(array(
			'in' => $parameters['databases']['system_user_authentication_token_scopes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$response['message'] = 'System user authentication token scope deleted successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'delete_system_user_authentication_token_scope') === true) {
		$response = _deleteSystemUserAuthenticationTokenScope($parameters, $response);
		_output($response);
	}
?>
