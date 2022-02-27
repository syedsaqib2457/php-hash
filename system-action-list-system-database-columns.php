<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'systemDatabaseColumns'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['systemDatabaseColumns'] = $systemDatabasesConnections['systemDatabaseColumns'];

	function _listSystemDatabaseColumns($parameters, $response) {
		$pagination = array(
			'resultsCountPerPage' => 100,
			'resultsPageNumber' => 1
		);

		if (
			(empty($parameters['pagination']['resultsCountPerPage']) === false) &&
			(is_int($parameters['pagination']['resultsCountPerPage']) === true)
		) {
			$pagination['resultsCountPerPage'] = $parameters['pagination']['resultsCountPerPage'];
		}

		if (
			(empty($parameters['pagination']['resultsPageNumber']) === false) &&
			(is_int($parameters['pagination']['resultsPageNumber']) === true)
		) {
			$pagination['resultsPageNumber'] = $parameters['pagination']['resultsPageNumber'];
		}

		$pagination['resultsCountTotal'] = _count(array(
			'in' => $parameters['systemDatabases']['systemDatabaseColumns'],
			'where' => $parameters['where']
		), $response);
		$systemDatabaseColumns = _list(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['systemDatabaseColumns'],
			'limit' => $pagination['resultsCountPerPage'],
			'offset' => (($pagination['resultsPageNumber'] - 1) * $pagination['resultsCountPerPage']),
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
			$pagination['modified_timestamp'] = $mostRecentSystemDatabaseColumn['modifiedTimestamp'];
		}

		$response['data'] = $systemDatabaseColumns;
		$response['message'] = 'System database columns listed successfully.';
		$response['pagination'] = $pagination;
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list-system-database-columns') === true) {
		$response = _listSystemDatabaseColumns($parameters, $response);
	}
?>
