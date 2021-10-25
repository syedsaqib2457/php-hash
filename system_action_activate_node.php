<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		$databases['nodes']
	), $parameters['databases'], $response);

	function _activateNode($parameters, $response) {
		$nodeParameters = array(
			'in' => $parameters['databases']['nodes']
		);

		if (empty($parameters['where']['authentication_token']) === false) {
			$nodeParameters['where']['authentication_token'] = $parameters['where']['authentication_token'];
		}

		if (empty($parameters['where']['id']) === false) {
			$nodeParameters['where']['id'] = $parameters['where']['id'];
		}

		$response['message'] = 'Invalid node authentication token or ID, please try again.';

		if (empty($parameters['where']) === true) {
			return $response;
		}

		$node = _list($nodeParameters, $response);

		if (empty($node) === true) {
			return $response;
		}

		if ($node['status_deployed'] === false) {
			$response['data']['command'] = ''; // todo: updated node activation and deployment command
			$response['message'] = 'Node is ready for activation.';
			return $response;
		}

		if ($node['status_activated'] === true) {
			$response['message'] = 'Node is already activated, please try again.';
			return $response;
		}

		$nodeParameters['data'] = array(
			'status_activated' => true
		);
		_update($nodeParameters);
		$response['message'] = 'Node activated successfully.';
		$response['status_valid'] = true;
		return $response;
	}

	if ($parameters['action'] === 'activate_node') {
		$response = _activateNode($parameters, $response);
		_output($response);
	}
?>
