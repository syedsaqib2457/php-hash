<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		$databases['nodes']
	), $parameters['databases'], $response);

	function _deployNode($parameters, $response) {
		if (empty($parameters['where']['authentication_token']) === true) {
			$response['message'] = 'Node authentication token is required, please try again.';
			return $response;
		}

		$nodeParameters = array(
			'columns' => array(
				'id',
				'node_id',
				'status_deployed'
			),
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'authentication_token' => $parameters['where']['authentication_token']
			)
		);
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

		$nodeIds = array_filter(array(
			$node['id'],
			$node['node_id']
		));
		$nodeParameters['data'] = array(
			'status_deployed' => true
		);
		$nodeParameters['where'] = array(
			'either' => array(
				'id' => $nodeIds,
				'node_id' => $nodeIds
			)
		);
		_update($nodeParameters, $response);
		$response['message'] = 'Node deployed successfully.';
		$response['status_valid'] = true;
		return $response;
	}

	if ($parameters['action'] === 'deploy_node') {
		$response = _deployNode($parameters, $response);
		_output($response);
	}
?>
