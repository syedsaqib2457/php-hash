<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'systemUserAuthenticationTokens',
		'systemUserAuthenticationTokenSources',
		'systemUserSystemUsers'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['systemUserAuthenticationTokens'] = $systemDatabasesConnections['systemUserAuthenticationTokens'];
	$parameters['systemDatabases']['systemUserAuthenticationTokenSources'] = $systemDatabasesConnections['systemUserAuthenticationTokenSources'];
	$parameters['systemDatabases']['systemUserSystemUsers'] = $systemDatabasesConnections['systemUserSystemUsers'];

	function _addSystemUserAuthenticationTokenSource($parameters, $response) {
		if (
			(empty($parameters['data']['ipAddressRangeStart']) === true) ||
			(empty($parameters['data']['ipAddressRangeStop']) === true)
		) {
			$response['message'] = 'System user authentication token source must have an IP address range, please try again.';
			return $response;
		}

		$parameters['data']['ipAddressRangeVersionNumber'] = '4';

		if ((strpos($parameters['data']['ipAddressRangeStart'], ':') === false) === false) {
			$parameters['data']['ipAddressRangeVersionNumber'] = '6';
		}

		$parameters['data']['ipAddressRangeStart'] = _validateIpAddressVersionNumber($parameters['data']['ipAddressRangeStart'], $parameters['data']['ipAddressRangeVersionNumber']);

		if ($parameters['data']['ipAddressRangeStart'] === false) {
			$response['message'] = 'Invalid system user authentication token source IP address range start, please try again.';
			return $response;
		}

		$parameters['data']['ipAddressRangeStop'] = _validateIpAddressVersionNumber($parameters['data']['ipAddressRangeStop'], $parameters['data']['ipAddressRangeVersionNumber']);

		if ($parameters['data']['ipAddressRangeStop'] === false) {
			$response['message'] = 'Invalid system user authentication token source IP address range stop, please try again.';
			return $response;
		}

		if (($parameters['data']['ipAddressRangeStart'] > $parameters['data']['ipAddressRangeStop']) === true) {
			$response['message'] = 'System user authentication token source IP address range stop must be greater than or equal to system user authentication token source IP address range start, please try again.';
			return $response;
		}

		if (empty($parameters['data']['systemUserAuthenticationTokenId']) === true) {
			$response['message'] = 'System user authentication token source must have a system user authentication token ID, please try again.';
			return $response;
		}

		$systemUserAuthenticationToken = _list(array(
			'data' => array(
				'systemUserId'
			),
			'in' => $parameters['systemDatabases']['systemUserAuthenticationTokens'],
			'where' => array(
				'id' => $parameters['data']['$systemUserAuthenticationTokenId']
			)
		), $response);
		$systemUserAuthenticationToken = current($systemUserAuthenticationToken);

		if (empty($systemUserAuthenticationToken) === true) {
			$response['message'] = 'Error listing system user authentication token source system user authentication token, please try again.';
			return $response;
		}

		$systemUserSystemUserCount = _count(array(
			'in' => $parameters['systemDatabases']['systemUserSystemUsers'],
			'where' => array(
				'systemUserId' => $systemUserAuthenticationToken['systemUserId'],
				'systemUserSystemUserId' => $parameters['systemUserId']
			)
		), $response);

		if (
			(($systemUserSystemUserCount === 1) === false) &&
			(($parameters['systemUserId'] === $systemUserAuthenticationToken['systemUserId']) === false)
		) {
			$response['message'] = 'Invalid permissions to add system user authentication token source, please try again.';
			return $response;
		}

		$parameters['data']['systemUserId'] = $systemUserAuthenticationToken['systemUserId'];
		$existingSystemUserAuthenticationTokenSourceCount = _count(array(
			'in' => $parameters['systemDatabases']['systemUserAuthenticationTokenSources'],
			'where' => array(
				'ipAddressRangeStart' => $parameters['data']['ipAddressRangeStart'],
				'ipAddressRangeStop' => $parameters['data']['ipAddressRangeStop'],
				'ipAddressRangeVersionNumber' => $parameters['data']['ipAddressRangeVersionNumber'],
				'systemUserAuthenticationTokenId' => $parameters['data']['systemUserAuthenticationTokenId'],
				'systemUserId' => $parameters['data']['systemUserId']
			)
		), $response);

		if (($existingSystemUserAuthenticationTokenSourceCount === 1) === true) {
			$response['message'] = 'System user authentication token source already exists, please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['systemUserAuthenticationTokenSources']
		), $response);
		$systemUserAuthenticationTokenSource = _list(array(
			'in' => $parameters['systemDatabases']['systemUserAuthenticationTokenSources'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$systemUserAuthenticationTokenSource = current($systemUserAuthenticationTokenSource);
		$response['data'] = $systemUserAuthenticationTokenSource;
		$response['message'] = 'System user authentication token source added successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add-system-user-authentication-token-source') === true) {
		$response = _addSystemUserAuthenticationTokenSource($parameters, $response);
	}
?>
