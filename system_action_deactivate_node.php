<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		$databases['nodes']
	), $parameters['databases'], $response);

	function _deactivateNode($parameters, $response) {
		$nodeParameters = array(
			'in' => $parameters['databases']['nodes']
		);

		if (empty($parameters['where']['authentication_token']) === false) {
			$nodeParameters['where']['authentication_token'] = $parameters['where']['authentication_token'];
		}

		if (empty($parameters['where']['id']) === false) {
			$nodeParameters['where']['id'] = $parameters['where']['id'];
		}

		if (empty($parameters['where']) === true) {
			$response['message'] = 'Node authentication token or ID is required, please try again.';
			return $response;
		}

		$node = _list($nodeParameters, $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node authentication token or ID, please try again.';
			return $response;
		}

		if ($node['status_activated'] === false) {
			$response['message'] = 'Node is already deactivated, please try again.';
			return $response;
		}

		$nodeParameters['data'] = array(
			'status_activated' => false
		);
		_update($nodeParameters);
		$response['message'] = 'Node deactivated successfully.';
		$response['status_valid'] = true;
		return $response;
	}

	if ($parameters['action'] === 'deactivate_node') {
		$response = _deactivateNode($parameters, $response);
		_output($response);
	}
?>
