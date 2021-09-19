<?php
	class ProcessNodeUserRequestLogs {

		public $parameters;

		public function __construct($parameters) {
			$this->parameters = $parameters;
		}

		public function process() {
			$nodeProcessTypeNodeUserRequestLogPaths = array(
				'http_proxy' => '/var/log/http_proxy',
				'recursive_dns' => '/var/log/recursive_dns',
				'socks_proxy' => '/var/log/socks_proxy'
			);

			foreach ($nodeProcessTypeNodeUserRequestLogPaths as $nodeProcessType => $nodeProcessTypeNodeUserRequestLogPath) {
				if (is_dir($nodeProcessTypeNodeUserRequestLogPath) === true) {
					$nodeProcessTypeNodeUserRequestLogFiles = scandir($nodeProcessTypeNodeUserRequestLogPath);

					if (empty($nodeProcessTypeNodeUserRequestLogFiles) === false) {
						unset($nodeProcessTypeNodeUserRequestLogFiles[0]);
						unset($nodeProcessTypeNodeUserRequestLogFiles[1]);

						foreach ($nodeProcessTypeNodeUserRequestLogFiles as $nodeProcessTypeNodeUserRequestLogFile) {
							$nodeId = $nodeProcessTypeNodeUserRequestLogFile;
							$nodeProcessTypeNodeUserRequestLogFile = $nodeProcessTypeNodeUserRequestLogPath . '/' . $nodeId;
							exec('sudo curl -s --form "data=@' . $nodeProcessTypeNodeUserRequestLogFile . '" --form-string "json={\"action\":\"add\",\"data\":{\"node_id\":\"' . $nodeId . '\", \"type\":\"' . $nodeProcessType . '\"}}" ' . $this->parameters['system_url'] . '/endpoint/node-user-request-logs 2>&1', $response);
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
