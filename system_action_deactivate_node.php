<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'nodes'
	), $parameters['databases'], $response);

	function _deactivateNode($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'Node ID is required, please try again.';
			return $response;
		}

		$nodeParameters = array(
			'columns' => array(
				'activated_status'
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

		if (($node['activated_status'] === '0') === true) {
			$response['message'] = 'Node is already deactivated, please try again.';
			return $response;
		}

		$nodeParameters['data'] = array(
			'activated_status' => '0'
		);
		_update($nodeParameters, $response);
		$response['message'] = 'Node deactivated successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'deactivate_node') === true) {
		$response = _deactivateNode($parameters, $response);
		_output($response);
	}
?>
