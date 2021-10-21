<?php
	if (empty($parameters) === true) {
		exit;
	}

	$databaseTables = array(
		'node_process_forwarding_destinations',
		'node_process_node_user_request_destination_logs',
		'node_process_node_user_request_logs',
		'node_process_node_user_resource_usage_logs',
		'node_process_node_users',
		'node_process_recursive_dns_destinations',
		'node_process_resource_usage_logs',
		'node_processes',
		'node_recursive_dns_destinations',
		'node_reserved_internal_destinations',
		'node_resource_usage_logs',
		'nodes'
	);

	foreach ($databaseTables as $databaseTable) {
		if (empty($parameters['databases'][$databaseTable]) === true) {
			$parameters['databases'] += _connect(array(
				$databases[$databaseTable]
			), $response);
		}
	}

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

		$databases = array(
			'node_process_forwarding_destinations',
			'node_process_node_user_request_destination_logs',
			'node_process_node_user_request_logs',
			'node_process_node_user_resource_usage_logs',
			'node_process_node_users',
			'node_process_recursive_dns_destinations',
			'node_process_resource_usage_logs',
			'node_processes',
			'node_recursive_dns_destinations',
			'node_resource_usage_logs'
		);

		foreach ($databases as $database) {
			_delete(array(
				'in' => $parameters['databases'][$database],
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
