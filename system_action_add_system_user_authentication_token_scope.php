<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'system_user_authentication_token_scopes',
		'system_user_authentication_tokens'
	), $parameters['databases'], $response);

	function _addSystemUserAuthenticationTokenScope($parameters, $response) {
		$parameters['data']['id'] = random_bytes(10) . time() . random_bytes(10);

		if (empty($parameters['data']['system_action']) === true) {
			$response['message'] = 'System user authentication token scope must have a system action, please try again.';
			return $response;
		}

		if (
			(ctype_alnum(str_replace('_', '', $parameters['data']['system_action'])) === false) ||
			(file_exists('/var/www/ghostcompute/system_action_' . $parameters['data']['system_action'] . '.php') === false)
		) {
			$response['message'] = 'Invalid system user authentication token scope system action, please try again.';
			return $response;
		}

		if (empty($parameters['data']['system_user_authentication_token_id']) === true) {
			$response['message'] = 'System user authentication token source must have a system user authentication token ID, please try again.';
			return $response;
		}

		$systemUserAuthenticationToken = _list(array(
			'columns' => array(
				'id',
				'system_user_id'
			),
			'in' => $parameters['databases']['system_user_authentication_tokens'],
			'where' => array(
				'id' => $parameters['data']['system_user_authentication_token_id']
			)
		), $response);
		$systemUserAuthenticationToken = current($systemUserAuthenticationToken);

		if (empty($systemUserAuthenticationToken) === true) {
			$response['message'] = 'Invalid system user authentication token ID, please try again.';
			return $response;
		}

		$parameters['data']['system_user_id'] = $systemUserAuthenticationToken['system_user_id'];
		$existingSystemUserAuthenticationTokenScopeCount = _count(array(
			'in' => $parameters['databases']['system_user_authentication_token_scopes'],
			'where' => array_intersect_key($parameters['data'], array(
				'system_action' => true,
				'system_user_authentication_token_id' => true,
				'system_user_id' => true
			))
		));

		if (($existingSystemUserAuthenticationTokenScopeCount > 0) === true) {
			$response['message'] = 'System user authentication token scope already exists, please try again.';
			return $response;
		}

		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'id' => true,
				'system_action' => true,
				'system_user_authentication_token_id' => true,
				'system_user_id' => true
			)),
			'in' => $parameters['databases']['system_user_authentication_token_sources']
		), $response);
		$systemUserAuthenticationTokenScope = _list(array(
			'in' => $parameters['databases']['system_user_authentication_token_sources'],
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

	if (($parameters['action'] === 'add_system_user_authentication_token_scopes') === true) {
		$response = _addSystemUserAuthenticationTokenScope($parameters, $response);
		_output($response);
	}
?>
