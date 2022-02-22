<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_request_limit_rules'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_request_limit_rules'] = $systemDatabasesConnections['node_request_limit_rules'];

	function _addNodeRequestLimitRule($parameters, $response) {
		if (empty($parameters['data']['interval_minutes']) === true) {
			$response['message'] = 'Node request limit rule must have interval minutes, please try again.';
			return $response;
		}

		if (is_numeric($parameters['data']['interval_minutes']) === false) {
			$response['message'] = 'Invalid node request limit rule interval minutes, please try again.';
			return $response;
		}

		if (
			(isset($parameters['data']['request_count']) === true) &&
			(isset($parameters['data']['request_count_interval_minutes']) === true)
		) {
			if (
				(is_numeric($parameters['data']['request_count']) === false) ||
				(($parameters['data']['request_count'] < 0) === true)
			) {
				$response['message'] = 'Invalid node request limit rule request count, please try again.';
				return $response;
			}

			if (
				(is_numeric($parameters['data']['request_count_interval_minutes']) === false) ||
				(($parameters['data']['request_count_interval_minutes'] < 0) === true)
			) {
				$response['message'] = 'Invalid node request limit rule request count interval minutes, please try again.';
				return $response;
			}
		} else {
			$parameters['data']['request_count'] = '0';
			$parameters['data']['request_count_interval_minutes'] = '0';
		}

		$existingNodeRequestLimitRuleCount = _count(array(
			'in' => $parameters['system_databases']['node_request_limit_rules'],
			'where' => array(
				'interval_minutes' => $parameters['data']['interval_minutes'],
				'request_count' => $parameters['data']['request_count'],
				'request_count_interval_minutes' => $parameters['data']['request_count_interval_minutes']
			)
		), $response);

		if (($existingNodeRequestLimitRuleCount === 1) === true) {
			$response['message'] = 'Node request limit rule already exists, please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_request_limit_rules']
		), $response);
		$nodeRequestLimitRule = _list(array(
			'in' => $parameters['system_databases']['node_request_limit_rules'],
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

	if (($parameters['action'] === 'add_node_request_limit_rule') === true) {
		$response = _addNodeRequestLimitRule($parameters, $response);
	}
?>
