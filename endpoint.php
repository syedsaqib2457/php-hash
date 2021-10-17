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

		require_once('/var/www/ghostcompute/system_settings.php');
		require_once('/var/www/ghostcompute/system_database.php');

		if (
			(ctype_alnum(str_replace('_', '', $parameters['action'])) === false) ||
			(file_exists('/var/www/ghostcompute/system_action_' . $parameters['action'] . '.php') === false)
		) {
			$response['message'] = 'Invalid endpoint request action, please try again.';
			_output($response);
		}

		$response['message'] = 'Error processing request to ' . str_replace('_', ' ', $parameters['action']) . ', please try again.';

		if (
			(empty($parameters['authentication_token']) === true) ||
			(ctype_alnum($parameters['authentication_token']) === false)
		) {
			$response['message'] = 'Invalid endpoint system user authentication token, please try again.';
			_output($response);
		}

		$systemUserAuthenticationToken = _fetch(array(
			'from' => $parameters['databases']['system_user_authentication_tokens'],
			'where' => array(
				'string' => $parameters['authentication_token']
			)
		));
		$response['status_authenticated'] = true;

		if ($systemUserAuthenticationToken === false) {
			$response['message'] = 'Error connecting to system user authentication tokens database, please try again.';
			_output($response);
		}

		$response['status_authenticated'] = false;

		if (empty($systemUserAuthenticationToken) === true) {
			$response['message'] = 'Invalid endpoint system user authentication token, please try again.';
			_output($response);
		}

		$response['status_authenticated'] = true;

		// todo: authorize system user authentication token scope before processing function

		$systemUserAuthenticationTokenSource = _fetch(array(
			'from' => $parameters['databases']['system_user_authentication_token_sources'],
			'where' => array(
				'address' => ($sourceIp = $_SERVER['REMOTE_ADDR']),
				'system_user_authentication_token_id' => $systemUserAuthenticationToken['id']
			)
		));

		if ($systemUserAuthenticationTokenSource === false) {
			$response['message'] = 'Error connecting to system user authentication token sources database, please try again.';
			_output($response);
		}

		$response['status_authenticated'] = false;

		if (empty($systemUserAuthenticationTokenSource) === true) {
			// todo: validate non-empty token source count before returning false
			// todo: validate cidr if source IP isn't found before returning false

			$response['message'] = 'Invalid endpoint system user authentication token source IP address ' . $sourceIp . ', please try again.';
			_output($response);
		}

		$response['status_authenticated'] = true;
		require_once('/var/www/ghostcompute/system_action_' . $parameters['action'] . '.php');
	}

	_output($response);
?>
