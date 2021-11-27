<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'system_databases'
	), $parameters['system_databases'], $response);

	function _listSystemDatabases($parameters, $response) {
		$pagination = array(
			'results_count_per_page' => 100,
			'results_page_number' => 1
		);

		if (
			(empty($parameters['pagination']['results_count_per_page']) === false) &&
			(is_int($parameters['pagination']['results_count_per_page']) === true)
		) {
			$pagination['results_count_per_page'] = $parameters['pagination']['results_count_per_page'];
		}

		if (
			(empty($parameters['pagination']['results_page_number']) === false) &&
			(is_int($parameters['pagination']['results_page_number']) === true)
		) {
			$pagination['results_page_number'] = $parameters['pagination']['results_page_number'];
		}

		$pagination['results_count_total'] = _count(array(
			'in' => $parameters['system_databases']['system_databases'],
			'where' => $parameters['where']
		), $response);
		// todo: add user input for sort order
		$systemDatabases = _list(array(
			'in' => $parameters['system_databases']['system_databases'],
			'limit' => $pagination['results_count_per_page'],
			'offset' => (($pagination['results_page_number'] - 1) * $pagination['results_count_per_page']),
			'where' => $parameters['where']
		), $response);

		if (empty($systemDatabases) === false) {
			$mostRecentSystemDatabase = _list(array(
				'columns' => array(
					'modified_timestamp'
				),
				'in' => $parameters['system_databases']['system_databases'],
				'limit' => 1,
				'sort' => array(
					'column' => 'modified_timestamp',
					'order' => 'descending'
				),
				'where' => $parameters['where']
			), $response);
			$mostRecentSystemDatabase = current($mostRecentSystemDatabase);
			$pagination['modified_timestamp'] = $mostRecentSystemDatabase['modified_timestamp'];
		}

		$response['data'] = $systemDatabases;
		$response['message'] = 'System databases listed successfully.';	
		$response['pagination'] = $pagination;
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list_system_databases') === true) {
		$response = _listSystemDatabases($parameters, $response);
		_output($response);
	}
?>
