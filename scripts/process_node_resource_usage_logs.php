<?php
	class ProcessNodeResourceUsageLogs {

		public $parameters;

		public function __construct($parameters) {
			$this->parameters = $parameters;
		}

		public function process() {
			// todo: use same columns for system resource usage and node resource usage
			// todo: create rule to allocate more processes if nameserver or proxy cpu percentage exceeds X
			// reference previous 10 second values for each calculation
			// todo: make sure correct unit of time is used for each calculation

			$processNodeResourceUsageLogStart = time();
			$nodeResourceUsageLogData = array();
			exec('getconf CLK_TCK 2>&1', $clockTickSize);
			$clockTickSize = current($clockTickSize);

			while (($processNodeResourceUsageLogStart + 540) > time()) {
				$nodeCpuResourceUsage = array();
				exec('sudo cat /proc/stat | grep "cpu" 2>&1', $nodeCpuResourceUsage);

				if (empty($nodeResourceUsageLogData['cpu_capacity_cores']) === true) {
					end($nodeCpuResourceUsage);
					$nodeResourceUsageLogData['cpu_capacity_cores'] = key($nodeCpuResourceUsage);
				}

				$nodeCpuResourceUsageTime = array_shift($nodeCpuResourceUsage);
				exec('echo ' . $nodeCpuResourceUsageTime . ' | awk \'{print ""$2"+"$3"+"$4"+"$5"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11"_"$2"+"$3"+"$4"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeCpuResourceUsageTime);
				$nodeCpuResourceUsageTime = current($nodeCpuResourceUsageTime);
				$nodeCpuResourceUsageTimeParts = explode('_', $nodeCpuResourceUsageTime);
				$nodeCpuResourceUsageTime = $nodeProcessingCpuResourceUsageTime = 1;

				foreach ($nodeCpuResourceUsageTimeParts as $nodeCpuResourceUsageTimePart) {
					$nodeCpuResourceUsageTimePart = array_sum(explode('+', $nodeCpuResourceUsageTimePart));
					$nodeCpuResourceUsageTime = $nodeCpuResourceUsageTimePart / $nodeCpuResourceUsageTime;
				}

				$nodeResourceUsageLogData['cpu_percentage_node_processing'][] = ceil($nodeCpuResourceUsageTime / 10);
				exec('pgrep php', $nodeProcessingProcessIds);

				foreach ($nodeProcessingProcessIds as $nodeProcessingProcessId) {
					$nodeProcessingProcessCpuResourceUsageTime = array();
					exec('bash -c "cat /proc/' . $nodeProcessingProcessId . '/stat" | awk \'{print ""$14"+"$15"+"$16"+"$17""}\'', $nodeProcessingProcessCpuResourceUsageTime);
					$nodeProcessingProcessCpuResourceUsageTime = current($nodeProcessingProcessCpuResourceUsageTime);
					$nodeProcessingCpuResourceUsageTime += ceil(array_sum(explode('+', $nodeProcessingProcessCpuResourceUsageTime)) / 1000);
				}

				$nodeResourceUsageLogData['cpu_percentage_node_processing'][] = $nodeProcessingCpuResourceUsageTime;

				if (empty($nodeResourceUsageLogData['cpu_percentage_node_processing'][1]) === false) {
					// todo: measure cpu utilization after first interval
				}

				sleep(10);
			}

			$nodeResourceUsageLogAverageKeys = array(
				'cpu_capacity_megahertz',
				'cpu_percentage_node_processing',
				'cpu_percentage_node_usage',
				'memory_percentage_node_processing',
				'memory_percentage_node_usage',
				'memory_percentage_tcp',
				'memory_percentage_udp',
				'storage_percentage'
			);

			foreach ($nodeResourceUsageLogAverageKeys as $nodeResourceUsageLogAverageKey) {
				if (empty($nodeResourceUsageLogData[$nodeResourceUsageLogAverageKey]) === false) {
					rsort($nodeResourceUsageLogData[$nodeResourceUsageLogAverageKey]);
					$nodeResourceUsageLogData[$nodeResourceUsageLogAverageKey] = ceil(current($nodeResourceUsageLogData[$nodeResourceUsageLogAverageKey]) * 1000);
				}
			}

			// ..

			//exec('sudo curl -s --form-string "json={\"action\":\"archive\"}" ' . $this->parameters['system_url'] . '/endpoint/resource-usage-logs 2>&1', $response);
			$response = json_decode(current($response), true);
		}

	}

	$parameters = array(
		'system_url' => '127.0.0.1'
	);
	$processNodeResourceUsageLogs = new ProcessNodeResourceUsageLogs($parameters);
	$processNodeResourceUsageLogs->process();
?>
