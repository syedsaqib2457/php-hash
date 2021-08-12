<?php
	class ProcessNodeResourceUsageLogs {

		public $parameters;

		public function __construct($parameters) {
			$this->parameters = $parameters;
		}

		public function process() {
			// todo: log cpu, memory and storage usage for X minutes with unix timestamp, then store in /tmp file and send data to api (free, htop, top, proc, etc)
			// todo: use same columns for system resource usage and node resource usage
			exec('sudo curl -s --form "data=@' . $nodeResourceUsageLogFile . '" --form-string "json={\"action\":\"archive\"}" ' . $this->parameters['system_url'] . '/endpoint/resource-usage-logs 2>&1', $response);
			$response = json_decode(current($response), true);
			// ..
		}

	}
?>
