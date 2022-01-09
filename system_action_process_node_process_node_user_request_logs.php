<?php
	if (empty($_SERVER['argv'][1]) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_node_user_request_logs',
		'node_process_node_users'
	), $parameters['system_databases'], $response);

	function _processNodeProcessNodeUserRequestLogs($parameters, $response) {
		$nodeProcessNodeUserRequestLogParameters = array(
			'data' => array(
				'processing_process_id' => $parameters['process_id']
			),
			'in' => $parameters['system_databases']['node_process_node_user_request_logs'],
			'limit' => 10000,
			'where' => array(
				'processed_status' => '0',
				'processing_process_id' => null
			)
		);
		_update($nodeProcessNodeUserRequestLogParameters, $response);

		/* todo: previous code to refactor
			$nodeProcessNodeUserRequestLogData = array();
			$response = array(
				'message' => 'Error processing node process user request logs, please try again.',
				'status_valid' => false
			);
			$nodeProcessUserRequestLogsToProcessParameters = array(
				'in' => 'node_process_user_request_logs',
				'where' => array(
					'status_processed' => false,
					'OR' => array(
						'modified >' => date('Y-m-d H:i:s', strtotime('-10 minutes')),
						'status_processing' => false
					)
				)
			);
			$nodeProcessUserRequestLogsToProcessCount = $this->count($nodeProcessUserRequestLogsToProcessParameters);
			$response['status_valid'] = (
				(is_int($nodeProcessUserRequestLogsToProcessCount) === true) &&
				($nodeProcessUserRequestLogsToProcessCount !== 0)
			);

			if ($response['status_valid'] === true) {
				$nodeProcessUserRequestLogsToProcessParameters['data'] = array(
					'status_processing' => true
				);
				$nodeProcessUserRequestLogsToProcessUpdated = $this->update($nodeProcessUserRequestLogsToProcessParameters);

				if ($nodeProcessUserRequestLogsToProcessUpdated === true) {
					$nodeProcessUserRequestLogData = $requestDestinationIds = array();
					$nodeProcessUserRequestLogsToProcessIndex = 0;
					$nodeProcessUserRequestLogsToProcessParameters['fields'] = array(
						'bytes_received',
						'bytes_sent',
						'created',
						'destination_hostname',
						'destination_ip',
						'id',
						'node_id',
						'node_process_type',
						'node_user_id',
						'response_code',
						'source_ip',
						'username'
					);
					$nodeProcessUserRequestLogsToProcessParameters['from'] = 'node_process_user_request_logs';
					$nodeProcessUserRequestLogsToProcess = $this->fetch($nodeProcessUserRequestLogsToProcessParameters);

					foreach ($nodeProcessUserRequestLogsToProcess as $nodeProcessUserRequestLogToProcessKey => $nodeProcessUserRequestLogToProcess) {
						$nodeProcessUserRequestLogCreated = substr(date('Y-m-d H:i', $nodeProcessUserRequestLogToProcess['created']), 0, 15) . '0:00';
						$nodeProcessUserRequestLogData[$nodeProcessUserRequestLogToProcess['id']] = array(
							'id' => $nodeProcessUserRequestLogToProcess['id'],
							'status_processed' => true
						);

						// todo: track node_user_id bandwidth usage

						if (
							(isset($requestDestinationIds[$nodeProcessUserRequestLogToProcess['destination_hostname']]) === false) &&
							($this->_validateHostname($nodeProcessUserRequestLogToProcess['destination_hostname']) !== false)
						) {
							$requestDestination = $this->fetch(array(
								'fields' => array(
									'id'
								),
								'from' => 'request_destinations',
								'where' => array(
									'address' => $nodeProcessUserRequestLogToProcess['destination_hostname']
								)
							));
							$response['status_valid'] = ($requestDestination !== false);

							if ($response['status_valid'] === false) {
								continue;
							}

							if (empty($requestDestination['id']) === false) {
								$requestDestinationIds[$nodeProcessUserRequestLogToProcess['destination_hostname']] = $requestDestination['id'];
							}
						}

						if (empty($requestDestinationIds[$nodeProcessUserRequestLogToProcess['destination_hostname']]) === false) {
							$nodeProcessUserRequestLogData[$nodeProcessUserRequestLogToProcess['id']]['destination_id'] = $requestDestinationIds[$nodeProcessUserRequestLogToProcess['destination_hostname']];
						}

						$nodeProcessUserRequestLogsToProcessIndex++;

						if (
							($nodeProcessUserRequestLogsToProcessIndex === 10000) ||
							(empty($nodeProcessUserRequestLogsToProcess[($nodeProcessUserRequestLogToProcessKey + 1)]) === true)
						) {
							$nodeProcessUserRequestLogsToProcessIndex = 0;
							$this->save(array(
								'data' => $nodeProcessUserRequestLogData,
								'to' => 'node_process_user_request_logs'
							));
							$nodeProcessUserRequestLogData = array();
						}
					}
				}

				// todo: support additional VMs for processing request logs if necessary for millions of logs per minute
			}

			return $response;
		} todo */
		_save(array(
			'data' => $nodeProcessNodeUserRequestLogData,
			'in' => $parameters['system_databases']['node_process_node_user_request_logs']
		), $response);
		$response['message'] = 'Node process node user request logs processed successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	_processNodeProcessNodeUserRequestLogs($parameters, $response);
?>
