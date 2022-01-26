<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_request_limit_rules'
	), $parameters['system_databases'], $response);

	function _addNodeRequestLimitRule($parameters, $response) {
		if (empty($parameters['data']['interval_minutes']) === true) {
			$response['message'] = 'Node request limit rule must have interval minutes, please try again.';
			return $response;
		}

		if (
			(is_numeric($parameters['data']['interval_minutes']) === false) ||
			((strval(intval($parameters['data']['interval_minutes'])) === $parameters['data']['interval_minutes']) === false)
		) {
			$response['message'] = 'Invalid node request limit rule interval minutes, please try again.';
			return $response;
		}

		if (
			(isset($parameters['data']['request_count']) === true) &&
			(isset($parameters['data']['request_count_interval_minutes']) === true)
		) {
			if (
				(is_numeric($parameters['data']['request_count']) === false) ||
				((strval(intval($parameters['data']['request_count'])) === $parameters['data']['request_count']) === false)
			) {
				$response['message'] = 'Invalid node request limit rule request count, please try again.';
				return $response;
			}

			if (
				(is_numeric($parameters['data']['request_count_interval_minutes']) === false) ||
				((strval(intval($parameters['data']['request_count_interval_minutes'])) === $parameters['data']['request_count_interval_minutes']) === false)
			) {
				$response['message'] = 'Invalid node request limit rule request count, please try again.';
				return $response;
			}
		} else {
			$parameters['data']['request_count'] = '0';
			$parameters['data']['request_count_interval_minutes'] = '0';
		}

		$existingNodeRequestLimitRuleCount = _count(array(
			'in' => $parameters['system_databases']['node_request_limit_rules'],
			'where' => array_intersect_key($parameters['data'], array(
				'interval_minutes' => true,
				'request_count' => true,
				'request_count_interval_minutes' => true
			))
		), $response);

		if (($existingNodeRequestLimitRuleCount > 0) === true) {
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
