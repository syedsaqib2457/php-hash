<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'system_user_authentication_token_sources'
	), $parameters['databases'], $response);

	function _deleteSystemUserAuthenticationTokenSource($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'System user authentication token source must have an ID, please try again.';
			return $response;
		}

		if (is_string($parameters['where']['id']) === false) {
			$response['message'] = 'Invalid system user authentication token source ID, please try again.';
			return $response;
		}

		$systemUserAuthenticationToken = _list(array(
			'columns' => array(
				'id',
				'system_user_id'
			),
			'in' => $parameters['databases']['system_user_authentication_tokens'],
			'where' => array(
				'string' => $parameters['authentication_token']
			)
		), $response);
		$systemUserAuthenticationToken = current($systemUserAuthenticationToken);
		$systemUser = _list(array(
			'columns' => array(
				'id'
			),
			'in' => $parameters['databases']['system_user_authentication_tokens'],
			'where' => array(
				'either' => array(
					'id' => $systemUserAuthenticationToken['system_user_id'],
					'system_user_id' => array(
						null,
						$systemUserAuthenticationToken['system_user_id']
					)
				)
			)
		), $response);
		$systemUser = current($systemUser);

		if (empty($systemUser) === true) {
			$response['message'] = 'Invalid permissions to delete system user authentication token source, please try again.';
			return $response;
		}

		_delete(array(
			'in' => $parameters['databases']['system_user_authentication_token_sources'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$response['message'] = 'System user authentication token source deleted successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'delete_system_user_authentication_token_source') === true) {
		$response = _deleteSystemUserAuthenticationTokenSource($parameters, $response);
		_output($response);
	}
?>
