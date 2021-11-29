<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_processes'
	), $parameters['system_databases'], $response);

	function _listNodeProcesses($parameters, $response) {
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
			'in' => $parameters['system_databases']['node_processes'],
			'where' => $parameters['where']
		), $response);
		// todo: add user input for columns and sort order
		$nodeProcesses = _list(array(
			'in' => $parameters['system_databases']['node_processes'],
			'limit' => $pagination['results_count_per_page'],
			'offset' => (($pagination['results_page_number'] - 1) * $pagination['results_count_per_page']),
			'where' => $parameters['where']
		), $response);

		if (empty($nodeProcesses) === false) {
			$mostRecentNodeProcess = _list(array(
				'data' => array(
					'modified_timestamp'
				),
				'in' => $parameters['system_databases']['node_processes'],
				'limit' => 1,
				'sort' => array(
					'data' => 'modified_timestamp',
					'order' => 'descending'
				),
				'where' => $parameters['where']
			), $response);
			$mostRecentNodeProcess = current($mostRecentNodeProcess);
			$pagination['modified_timestamp'] = $mostRecentNodeProcess['modified_timestamp'];
		}

		$response['data'] = $nodeProcesses;
		$response['message'] = 'Node processes listed successfully.';
		$response['pagination'] = $pagination;
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list_node_processes') === true) {
		$response = _listNodeProcesses($parameters, $response);
		_output($response);
	}
?>
