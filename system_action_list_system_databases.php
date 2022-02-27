<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'systemDatabases'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['systemDatabases'] = $systemDatabasesConnections['systemDatabases'];

	function _listSystemDatabases($parameters, $response) {
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
			'in' => $parameters['systemDatabases']['systemDatabases'],
			'where' => $parameters['where']
		), $response);
		$systemDatabases = _list(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['systemDatabases'],
			'limit' => $pagination['resultsCountPerPage'],
			'offset' => (($pagination['resultsPageNumber'] - 1) * $pagination['resultsCountPerPage']),
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
			$pagination['modifiedTimestamp'] = $mostRecentSystemDatabase['modifiedTimestamp'];
		}

		$response['data'] = $systemDatabases;
		$response['message'] = 'System databases listed successfully.';	
		$response['pagination'] = $pagination;
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list-system-databases') === true) {
		$response = _listSystemDatabases($parameters, $response);
	}
?>
