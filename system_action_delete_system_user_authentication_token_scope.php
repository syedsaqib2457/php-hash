<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'system_user_authentication_token_scopes',
		'system_user_system_users'
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

		if (($parameters['system_user_id'] === $systemUserAuthenticationTokenScope['system_user_id']) === true) {
			$response['message'] = 'System user authentication token scope must belong to another user, please try again.';
			return $response;
		}

		$systemUserSystemUserCount = _count(array(
			'in' => $parameters['databases']['system_user_system_users'],
			'where' => array(
				'system_user_id' => $systemUserAuthenticationTokenScope['system_user_id'],
				'system_user_system_user_id' => $parameters['system_user_id']
			)
		), $response);

		if (
			(($systemUserSystemUserCount > 0) === false) &&
			(($parameters['system_user_id'] === $systemUserAuthenticationTokenScope['system_user_id']) === false)
		) {
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
