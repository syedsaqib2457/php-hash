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

		$response['message'] = 'Error processing request to ' . str_replace('_', ' ', $parameters['action']) . ', please try again.';

		if (
			(empty($parameters['authentication_token']) === true) ||
			(ctype_alnum($parameters['authentication_token']) === false)
		) {
			$response['message'] = 'Invalid endpoint system user authentication token, please try again.';
			_output($response);
		}

		$response['status_authenticated'] = true;
		$systemUserAuthenticationToken = _list(array(
			'in' => $parameters['databases']['system_user_authentication_tokens'],
			'where' => array(
				'string' => $parameters['authentication_token']
			)
		));

		if ($systemUserAuthenticationToken === false) {
			$response['message'] = 'Error listing data in system_user_authentication_tokens database, please try again.';
			_output($response);
		}

		$response['status_authenticated'] = false;
		$systemUserAuthenticationToken = current($systemUserAuthenticationToken);

		if (empty($systemUserAuthenticationToken) === true) {
			$response['message'] = 'Invalid endpoint system user authentication token, please try again.';
			_output($response);
		}

		$response['status_authenticated'] = true;
		$systemUserAuthenticationTokenScopeCount = _count(array(
			'in' => $parameters['databases']['system_user_authentication_token_scopes'],
			'where' => array(
				'system_action' => $parameters['action'],
				'system_user_authentication_token_id' => $systemUserAuthenticationToken['id']
			)
		));

		if (is_int($systemUserAuthenticationTokenScopeCount) === false) {
			$response['message'] = 'Error counting data in system_user_authentication_token_scopes database, please try again.';
			_output($response);
		}

		$response['status_authenticated'] = false;

		if (($systemUserAuthenticationTokenScopeCount <= 0) === true) {
			$response['message'] = 'Invalid endpoint system user authentication token scope, please try again.';
			_output($response);
		}

		$systemUserAuthenticationTokenSourceCountParameters = array(
			'in' => $parameters['databases']['system_user_authentication_token_sources'],
			'where' => array(
				'system_user_authentication_token_id' => $systemUserAuthenticationToken['id']
			)
		);
		$systemUserAuthenticationTokenSourceCount = _count($systemUserAuthenticationTokenSourceCountParameters);

		if (is_int($systemUserAuthenticationTokenSourceCount) === false) {
			$response['message'] = 'Error counting data in system_user_authentication_token_sources database, please try again.';
			_output($response);
		}

		$response['status_authenticated'] = false;

		if (($systemUserAuthenticationTokenSourceCount > 0) === true) {
			$systemUserAuthenticationTokenSourceCountParameters['where']['address'] = $_SERVER['REMOTE_ADDR'];
			$systemUserAuthenticationTokenSourceCount = _count($systemUserAuthenticationTokenSourceCountParameters);

			if (is_int($systemUserAuthenticationTokenSourceCount) === false) {
				$response['message'] = 'Error counting data in system_user_authentication_token_sources database, please try again.'
				_output($response);
			}

			if (($systemUserAuthenticationTokenSourceCount > 0) === false) {
				// todo: validate cidr if source IP isn't found before returning false

				$response['message'] = 'Invalid endpoint system user authentication token source IP address ' . $_SERVER['REMOTE_ADDR'] . ', please try again.';
				_output($response);
			}
		}

		$response['status_authenticated'] = true;
		require_once('/var/www/ghostcompute/system_action_' . $parameters['action'] . '.php');
	}

	_output($response);
?>
