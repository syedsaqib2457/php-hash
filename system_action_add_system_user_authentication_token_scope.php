<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'system_user_authentication_token_scopes',
		'system_user_authentication_tokens',
		'system_user_system_users'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['system_user_authentication_token_scopes'] = $systemDatabasesConnections['system_user_authentication_token_scopes'];
	$parameters['system_databases']['system_user_authentication_tokens'] = $systemDatabasesConnections['system_user_authentication_tokens'];
	$parameters['system_databases']['system_user_system_users'] = $systemDatabasesConnections['system_user_system_users'];

	function _addSystemUserAuthenticationTokenScope($parameters, $response) {
		if (empty($parameters['data']['system_action']) === true) {
			$response['message'] = 'System user authentication token scope must have a system action, please try again.';
			return $response;
		}

		if (
			((strpos($parameters['data']['system_action'], '/') === false) === false) ||
			(file_exists('/var/www/nodecompute/system_action_' . $parameters['data']['system_action'] . '.php') === false)
		) {
			$response['message'] = 'Invalid system user authentication token scope system action, please try again.';
			return $response;
		}

		if (empty($parameters['data']['system_user_authentication_token_id']) === true) {
			$response['message'] = 'System user authentication token source must have a system user authentication token ID, please try again.';
			return $response;
		}

		$systemUserAuthenticationToken = _list(array(
			'data' => array(
				'system_user_id'
			),
			'in' => $parameters['system_databases']['system_user_authentication_tokens'],
			'where' => array(
				'id' => $parameters['data']['system_user_authentication_token_id']
			)
		), $response);
		$systemUserAuthenticationToken = current($systemUserAuthenticationToken);

		if (empty($systemUserAuthenticationToken) === true) {
			$response['message'] = 'Invalid system user authentication token ID, please try again.';
			return $response;
		}

		$systemUserSystemUserCount = _count(array(
			'in' => $parameters['system_databases']['system_user_system_users'],
			'where' => array(
				'system_user_id' => $systemUserAuthenticationToken['system_user_id'],
				'system_user_system_user_id' => $parameters['system_user_id']
			)
		), $response);

		if (($systemUserSystemUserCount > 0) === false) {
			$response['message'] = 'Invalid permissions to add system user authentication token scope, please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		$parameters['data']['system_user_id'] = $systemUserAuthenticationToken['system_user_id'];
		$existingSystemUserAuthenticationTokenScopeCount = _count(array(
			'in' => $parameters['system_databases']['system_user_authentication_token_scopes'],
			'where' => array(
				'system_action' => $parameters['data']['system_action'],
				'system_user_authentication_token_id' => $parameters['data']['system_user_authentication_token_id'],
				'system_user_id' => $parameters['data']['system_user_id']
			)
		), $response);

		if (($existingSystemUserAuthenticationTokenScopeCount === 1) === true) {
			$response['message'] = 'System user authentication token scope already exists, please try again.';
			return $response;
		}

		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['system_user_authentication_token_sources']
		), $response);
		$systemUserAuthenticationTokenScope = _list(array(
			'in' => $parameters['system_databases']['system_user_authentication_token_sources'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$systemUserAuthenticationTokenScope = current($systemUserAuthenticationTokenScope);
		$response['data'] = $systemUserAuthenticationTokenScope;
		$response['message'] = 'System user authentication token scope added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_system_user_authentication_token_scope') === true) {
		$response = _addSystemUserAuthenticationTokenScope($parameters, $response);
	}
?>
