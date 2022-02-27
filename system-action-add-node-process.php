<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeProcesses',
		'nodes'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['node_processes'] = $systemDatabasesConnections['nodeProcesses'];
	$parameters['systemDatabases']['nodes'] = $systemDatabasesConnections['nodes'];
	require_once('/var/www/firewall-security-api/system-action-validate-port-number.php');

	function _addNodeProcess($parameters, $response) {
		if (empty($parameters['data']['nodeId']) === true) {
			$response['message'] = 'Node process must have a node ID, please try again.';
			return $response;
		}

		if (empty($parameters['data']['portNumber']) === true) {
			$response['message'] = 'Node process must have a port number, please try again.';
			return $response;
		}

		if (_validatePortNumber($parameters['data']['portNumber']) === false) {
			$response['message'] = 'Invalid node process port number, please try again.';
			return $response;
		}

		if (empty($parameters['data']['type']) === true) {
			$response['message'] = 'Node process must have a type, please try again.';
			return $response;
		}

		if (
			(($parameters['data']['type'] === 'httpProxy') === false) &&
			(($parameters['data']['type'] === 'loadBalancer') === false) &&
			(($parameters['data']['type'] === 'recursiveDns') === false) &&
			(($parameters['data']['type'] === 'socksProxy') === false)
		) {
			$response['message'] = 'Invalid node process type, please try again.';
			return $response;
		}

		$node = _list(array(
			'data' => array(
				'id',
				'nodeId'
			),
			'in' => $parameters['systemDatabases']['nodes'],
			'where' => array(
				'id' => $parameters['data']['nodeId']
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node process node ID, please try again.';
			return $response;
		}

		$parameters['data']['nodeNodeId'] = $node['id'];

		if (empty($node['nodeId']) === false) {
			$parameters['data']['nodeNodeId'] = $node['nodeId'];
		}

		$existingNodeProcessCountParameters = array(
			'in' => $parameters['system_databases']['portNumber'],
			'where' => array(
				'nodeId' => $parameters['data']['nodeId'],
				'portNumber' => $parameters['data']['portNumber']
			)
		);
		$existingNodeProcessCount = _count($existingNodeProcessCountParameters, $response);

		if (($existingNodeProcessCount === 1) === true) {
			$response['message'] = 'Node process already exists with the same port number ' . $parameters['data']['portNumber'] . ', please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['nodeProcesses']
		), $response);
		$nodeProcess = _list(array(
			'in' => $parameters['systemDatabases']['nodeProcesses'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeProcess = current($nodeProcess);
		$response['data'] = $nodeProcess;
		$response['message'] = 'Node process added successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add-node-process') === true) {
		$response = _addNodeProcess($parameters, $response);
	}
?>
