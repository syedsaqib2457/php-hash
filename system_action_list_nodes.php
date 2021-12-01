<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'nodes'
	), $parameters['system_databases'], $response);

	function _listNodes($parameters, $response) {
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
			'in' => $parameters['system_databases']['nodes'],
			'where' => $parameters['where']
		), $response);
		$nodes = _list(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['nodes'],
			'limit' => $pagination['results_count_per_page'],
			'offset' => (($pagination['results_page_number'] - 1) * $pagination['results_count_per_page']),
			'sort' => $parameters['sort'],
			'where' => $parameters['where']
		), $response);

		if (empty($nodes) === false) {
			$mostRecentNode = _list(array(
				'data' => array(
					'modified_timestamp'
				),
				'in' => $parameters['system_databases']['nodes'],
				'limit' => 1,
				'sort' => array(
					'modified_timestamp' => 'descending'
				),
				'where' => $parameters['where']
			), $response);
			$mostRecentNode = current($mostRecentNode);
			$pagination['modified_timestamp'] = $mostRecentNode['modified_timestamp'];
		}

		$response['data'] = $nodes;
		$response['message'] = 'Nodes listed successfully.';
		$response['pagination'] = $pagination;
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list_nodes') === true) {
		$response = _listNodes($parameters, $response);
		_output($response);
	}
?>
