<?php
	function processNodeProcessNodeUserRequestLogs($parameters, $response) {
		$nodeProcessTypes = array(
			'http_proxy',
			'recursive_dns',
			'socks_proxy'
		);
		$systemParameters = array(
			'action' => 'add_node_process_node_user_request_logs',
			'node_authentication_token' => $parameters['node_authentication_token']
		);

		foreach ($nodeProcessTypes as $nodeProcessType) {
			$nodeProcessNodeUserRequestLogFiles = scandir('/var/log/' . $nodeProcessType);

			if (empty($nodeProcessNodeUserRequestLogFiles) === false) {
				unset($nodeProcessNodeUserRequestLogFiles[0]);
				unset($nodeProcessNodeUserRequestLogFiles[1]);

				foreach ($nodeProcessNodeUserRequestLogFiles as $nodeProcessNodeUserRequestLogFile) {
					$nodeProcessNodeUserRequestLogFileParts = explode('_', $nodeProcessNodeUserRequestLogFile);

					if (
						(empty($nodeProcessNodeUserRequestLogFileParts[1]) === false) &&
						(empty($nodeProcessNodeUserRequestLogFileParts[2]) === true)
					) {
						// todo: add id data to $systemParameters
						$encodedSystemParameters = json_encode($systemParameters);

						if ($encodedSystemParameters === false) {
							$response['message'] = 'Error processing node process node user request logs, please try again.';
							return $response;
						}

						$nodeProcessNodeId = $nodeProcessNodeUserRequestLogFileParts[0];
						$nodeProcessNodeUserId = $nodeProcessNodeUserRequestLogFileParts[1];
						exec('sudo curl -s --form "data=@/var/log/' . $nodeProcessType . '/' . $nodeProcessNodeUserRequestLogFile . '" --form-string \'json=' . $encodedSystemParameters . '\' ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php 2>&1', $processNodeProcessNodeUserRequestLogsResponse);
						$processNodeProcessNodeUserRequestLogsResponse = current($processNodeProcessNodeUserRequestLogsResponse);
						$processNodeProcessNodeUserRequestLogsResponse = json_decode($processNodeProcessNodeUserRequestLogsResponse, true);

						if (empty($processNodeProcessNodeUserRequestLogsResponse['valid_status']) === true) {
							$response['message'] = 'Error processing node process node user request logs, please try again.';
							return $response;
						}

						$response['message'] = $processNodeProcessNodeUserRequestLogsResponse['message'];

						if (empty($response['data']['most_recent_node_process_node_user_request_log']) === false) {
							$mostRecentNodeProcessNodeUserRequestLog = $response['data']['most_recent_node_process_node_user_request_log'];
							$mostRecentNodeProcessNodeUserRequestLogLength = strlen($mostRecentNodeProcessNodeUserRequestLog);
							$nodeProcessNodeUserRequestLogs = file_get_contents('/var/log/' . $nodeProcessType . '/' . $nodeProcessNodeUserRequestLogFile);
							$mostRecentNodeProcessNodeUserRequestLogPosition = strpos($nodeProcessNodeUserRequestLogs, $mostRecentNodeProcessNodeUserRequestLog);

							if ($mostRecentNodeProcessNodeUserRequestLogPosition === false) {
								continue;
							}

							$updatedNodeProcessNodeUserRequestLogs = substr($nodeProcessNodeUserRequestLogs, $mostRecentNodeProcessNodeUserRequestLogPosition + $mostRecentNodeProcessNodeUserRequestLogLength);
							$updatedNodeProcessNodeUserRequestLogs = trim($updatedNodeProcessNodeUserRequestLogs);
							file_put_contents('/var/log/' . $nodeProcessType . '/' . $nodeProcessNodeUserRequestLogFile, $updatedNodeProcessNodeUserRequestLogs);
						}
					}
				}
			}
		}

		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'process_node_process_node_user_request_logs') === true) {
		$response = _processNodeProcessNodeUserRequestLogs($parameters, $response);
		_output($response);
	}
?>
