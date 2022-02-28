<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodes'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodes'] = $systemDatabasesConnections['nodes'];

	function _listNodes($parameters, $response) {
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
			'in' => $parameters['systemDatabases']['nodes'],
			'where' => $parameters['where']
		), $response);
		$nodes = _list(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['nodes'],
			'limit' => $parameters['pagination']['resultsPerPageCount'],
			'offset' => (($parameters['pagination']['resultsPageNumber'] - 1) * $parameters['pagination']['resultsPerPageCount']),
			'sort' => $parameters['sort'],
			'where' => $parameters['where']
		), $response);

		if (empty($nodes) === false) {
			$mostRecentNode = _list(array(
				'data' => array(
					'modifiedTimestamp'
				),
				'in' => $parameters['systemDatabases']['nodes'],
				'limit' => 1,
				'sort' => array(
					'modifiedTimestamp' => 'descending'
				),
				'where' => $parameters['where']
			), $response);
			$mostRecentNode = current($mostRecentNode);
			$parameters['pagination']['modifiedTimestamp'] = $mostRecentNode['modifiedTimestamp'];
		}

		$response['data'] = $nodes;
		$response['message'] = 'Nodes listed successfully.';
		$response['pagination'] = $parameters['pagination'];
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'listNodes') === true) {
		$response = _listNodes($parameters, $response);
	}
?>
