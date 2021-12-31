<?php
	function processNodeUserRequestLogs($parameters, $response) {
		$systemParameters = array(
			'action' => 'process_node_user_request_logs',
			'node_authentication_token' => $parameters['node_authentication_token']
		);
		$encodedSystemParameters = json_encode($systemParameters);

		if ($encodedSystemParameters === false) {
			$response['message'] = 'Error processing node user request logs, please try again.' . "\n";
			return $response;
		}

		$nodeProcessTypes = array(
			'http_proxy',
			'recursive_dns',
			'socks_proxy'
		);

		foreach ($nodeProcessTypes as $nodeProcessType) {
			$nodeProcessUserRequestLogFiles = scandir('/var/log/' . $nodeProcessType);

			if (empty($nodeProcessUserRequestLogFiles) === false) {
				unset($nodeProcessUserRequestLogFiles[0]);
				unset($nodeProcessUserRequestLogFiles[1]);

				foreach ($nodeProcessUserRequestLogFiles as $nodeProcessUserRequestLogFile) {
					$nodeProcessUserRequestLogFileParts = explode('_', $nodeProcessUserRequestLogFile);

					if (
						(empty($nodeProcessUserRequestLogFileParts[1]) === false) &&
						(empty($nodeProcessUserRequestLogFileParts[2]) === true) &&
						(is_numeric($nodeProcessUserRequestLogFileParts[0]) === true) &&
						(is_numeric($nodeProcessUserRequestLogFileParts[1]) === true)
					) {
						$nodeProcessNodeId = $nodeProcessUserRequestLogFileParts[0];
						$nodeProcessNodeUserId = $nodeProcessUserRequestLogFileParts[1];
						exec('sudo curl -s --form "data=@/var/log/' . $nodeProcessType . '/' . $nodeProcessUserRequestLogFile . '" --form-string \'json=' . $encodedSystemParameters . '\' ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php 2>&1', $processNodeUserRequestLogsResponse);
						$processNodeUserRequestLogsResponse = current($processNodeUserRequestLogsResponse);
						$processNodeUserRequestLogsResponse = json_decode($processNodeUserRequestLogsResponse, true);

						if (empty($processNodeUserRequestLogsResponse['valid_status']) === true) {
							$response['message'] = 'Error processing node user request logs, please try again.' . "\n";
							return $response;
						}

						$response['message'] = $processNodeUserRequestLogsResponse['message'];

						if (empty($response['data']['most_recent_node_process_user_request_log']) === false) {
							$mostRecentNodeProcessUserRequestLog = $response['data']['most_recent_node_process_user_request_log'];
							$mostRecentNodeProcessUserRequestLogLength = strlen($mostRecentNodeProcessUserRequestLog);
							$nodeProcessUserRequestLogs = file_get_contents('/var/log/' . $nodeProcessType . '/' . $nodeProcessUserRequestLogFile);
							$mostRecentNodeProcessUserRequestLogPosition = strpos($nodeProcessUserRequestLogs, $mostRecentNodeProcessUserRequestLog);
							$updatedNodeProcessUserRequestLogs = substr($nodeProcessUserRequestLogs, $mostRecentNodeProcessUserRequestLogPosition + $mostRecentNodeProcessUserRequestLogLength);
							$updatedNodeProcessUserRequestLogs = trim($updatedNodeProcessUserRequestLogs);
							$filePutContentsResponse = file_put_contents('/var/log/' . $nodeProcessType . '/' . $nodeProcessUserRequestLogFile, $updatedNodeProcessUserRequestLogs);

							if (empty($filePutContentsResponse) === true) {
								$response['message'] = 'Error processing node user request logs, please try again.' . "\n";
								return $response;
							}
						}
					}
				}
			}
		}

		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'process_node_user_request_logs') === true) {
		$response = _processNodeUserRequestLogs($parameters, $response);
		_output($response);
	}
?>
