<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'systemUserAuthenticationTokenScopes',
		'systemUserSystemUsers'
	), $parameters['system_databases'], $response);
	$parameters['systemDatabases']['systemUserAuthenticationTokenScopes'] = $systemDatabasesConnections['systemUserAuthenticationTokenScopes'];
	$parameters['systemDatabases']['systemUserSystemUsers'] = $systemDatabasesConnections['systemUserSystemUsers'];

	function _deleteSystemUserAuthenticationTokenScopes($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'System user authentication token scope must have an ID, please try again.';
			return $response;
		}

		$systemUserAuthenticationTokenScopes = _list(array(
			'data' => array(
				'id',
				'systemUserId'
			),
			'in' => $parameters['systemDatabases']['systemUserAuthenticationTokenScopes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);

		foreach ($systemUserAuthenticationTokenScopes as $systemUserAuthenticationTokenScope) {
			if (($parameters['systemUserId'] === $systemUserAuthenticationTokenScope['systemUserId']) === true) {
				$response['message'] = 'System user authentication token scope must belong to another user, please try again.';
				return $response;
			}

			$systemUserSystemUserCount = _count(array(
				'in' => $parameters['system_databases']['system_user_system_users'],
				'where' => array(
					'systemUserId' => $systemUserAuthenticationTokenScope['systemUserId'],
					'systemUserSystemUserId' => $parameters['systemUserId']
				)
			), $response);

			if (
				(($systemUserSystemUserCount > 0) === false) &&
				(($parameters['systemUserId'] === $systemUserAuthenticationTokenScope['systemUserId']) === false)
			) {
				$response['message'] = 'Invalid permissions to delete system user authentication token scope ID ' . $systemUserAuthenticationTokenScope['id'] . ', please try again.';
				return $response;
			}
		}

		_delete(array(
			'in' => $parameters['systemDatabases']['systemUserAuthenticationTokenScopes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$response['message'] = 'System user authentication token scope deleted successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'deleteSystemUserAuthenticationTokenScopes') === true) {
		$response = _deleteSystemUserAuthenticationTokenScopes($parameters, $response);
	}
?>
