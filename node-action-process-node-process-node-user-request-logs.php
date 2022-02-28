<?php
	if (empty($parameters) === true) {
		exit;
	}

	function processNodeProcessNodeUserRequestLogs($parameters, $response) {
		$nodeProcessTypes = array(
			'httpProxy',
			'recursiveDns',
			'socksProxy'
		);
		$systemParameters = array(
			'action' => 'addNodeProcessNodeUserRequestLogs',
			'nodeAuthenticationToken' => $parameters['nodeAuthenticationToken']
		);

		foreach ($nodeProcessTypes as $nodeProcessType) {
			$nodeProcessNodeUserRequestLogFiles = scandir('/var/log/' . $nodeProcessType);
			$systemParameters['data']['nodeProcessType'] = $nodeProcessType;

			if (empty($nodeProcessNodeUserRequestLogFiles) === false) {
				unset($nodeProcessNodeUserRequestLogFiles[0]);
				unset($nodeProcessNodeUserRequestLogFiles[1]);

				foreach ($nodeProcessNodeUserRequestLogFiles as $nodeProcessNodeUserRequestLogFile) {
					$nodeProcessNodeUserRequestLogFileParts = explode('_', $nodeProcessNodeUserRequestLogFile);

					if (
						(empty($nodeProcessNodeUserRequestLogFileParts[1]) === false) &&
						(empty($nodeProcessNodeUserRequestLogFileParts[2]) === true)
					) {
						$systemParameters['data']['nodeId'] = $nodeProcessNodeUserRequestLogFileParts[0];
						$systemParameters['data']['nodeUserId'] = $nodeProcessNodeUserRequestLogFileParts[1];
						$encodedSystemParameters = json_encode($systemParameters);

						if ($encodedSystemParameters === false) {
							$response['message'] = 'Error processing node process node user request logs, please try again.';
							return $response;
						}

						exec('sudo curl -s --form "data=@/var/log/' . $nodeProcessType . '/' . $nodeProcessNodeUserRequestLogFile . '" --form-string \'json=' . $encodedSystemParameters . '\' ' . $parameters['systemEndpointDestinationAddress'] . '/system-endpoint.php 2>&1', $systemActionProcessNodeProcessNodeUserRequestLogsResponse);
						$systemActionProcessNodeProcessNodeUserRequestLogsResponse = current($systemActionProcessNodeProcessNodeUserRequestLogsResponse);
						$systemActionProcessNodeProcessNodeUserRequestLogsResponse = json_decode($systemActionProcessNodeProcessNodeUserRequestLogsResponse, true);

						if (empty($systemActionProcessNodeProcessNodeUserRequestLogsResponse['validStatus']) === true) {
							$response['message'] = 'Error processing node process node user request logs, please try again.';
							return $response;
						}

						$response['message'] = $systemActionProcessNodeProcessNodeUserRequestLogsResponse['message'];

						if (empty($response['data']['mostRecentNodeProcessNodeUserRequestLog']) === false) {
							$mostRecentNodeProcessNodeUserRequestLog = $response['data']['mostRecentNodeProcessNodeUserRequestLog'];
							$mostRecentNodeProcessNodeUserRequestLogLength = strlen($mostRecentNodeProcessNodeUserRequestLog);
							$nodeProcessNodeUserRequestLogs = file_get_contents('/var/log/' . $nodeProcessType . '/' . $nodeProcessNodeUserRequestLogFile);
							$mostRecentNodeProcessNodeUserRequestLogPosition = strpos($nodeProcessNodeUserRequestLogs, $mostRecentNodeProcessNodeUserRequestLog);

							if ($mostRecentNodeProcessNodeUserRequestLogPosition === false) {
								continue;
							}

							$updatedNodeProcessNodeUserRequestLogs = substr($nodeProcessNodeUserRequestLogs, ($mostRecentNodeProcessNodeUserRequestLogPosition + $mostRecentNodeProcessNodeUserRequestLogLength));
							$updatedNodeProcessNodeUserRequestLogs = trim($updatedNodeProcessNodeUserRequestLogs);
							file_put_contents('/var/log/' . $nodeProcessType . '/' . $nodeProcessNodeUserRequestLogFile, $updatedNodeProcessNodeUserRequestLogs);
						}
					}
				}
			}
		}

		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'process-node-process-node-user-request-logs') === true) {
		$response = _processNodeProcessNodeUserRequestLogs($parameters, $response);
	}
?>
