<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'system_user_authentication_token_sources',
		'system_user_authentication_tokens'
	), $parameters['databases'], $response);

	function _addSystemUserAuthenticationTokenSource($parameters, $response) {
		$parameters['data']['id'] = random_bytes(10) . time() . random_bytes(10);

		if (empty($parameters['data']['system_user_authentication_token_id']) === true) {
			$response['message'] = 'System user authentication token ID is required, please try again.';
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
		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'id' => true,
				'system_user_id' => true
			)),
			'in' => $parameters['databases']['system_user_authentication_token_sources']
		), $response);
		$systemUserAuthenticationTokenSource = _list(array(
			'in' => $parameters['databases']['system_user_authentication_token_sources'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$systemUserAuthenticationTokenSource = current($systemUserAuthenticationTokenSource);
		$response['data'] = $systemUserAuthenticationTokenSource;
		$response['message'] = 'System user authentication token source added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_system_user_authentication_token_sources') === true) {
		$response = _addSystemUserAuthenticationTokenSource($parameters, $response);
		_output($response);
	}
?>
