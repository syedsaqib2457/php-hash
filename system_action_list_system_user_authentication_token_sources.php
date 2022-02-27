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

	function _listSystemUserAuthenticationTokenSources($parameters, $response) {
		if (empty($parameters['systemUserAuthenticationToken']) === true) {
			return $response;
		}

		if (empty($parameters['pagination']['resultsPageNumber']) === true) {
			$parameters['pagination']['resultsPageNumber'] = 1;
		}

		if (empty($parameters['pagination']['resultsPerPageCount']) === true) {
			$parameters['pagination']['resultsPerPageCount'] = 100;
		}

		if (empty($parameters['where']['systemUserId']) === true) {
			$systemUserSystemUsers =  = _list(array(
				'data' => array(
					'systemUserId'
				),
				'in' => $parameters['systemDatabases']['systemUserSystemUsers'],
				'where' => array(
					'either' => array(
						'systemUserId' => $parameters['systemUserId'],
						'systemUserSystemUserId' => $parameters['systemUserId']
					)
				)
			), $response);

			foreach ($systemUserSystemUsers as $systemUserSystemUser) {
				$parameters['where']['systemUserId'][] = $systemUserSystemUser['systemUserId'];
			}
		} else {
			if (is_array($parameters['where']['systemUserId']) === false) {
				$parameters['where']['systemUserId'][] = $parameters['where']['systemUserId'];
			}

			foreach ($parameters['where']['systemUserId'] as $systemUserId) {
				$systemUserSystemUserCount = _count(array(
					'in' => $parameters['systemDatabases']['systemUserSystemUsers'],
					'where' => array(
						'systemUserId' => $systemUserId,
						'systemUserSystemUserId' => $parameters['systemUserId']
					)
				), $response);

				if (($systemUserSystemUserCount === 0) === true) {
					$response['message'] = 'Invalid permissions to list system user authentication token sources for system user ID ' . $systemUserId . ', please try again.';
					return $response;
				}
			}
		}

		$parameters['pagination']['resultsTotalCount'] = _count(array(
			'in' => $parameters['systemDatabases']['systemUserAuthenticationTokenSources'],
			'where' => $parameters['where']
		), $response);
		$systemUserAuthenticationTokenSources = _list(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['systemUserAuthenticationTokenSources'],
			'limit' => $parameters['pagination']['resultsPerPageCount'],
			'offset' => (($parameters['pagination']['resultsPageNumber'] - 1) * $parameters['pagination']['resultsPerPageCount']),
			'sort' => $parameters['sort'],
			'where' => $parameters['where']
		), $response);
		$response['data'] = $systemUserAuthenticationTokenSources;
		$response['message'] = 'System user authentication token sources listed successfully.';
		$response['pagination'] = $parameters['pagination'];
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list-system-user-authentication-token-sources') === true) {
		$response = _listSystemUserAuthenticationTokenSources($parameters, $response);
	}
?>
