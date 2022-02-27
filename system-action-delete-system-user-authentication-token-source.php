<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'systemUserAuthenticationTokenSources',
		'systemUserSystemUsers'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['systemUserAuthenticationTokenSources'] = $systemDatabasesConnections['systemUserAuthenticationTokenSources'];
	$parameters['systemDatabases']['systemUserSystemUsers'] = $systemDatabasesConnections['systemUserSystemUsers'];

	function _deleteSystemUserAuthenticationTokenSource($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'System user authentication token source must have an ID, please try again.';
			return $response;
		}

		$systemUserAuthenticationTokenSources = _list(array(
			'data' => array(
				'id',
				'systemUserId'
			),
			'in' => $parameters['systemDatabases']['systemUserAuthenticationTokenSources'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);

		foreach ($systemUserAuthenticationTokenSources as $systemUserAuthenticationTokenSource) {
			$systemUserSystemUserCount = _count(array(
				'in' => $parameters['systemDatabases']['systemUserSystemUsers'],
				'where' => array(
					'systemUserId' => $systemUserAuthenticationTokenSource['systemUserId'],
					'systemUserSystemUserId' => $parameters['systemUserId']
				)
			), $response);

			if (
				(($systemUserSystemUserCount === 1) === false) ||
				(($parameters['systemUserId'] === $systemUserAuthenticationTokenSource['systemUserId']) === false)
			) {
				$response['message'] = 'Invalid permissions to delete system user authentication token source ID ' . $systemUserAuthenticationTokenSource['id'] . ', please try again.';
				return $response;
			}
		}

		_delete(array(
			'in' => $parameters['systemDatabases']['systemUserAuthenticationTokenSources'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$response['message'] = 'System user authentication token source deleted successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'delete-system-user-authentication-token-source') === true) {
		$response = _deleteSystemUserAuthenticationTokenSource($parameters, $response);
	}
?>
