<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'nodes'
	), $parameters['system_databases'], $response);

	function _deployNode($parameters, $response) {
		if (empty($parameters['where']['authentication_token']) === true) {
			$response['message'] = 'Node authentication token is required, please try again.';
			return $response;
		}

		$nodeParameters = array(
			'data' => array(
				'deployed_status',
				'id',
				'node_id'
			),
			'in' => $parameters['system_databases']['nodes'],
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

		if (($node['deployed_status'] === '1') === true) {
			$response['message'] = 'Node is already deployed, please try again.';
			return $response;
		}

		$nodeIds = array_filter(array(
			$node['id'],
			$node['node_id']
		));
		$nodeParameters['data'] = array(
			'deployed_status' => '1'
		);
		$nodeParameters['where'] = array(
			'either' => array(
				'id' => $nodeIds,
				'node_id' => $nodeIds
			)
		);
		_update($nodeParameters, $response);
		$response['message'] = 'Node deployed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'deploy_node') === true) {
		$response = _deployNode($parameters, $response);
		_output($response);
	}
?>
