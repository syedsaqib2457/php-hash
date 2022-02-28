<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'systemUserAuthenticationTokens',
		'systemUserAuthenticationTokenScopes',
		'systemUserAuthenticationTokenSources',
		'systemUserSystemUsers'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['systemUserAuthenticationTokens'] = $systemDatabasesConnections['systemUserAuthenticationTokens'];
	$parameters['system_databases']['systemUserAuthenticationTokenScopes'] = $systemDatabasesConnections['systemUserAuthenticationTokenScopes'];
	$parameters['system_databases']['systemUserAuthenticationTokenSources'] = $systemDatabasesConnections['systemUserAuthenticationTokenSources'];
	$parameters['system_databases']['systemUserSystemUsers'] = $systemDatabasesConnections['systemUserSystemUsers'];

	function _deleteSystemUserAuthenticationTokens($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'System user authentication token must have an ID, please try again.';
			return $response;
		}

		$systemUserAuthenticationTokens = _list(array(
			'data' => array(
				'id',
				'systemUserId',
				'value'
			),
			'in' => $parameters['systemDatabases']['systemUserAuthenticationTokens'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);

		foreach ($systemUserAuthenticationTokens as $systemUserAuthenticationToken) {
			$systemUserSystemUserCount = _count(array(
				'in' => $parameters['systemDatabases']['systemUserSystemUsers'],
				'where' => array(
					'systemUserId' => $systemUserAuthenticationToken['systemUserId'],
					'systemUserSystemUserId' => $parameters['systemUserId']
				)
			), $response);

			if (
				(($systemUserSystemUserCount > 0) === false) &&
				(($parameters['systemUserId'] === $systemUserAuthenticationToken['systemUserId']) === false)
			) {
				$response['message'] = 'Invalid permissions to delete system user authentication token ' . $systemUserAuthenticationToken['id'] . ', please try again.';
				return $response;
			}

			if (($parameters['systemUserAuthenticationToken'] === $systemUserAuthenticationToken['value']) === true) {
				$response['message'] = 'System user authentication token ID ' . $systemUserAuthenticationToken['id'] . ' is the current system user authentication token, please try again.';
				return $response;
			}
		}

		$systemDatabaseTableKeys = array(
			'systemUserAuthenticationTokenScopes',
			'systemUserAuthenticationTokenSources',
		);

		foreach ($systemDatabaseTableKeys as $systemDatabaseTableKey) {
			_delete(array(
				'in' => $parameters['systemDatabases'][$systemDatabaseTableKey],
				'where' => array(
					'systemUserAuthenticationTokenId' => $parameters['where']['id']
				)
			), $response);
		}

		_delete(array(
			'in' => $parameters['systemDatabases']['systemUserAuthenticationTokens'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$response['message'] = 'System user authentication tokens deleted successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'deleteSystemUserAuthenticationTokens') === true) {
		$response = _deleteSystemUserAuthenticationTokens($parameters, $response);
	}
?>
