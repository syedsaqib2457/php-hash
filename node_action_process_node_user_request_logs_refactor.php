<?php
	function processNodeUserRequestLogs($parameters, $response) {
		$nodeProcessTypes = array(
			'http_proxy',
			'recursive_dns',
			'socks_proxy'
		);

		$systemParameters = array(
			'action' => 'process_node_user_request_logs',
			'node_authentication_token' => $parameters['node_authentication_token']
		);
		$encodedSystemParameters = json_encode($systemParameters);

		if ($encodedSystemParameters === false) {
			$response['message'] = 'Error processing node user request logs, please try again.' . "\n";
			return $response;
		}

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
						exec('sudo curl -s --form "data=@/var/log/' . $nodeProcessType . '/' . $nodeProcessUserRequestLogFile . '" --form-string \'json=' . $encodedSystemParameters . '\' ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php 2>&1', $response);
						$response = current($response);
						$response = json_decode($response, true);

						if (empty($response['data']['most_recent_node_process_user_request_log']) === false) {
							$mostRecentNodeProcessUserRequestLog = $response['data']['most_recent_node_process_user_request_log'];
							$nodeProcessUserRequestLogFileContents = file_get_contents('/var/log/' . $nodeProcessType . '/' . $nodeProcessUserRequestLogFile);
							$updatedNodeProcessUserRequestLogs = substr($nodeProcessUserRequestLogFileContents, strpos($nodeProcessUserRequestLogFileContents, $mostRecentNodeProcessUserRequestLog) + strlen($mostRecentNodeProcessUserRequestLog));
							file_put_contents('/var/log/' . $nodeProcessType . '/' . $nodeProcessUserRequestLogFile, trim($updatedNodeProcessUserRequestLogs));
						}
					}
				}
			}
		}

		return $response;
	}

	if (($parameters['action'] === 'process_node_user_request_logs') === true) {
		$response = _processNodeUserRequestLogs($parameters, $response);
		_output($response);
	}
?>
