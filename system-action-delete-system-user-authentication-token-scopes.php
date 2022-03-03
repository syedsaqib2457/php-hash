<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'systemUserAuthenticationTokenScopes',
		'systemUserSystemUsers'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['systemUserAuthenticationTokenScopes'] = $systemDatabasesConnections['systemUserAuthenticationTokenScopes'];
	$parameters['systemDatabases']['systemUserSystemUsers'] = $systemDatabasesConnections['systemUserSystemUsers'];

	function _deleteSystemUserAuthenticationTokenScopes($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'System user authentication token scopes must have IDs, please try again.';
			return $response;
		}

		$systemUserAuthenticationTokenScopesIds = $parameters['where']['id'];

		if (is_array($systemUserAuthenticationTokenScopesIds) === false) {
			$systemUserAuthenticationTokenScopesIds = array(
				$systemUserAuthenticationTokenScopesIds
			);
		}

		$systemUserAuthenticationTokenScopesIdsPartsIndex = 0;
		$systemUserAuthenticationTokenScopesIdsParts = array();

		foreach ($systemUserAuthenticationTokenScopesIds as $systemUserAuthenticationTokenScopesId) {
			if (empty($systemUserAuthenticationTokenScopesIdsParts[$systemUserAuthenticationTokenScopesIdsPartsIndex][10]) === false) {
				$systemUserAuthenticationTokenScopesIdsPartsIndex++;
			}

			$systemUserAuthenticationTokenScopesIdsParts[$systemUserAuthenticationTokenScopesIdsPartsIndex][] = $systemUserAuthenticationTokenScopesId;
		}

		foreach ($systemUserAuthenticationTokenScopesIdsParts as $systemUserAuthenticationTokenScopesIdsPart) {
			$systemUserAuthenticationTokenScopes = _list(array(
				'data' => array(
					'id',
					'systemUserId'
				),
				'in' => $parameters['systemDatabases']['systemUserAuthenticationTokenScopes'],
				'where' => array(
					'id' => $systemUserAuthenticationTokenScopesIdsPart
				)
			), $response);

			foreach ($systemUserAuthenticationTokenScopes as $systemUserAuthenticationTokenScope) {
				if (($parameters['systemUserId'] === $systemUserAuthenticationTokenScope['systemUserId']) === true) {
					$response['message'] = 'System user authentication token scope must belong to another user, please try again.';
					return $response;
				}

				$systemUserSystemUserCount = _count(array(
					'in' => $parameters['systemDatabases']['systemUserSystemUsers'],
					'where' => array(
						'systemUserId' => $systemUserAuthenticationTokenScope['systemUserId'],
						'systemUserSystemUserId' => $parameters['systemUserId']
					)
				), $response);

				if (($systemUserSystemUserCount === 1) === false) {
					$response['message'] = 'Invalid permissions to delete system user authentication token scope ID ' . $systemUserAuthenticationTokenScope['id'] . ', please try again.';
					return $response;
				}
			}

			_delete(array(
				'in' => $parameters['systemDatabases']['systemUserAuthenticationTokenScopes'],
				'where' => array(
					'id' => $systemUserAuthenticationTokenScopesIdsPart
				)
			), $response);
		}

		$response['message'] = 'System user authentication token scopes deleted successfully.';
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
