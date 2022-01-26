<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_users'
	), $parameters['system_databases'], $response);

	function _addNodeUser($parameters, $response) {
		$parameters['data']['authentication_strict_only_allowed_status'] = strval(intval(empty($parameters['data']['authentication_strict_only_allowed_status']) === false));
		$parameters['data']['id'] = _createUniqueId();
		$parameters['data']['node_request_destinations_only_allowed_status'] = strval(intval(empty($parameters['data']['node_request_destinations_only_allowed_status']) === false));
		$parameters['data']['node_request_logs_allowed_status'] = strval(intval(empty($parameters['data']['node_request_logs_allowed_status']) === false));
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_users']
		), $response);
		$nodeUser = _list(array(
			'in' => $parameters['system_databases']['node_users'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeUser = current($nodeUser);
		$response['data'] = $nodeUser;
		$response['message'] = 'Node user added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_user') === true) {
		$response = _addNodeUser($parameters, $response);
	}
?>
