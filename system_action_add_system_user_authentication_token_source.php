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

		if (
			(empty($parameters['data']['ip_address_range_start']) === true) ||
			(empty($parameters['data']['ip_address_range_stop']) === true)
		) {
			$response['message'] = 'System user authentication token source must have an IP address range, please try again.';
			return $response;
		}

		$parameters['data']['ip_address_range_version'] = '4';

		if (is_int(strpos($parameters['data']['ip_address_range_start'], ':')) === true) {
			$parameters['data']['ip_address_range_version'] = '6';
		}

		$parameters['data']['ip_address_range_start'] = _validateIpAddressVersion($parameters['data']['ip_address_range_start'], $parameters['data']['ip_address_range_version']);

		if ($parameters['data']['ip_address_range_start'] === false) {
			$response['message'] = 'Invalid system user authentication token source IP address range start, please try again.';
			return $response;
		}

		$parameters['data']['ip_address_range_stop'] = _validateIpAddressVersion($parameters['data']['ip_address_range_stop'], $parameters['data']['ip_address_range_version']);

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
			'columns' => array(
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

		// todo: validate permissions for $systemUserAuthenticationToken['system_user_id'] from $parameters['system_user_id'] in system_user_system_users

		$parameters['data']['system_user_id'] = $systemUserAuthenticationToken['system_user_id'];
		$existingSystemUserAuthenticationTokenSourceCount = _count(array(
			'in' => $parameters['databases']['system_user_authentication_token_sources'],
			'where' => array_intersect_key($parameters['data'], array(
				'ip_address_range_start' => true,
				'ip_address_range_stop' => true,
				'ip_address_range_version' => true,
				'system_user_authentication_token_id' => true,
				'system_user_id' => true
			))
		), $response);

		if (($existingSystemUserAuthenticationTokenSourceCount > 0) === true) {
			$response['message'] = 'System user authentication token source already exists, please try again.';
			return $response;
		}

		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'id' => true,
				'ip_address_range_start' => true,
				'ip_address_range_stop' => true,
				'ip_address_range_version' => true,
				'system_user_authentication_token_id' => true,
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

	if (($parameters['action'] === 'add_system_user_authentication_token_source') === true) {
		$response = _addSystemUserAuthenticationTokenSource($parameters, $response);
		_output($response);
	}
?>
