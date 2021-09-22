<?php
	class ProcessNodeUserRequestLogs {

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
				$nodeProcessTypeRequestLogFiles = scandir('/var/log/' . $nodeProcessType);

				if (empty($nodeProcessTypeRequestLogFiles) === false) {
					unset($nodeProcessTypeRequestLogFiles[0]);
					unset($nodeProcessTypeRequestLogFiles[1]);

					foreach ($nodeProcessTypeRequestLogFiles as $nodeProcessTypeRequestLogFile) {
						$nodeProcessTypeRequestLogFileParts = explode('_', $nodeProcessTypeRequestLogFile);

						if (
							(empty($nodeProcessTypeRequestLogFileParts[1]) === false) &&
							(empty($nodeProcessTypeRequestLogFileParts[2]) === true) &&
							(is_numeric($nodeProcessTypeRequestLogFileParts[0]) === true) &&
							(is_numeric($nodeProcessTypeRequestLogFileParts[1]) === true)
						) {
							$nodeProcessNodeId = $nodeProcessTypeRequestLogFileParts[0];
							$nodeProcessNodeUserId = $nodeProcessTypeRequestLogFileParts[1];
							$nodeProcessTypeNodeUserRequestLogFile = '/var/log/' . $nodeProcessType . '/' . $nodeProcessTypeRequestLogFile;
							exec('sudo curl -s --form "data=@' . $nodeProcessTypeNodeUserRequestLogFile . '" --form-string "json={\"action\":\"add\",\"data\":{\"node_id\":\"' . $nodeProcessNodeId . '\", \"node_user_id\":\"' . $nodeProcessNodeUserId . '\", \"type\":\"' . $nodeProcessType . '\"}}" ' . $this->parameters['system_url'] . '/endpoint/node-user-request-logs 2>&1', $response);
							$response = json_decode(current($response), true);

							if (empty($response['data']['most_recent_node_user_request_log']) === false) {
								$mostRecentNodeUserRequestLog = $response['data']['most_recent_node_user_request_log'];
								$nodeUserRequestLogFileContents = file_get_contents($nodeProcessTypeNodeUserRequestLogFile);
								$updatedNodeUserRequestLogs = substr($nodeUserRequestLogFileContents, strpos($nodeUserRequestLogFileContents, $mostRecentNodeUserRequestLog) + strlen($mostRecentNodeUserRequestLog));
								file_put_contents($nodeProcessTypeNodeUserRequestLogFile, trim($updatedNodeUserRequestLogs));
							}
						}
					}
				}
			}

			return;
		}

	}
?>
