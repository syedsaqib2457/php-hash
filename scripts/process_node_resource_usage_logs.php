<?php
	class ProcessNodeResourceUsageLogs {

		public $parameters;

		public function __construct($parameters) {
			$this->parameters = $parameters;
		}

		public function process() {
			// todo: use same columns for system resource usage and node resource usage
			// todo: create rule to allocate more processes if nameserver or proxy cpu percentage exceeds X

			$processNodeResourceUsageLogStart = time();
			$nodeResourceUsageLogData = array();

			while (($processNodeResourceUsageLogStart + 540) > time()) {
				if (empty($nodeResourceUsageLogData['cpu_time']['interval']) === true) {
					$nodeCpuTime = $nodeCpuTimeStart = microtime();
					exec('sudo cat /proc/stat | grep "cpu" 2>&1', $nodeCpuTime);
					end($nodeCpuTime);
					$nodeResourceUsageLogData['cpu_capacity_cores'] = key($nodeCpuTime);
					$nodeCpuTime = array_shift($nodeCpuTime);
					exec('echo ' . $nodeCpuTime . ' | awk \'{print ""$2"+"$3"+"$4"+"$5"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeCpuTime);
					$nodeCpuTime = current($nodeCpuResourceUsageTime);
					$nodeResourceUsageLogData['cpu_time'][] = array(
						'cpu_time' => array_sum(explode('+', $nodeCpuTime)),
						'timestamp' => $nodeCpuTimeStart
					);

					if (empty($nodeResourceUsageLogData['cpu_time'][1]) === false) {
						$nodeResourceUsageLogData['cpu_time'] = array(
							'cpu_time' => $nodeResourceUsageLogData['cpu_time'][1]['cpu_time'] - $nodeResourceUsageLogData['cpu_time'][0]['cpu_time'],
							'interval' => $nodeResourceUsageLogData['cpu_time'][1]['timestamp'] - $nodeResourceUsageLogData['cpu_time'][0]['timestamp']
						);
					}
				} else {
					exec('pgrep php', $nodeProcessingProcessIds);

					/*foreach ($nodeProcessingProcessIds as $nodeProcessingProcessId) {
						$nodeProcessingProcessCpuResourceUsageTime = array();
						exec('bash -c "cat /proc/' . $nodeProcessingProcessId . '/stat" | awk \'{print ""$14"+"$15"+"$16"+"$17""}\'', $nodeProcessingProcessCpuResourceUsageTime);
						$nodeProcessingProcessCpuResourceUsageTime = current($nodeProcessingProcessCpuResourceUsageTime);
						$nodeProcessingCpuResourceUsageTime += array_sum(explode('+', $nodeProcessingProcessCpuResourceUsageTime));
					}

					$nodeResourceUsageLogData['cpu_percentage_node_processing'][] = $nodeProcessingCpuResourceUsageTime;

					if (empty($nodeResourceUsageLogData['cpu_percentage_node_processing'][1]) === false) {
						end($nodeResourceUsageLogData['cpu_time']);
						end($nodeResourceUsageLogData['cpu_percentage_node_processing']);
						$nodeProcessingUsageLogIndex = key($nodeResourceUsageLogData);
						$nodeProcessingProcessCpuResourceUsageTime = $nodeResourceUsageLogData['cpu_percentage_node_processing'][$nodeProcessingUsageLogIndex] - $nodeResourceUsageLogData['cpu_percentage_node_processing'][($nodeProcessingUsageLogIndex - 1)];
						// todo: measure cpu utilization after first interval
					}*/
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
