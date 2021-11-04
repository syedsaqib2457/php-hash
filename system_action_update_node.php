<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'nodes'
	), $parameters['databases'], $response);

	function _updateNode($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'Node ID is required, please try again.';
			return $response;
		}

		$node = _list(array(
			'columns' => array(
				'id'
			),
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node ID, please try again.';
			return $response;
		}

		_update(array(
			'data' => array(
				'processed_status' => '0'
			),
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'id' => $node['id']
			)
		), $response);
		$response['message'] = 'Node updated successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'update_node') === true) {
		$response = _updateNode($parameters, $response);
		_output($response);
	}
?>
