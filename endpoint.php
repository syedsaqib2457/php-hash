<?php
	function _output($response) {
		if (empty($response['status_authenticated']) === true) {
			// todo: log invalid action for DDoS protection
		}

		echo json_encode($response);
		exit;
	}

	$response = array(
		'data' => array(),
		'message' => 'Invalid endpoint request, please try again.',
		'status_authenticated' => false,
		'status_valid' => false
	);

	if (empty($_POST) === false) {
		$parameters = json_decode($_POST, true);

		if (empty($parameters) === true) {
			_output($response);
		}

		require_once('/var/www/ghostcompute/system_databases.php');

		if (
			(ctype_alnum(str_replace('_', '', $parameters['action'])) === false) ||
			(file_exists('/var/www/ghostcompute/system_action_' . $parameters['action'] . '.php') === false)
		) {
			$response['message'] = 'Invalid endpoint request action, please try again.';
			_output($response);
		}

		if (
			(empty($parameters['authentication_token']) === true) ||
			(ctype_alnum($parameters['authentication_token']) === false)
		) {
			$response['message'] = 'Invalid endpoint system user authentication token, please try again.';
			_output($response);
		}

		$systemUserAuthenticationToken = _list(array(
			'in' => $parameters['databases']['system_user_authentication_tokens'],
			'where' => array(
				'string' => $parameters['authentication_token']
			)
		));
		$systemUserAuthenticationToken = current($systemUserAuthenticationToken);

		if (empty($systemUserAuthenticationToken) === true) {
			$response['message'] = 'Invalid endpoint system user authentication token, please try again.';
			_output($response);
		}

		$systemUserAuthenticationTokenScopeCount = _count(array(
			'in' => $parameters['databases']['system_user_authentication_token_scopes'],
			'where' => array(
				'system_action' => $parameters['action'],
				'system_user_authentication_token_id' => $systemUserAuthenticationToken['id']
			)
		));

		if (($systemUserAuthenticationTokenScopeCount <= 0) === true) {
			$response['message'] = 'Invalid endpoint system user authentication token scope, please try again.';
			_output($response);
		}

		// todo: convert $_SERVER['REMOTE_ADDR'] to full ipv6 notation with validation function
		$systemUserAuthenticationTokenSourceCountParameters = array(
			'in' => $parameters['databases']['system_user_authentication_token_sources'],
			'where' => array(
				'address_range_start <=' => $_SERVER['REMOTE_ADDR'],
				'address_range_stop >=' => $_SERVER['REMOTE_ADDR'],
				'system_user_authentication_token_id' => $systemUserAuthenticationToken['id']
			)
		);
		$systemUserAuthenticationTokenSourceCount = _count($systemUserAuthenticationTokenSourceCountParameters);

		if (($systemUserAuthenticationTokenSourceCount > 0) === true) {
			$response['message'] = 'Invalid endpoint system user authentication token source IP address ' . $_SERVER['REMOTE_ADDR'] . ', please try again.';
			_output($response);
		}

		$response['status_authenticated'] = true;
		require_once('/var/www/ghostcompute/system_action_' . $parameters['action'] . '.php');
	}

	_output($response);
?>
