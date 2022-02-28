<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'systemDatabaseColumns'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['systemDatabaseColumns'] = $systemDatabasesConnections['systemDatabaseColumns'];

	function _listSystemDatabaseColumns($parameters, $response) {
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
			'in' => $parameters['systemDatabases']['systemDatabaseColumns'],
			'where' => $parameters['where']
		), $response);
		$systemDatabaseColumns = _list(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['systemDatabaseColumns'],
			'limit' => $parameters['pagination']['resultsPerPageCount'],
			'offset' => (($parameters['pagination']['resultsPageNumber'] - 1) * $parameters['pagination']['resultsPerPageCount']),
			'sort' => $parameters['sort'],
			'where' => $parameters['where']
		), $response);

		if (empty($systemDatabaseColumns) === false) {
			$mostRecentSystemDatabaseColumn = _list(array(
				'data' => array(
					'modifiedTimestamp'
				),
				'in' => $parameters['systemDatabases']['systemDatabaseColumns'],
				'limit' => 1,
				'sort' => array(
					'modifiedTimestamp' => 'descending'
				),
				'where' => $parameters['where']
			), $response);
			$mostRecentSystemDatabaseColumn = current($mostRecentSystemDatabaseColumn);
			$parameters['pagination']['modifiedTimestamp'] = $mostRecentSystemDatabaseColumn['modifiedTimestamp'];
		}

		$response['data'] = $systemDatabaseColumns;
		$response['message'] = 'System database columns listed successfully.';
		$response['pagination'] = $parameters['pagination'];
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
