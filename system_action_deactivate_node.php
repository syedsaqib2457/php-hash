<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		$databases['nodes']
	), $parameters['databases'], $response);

	function _deactivateNode($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'Node ID is required, please try again.';
			return $response;
		}

		$nodeParameters = array(
			'columns' => array(
				'status_activated'
			),
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		);
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
		_update($nodeParameters, $response);
		$response['message'] = 'Node deactivated successfully.';
		$response['status_valid'] = true;
		return $response;
	}

	if ($parameters['action'] === 'deactivate_node') {
		$response = _deactivateNode($parameters, $response);
		_output($response);
	}
?>
