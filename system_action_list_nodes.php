<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'nodes'
	), $parameters['databases'], $response);

	function _listNodes($parameters, $response) {
		$pagination = array(
			'results_count_per_page' => 100
		);

		if (empty($parameters['pagination']['results_count_per_page']) === false) {

		}

		// todo: count results_count_total
		// todo: set current_results_page_number
		$nodes = _list(array(
			'in' => $parameters['databases']['nodes'],
			'where' => $parameters['where']
		), $response);
		$response['data'] = $node;
		$response['message'] = 'Nodes listed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list_nodes') === true) {
		$response = _listNodes($parameters, $response);
		_output($response);
	}
?>
