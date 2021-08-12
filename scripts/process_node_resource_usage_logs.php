<?php
	class ProcessNodeResourceUsageLogs {

		public $parameters;

		public function __construct($parameters) {
			$this->parameters = $parameters;
		}

		public function process() {
			// todo: log cpu, memory and storage usage for X minutes with unix timestamp, then store in /tmp file and send data to api (free, htop, top, proc, etc)
			// todo: use same columns for system resource usage and node resource usage
			// todo: create rule to allocate more processes if nameserver or proxy cpu percentage exceeds X
			// todo: fetch CPU and mem % for all process types, add together for a total and decrease all to a value based on 100%
			$nodeResourceUsageLogData = array();

			// repeat this for 9 minutes, send peak usage to api, execute again after 10 minutes
			exec('sudo cat /proc/stat | grep "cpu" 2>&1', $nodeCpuResourceUsageResponse);
			exec('sudo ps -h -p "$(echo $(pgrep php) | while read -r processId; do echo $processId && break; done;)" -o pcpu -o %mem | grep "" 2>&1', $nodeProcessingCpuResourceUsageResponse);
			// ..

			if (empty($nodeResourceUsageLogData['cpu_capacity_cores']) === true) {
				end($nodeCpuResourceUsageResponse);
				$nodeResourceUsageLogData['cpu_capacity_cores'] = key($nodeCpuResourceUsageResponse);
			}

			$nodeCpuResourceUsageTime = array_shift($nodeCpuResourceUsageResponse);
			exec('echo ' . $nodeCpuResourceUsageTime . ' | awk \'{print ""$2"+"$3"+"$4"+"$5"+"$6"+"$7"+"$8"+"$9"_"$2"+"$3"+"$4"+"$6"+"$7"+"$8"+"$9""}\' 2>&1', $nodeCpuResourceUsageTimeResponse);
			$nodeCpuResourceUsageTimeResponse = current($nodeCpuResourceUsageTimeResponse);
			$nodeCpuResourceUsageTime = 1;
			$nodeCpuResourceUsageTimeParts = explode('_', $nodeCpuResourceUsageTimeResponse);

			foreach ($nodeCpuResourceUsageTimeParts as $nodeCpuResourceUsageTimePart) {
				$nodeCpuResourceUsageTimePart = array_sum(explode('+', $nodeCpuResourceUsageTimePart));
				$nodeCpuResourceUsageTime = $nodeCpuResourceUsageTimePart / $nodeCpuResourceUsageTime;
			}

			$nodeCpuResourceUsageTime *= 100;
			// ..

			//exec('sudo curl -s --form-string "json={\"action\":\"archive\"}" ' . $this->parameters['system_url'] . '/endpoint/resource-usage-logs 2>&1', $response);
			$response = json_decode(current($response), true);
			// ..
		}

	}

	/*
	$parameters = array(
		'system_url' => '127.0.0.1'
	);
	$processNodeResourceUsageLogs = new ProcessNodeResourceUsageLogs($parameters);
	$processNodeResourceUsageLogs->process();
	*/
?>
