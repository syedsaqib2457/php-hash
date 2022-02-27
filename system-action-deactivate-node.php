<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodes'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodes'] = $systemDatabasesConnections['nodes'];

	function _deactivateNode($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'Node ID is required, please try again.';
			return $response;
		}

		$nodeParameters = array(
			'data' => array(
				'activatedStatus'
			),
			'in' => $parameters['systemDatabases']['nodes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		);
		$node = _list($nodeParameters, $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node authentication token or ID, please try again.';
			return $response;
		}

		if (($node['activatedStatus'] === '0') === true) {
			$response['message'] = 'Node is already deactivated, please try again.';
			return $response;
		}

		$nodeParameters['data'] = array(
			'activatedStatus' => '0'
		);
		_edit($nodeParameters, $response);
		$response['message'] = 'Node deactivated successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'deactivate-node') === true) {
		$response = _deactivateNode($parameters, $response);
	}
?>
