<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		$databases['nodes']
	), $parameters['databases'], $response);

	function _deployNode($parameters, $response) {
		$nodeParameters = array(
			'in' => $parameters['databases']['nodes']
		);

		if (empty($parameters['where']['authentication_token']) === true) {
			$response['message'] = 'Node authentication token is required, please try again.';
			return $response;
		}

		$nodeParameters['where']['authentication_token'] = $parameters['where']['authentication_token'];
		$node = _list($nodeParameters, $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node authentication token, please try again.';
			return $response;
		}

		if ($node['status_deployed'] === true) {
			$response['message'] = 'Node is already deployed, please try again.';
			return $response;
		}

		$nodeParameters['data'] = array(
			'status_deployed' => true
		);
		_update($nodeParameters);
		$response['message'] = 'Node deployed successfully.';
		$response['status_valid'] = true;
		return $response;
	}

	if ($parameters['action'] === 'deploy_node') {
		$response = _deployNode($parameters, $response);
		_output($response);
	}
?>
