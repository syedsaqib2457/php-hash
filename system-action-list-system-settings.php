<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _listSystemSettings($parameters, $response) {
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
			'in' => $parameters['systemDatabases']['systemSettings'],
			'where' => $parameters['where']
		), $response);
		$systemSettings = _list(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['systemSettings'],
			'limit' => $parameters['pagination']['resultsPerPageCount'],
			'offset' => (($parameters['pagination']['resultsPageNumber'] - 1) * $parameters['pagination']['resultsPerPageCount']),
			'sort' => $parameters['sort'],
			'where' => $parameters['where']
		), $response);
		$response['data'] = $systemSettings;
		$response['message'] = 'System settings listed successfully.';
		$response['pagination'] = $parameters['pagination'];
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list-system-settings') === true) {
		$response = _listSystemSettings($parameters, $response);
	}
?>
