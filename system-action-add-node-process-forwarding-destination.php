<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeProcessForwardingDestinations',
		'nodes'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeProcessForwardingDestinations'] = $systemDatabasesConnections['nodeProcessForwardingDestinations'];
	$parameters['systemDatabases']['nodes'] = $systemDatabasesConnections['nodes'];
	require_once('/var/www/firewall-security-api/system-action-validate-hostname-address.php');
	require_once('/var/www/firewall-security-api/system-action-validate-port-number.php');

	function _addNodeProcessForwardingDestination($parameters, $response) {
		if (empty($parameters['data']['nodeId']) === true) {
			$response['message'] = 'Node process forwarding destination must have a node ID, please try again.';
			return $response;
		}

		if (empty($parameters['data']['nodeProcessType']) === true) {
			$response['message'] = 'Node process forwarding destination must have a node process type, please try again.';
			return $response;
		}

		if (
			(($parameters['data']['nodeProcessType'] === 'httpProxy') === false) &&
			(($parameters['data']['nodeProcessType'] === 'socksProxy') === false)
		) {
			$response['message'] = 'Invalid node process forwarding destination node process type, please try again.';
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
			$response['message'] = 'Error listing node process node, please try again.';
			return $response;
		}

		$nodeIpAddressVersions = array(
			'4',
			'6'
		);

		foreach ($nodeIpAddressVersions as $nodeIpAddressVersion) {
			if (empty($parameters['data']['addressVersion' . $nodeIpAddressVersion]) === false) {
				$parameters['data']['addressVersion' . $nodeIpAddressVersion] = _validateHostnameAddress($parameters['data']['addressVersion' . $nodeIpAddressVersion], true);

				if ($parameters['data']['addressVersion' . $nodeIpAddressVersion] === false) {
					$response['message'] = 'Invalid node process forwarding destination address version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}

				if (empty($parameters['data']['portNumberVersion' . $nodeIpAddressVersion]) === true) {
					$response['message'] = 'Node process forwarding destination must have a port number version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}

				if (_validatePortNumber($parameters['data']['portNumberVersion' . $nodeIpAddressVersion]) === false) {
					$response['message'] = 'Invalid node process forwarding destination port number version ' . $nodeIpAddressVersion . ', please try again.';
					return $response;
				}
			} else {
				unset($parameters['data']['addressVersion' . $nodeIpAddressVersion]);
				unset($parameters['data']['portNumberVersion' . $nodeIpAddressVersion]);
			}
		}

		$parameters['data']['nodeNodeId'] = $node['id'];

		if (empty($node['nodeId']) === false) {
			$parameters['data']['nodeNodeId'] = $node['nodeId'];
		}

		$existingNodeProcessForwardingDestinationCount = _count(array(
			'in' => $parameters['systemDatabases']['nodeProcessRecursiveDnsDestinations'],
			'where' => array(
				'nodeId' => $parameters['data']['nodeId'],
				'nodeProcessType' => $parameters['data']['nodeProcessType']
			)
		), $response);

		if (($existingNodeProcessForwardingDestinationCount === 1) === true) {
			$response['message'] = 'Node process forwarding destination already exists with the same node process type ' . $parameters['data']['nodeProcessType'] . ', please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['nodeProcessForwardingDestinations']
		), $response);
		$nodeProcessForwardingDestination = _list(array(
			'in' => $parameters['systemDatabases']['nodeProcessForwardingDestinations'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeProcessForwardingDestination = current($nodeProcessForwardingDestination);
		$response['data'] = $nodeProcessForwardingDestination;
		$response['message'] = 'Node process forwarding destination added successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add-node-process-forwarding-destination') === true) {
		$response = _addNodeProcessForwardingDestination($parameters, $response);
	}
?>
