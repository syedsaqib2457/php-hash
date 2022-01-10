<?php
	if (empty($_SERVER['argv'][1]) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_node_user_request_logs',
		'node_process_node_users'
	), $parameters['system_databases'], $response);

	function _processNodeProcessNodeUserRequestLogs($parameters, $response) {
		_update(array(
			'data' => array(
				'processing_process_id' => $parameters['process_id']
			),
			'in' => $parameters['system_databases']['node_process_node_user_request_logs'],
			'limit' => 10000,
			'where' => array(
				'processed_status' => '0',
				'processing_process_id' => null
			)
		), $response);
		$nodeProcessNodeUserRequestLogPartIndex = 0;

		while (($nodeProcessNodeUserRequestLogPartIndex === 9) === false) {
			$nodeProcessNodeUserRequestLogData = array();
			$nodeProcessNodeUserRequestLogs = _list(array(
				'data' => array(
					'destination_hostname',
					'id'
				),
				'in' => $parameters['system_databases']['node_process_node_user_request_logs'],
				'limit' => 1000,
				'where' => array(
					'processed_status' => '0',
					'processing_process_id' => $parameters['process_id']
				)
			), $response);
			$nodeRequestDestinations = array();

			foreach ($nodeProcessNodeUserRequestLogs as $nodeProcessNodeUserRequestLog) {
				if (empty($nodeRequestDestinations[$nodeProcessNodeUserRequestLog['destination_hostname']]) === true) {
					$nodeRequestDestination = _list(array(
						'data' => array(
							'id'
						),
						'in' => $parameters['system_databases']['node_process_node_user_request_logs'],
						'where' => array(
							'hostname' => $nodeProcessNodeUserRequestLog['destination_hostname']
						)
					), $response);
					$nodeRequestDestination = current($nodeRequestDestination);
					$nodeRequestDestinations[$nodeProcessNodeUserRequestLog['destination_hostname']] = $nodeRequestDestination['id'];
				}

				$nodeProcessNodeUserRequestLogData[] = array(
					'id' => $nodeProcessNodeUserRequestLog['id'],
					'node_request_destination_id' => $nodeRequestDestinations[$nodeProcessNodeUserRequestLog['destination_hostname']]
				);

				// todo: update node_request_destination_id
			}

			$nodeProcessNodeUserRequestLogPartIndex++;
		}

		// todo: update records with processing_process_id != null and modified > 10 mins ago
		$response['message'] = 'Node process node user request logs processed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	_processNodeProcessNodeUserRequestLogs($parameters, $response);
?>
