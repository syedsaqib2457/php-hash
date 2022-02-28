<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeRequestDestinations'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeRequestDestinations'] = $systemDatabasesConnections['nodeRequestDestinations'];
	require_once('/var/www/firewall-security-api/system-action-validate-hostname-address.php');

	function _addNodeRequestDestination($parameters, $response) {
		if (empty($parameters['data']['address']) === true) {
			$response['message'] = 'Node request destination must have an address, please try again.';
			return $response;
		}

		if (_validateHostnameAddress($parameters['data']['address'], true) === false) {
			$response['message'] = 'Invalid node request destination address, please try again.';
			return $response;
		}

		$existingNodeRequestDestinationCount = _count(array(
			'in' => $parameters['systemDatabases']['nodeRequestDestinations'],
			'where' => array(
				'address' => $parameters['data']['address']
			)
		), $response);

		if (($existingNodeRequestDestinationCount === 1) === true) {
			$response['message'] = 'Node request destination already exists, please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['nodeRequestDestinations']
		), $response);
		$nodeRequestDestination = _list(array(
			'in' => $parameters['systemDatabases']['nodeRequestDestinations'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeRequestDestination = current($nodeRequestDestination);
		$response['data'] = $nodeRequestDestination;
		$response['message'] = 'Node request destination added successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'addNodeRequestDestination') === true) {
		$response = _addNodeRequestDestination($parameters, $response);
	}
?>
