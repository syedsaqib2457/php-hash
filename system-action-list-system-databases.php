<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'systemDatabases'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['systemDatabases'] = $systemDatabasesConnections['systemDatabases'];

	function _listSystemDatabases($parameters, $response) {
		if (empty($parameters['systemUserAuthenticationToken']) === true) {
			return $response;
		}

		if (empty($parameters['pagination']['resultsPageNumber']) === true) {
			$parameters['pagination']['resultsPageNumber'] = 1;
		}

		if (empty($parameters['pagination']['resultsPerPageCount']) === true) {
			$parameters['pagination']['resultsPerPageCount'] = 100;
		}

		$parameters['pagination']['resultsTotalCount'] = _count(array(
			'in' => $parameters['systemDatabases']['systemDatabases'],
			'where' => $parameters['where']
		), $response);
		$systemUserSystemUsers = _list(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['systemDatabases'],
			'limit' => $parameters['pagination']['resultsPerPageCount'],
			'offset' => (($parameters['pagination']['resultsPageNumber'] - 1) * $parameters['pagination']['resultsPerPageCount']),
			'sort' => $parameters['sort'],
			'where' => $parameters['where']
		), $response);

		if (empty($systemDatabases) === false) {
			$mostRecentSystemDatabase = _list(array(
				'data' => array(
					'modifiedTimestamp'
				),
				'in' => $parameters['systemDatabases']['systemDatabases'],
				'limit' => 1,
				'sort' => array(
					'modifiedTimestamp' => 'descending'
				),
				'where' => $parameters['where']
			), $response);
			$mostRecentSystemDatabase = current($mostRecentSystemDatabase);
			$parameters['pagination']['modifiedTimestamp'] = $mostRecentSystemDatabase['modifiedTimestamp'];
		}

		$response['data'] = $systemDatabases;
		$response['message'] = 'System databases listed successfully.';	
		$response['pagination'] = $parameters['pagination'];
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list-system-databases') === true) {
		$response = _listSystemDatabases($parameters, $response);
	}
?>
