<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'systemUserRequestLogs',
		'systemUserSystemUsers'
	), $parameters['system_databases'], $response);
	$parameters['systemDatabases']['systemUserRequestLogs'] = $systemDatabasesConnections['systemUserRequestLogs'];
	$parameters['systemDatabases']['systemUserSystemUsers'] = $systemDatabasesConnections['systemUserSystemUsers'];

	function _listSystemUserRequestLogs($parameters, $response) {
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
					$response['message'] = 'Invalid permissions to list system user request logs for system user ID ' . $systemUserId . ', please try again.';
					return $response;
				}
			}
		}

		$parameters['pagination']['resultsTotalCount'] = _count(array(
			'in' => $parameters['systemDatabases']['systemUserRequestLogs'],
			'where' => $parameters['where']
		), $response);
		$systemUserRequestLogs = _list(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['systemUserRequestLogs'],
			'limit' => $parameters['pagination']['resultsPerPageCount'],
			'offset' => (($parameters['pagination']['resultsPageNumber'] - 1) * $parameters['pagination']['resultsPerPageCount']),
			'sort' => $parameters['sort'],
			'where' => $parameters['where']
		), $response);
		$response['data'] = $systemUserRequestLogs;
		$response['message'] = 'System user request logs listed successfully.';
		$response['pagination'] = $parameters['pagination'];
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
