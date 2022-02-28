<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'systemUserAuthenticationTokens',
		'systemUserAuthenticationTokenScopes',
		'systemUserSystemUsers'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['systemUserAuthenticationTokens'] = $systemDatabasesConnections['systemUserAuthenticationTokens'];
	$parameters['systemDatabases']['systemUserAuthenticationTokenScopes'] = $systemDatabasesConnections['systemUserAuthenticationTokenScopes'];
	$parameters['systemDatabases']['systemUserSystemUsers'] = $systemDatabasesConnections['systemUserSystemUsers'];

	function _addSystemUserAuthenticationTokenScope($parameters, $response) {
		if (empty($parameters['data']['systemAction']) === true) {
			$response['message'] = 'System user authentication token scope must have a system action, please try again.';
			return $response;
		}

		if ((strpos($systemActionFile, '/') === false) === false) {
			$response['message'] = 'Invalid system user authentication token scope system action, please try again.';
			return $response;
		}

		$systemActionFile = '';
		$systemActionIndex = 0;

		while (isset($parameters['data']['systemAction'][$systemActionIndex]) === true) {
			if (ctype_upper($parameters['data']['systemAction'][$systemActionIndex]) === true) {
				$systemActionFile .= '-' . strtolower($parameters['data']['systemAction'][$systemActionIndex]);
			} else {
				$systemActionFile .= $parameters['data']['systemAction'][$systemActionIndex];
			}

			$systemActionIndex++;
		}

		if (file_exists('/var/www/firewall-security-api/system-action-' . $systemActionFile . '.php') === false) {
			$response['message'] = 'Error listing system user authentication token scope system action file, please try again.';
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
				'id' => $parameters['data']['systemUserAuthenticationTokenId']
			)
		), $response);
		$systemUserAuthenticationToken = current($systemUserAuthenticationToken);

		if (empty($systemUserAuthenticationToken) === true) {
			$response['message'] = 'Error listing system user authentication token scope system user authentication token, please try again.';
			return $response;
		}

		$systemUserSystemUserCount = _count(array(
			'in' => $parameters['systemDatabases']['systemUserSystemUsers'],
			'where' => array(
				'systemUserId' => $systemUserAuthenticationToken['systemUserId'],
				'systemUserSystemUserId' => $parameters['systemUserId']
			)
		), $response);

		if (($systemUserSystemUserCount === 0) === true) {
			$response['message'] = 'Invalid permissions to add system user authentication token scope, please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		$parameters['data']['systemUserId'] = $systemUserAuthenticationToken['systemUserId'];
		$existingSystemUserAuthenticationTokenScopeCount = _count(array(
			'in' => $parameters['systemDatabases']['systemUserAuthenticationTokenScopes'],
			'where' => array(
				'systemAction' => $parameters['data']['systemAction'],
				'systemUserAuthenticationTokenId' => $parameters['data']['systemUserAuthenticationTokenId'],
				'systemUserId' => $parameters['data']['systemUserId']
			)
		), $response);

		if (($existingSystemUserAuthenticationTokenScopeCount === 1) === true) {
			$response['message'] = 'System user authentication token scope already exists, please try again.';
			return $response;
		}

		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['systemUserAuthenticationTokenSources']
		), $response);
		$systemUserAuthenticationTokenScope = _list(array(
			'in' => $parameters['systemDatabases']['systemUserAuthenticationTokenSources'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$systemUserAuthenticationTokenScope = current($systemUserAuthenticationTokenScope);
		$response['data'] = $systemUserAuthenticationTokenScope;
		$response['message'] = 'System user authentication token scope added successfully.';
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
