<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'nodes'
	), $parameters['system_databases'], $response);

	function _updateNode($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'Node must have an ID, please try again.';
			return $response;
		}

		$node = _list(array(
			'data' => array(
				'id',
				'node_id'
			),
			'in' => $parameters['system_databases']['nodes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node ID, please try again.';
			return $response;
		}

		$nodeNodeId = $node['id'];

		if (empty($node['node_id']) === false) {
			$nodeNodeId = $node['node_id'];
		}

		_update(array(
			'data' => array(
				'processed_status' => '0',
				'processing_progress_checkpoint' => 'processing_queued',
				'processing_progress_percentage' => '0',
				'processing_status' => '0'
			),
			'in' => $parameters['system_databases']['nodes'],
			'where' => array(
				'either' => array(
					'id' => $nodeNodeId,
					'node_id' => $nodeNodeId
				)
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
