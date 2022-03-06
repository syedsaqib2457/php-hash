<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodes'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodes'] = $systemDatabasesConnections['nodes'];

	function _activateNode($parameters, $response) {
		$nodeParameters = array(
			'data' => array(
				'activatedStatus',
				'authenticationToken',
				'deployedStatus'
			),
			'in' => $parameters['systemDatabases']['nodes']
		);

		if (empty($parameters['nodeAuthenticationToken']) === false) {
			$nodeParameters['where']['authenticationToken'] = $parameters['nodeAuthenticationToken'];
		}

		if (empty($parameters['where']['id']) === false) {
			$nodeParameters['where']['id'] = $parameters['where']['id'];
		}

		$node = _list($nodeParameters, $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Error listing node, please try again.';
			return $response;
		}

		if (
			(($node['deployedStatus'] === '0') === true) &&
			(empty($parameters['nodeAuthenticationToken']) === true)
		) {
			$systemEndpointDestination = $parameters['systemEndpointDestinationProtocol'] . '://' . $parameters['systemEndpointDestinationIpAddress'] . ':' . $parameters['systemEndpointDestinationPortNumber'] . '/' . $parameters['systemEndpointDestinationSubdirectory'];

			if (($parameters['systemEndpointDestinationIpAddressVersionNumber'] === '6') === true) {
				$systemEndpointDestination = $parameters['systemEndpointDestinationProtocol'] . '://[' . $parameters['systemEndpointDestinationIpAddress'] . ']:' . $parameters['systemEndpointDestinationPortNumber'] . '/' . $parameters['systemEndpointDestinationSubdirectory'];
			}

			$response['data']['terminalConsoleCommand'] = "cd /tmp && rm -rf /etc/cloud/ /var/lib/cloud/ ; apt-get update ; DEBIAN_FRONTEND=noninteractive apt-get -y install sudo ; sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk '{print $1}') ; sudo $(whereis telinit | awk '{print $2}') u ; sudo rm -rf /etc/cloud/ /var/lib/cloud/ ; sudo dpkg --configure -a ; sudo apt-get update && sudo DEBIAN_FRONTEND=noninteractive apt-get -y purge php* ; sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php wget --fix-missing && sudo wget -O node-action-deploy-node.php --no-dns-cache " . $systemEndpointDestination . '/node-action-deploy-node.php?' . _createUniqueId() . ' && sudo php node-action-deploy-node.php ' . $node['authenticationToken'] . ' ' . $systemEndpointDestination;
			$response['message'] = 'Node is ready for activation and deployment.';
			$response['validatedStatus'] = '1';
			return $response;
		}

		if (
			(($node['activatedStatus'] === '1') === true) &&
			(empty($parameters['systemUserAuthenticationToken']) === false)
		) {
			$response['message'] = 'Node is already activated, please try again.';
			return $response;
		}

		$nodeParameters['data'] = array(
			'activatedStatus' => '1'
		);
		_edit($nodeParameters, $response);
		$response['message'] = 'Node activated successfully.';
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
