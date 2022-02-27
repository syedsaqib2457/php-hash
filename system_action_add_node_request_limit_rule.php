<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeRequestLimitRules'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeRequestLimitRules'] = $systemDatabasesConnections['nodeRequestLimitRules'];

	function _addNodeRequestLimitRule($parameters, $response) {
		if (empty($parameters['data']['intervalMinutes']) === true) {
			$response['message'] = 'Node request limit rule must have interval minutes, please try again.';
			return $response;
		}

		if (is_numeric($parameters['data']['intervalMinutes']) === false) {
			$response['message'] = 'Invalid node request limit rule interval minutes, please try again.';
			return $response;
		}

		if (
			(isset($parameters['data']['requestCount']) === true) &&
			(isset($parameters['data']['requestCountIntervalMinutes']) === true)
		) {
			if (
				(is_numeric($parameters['data']['requestCount']) === false) ||
				(($parameters['data']['requestCount'] < 0) === true)
			) {
				$response['message'] = 'Invalid node request limit rule request count, please try again.';
				return $response;
			}

			if (
				(is_numeric($parameters['data']['requestCountIntervalMinutes']) === false) ||
				(($parameters['data']['requestCountIntervalMinutes'] < 0) === true)
			) {
				$response['message'] = 'Invalid node request limit rule request count interval minutes, please try again.';
				return $response;
			}
		} else {
			$parameters['data']['requestCount'] = '0';
			$parameters['data']['requestCountIntervalMinutes'] = '0';
		}

		$existingNodeRequestLimitRuleCount = _count(array(
			'in' => $parameters['systemDatabases']['nodeRequestLimitRules'],
			'where' => array(
				'intervalMinutes' => $parameters['data']['intervalMinutes'],
				'requestCount' => $parameters['data']['requestCount'],
				'requestCountIntervalMinutes' => $parameters['data']['requestCountIntervalMinutes']
			)
		), $response);

		if (($existingNodeRequestLimitRuleCount === 1) === true) {
			$response['message'] = 'Node request limit rule already exists, please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['nodeRequestLimitRules']
		), $response);
		$nodeRequestLimitRule = _list(array(
			'in' => $parameters['systemDatabases']['nodeRequestLimitRules'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeRequestLimitRule = current($nodeRequestLimitRule);
		$response['data'] = $nodeRequestLimitRule;
		$response['message'] = 'Node request limit rule added successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add-node-request-limit-rule') === true) {
		$response = _addNodeRequestLimitRule($parameters, $response);
	}
?>
