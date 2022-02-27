<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'systemUserAuthenticationTokens',
		'systemUserAuthenticationTokenScopes',
		'systemUserAuthenticationTokenSources',
		'systemUserRequestLogs',
		'systemUsers',
		'systemUserSystemUsers'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['systemUserAuthenticationTokens'] = $systemDatabasesConnections['systemUserAuthenticationTokens'];
	$parameters['systemDatabases']['systemUserAuthenticationTokenScopes'] = $systemDatabasesConnections['systemUserAuthenticationTokenScopes'];
	$parameters['systemDatabases']['systemUserAuthenticationTokenSources'] = $systemDatabasesConnections['systemUserAuthenticationTokenSources'];
	$parameters['systemDatabases']['systemUserRequestLogs'] = $systemDatabasesConnections['systemUserRequestLogs'];
	$parameters['systemDatabases']['systemUsers'] = $systemDatabasesConnections['systemUsers'];
	$parameters['systemDatabases']['systemUserSystemUsers'] = $systemDatabasesConnections['systemUserSystemUsers'];

	function _deleteSystemUser($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'System user must have an ID, please try again.';
			return $response;
		}

		if (is_string($parameters['where']['id']) === false) {
			$response['message'] = 'Invalid system user ID, please try again.';
			return $response;
		}

		$systemUserSystemUserCount = _count(array(
			'in' => $parameters['systemDatabases']['systemUserSystemUsers'],
			'where' => array(
				'systemUserId' => $parameters['where']['id'],
				'systemUserSystemUserId' => $parameters['systemUserId']
			)
		), $response);

		if (
			(($systemUserSystemUserCount > 0) === false) ||
			(($parameters['systemUserId'] === $parameters['where']['id']) === true)
		) {
			$response['message'] = 'Invalid permissions to delete system user, please try again.';
			return $response;
		}

		$systemUserSystemUserIdPartIndex = 0;
		$systemUserSystemUserIdParts = array(
			array(
				$parameters['where']['id']
			)
		);
		$systemUserSystemUsers = _list(array(
			'data' => array(
				'systemUserId'
			),
			'in' => $parameters['systemDatabases']['systemUserSystemUsers'],
			'where' => array(
				'systemUserSystemUserId' => $parameters['where']['id']
		), $response);
		$systemUserSystemUsers = current($systemUserSystemUsers);

		foreach ($systemUserSystemUsers as $systemUserSystemUser) {
			if (empty($systemUserSystemUserIdParts[$systemUserSystemUserIdPartIndex][10]) === false) {
				$systemUserSystemUserIdPartIndex++;
			}

			$systemUserSystemUserIdParts[$systemUserSystemUserIdPartIndex][] = $systemUserSystemUser['systemUserId'];
		}

		$systemDatabaseTableKeys = array(
			'systemUserAuthenticationTokens',
			'systemUserAuthenticationTokenScopes',
			'systemUserAuthenticationTokenSources',
			'systemUserRequestLogs'
		);

		foreach ($systemUserSystemUserIdParts as $systemUserSystemUserIdPart) {
			foreach ($systemDatabaseTableKeys as $systemDatabaseTableKey) {
				_delete(array(
					'in' => $parameters['systemDatabases'][$systemDatabaseTableKey],
					'where' => array(
						'systemUserId' => $systemUserSystemUserIdPart
					)
				), $response);
			}

			_delete(array(
				'in' => $parameters['systemDatabases']['systemUsers'],
				'where' => array(
					'id' => $systemUserSystemUserIdPart
				)
			), $response);
		}

		_delete(array(
			'in' => $parameters['systemDatabases']['systemUserSystemUsers'],
			'where' => array(
				'either' => array(
					'systemUserId' => $parameters['where']['id'],
					'systemUserSystemUserId' => $parameters['where']['id']
				)
			)
		), $response);
		$response['message'] = 'System user deleted successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'delete-system-user') === true) {
		$response = _deleteSystemUser($parameters, $response);
	}
?>
