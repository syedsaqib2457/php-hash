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

	function _deleteSystemUsers($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'System user must have an ID, please try again.';
			return $response;
		}

		$systemUserIds = $parameters['where']['id'];

		if (is_string($parameters['where']['id']) === true) {
			$systemUserIds = array(
				$parameters['where']['id']
			);
		}

		foreach ($systemUserIds as $systemUserId) {
			$systemUserSystemUserCount = _count(array(
				'in' => $parameters['systemDatabases']['systemUserSystemUsers'],
				'where' => array(
					'systemUserId' => $systemUserId,
					'systemUserSystemUserId' => $parameters['systemUserId']
				)
			), $response);

			if (
				(($systemUserSystemUserCount === 1) === false) ||
				(($parameters['systemUserId'] === $systemUserId) === true)
			) {
				$response['message'] = 'Invalid permissions to delete system user ID ' . $systemUserId . ', please try again.';
				return $response;
			}
		}

		$systemUserSystemUserIdPartIndex = 0;
		$systemUserSystemUserIdParts = array();
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
		$response['message'] = 'System users deleted successfully.';
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
