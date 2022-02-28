<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeUsers'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeUsers'] = $systemDatabasesConnections['nodeUsers'];

	function _addNodeUser($parameters, $response) {
		if (isset($parameters['data']['authenticationStrictOnlyAllowedStatus']) === false) {
			$response['message'] = 'Node user must have an authentication strict only allowed status, please try again.';
			return $response;
		}

		if (isset($parameters['data']['nodeRequestDestinationsOnlyAllowedStatus']) === false) {
			$response['message'] = 'Node user must have a node request destinations only allowed status, please try again.';
			return $response;
		}

		if (isset($parameters['data']['nodeRequestLogsAllowedStatus']) === false) {
			$response['message'] = 'Node user must have a node request logs allowed status, please try again.';
			return $response;
		}

		$parameters['data'] = array(
			'authenticationStrictOnlyAllowedStatus' => intval($parameters['data']['authenticationStrictOnlyAllowedStatus']),
			'id' => _createUniqueId(),
			'nodeRequestDestinationsOnlyAllowedStatus' => intval($parameters['data']['nodeRequestDestinationsOnlyAllowedStatus']),
			'nodeRequestLogsAllowedStatus' => intval($parameters['data']['nodeRequestLogsAllowedStatus'])
		);
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['nodeUsers']
		), $response);
		$nodeUser = _list(array(
			'in' => $parameters['systemDatabases']['nodeUsers'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeUser = current($nodeUser);
		$response['data'] = $nodeUser;
		$response['message'] = 'Node user added successfully.';
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
