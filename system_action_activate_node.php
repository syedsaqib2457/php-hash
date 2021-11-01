<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		$databases['nodes']
	), $parameters['databases'], $response);

	function _activateNode($parameters, $response) {
		$nodeParameters = array(
			'columns' => array(
				'status_activated',
				'status_deployed'
			),
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

		if (
			(empty($parameters['where']['authentication_token']) === true) &&
			(($node['status_deployed'] === "0") === true)
		) {
			$response['data']['command'] = ''; // todo: updated node activation and deployment command
			$response['message'] = 'Node is ready for activation.';
			return $response;
		}

		if (($node['status_activated'] === "1") === true) {
			$response['message'] = 'Node is already activated, please try again.';
			return $response;
		}

		$nodeParameters['data'] = array(
			'status_activated' => "1"
		);
		_update($nodeParameters, $response);
		$response['message'] = 'Node activated successfully.';
		$response['status_valid'] = "1";
		return $response;
	}

	if (($parameters['action'] === 'activate_node') === true) {
		$response = _activateNode($parameters, $response);
		_output($response);
	}
?>
