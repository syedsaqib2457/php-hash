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

	function _deleteSystemUserAuthenticationTokenSources($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'System user authentication token sources must have IDs, please try again.';
			return $response;
		}

		$systemUserAuthenticationTokenSourcesIds = $parameters['where']['id'];
			
		if (is_array($systemUserAuthenticationTokenSourcesIds) === false) {
			$systemUserAuthenticationTokenSourcesIds = array(
				$systemUserAuthenticationTokenSourcesIds
			);
		}

		$systemUserAuthenticationTokensSourcesIdsPartsIndex = 0;
		$systemUserAuthenticationTokensSourcesIdsParts = array();

		foreach ($systemUserAuthenticationTokenSourcesIds as $systemUserAuthenticationTokenSourcesId) {
			if (empty($systemUserAuthenticationTokensSourcesIdsParts[$systemUserAuthenticationTokensSourcesIdsPartsIndex][10]) === false) {
				$systemUserAuthenticationTokensSourcesIdsPartsIndex++;
			}

			$systemUserAuthenticationTokensSourcesIdsParts[$systemUserAuthenticationTokensSourcesIdsPartsIndex][] = $systemUserAuthenticationTokenSourcesId;
		}

		foreach ($systemUserAuthenticationTokensSourcesIdsParts as $systemUserAuthenticationTokensSourcesIdsPart) {
			$systemUserAuthenticationTokenSources = _list(array(
				'data' => array(
					'id',
					'systemUserId'
				),
				'in' => $parameters['systemDatabases']['systemUserAuthenticationTokenSources'],
				'where' => array(
					'id' => $systemUserAuthenticationTokensSourcesIdsPart
				)
			), $response);

			foreach ($systemUserAuthenticationTokenSources as $systemUserAuthenticationTokenSource) {
				$systemUserSystemUsersCount = _count(array(
					'in' => $parameters['systemDatabases']['systemUserSystemUsers'],
					'where' => array(
						'systemUserId' => $systemUserAuthenticationTokenSource['systemUserId'],
						'systemUserSystemUserId' => $parameters['systemUserId']
					)
				), $response);

				if (($systemUserSystemUsersCount === 1) === false) {
					$response['message'] = 'Invalid permissions to delete system user authentication token source ID ' . $systemUserAuthenticationTokenSource['id'] . ', please try again.';
					return $response;
				}
			}

			_delete(array(
				'in' => $parameters['systemDatabases']['systemUserAuthenticationTokenSources'],
				'where' => array(
					'id' => $systemUserAuthenticationTokensSourcesIdsPart
				)
			), $response);
		}

		$response['message'] = 'System user authentication token sources deleted successfully.';
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
