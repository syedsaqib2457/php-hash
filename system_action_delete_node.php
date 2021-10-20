<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		$databases['node_process_forwarding_destinations'],
		$databases['node_process_node_user_request_destination_logs'],
		$databases['node_process_node_user_request_logs'],
		$databases['node_process_node_user_resource_usage_logs'],
		$databases['node_process_node_users'],
		$databases['node_process_recursive_dns_destinations'],
		$databases['node_process_resource_usage_logs'],
		$databases['node_processes'],
		$databases['node_recursive_dns_destinations'],
		$databases['node_reserved_internal_destinations'],
		$databases['node_resource_usage_logs'],
		$databases['nodes']
	), $response);

	function _deleteNode($parameters, $response) {
		_delete(array(
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'OR' => array(
					'id' => ($nodeIds = array_filter($parameters['where']['id'])),
					'node_id' => $nodeIds
				)
			)
		), $response);

		/*
		node_reserved_internal_destinations
			update status_added false, node_id null, node_node_id null, status_processed false
			add status_processed field to prevent adding deleted / unassigned internal ips to other nodes before processing
			set status_processed to true after process_node action completes
		*/

		unset($parameters['databases']['node_reserved_internal_destinations']);
		unset($parameters['databases']['nodes']);

		foreach ($parameters['databases'] as $databaseKey => $database) {
			_delete(array(
				'in' => $parameters['databases'][$databaseKey],
				'where' => array(
					'OR' => array(
						'node_id' => $nodeIds,
						'node_node_id' => $nodeIds
					)
				)
			), $response);
		}

		$response['message'] = 'Nodes removed successfully.';
		$response['status_valid'] = true;
		return $response;
	}

	if ($parameters['action'] === 'delete_node') {
		$response = _removeNode($parameters, $response);
		_output($response);
	}
?>
