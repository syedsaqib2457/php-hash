<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'system_user_authentication_token_sources',
		'system_user_authentication_tokens',
		'system_user_system_users'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['system_user_authentication_token_sources'] = $systemDatabasesConnections['system_user_authentication_token_sources'];
	$parameters['system_databases']['system_user_authentication_tokens'] = $systemDatabasesConnections['system_user_authentication_tokens'];
	$parameters['system_databases']['system_user_system_users'] = $systemDatabasesConnections['system_user_system_users'];

	function _addSystemUserAuthenticationTokenSource($parameters, $response) {
		if (
			(empty($parameters['data']['ip_address_range_start']) === true) ||
			(empty($parameters['data']['ip_address_range_stop']) === true)
		) {
			$response['message'] = 'System user authentication token source must have an IP address range, please try again.';
			return $response;
		}

		$parameters['data']['ip_address_range_version_number'] = '4';

		if ((strpos($parameters['data']['ip_address_range_start'], ':') === false) === false) {
			$parameters['data']['ip_address_range_version_number'] = '6';
		}

		$parameters['data']['ip_address_range_start'] = _validateIpAddressVersionNumber($parameters['data']['ip_address_range_start'], $parameters['data']['ip_address_range_version_number']);

		if ($parameters['data']['ip_address_range_start'] === false) {
			$response['message'] = 'Invalid system user authentication token source IP address range start, please try again.';
			return $response;
		}

		$parameters['data']['ip_address_range_stop'] = _validateIpAddressVersionNumber($parameters['data']['ip_address_range_stop'], $parameters['data']['ip_address_range_version_number']);

		if ($parameters['data']['ip_address_range_stop'] === false) {
			$response['message'] = 'Invalid system user authentication token source IP address range stop, please try again.';
			return $response;
		}

		if (($parameters['data']['ip_address_range_start'] > $parameters['data']['ip_address_range_stop']) === true) {
			$response['message'] = 'System user authentication token source IP address range stop must be greater than or equal to system user authentication token source IP address range start, please try again.';
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

		if (
			(($systemUserSystemUserCount === 1) === false) &&
			(($parameters['system_user_id'] === $systemUserAuthenticationToken['system_user_id']) === false)
		) {
			$response['message'] = 'Invalid permissions to add system user authentication token source, please try again.';
			return $response;
		}

		$parameters['data']['system_user_id'] = $systemUserAuthenticationToken['system_user_id'];
		$existingSystemUserAuthenticationTokenSourceCount = _count(array(
			'in' => $parameters['system_databases']['system_user_authentication_token_sources'],
			'where' => array(
				'ip_address_range_start' => $parameters['data']['ip_address_range_start'],
				'ip_address_range_stop' => $parameters['data']['ip_address_range_stop'],
				'ip_address_range_version_number' => $parameters['data']['ip_address_range_version_number'],
				'system_user_authentication_token_id' => $parameters['data']['system_user_authentication_token_id'],
				'system_user_id' => $parameters['data']['system_user_id']
			)
		), $response);

		if (($existingSystemUserAuthenticationTokenSourceCount === 1) === true) {
			$response['message'] = 'System user authentication token source already exists, please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['system_user_authentication_token_sources']
		), $response);
		$systemUserAuthenticationTokenSource = _list(array(
			'in' => $parameters['system_databases']['system_user_authentication_token_sources'],
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

	if (($parameters['action'] === 'add_system_user_authentication_token_source') === true) {
		$response = _addSystemUserAuthenticationTokenSource($parameters, $response);
	}
?>
