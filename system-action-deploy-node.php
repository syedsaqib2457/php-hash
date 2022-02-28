<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodes'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodes'] = $systemDatabasesConnections['nodes'];

	function _deployNode($parameters, $response) {
		if (empty($parameters['nodeAuthenticationToken']) === true) {
			$response['message'] = 'Node authentication token is required, please try again.';
			return $response;
		}

		$nodeParameters = array(
			'data' => array(
				'deployedStatus',
				'id',
				'nodeId'
			),
			'in' => $parameters['systemDatabases']['nodes'],
			'where' => array(
				'authenticationToken' => $parameters['nodeAuthenticationToken']
			)
		);
		$node = _list($nodeParameters, $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node authentication token, please try again.';
			return $response;
		}

		if (($node['deployedStatus'] === '1') === true) {
			$response['message'] = 'Node is already deployed, please try again.';
			return $response;
		}

		$nodeIds = array_filter(array(
			$node['id'],
			$node['nodeId']
		));
		$nodeParameters['data'] = array(
			'deployedStatus' => '1'
		);
		$nodeParameters['where'] = array(
			'either' => array(
				'id' => $nodeIds,
				'nodeId' => $nodeIds
			)
		);
		_edit($nodeParameters, $response);
		$response['message'] = 'Node deployed successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'deployNode') === true) {
		$response = _deployNode($parameters, $response);
	}
?>
