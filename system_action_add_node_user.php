<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_users'
	), $parameters['system_databases'], $response);

	function _addNodeUser($parameters, $response) {
		if (isset($parameters['data']['authentication_strict_only_allowed_status']) === false) {
			$response['message'] = 'Node user must have an authentication strict only allowed status, please try again.';
			return $response;
		}

		if (isset($parameters['data']['node_request_destinations_only_allowed_status']) === false) {
			$response['message'] = 'Node user must have a node request destinations only allowed status, please try again.';
			return $response;
		}

		if (isset($parameters['data']['node_request_logs_allowed_status']) === false) {
			$response['message'] = 'Node user must have a node request logs allowed status, please try again.';
			return $response;
		}

		$parameters['data'] = array(
			'authentication_strict_only_allowed_status' => intval($parameters['data']['authentication_strict_only_allowed_status']),
			'id' => _createUniqueId(),
			'node_request_destinations_only_allowed_status' => intval($parameters['data']['node_request_destinations_only_allowed_status']),
			'node_request_logs_allowed_status' => intval($parameters['data']['node_request_logs_allowed_status'])
		);
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
