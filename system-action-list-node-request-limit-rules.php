<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeRequestLimitRules'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeRequestLimitRules'] = $systemDatabasesConnections['nodeRequestLimitRules'];

	function _listNodeRequestLimitRules($parameters, $response) {
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
			'in' => $parameters['systemDatabases']['nodeRequestLimitRules'],
			'where' => $parameters['where']
		), $response);
		$nodeRequestLimitRules = _list(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['nodeRequestLimitRules'],
			'limit' => $parameters['pagination']['resultsPerPageCount'],
			'offset' => (($parameters['pagination']['resultsPageNumber'] - 1) * $parameters['pagination']['resultsPerPageCount']),
			'sort' => $parameters['sort'],
			'where' => $parameters['where']
		), $response);

		if (empty($nodeRequestLimitRules) === false) {
			$mostRecentNodeRequestLimitRules = _list(array(
				'data' => array(
					'modifiedTimestamp'
				),
				'in' => $parameters['systemDatabases']['nodeRequestLimitRules'],
				'limit' => 1,
				'sort' => array(
					'modifiedTimestamp' => 'descending'
				),
				'where' => $parameters['where']
			), $response);
			$mostRecentNodeRequestLimitRules = current($mostRecentNodeRequestLimitRules);
			$pagination['modifiedTimestamp'] = $mostRecentNodeRequestLimitRules['modifiedTimestamp'];
		}

		$response['data'] = $nodeRequestLimitRules;
		$response['message'] = 'Node request limit rules listed successfully.';
		$response['pagination'] = $parameters['pagination'];
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
