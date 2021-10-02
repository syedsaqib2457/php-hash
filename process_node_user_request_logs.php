<?php
	class ProcessNodeProcessUserRequestLogs {

		public $parameters;

		public function __construct($parameters) {
			$this->parameters = $parameters;
		}

		public function process() {
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
							$nodeProcessUserRequestLogFile = '/var/log/' . $nodeProcessType . '/' . $nodeProcessUserRequestLogFile;
							exec('sudo curl -s --form "data=@' . $nodeProcessUserRequestLogFile . '" --form-string "json={\"action\":\"add\",\"data\":{\"node_id\":\"' . $nodeProcessNodeId . '\", \"node_process_type\":\"' . $nodeProcessType . '\", \"node_user_id\":\"' . $nodeProcessNodeUserId . '\"},\"where\":{\"token\":\"' . $this->parameters['token'] . '\"}}" ' . $this->parameters['system_url'] . '/endpoint/node-process-user-request-logs 2>&1', $response);
							$response = json_decode(current($response), true);

							if (empty($response['data']['most_recent_node_process_user_request_log']) === false) {
								$mostRecentNodeProcessUserRequestLog = $response['data']['most_recent_node_process_user_request_log'];
								$nodeProcessUserRequestLogFileContents = file_get_contents($nodeProcessUserRequestLogFile);
								$updatedNodeProcessUserRequestLogs = substr($nodeProcessUserRequestLogFileContents, strpos($nodeProcessUserRequestLogFileContents, $mostRecentNodeProcessUserRequestLog) + strlen($mostRecentNodeProcessUserRequestLog));
								file_put_contents($nodeProcessUserRequestLogFile, trim($updatedNodeProcessUserRequestLogs));
							}
						}
					}
				}
			}

			return;
		}

	}
?>
