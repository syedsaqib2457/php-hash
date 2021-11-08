<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'node_request_limit_rules'
	), $parameters['databases'], $response);

	function _addNodeRequestLimitRules($parameters, $response) {
		$parameters['data']['id'] = random_bytes(10) . time() . random_bytes(10);

		if (empty($parameters['data']['interval_minutes']) === true) {
			$response['message'] = 'Node request limit rule must have interval minutes, please try again.';
			return $response;
		}

		if (true) {
			$response['message'] = 'Invalid node request limit rule interval minutes, please try again.';
			return $response;
		}

		// todo: validate + save data

		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'id' => true
			)),
			'in' => $parameters['databases']['node_request_limit_rules']
		), $response);
		$nodeRequestLimitRule = _list(array(
			'in' => $parameters['databases']['node_request_limit_rules'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeRequestLimitRule = current($nodeRequestLimitRule);
		$response['data'] = $nodeRequestLimitRule;
		$response['message'] = 'Node request limit rule added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_request_limit_rules') === true) {
		$response = _addNodeRequestLimitRules($parameters, $response);
		_output($response);
	}
?>
