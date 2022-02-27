<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeRequestDestinations'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeRequestDestinations'] = $systemDatabasesConnections['nodeRequestDestinations'];

	function _listNodeRequestDestinations($parameters, $response) {
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
			'in' => $parameters['systemDatabases']['nodeRequestDestinations'],
			'where' => $parameters['where']
		), $response);
		$nodeRequestDestinations = _list(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['nodeRequestDestinations'],
			'limit' => $parameters['pagination']['resultsPerPageCount'],
			'offset' => (($parameters['pagination']['resultsPageNumber'] - 1) * $parameters['pagination']['resultsPerPageCount']),
			'sort' => $parameters['sort'],
			'where' => $parameters['where']
		), $response);

		if (empty($nodeRequestDestinations) === false) {
			$mostRecentNodeRequestDestination = _list(array(
				'data' => array(
					'modifiedTimestamp'
				),
				'in' => $parameters['systemDatabases']['nodeRequestDestinations'],
				'limit' => 1,
				'sort' => array(
					'modifiedTimestamp' => 'descending'
				),
				'where' => $parameters['where']
			), $response);
			$mostRecentNodeRequestDestination = current($mostRecentNodeRequestDestination);
			$pagination['modifiedTimestamp'] = $mostRecentNodeRequestDestination['modifiedTimestamp'];
		}

		$response['data'] = $nodeRequestDestinations;
		$response['message'] = 'Node request destinations listed successfully.';
		$response['pagination'] = $parameters['pagination'];
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list-node-request-destinations') === true) {
		$response = _listNodeRequestDestinations($parameters, $response);
	}
?>
