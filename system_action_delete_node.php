<?php
	if (empty($parameters) === true) {
		exit;
	}

	/*
		node_process_forwarding_destinations
			node_id
			node_node_id
		node_process_node_user_request_destination_logs
			node_id
			node_node_id
		node_process_node_user_request_logs
			node_id
			node_node_id
		node_process_node_user_resource_usage_logs
			node_id
			node_node_id
		node_process_node_users
			node_id
			node_node_id
		node_process_recursive_dns_destinations
                        node_id
                        node_node_id
		node_process_resource_usage_logs
			node_id
			node_node_id
		node_recursive_dns_destinations
			node_id
			node_node_id
		node_resource_usage_logs,
			node_id
		node_reserved_internal_destinations
			update status_added false, node_id null, node_node_id null, status_processed false
			add status_processed field to prevent adding deleted / unassigned internal ips to other nodes before processing
				set status_processed to true after process_node action completes
	*/

	$parameters['databases'] += array(
		'node_processes' => $settings['databases']['node_processes'],
		'node_recursive_dns_destinations' => $settings['databases']['node_recursive_dns_destinations'],
		'node_reserved_internal_destinations' => $settings['databases']['node_reserved_internal_destinations'],
		'nodes' => $settings['databases']['nodes']
	);
	$parameters['databases'] = _connect($parameters['databases']);

	if (
		(empty($parameters['databases']['message']) === false) &&
		(is_string($parameters['databases']['message']) === true)
	) {
		$response['message'] = $parameters['database']['message'];
		_output($response);
	}

	function _deleteNode($parameters) {
		$response = array(
			'message' => 'Nodes removed successfully.';
		);
		$nodeDataDeleted = _delete(array(
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'OR' => array(
					'id' => $nodeIds,
					'node_id' => $nodeIds
				)
			)
		));

		if ($nodesDeleted === false) {
			
		}

		// update node_reserved_internal_destinations

		unset($parameters['databases']['node_reserved_internal_destinations']);
		unset($parameters['databases']['nodes']);

		foreach ($parameters['databases'] as $databaseKey => $database) {
			
		}

		$response['status_valid'] = true;
		return $response;
	}

	if ($parameters['action'] === 'delete_node') {
		$response = _removeNode($parameters);
		_output($response);
	}
?>
