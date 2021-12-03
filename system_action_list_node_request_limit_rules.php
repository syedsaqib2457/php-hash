<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_request_limit_rules'
	), $parameters['system_databases'], $response);

	function _listNodeRequestLimitRules($parameters, $response) {
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
			'in' => $parameters['system_databases']['node_request_limit_rules'],
			'where' => $parameters['where']
		), $response);
		$nodeRequestLimitRules = _list(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_request_limit_rules'],
			'limit' => $pagination['results_count_per_page'],
			'offset' => (($pagination['results_page_number'] - 1) * $pagination['results_count_per_page']),
			'sort' => $parameters['sort'],
			'where' => $parameters['where']
		), $response);

		if (empty($nodeRequestLimitRules) === false) {
			$mostRecentNodeRequestLimitRules = _list(array(
				'data' => array(
					'modified_timestamp'
				),
				'in' => $parameters['system_databases']['node_request_limit_rules'],
				'limit' => 1,
				'sort' => array(
					'modified_timestamp' => 'descending'
				),
				'where' => $parameters['where']
			), $response);
			$mostRecentNodeRequestLimitRules = current($mostRecentNodeRequestLimitRules);
			$pagination['modified_timestamp'] = $mostRecentNodeRequestLimitRules['modified_timestamp'];
		}

		$response['data'] = $nodeRequestLimitRules;
		$response['message'] = 'Node request limit rules listed successfully.';
		$response['pagination'] = $pagination;
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'node_request_limit_rules') === true) {
		$response = _listNodeRequestLimitRules($parameters, $response);
		_output($response);
	}
?>
