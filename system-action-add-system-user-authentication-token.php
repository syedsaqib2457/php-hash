<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'systemUserAuthenticationTokens',
		'systemUserSystemUsers'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['systemUserAuthenticationTokens'] = $systemDatabasesConnections['systemUserAuthenticationTokens'];
	$parameters['systemDatabases']['systemUserSystemUsers'] = $systemDatabasesConnections['systemUserSystemUsers'];

	function _addSystemUserAuthenticationToken($parameters, $response) {
		if (empty($parameters['data']['systemUserId']) === true) {
			$response['message'] = 'System user authentication token must have a system user ID, please try again.';
			return $response;
		}

		$systemUserCount = _count(array(
			'in' => $parameters['systemDatabases']['systemUsers'],
			'where' => array(
				'id' => $parameters['data']['systemUserId']
			)
		), $response);

		if (($systemUserCount === 1) === false) {
			$response['message'] = 'Invalid system user ID, please try again.';
			return $response;
		}

		$systemUserSystemUserCount = _count(array(
			'in' => $parameters['systemDatabases']['systemUserSystemUsers'],
			'where' => array(
				'systemUserId' => $parameters['data']['systemUserId'],
				'systemUserSystemUserId' => $parameters['systemUserId']
			)
		), $response);

		if (
			(($systemUserSystemUserCount === 1) === false) &&
			(($parameters['systemUserId'] === $parameters['data']['systemUserId']) === false)
		) {
			$response['message'] = 'Invalid permissions to add system user authentication token, please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['systemUserAuthenticationTokens']
		), $response);
		$systemUserAuthenticationToken = _list(array(
			'in' => $parameters['systemDatabases']['systemUserAuthenticationTokens'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$systemUserAuthenticationToken = current($systemUserAuthenticationToken);
		$response['data'] = $systemUserAuthenticationToken;
		$response['message'] = 'System user authentication token added successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'addSystemUserAuthenticationToken') === true) {
		$response = _addSystemUserAuthenticationToken($parameters, $response);
	}
?>
