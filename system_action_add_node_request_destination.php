<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_request_destinations'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_request_destinations'] = $systemDatabasesConnections['node_request_destinations'];
	require_once('/var/www/nodecompute/system_action_validate_hostname.php');

	function _addNodeRequestDestination($parameters, $response) {
		if (empty($parameters['data']['hostname']) === true) {
			$response['message'] = 'Node request destination must have a hostname, please try again.';
			return $response;
		}

		if (_validateHostname($parameters['data']['hostname'], true) === false) {
			$response['message'] = 'Invalid node request destination hostname, please try again.';
			return $response;
		}

		$existingNodeRequestDestinationCount = _count(array(
			'in' => $parameters['system_databases']['node_request_destinations'],
			'where' => array(
				'hostname' => $parameters['data']['hostname']
			)
		), $response);

		if (($existingNodeRequestDestinationCount === 1) === true) {
			$response['message'] = 'Node request destination already exists, please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_request_destinations']
		), $response);
		$nodeRequestDestination = _list(array(
			'in' => $parameters['system_databases']['node_request_destinations'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeRequestDestination = current($nodeRequestDestination);
		$response['data'] = $nodeRequestDestination;
		$response['message'] = 'Node request destination added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_node_request_destination') === true) {
		$response = _addNodeRequestDestination($parameters, $response);
	}
?>
