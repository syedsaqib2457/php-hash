<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'nodes'
	), $parameters['databases'], $response);

	function _listNodes($parameters, $response) {
		// todo: validate pagination + where conditions
		$nodes = _list(array(
			'in' => $parameters['databases']['nodes'],
			'where' => '' // todo
		), $response);
		$response['data'] = $node;
		$response['message'] = 'Nodes listed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list_nodes') === true) {
		$response = _listNodes($parameters, $response);
		_output($response);
	}
?>
