<?php
	class ProcessNodeRequestLogs {

		public $parameters;

		public function __construct($parameters) {
			$this->parameters = $parameters;
		}

		public function process() {
			$nodeProcessTypeRequestLogFiles = array(
				'http_proxy' => '/var/log/http_proxy',
				'recursive_dns' => '/var/log/recursive_dns',
				'socks_proxy' => '/var/log/socks_proxy'
			);

			foreach ($nodeProcessTypeRequestLogFiles as $nodeProcessType => $nodeProcessTypeRequestLogFile) {
				if (file_exists($nodeProcessTypeRequestLogFile) === true) {
					exec('sudo curl -s --form "data=@' . $nodeProcessTypeRequestLogFile . '" --form-string "json={\"action\":\"add\",\"data\":{\"type\":\"' . $nodeProcessType . '\"}}" ' . $this->parameters['system_url'] . '/endpoint/request-logs 2>&1', $response);
					$response = json_decode(current($response), true);

					if (empty($response['data']['most_recent_request_log']) === false) {
						$mostRecentNodeRequestLog = $response['data']['most_recent_request_log'];
						$nodeRequestLogFileContents = file_get_contents($nodeProcessTypeRequestLogFile);
						$updateNodeRequestLogs = substr($nodeRequestLogFileContents, strpos($nodeRequestLogFileContents, $mostRecentNodeRequestLog) + strlen($mostRecentNodeRequestLog));
						file_put_contents($nodeProcessTypeRequestLogFile, trim($updatedNodeRequestLogs));
					}
				}
			}

			return;
		}

	}
?>
