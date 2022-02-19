<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_process_node_user_node_request_limit_rules',
		'node_request_destinations',
		'node_request_limit_rules',
		'node_user_node_request_destinations',
		'node_user_node_request_limit_rules',
		'node_users'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_process_node_user_node_request_limit_rules'] = $systemDatabasesConnections['node_process_node_user_node_request_limit_rules'];
	$parameters['system_databases']['node_request_destinations'] = $systemDatabasesConnections['node_request_destinations'];
	$parameters['system_databases']['node_request_limit_rules'] = $systemDatabasesConnections['node_request_limit_rules'];
	$parameters['system_databases']['node_user_node_request_destinations'] = $systemDatabasesConnections['node_user_node_request_destinations'];
	$parameters['system_databases']['node_user_node_request_limit_rules'] = $systemDatabasesConnections['node_user_node_request_limit_rules'];
	$parameters['system_databases']['node_users'] = $systemDatabasesConnections['node_users'];

	function _addNodeUserNodeRequestLimitRule($parameters, $response) {
		if (empty($parameters['data']['node_request_destination_id']) === true) {
			$response['message'] = 'Node process node user node request limit rule must have a node request destination ID, please try again.';
			return $response;
		}

		if (empty($parameters['data']['node_request_limit_rule_id']) === true) {
			$response['message'] = 'Node process node user node request limit rule must have a node request limit rule ID, please try again.';
			return $response;
		}

		if (empty($parameters['data']['node_user_id']) === true) {
			$response['message'] = 'Node process node user node request limit rule must have a node user ID, please try again.';
			return $response;
		}

		$nodeRequestDestination = _list(array(
			'data' => array(
				'address'
			),
			'in' => $parameters['system_databases']['node_request_destinations'],
			'where' => array(
				'id' => $parameters['data']['node_request_destination_id']
			)
		), $response);
		$nodeRequestDestination = current($nodeRequestDestination);

		if (empty($nodeRequestDestination) === true) {
			$response['message'] = 'Invalid node user node request limit rule node request destination ID, please try again.';
			return $response;
		}

		$nodeRequestLimitRuleCount = _count(array(
			'in' => $parameters['system_databases']['node_request_limit_rules'],
			'where' => array(
				'id' => $parameters['data']['node_request_limit_rule_id']
			)
		), $response);

		if (($nodeRequestLimitRuleCount === 0) === true) {
			$response['message'] = 'Invalid node user node request limit rule node request limit rule ID, please try again.';
			return $response;
		}

		$nodeUserCount = _count(array(
			'in' => $parameters['system_databases']['node_users'],
			'where' => array(
				'id' => $parameters['data']['node_user_id']
			)
		), $response);

		if (($nodeUserCount === 0) === true) {
			$response['message'] = 'Invalid node user node request limit rule node user ID, please try again.';
			return $response;
		}

		$existingNodeUserNodeRequestLimitRuleCount = _count(array(
			'in' => $parameters['system_databases']['node_user_node_request_limit_rules'],
			'where' => array(
				'node_request_destination_id' => $parameters['data']['node_request_destination_id'],
				'node_request_limit_rule_id' => $parameters['data']['node_request_limit_rule_id'],
				'node_user_id' => $parameters['data']['node_user_id']
			)
		), $response);

		if (($existingNodeUserNodeRequestLimitRuleCount === 1) === true) {
			$response['message'] = 'Node user node request limit rule already exists, please try again.';
			return $response;
		}

		$nodeUserNodeRequestDestination = _list(array(
			'data' => array(
				'node_request_destination_address'
			),
			'in' => $parameters['system_databases']['node_user_node_request_destinations'],
			'where' => array(
				'node_request_destination_id' => $parameters['data']['node_request_destination_id'],
				'node_user_id' => $parameters['data']['node_user_id']
			)
		), $response);
		$nodeUserNodeRequestDestination = current($nodeUserNodeRequestDestination);

		if (empty($nodeUserNodeRequestDestination) === true) {
			_save(array(
				'data' => array(
					'id' => _createUniqueId(),
					'node_request_destination_address' => $nodeRequestDestination['address'],
					'node_request_destination_id' => $parameters['data']['node_request_destination_id'],
					'node_user_id' => $parameters['data']['node_user_id']
				),
				'in' => $parameters['system_databases']['node_user_node_request_destinations']
			));
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_user_node_request_limit_rules']
		));
		$nodeUserNodeRequestLimitRule = _list(array(
			'in' => $parameters['system_databases']['node_user_node_request_limit_rules'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeUserNodeRequestLimitRule = current($nodeUserNodeRequestLimitRule);
		$nodeProcessNodeUsers = _list(array(
			'data' => array(
				'node_id',
				'node_node_id',
				'node_process_type',
				'node_user_id'
			),
			'in' => $parameters['system_databases']['node_process_node_users'],
			'where' => array(
				'node_user_id' => $parameters['data']['node_user_id']
			)
		), $response);

		if (empty($nodeProcessNodeUsers) === false) {
			$nodeProcessNodeUserNodeRequestLimitRules = array();

			foreach ($nodeProcessNodeUsers as $nodeProcessNodeUser) {
				$nodeProcessNodeUser['activated_status' = $nodeUserNodeRequestLimitRule['activated_status'];
				$nodeProcessNodeUser['created_timestamp' = $nodeUserNodeRequestLimitRule['created_timestamp'];
				$nodeProcessNodeUser['expired_timestamp' = $nodeUserNodeRequestLimitRule['expired_timestamp'];
				$nodeProcessNodeUser['modified_timestamp' = $nodeUserNodeRequestLimitRule['modified_timestamp'];
				$nodeProcessNodeUser['node_request_destination_id'] = $nodeUserNodeRequestLimitRule['node_request_destination_id'];
				$nodeProcessNodeUser['node_request_limit_rule_id'] = $nodeUserNodeRequestLimitRule['node_request_limit_rule_id'];
				$nodeProcessNodeUserNodeRequestLimitRules[] = $nodeProcessNodeUser;
			}

			_save(array(
				'data' => $nodeProcessNodeUserNodeRequestLimitRules,
				'in' => $parameters['system_databases']['node_process_node_user_node_request_limit_rules']
			));
		}

		$response['data'] = $nodeUserNodeRequestLimitRule;
		$response['message'] = 'Node user node request limit rule added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_user_node_request_limit_rule') === true) {
		$response = _addNodeUserNodeRequestLimitRule($parameters, $response);
	}
?>
