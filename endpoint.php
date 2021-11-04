<?php
	function _output($response) {
		if (empty($response['status_authenticated']) === true) {
			// todo: log invalid action for DDoS protection
		}

		echo json_encode($response);
		exit;
	}

	$response = array(
		'authenticated_status' => '0',
		'data' => array(),
		'message' => 'Invalid endpoint request, please try again.',
		'valid_status' => '0'
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
			'columns' => array(
				'id'
			),
			'in' => $parameters['databases']['system_user_authentication_tokens'],
			'where' => array(
				'string' => $parameters['authentication_token']
			)
		), $response);
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
		), $response);

		if (($systemUserAuthenticationTokenScopeCount <= 0) === true) {
			$response['message'] = 'Invalid endpoint system user authentication token scope, please try again.';
			_output($response);
		}

		require_once('/var/www/ghostcompute/system_action_validate_ip_address_version.php');
		$parameters['source'] = array(
			'ip_address' => $_SERVER['REMOTE_ADDR'],
			'ip_address_version' => '4'
		);

		if (is_int(strpos($parameters['source']['ip_address'], ':')) === true) {
			$parameters['source']['ip_address_version'] = '6';
		}

		$parameters['source']['ip_address'] = _validateIpAddressVersion($parameters['source']['ip_address'], $parameters['source']['ip_address_version']);

		if ($parameters['source']['ip_address'] === false) {
			$response['message'] = 'Invalid source IP address, please try again.';
			_output($response);
		}

		$systemUserAuthenticationTokenSourceCountParameters = array(
			'in' => $parameters['databases']['system_user_authentication_token_sources'],
			'where' => array(
				'system_user_authentication_token_id' => $systemUserAuthenticationToken['id']
			)
		);
		$systemUserAuthenticationTokenSourceCount = _count($systemUserAuthenticationTokenSourceCountParameters, $response);

		if (($systemUserAuthenticationTokenSourceCount > 0) === true) {
			$systemUserAuthenticationTokenSourceCountParameters['where'] += array(
				'ip_address_range_start <=' => $parameters['source']['ip_address'],
				'ip_address_range_stop >=' => $parameters['source']['ip_address'],
				'ip_address_version' => $parameters['source']['ip_address_version']
			);
			$systemUserAuthenticationTokenSourceCount = _count($systemUserAuthenticationTokenSourceCountParameters, $response);

			if (($systemUserAuthenticationTokenSourceCount <= 0) === true) {
				$response['message'] = 'Invalid endpoint system user authentication token source IP address ' . $sourceIpAddress . ', please try again.';
				_output($response);
			}
		}

		$response['authenticated_status'] = '1';
		require_once('/var/www/ghostcompute/system_action_' . $parameters['action'] . '.php');
	}

	_output($response);
?>
