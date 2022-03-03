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
			$response['message'] = 'System users must have IDs, please try again.';
			return $response;
		}

		$systemUsersIds = $parameters['where']['id'];

		if (is_array($parameters['where']['id']) === false) {
			$systemUsersIds = array(
				$parameters['where']['id']
			);
		}

		foreach ($systemUsersIds as $systemUsersId) {
			$systemUserSystemUsersCount = _count(array(
				'in' => $parameters['systemDatabases']['systemUserSystemUsers'],
				'where' => array(
					'systemUserId' => $systemUsersId,
					'systemUserSystemUserId' => $parameters['systemUserId']
				)
			), $response);

			if (
				(($systemUserSystemUsersCount === 1) === false) ||
				(($parameters['systemUserId'] === $systemUsersId) === true)
			) {
				$response['message'] = 'Invalid permissions to delete system user ID ' . $systemUsersId . ', please try again.';
				return $response;
			}
		}

		$systemUserSystemUsersIdsPartsIndex = 0;
		$systemUserSystemUsersIdsParts = array();
		$systemUserSystemUsers = _list(array(
			'data' => array(
				'systemUserId'
			),
			'in' => $parameters['systemDatabases']['systemUserSystemUsers'],
			'where' => array(
				'systemUserSystemUserId' => $parameters['where']['id']
			)
		), $response);
		$systemUserSystemUsers = current($systemUserSystemUsers);

		foreach ($systemUserSystemUsers as $systemUserSystemUser) {
			if (empty($systemUserSystemUsersIdsParts[$systemUserSystemUsersIdsPartsIndex][10]) === false) {
				$systemUserSystemUsersIdsPartsIndex++;
			}

			$systemUserSystemUsersIdsParts[$systemUserSystemUsersIdsPartsIndex][] = $systemUserSystemUser['systemUserId'];
		}

		$systemDatabaseTableKeys = array(
			'systemUserAuthenticationTokens',
			'systemUserAuthenticationTokenScopes',
			'systemUserAuthenticationTokenSources',
			'systemUserRequestLogs'
		);

		foreach ($systemUserSystemUsersIdsParts as $systemUserSystemUsersIdsPart) {
			foreach ($systemDatabaseTableKeys as $systemDatabaseTableKey) {
				_delete(array(
					'in' => $parameters['systemDatabases'][$systemDatabaseTableKey],
					'where' => array(
						'systemUserId' => $systemUserSystemUsersIdsPart
					)
				), $response);
			}

			_delete(array(
				'in' => $parameters['systemDatabases']['systemUsers'],
				'where' => array(
					'id' => $systemUserSystemUsersIdsPart
				)
			), $response);
			_delete(array(
				'in' => $parameters['systemDatabases']['systemUserSystemUsers'],
				'where' => array(
					'either' => array(
						'systemUserId' => $systemUserSystemUsersIdsPart,
						'systemUserSystemUserId' => $systemUserSystemUsersIdsPart
					)
				)
			), $response);
		}

		$response['message'] = 'System users deleted successfully.';
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
