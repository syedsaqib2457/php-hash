<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		$databases['nodes']
	), $parameters['databases'], $response);

	function _updateNode($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'Node ID is required, please try again.';
			return $response;
		}

		$node = _list(array(
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'either' => array(
					'id' => $parameters['where']['id'],
					'node_id' => $parameters['where']['id']
				)
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node ID, please try again';
			return $response;
		}

		$nodeId = $node['id'];

		if (empty($node['node_id']) === false) {
			$nodeId = $node['node_id'];
		}

		_update(array(
			'data' => array(
				'status_processed' => true
			),
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'id' => $nodeId
			)
		), $response);
		$response['message'] = 'Node updated successfully.';
		$response['status_valid'] = true;
		return $response;
	}

	if ($parameters['action'] === 'update_node') {
		$response = _updateNode($parameters, $response);
		_output($response);
	}
?>
