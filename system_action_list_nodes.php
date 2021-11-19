<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'nodes'
	), $parameters['databases'], $response);

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

		// todo: count results_count_total
		$nodes = _list(array(
			'in' => $parameters['databases']['nodes'],
			'limit' => $pagination['results_count_per_page'],
			'offset' => (($pagination['results_page_number'] - 1) * $pagination['results_count_per_page']),
			'where' => $parameters['where']
		), $response);
		$response['data'] = $node;
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
