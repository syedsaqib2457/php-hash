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
			$response['message'] = 'System user authentication tokens must have IDs, please try again.';
			return $response;
		}

		$systemUserAuthenticationTokensIds = $parameters['where']['id'];

		if (is_array($systemUserAuthenticationTokensIds) === false) {
			$systemUserAuthenticationTokensIds = array(
				$systemUserAuthenticationTokensIds
			);
		}

		$systemUserAuthenticationTokensIdsPartsIndex = 0;
		$systemUserAuthenticationTokensIdsParts = array();

		foreach ($systemUserAuthenticationTokensIds as $systemUserAuthenticationTokensId) {
			if (empty($systemUserAuthenticationTokensIdsParts[$systemUserAuthenticationTokensIdsPartsIndex][10]) === false) {
				$systemUserAuthenticationTokensIdsPartsIndex++;
			}

			$systemUserAuthenticationTokensIdsParts[$systemUserAuthenticationTokensIdsPartsIndex][] = $systemUserAuthenticationTokensId;
		}

		foreach ($systemUserAuthenticationTokensIdsParts as $systemUserAuthenticationTokensIdsPart) {
			$systemUserAuthenticationTokens = _list(array(
				'data' => array(
					'id',
					'systemUserId'
				),
				'in' => $parameters['systemDatabases']['systemUserAuthenticationTokens'],
				'where' => array(
					'id' => $systemUserAuthenticationTokensIdsPart
				)
			), $response);

			foreach ($systemUserAuthenticationTokens as $systemUserAuthenticationToken) {
				$systemUserSystemUsersCount = _count(array(
					'in' => $parameters['systemDatabases']['systemUserSystemUsers'],
					'where' => array(
						'systemUserId' => $systemUserAuthenticationToken['systemUserId'],
						'systemUserSystemUserId' => $parameters['systemUserId']
					)
				), $response);

				if (($systemUserSystemUsersCount === 1) === false) {
					$response['message'] = 'Invalid permissions to delete system user authentication token ' . $systemUserAuthenticationToken['id'] . ', please try again.';
					return $response;
				}

				if (($parameters['systemUserId'] === $systemUserAuthenticationToken['systemUserId']) === true) {
					$systemUserAuthenticationTokensCount = _count(array(
						'in' => $parameters['systemDatabases']['systemUserAuthenticationTokens'],
						'where' => array(
							'systemUserId' => $systemUserAuthenticationToken['systemUserId']
						)
					), $response);

					if (($systemUserAuthenticationTokensCount === 1) === true) {
						$response['message'] = 'System user authentication token ID ' . $systemUserAuthenticationToken['id'] . ' is the only current system user authentication token, please try again.';
						return $response;
					}
				}
			}
		}

		$systemDatabaseTablesKeys = array(
			'systemUserAuthenticationTokenScopes',
			'systemUserAuthenticationTokenSources',
		);

		foreach ($systemUserAuthenticationTokensIdsParts as $systemUserAuthenticationTokensIdsPart) {
			foreach ($systemDatabaseTablesKeys as $systemDatabaseTablesKey) {
				_delete(array(
					'in' => $parameters['systemDatabases'][$systemDatabaseTablesKey],
					'where' => array(
						'systemUserAuthenticationTokenId' => $systemUserAuthenticationTokensIdsPart
					)
				), $response);
			}

			_delete(array(
				'in' => $parameters['systemDatabases']['systemUserAuthenticationTokens'],
				'where' => array(
					'id' => $systemUserAuthenticationTokensIdsPart
				)
			), $response);
		}

		$response['message'] = 'System user authentication tokens deleted successfully.';
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
