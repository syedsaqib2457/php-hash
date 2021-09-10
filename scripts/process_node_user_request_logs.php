<?php
	class ProcessNodeUserRequestLogs {

		public $parameters;

		public function __construct($parameters) {
			$this->parameters = $parameters;
		}

		public function process() {
			// todo: create directories for each process type, scandir, read file contents
			$nodeProcessTypeNodeUserRequestLogFiles = array(
				'http_proxy' => '/var/log/http_proxy',
				'recursive_dns' => '/var/log/recursive_dns',
				'socks_proxy' => '/var/log/socks_proxy'
			);

			foreach ($nodeProcessTypeNodeUserRequestLogFiles as $nodeProcessType => $nodeProcessTypeNodeUserRequestLogFile) {
				if (file_exists($nodeProcessTypeNodeUserRequestLogFile) === true) {
					exec('sudo curl -s --form "data=@' . $nodeProcessTypeNodeUserRequestLogFile . '" --form-string "json={\"action\":\"add\",\"data\":{\"type\":\"' . $nodeProcessType . '\"}}" ' . $this->parameters['system_url'] . '/endpoint/node-user-request-logs 2>&1', $response);
					$response = json_decode(current($response), true);

					if (empty($response['data']['most_recent_node_user_request_log']) === false) {
						$mostRecentNodeUserRequestLog = $response['data']['most_recent_node_user_request_log'];
						$nodeUserRequestLogFileContents = file_get_contents($nodeProcessTypeNodeUserRequestLogFile);
						$updateNodeUserRequestLogs = substr($nodeUserRequestLogFileContents, strpos($nodeUserRequestLogFileContents, $mostRecentNodeUserRequestLog) + strlen($mostRecentNodeUserRequestLog));
						file_put_contents($nodeProcessTypeNodeUserRequestLogFile, trim($updatedNodeUserRequestLogs));
					}
				}
			}

			return;
		}

	}
?>
