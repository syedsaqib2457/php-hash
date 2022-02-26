<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'systemUserSystemUsers',
		'systemUsers'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['systemUserSystemUsers'] = $systemDatabasesConnections['systemUserSystemUsers'];
	$parameters['systemDatabases']['systemUsers'] = $systemDatabasesConnections['systemUsers'];

	function _listSystemUsers($parameters, $response) {
		if (empty($parameters['systemUserAuthenticationToken']) === true) {
			return $response;
		}

		if (empty($parameters['pagination']['resultsPageNumber']) === true) {
			$parameters['pagination']['resultsPageNumber'] = 1;
		}

		if (empty($parameters['pagination']['resultsPerPageCount']) === true) {
			$parameters['pagination']['resultsPerPageCount'] = 100;
		}

		if (empty($parameters['where']['id']) === true) {
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
				$parameters['where']['id'][] = $systemUserSystemUser['systemUserId'];
			}
		} else {
			if (is_array($parameters['where']['id']) === false) {
				$parameters['where']['id'][] = $parameters['where']['id'];
			}

			foreach ($parameters['where']['id'] as $systemUserId) {
				$systemUserSystemUserCount = _count(array(
					'in' => $parameters['systemDatabases']['systemUserSystemUsers'],
					'where' => array(
						'systemUserId' => $systemUserId,
						'systemUserSystemUserId' => $parameters['systemUserId']
					)
				), $response);

				if (($systemUserSystemUserCount === 0) === true) {
					$response['message'] = 'Invalid permissions to list system user for system user ID ' . $systemUserId . ', please try again.';
					return $response;
				}
			}
		}

		$parameters['pagination']['resultsTotalCount'] = _count(array(
			'in' => $parameters['systemDatabases']['systemUsers'],
			'where' => $parameters['where']
		), $response);
		$systemUsers = _list(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['systemUsers'],
			'limit' => $parameters['pagination']['resultsPerPageCount'],
			'offset' => (($parameters['pagination']['resultsPageNumber'] - 1) * $parameters['pagination']['resultsPerPageCount']),
			'sort' => $parameters['sort'],
			'where' => $parameters['where']
		), $response);
		$response['data'] = $systemUsers;
		$response['message'] = 'System users listed successfully.';
		$response['pagination'] = $parameters['pagination'];
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list-system-users') === true) {
		$response = _listSystemUsers($parameters, $response);
	}
?>
