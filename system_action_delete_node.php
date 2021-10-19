<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += array(
		'node_process_forwarding_destinations' => $settings['databases']['node_process_forwarding_destinations'],
		'node_process_node_user_request_destination_logs' => $settings['databases']['node_process_node_user_request_destination_logs'],
		'node_process_node_user_request_logs' => $settings['databases']['node_process_node_user_request_logs'],
		'node_process_node_user_resource_usage_logs' => $settings['databases']['node_process_node_user_resource_usage_logs'],
		'node_process_node_users' => $settings['databases']['node_process_node_users'],
		'node_process_recursive_dns_destinations' => $settings['databases']['node_process_recursive_dns_destinations'],
		'node_process_resource_usage_logs' => $settings['databases']['node_process_resource_usage_logs'],
		'node_processes' => $settings['databases']['node_processes'],
		'node_recursive_dns_destinations' => $settings['databases']['node_recursive_dns_destinations'],
		'node_reserved_internal_destinations' => $settings['databases']['node_reserved_internal_destinations'],
		'node_resource_usage_logs' => $settings['databases']['node_resource_usage_logs'],
		'nodes' => $settings['databases']['nodes']
	);
	$parameters['databases'] = _connect($parameters['databases']);

	if (
		(empty($parameters['databases']['message']) === false) &&
		(is_string($parameters['databases']['message']) === true)
	) {
		$response['message'] = $parameters['databases']['message'];
		_output($response);
	}

	function _deleteNode($parameters) {
		$response = array(
			'message' => 'Nodes removed successfully.'
		);
		$nodeDataDeleted = _delete(array(
			'in' => $parameters['databases']['nodes'],
			'where' => array(
				'OR' => array(
					'id' => ($nodeIds = array_filter($parameters['where']['id'])),
					'node_id' => $nodeIds
				)
			)
		));

		if ($nodeDataDeleted === false) {
			$response['message'] = 'Error deleting data in nodes database, please try again.';
			return $response;
		}

		/*
		node_reserved_internal_destinations
			update status_added false, node_id null, node_node_id null, status_processed false
			add status_processed field to prevent adding deleted / unassigned internal ips to other nodes before processing
			set status_processed to true after process_node action completes
		*/

		unset($parameters['databases']['node_reserved_internal_destinations']);
		unset($parameters['databases']['nodes']);

		foreach ($parameters['databases'] as $databaseKey => $database) {
			$dataDeleted = _delete(array(
				'in' => $parameters['databases'][$databaseKey],
				'where' => array(
					'OR' => array(
						'node_id' => $nodeIds,
						'node_node_id' => $nodeIds
					)
				)
			));

			if ($dataDeleted === false) {
				$response['message'] = 'Error deleting data in ' . $databaseKey . ' database, please try again.';
				return $response;
			}
		}

		$response['status_valid'] = true;
		return $response;
	}

	if ($parameters['action'] === 'delete_node') {
		$response = _removeNode($parameters);
		_output($response);
	}
?>
