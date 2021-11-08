<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'node_request_limit_rules'
	), $parameters['databases'], $response);

	function _addNodeRequestLimitRules($parameters, $response) {
		$parameters['data']['id'] = random_bytes(10) . time() . random_bytes(10);

		if (isset($parameters['data']['interval_minutes']) === true) {
			if (
				(is_numeric($parameters['data']['interval_minutes']) === false) ||
				((strval(intval($parameters['data']['interval_minutes'])) === $parameters['data']['interval_minutes']) === false)
			) {
				$response['message'] = 'Invalid node request limit rule interval minutes, please try again.';
				return $response;
			}
		} else {
			$parameters['data']['interval_minutes'] = '0';
		}

		if (isset($parameters['data']['request_count']) === true) {
			if (
				(is_numeric($parameters['data']['request_count']) === false) ||
				((strval(intval($parameters['data']['request_count'])) === $parameters['data']['request_count']) === false)
			) {
				$response['message'] = 'Invalid node request limit rule request count, please try again.';
				return $response;
			}
		} else {
			$parameters['data']['request_count'] = '0';
		}

		// todo: validate + save data

		_save(array(
			'data' => array_intersect_key($parameters['data'], array(
				'id' => true,
				'interval_minutes' => true,
				'request_count' => true
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
