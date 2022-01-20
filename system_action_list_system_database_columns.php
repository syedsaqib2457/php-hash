<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'system_database_columns'
	), $parameters['system_databases'], $response);

	function _listSystemDatabaseColumns($parameters, $response) {
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
			'in' => $parameters['system_databases']['system_database_columns'],
			'where' => $parameters['where']
		), $response);
		$systemDatabaseColumns = _list(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['system_database_columns'],
			'limit' => $pagination['results_count_per_page'],
			'offset' => (($pagination['results_page_number'] - 1) * $pagination['results_count_per_page']),
			'sort' => $parameters['sort'],
			'where' => $parameters['where']
		), $response);

		if (empty($systemDatabaseColumns) === false) {
			$mostRecentSystemDatabaseColumn = _list(array(
				'data' => array(
					'modified_timestamp'
				),
				'in' => $parameters['system_databases']['system_database_columns'],
				'limit' => 1,
				'sort' => array(
					'modified_timestamp' => 'descending'
				),
				'where' => $parameters['where']
			), $response);
			$mostRecentSystemDatabaseColumn = current($mostRecentSystemDatabaseColumn);
			$pagination['modified_timestamp'] = $mostRecentSystemDatabaseColumn['modified_timestamp'];
		}

		$response['data'] = $systemDatabaseColumns;
		$response['message'] = 'System database columns listed successfully.';	
		$response['pagination'] = $pagination;
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list_system_database_columns') === true) {
		$response = _listSystemDatabaseColumns($parameters, $response);
	}
?>
