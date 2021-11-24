<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_forwarding_destinations',
		'node_process_node_user_authentication_credentials',
		'node_process_node_user_authentication_sources',
		'node_process_node_user_request_destination_logs',
		'node_process_node_user_node_request_destinations',
		'node_process_node_user_node_request_limit_rules',
		'node_process_node_user_request_logs',
		'node_process_node_user_resource_usage_logs',
		'node_process_node_users',
		'node_process_recursive_dns_destinations',
		'node_process_resource_usage_logs',
		'node_processes'
	), $parameters['system_databases'], $response);

	function _deleteNodeProcess($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'Node process must have an ID, please try again.';
			return $response;
		}

		$nodeProcess = _list(array(
			'columns' => array(
				'node_id',
				'type'
			),
			'in' => $parameters['system_databases']['node_processes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$nodeProcess = current($nodeProcess);

		if (empty($nodeProcess) === true) {
			$response['message'] = 'Invalid node process ID, please try again.';
			return $response;
		}

		$nodeProcessCount = _count(array(
			'in' => $parameters['system_databases']['node_processes'],
			'where' => array(
				'node_id' => $nodeProcess['node_id'],
				'type' => $nodeProcess['type']
			)
		), $response);

		if (($nodeProcessCount <= 1) === true) {
			$systemDatabaseNames = array(
				'node_process_forwarding_destinations',
				'node_process_node_user_authentication_credentials',
				'node_process_node_user_authentication_sources',
				'node_process_node_user_request_destination_logs',
				'node_process_node_user_node_request_destinations',
				'node_process_node_user_node_request_limit_rules',
				'node_process_node_user_request_logs',
				'node_process_node_user_resource_usage_logs',
				'node_process_node_users',
				'node_process_resource_usage_logs',
				'node_process_recursive_dns_destinations'
			);

			foreach ($systemDatabaseNames as $systemDatabaseName) {
				_delete(array(
					'in' => $parameters['system_databases'][$systemDatabaseName],
					'where' => array(
						'node_id' => $nodeProcess['node_id']
					)
				), $response);
			}
		}

		_delete(array(
			'in' => $parameters['system_databases']['node_processes'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$response['message'] = 'Node process deleted successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'delete_node_process') === true) {
		$response = _deleteNodeProcess($parameters, $response);
		_output($response);
	}
?>
