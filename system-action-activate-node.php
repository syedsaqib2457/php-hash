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
				'deployedStatus',
				'nodeAuthenticationToken'
			),
			'in' => $parameters['systemDatabases']['nodes']
		);

		if (empty($parameters['nodeAuthenticationToken']) === false) {
			$nodeParameters['where']['nodeAuthenticationToken'] = $parameters['nodeAuthenticationToken'];
		}

		if (empty($parameters['where']['id']) === false) {
			$nodeParameters['where']['id'] = $parameters['where']['id'];
		}

		if (empty($parameters['where']['nodeAuthenticationToken']) === true) {
			$response['message'] = 'Node must have an authentication token, please try again.';
			return $response;
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
			$systemEndpointDestinationIpAddress = _list(array(
				'data' => array(
					'value'
				),
				'in' => $parameters['systemDatabases']['systemSettings'],
				'where' => array(
					'key' => 'endpointDestinationIpAddress'
				)
			), $response);
			$systemEndpointDestinationIpAddress = current($systemEndpointDestinationIpAddress);
			$systemEndpointDestinationIpAddress = current($systemEndpointDestinationIpAddress);

			if (empty($systemEndpointDestinationIpAddress) === true) {
				$response['message'] = 'Error listing system endpoint destination IP address, please try again.';
				return $response;
			}

			$response['data']['command'] = 'cd /tmp && rm -rf /etc/cloud/ /var/lib/cloud/ ; apt-get update ; DEBIAN_FRONTEND=noninteractive apt-get -y install sudo ; sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk \'{print $1}\') ; sudo $(whereis telinit | awk \'{print $2}\') u ; sudo rm -rf /etc/cloud/ /var/lib/cloud/ ; sudo dpkg --configure -a ; sudo apt-get update && sudo DEBIAN_FRONTEND=noninteractive apt-get -y purge php* ; sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php wget --fix-missing && sudo wget -O node-action-deploy-node.php --connect-timeout=5 --dns-timeout=5 --no-dns-cache --read-timeout=60 --tries=1 "' . $systemEndpointDestinationIpAddress . '/node-action-deploy-node.php?$RANDOM" && sudo php node-action-deploy-node.php ' . $parameters['nodeAuthenticationToken'] . ' ' . $systemEndpointDestinationIpAddress;
			$response['message'] = 'Node is ready for activation and deployment.';
			$response['validatedStatus'] = '1';
			return $response;
		}

		if (($node['activatedStatus'] === '1') === true) {
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
