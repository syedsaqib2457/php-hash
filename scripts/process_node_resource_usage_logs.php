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
			exec('getconf PAGE_SIZE 2>&1', $kernelPageSize);
			exec('free | grep -v free | awk \'NR==1{print $2}\'', $totalSystemMemory);
			$kernelPageSize = current($kernelPageSize);
			$totalSystemMemory = current($totalSystemMemory);
			$nodeResourceUsageLogData = array(
				'memory_capacity_megabytes' => ($totalSystemMemory / 1000)
			);

			while (($processNodeResourceUsageLogStart + 540) > time()) {
				if (empty($nodeResourceUsageLogProcessingIntervalIndex) === true) {
					$nodeResourceUsageLogProcessingIntervalIndex = 0;
				}

				$nodeResourceUsageLogCpuNodeTime = $nodeResourceUsageLogCpuTimeNodeStart = microtime();

				if (empty($nodeResourceUsageLogData['cpu_time']['interval']) === true) {
					exec('sudo cat /proc/stat | grep "cpu" 2>&1', $nodeResourceUsageLogCpuTimeNode);
					end($nodeResourceUsageLogCpuTimeNode);
					$nodeResourceUsageLogData['cpu_capacity_cores'] = key($nodeResourceUsageLogCpuTimeNode);
					$nodeResourceUsageLogCpuTimeNode = array_shift($nodeResourceUsageLogCpuTimeNode);
					exec('echo ' . $nodeResourceUsageLogCpuTimeNode . ' | awk \'{print ""$2"+"$3"+"$4"+"$5"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeResourceUsageLogCpuTimeNode);
					$nodeResourceUsageLogCpuTimeNode = current($nodeResourceUsageLogCpuTimeNode);
					$nodeResourceUsageLogData['cpu_time'][] = array(
						'cpu_time' => array_sum(explode('+', $nodeResourceUsageLogCpuTimeNode)),
						'timestamp' => $nodeResourceUsageLogCpuTimeNodeStart
					);

					if (empty($nodeResourceUsageLogData['cpu_time'][1]) === false) {
						$nodeResourceUsageLogData['cpu_time'] = array(
							'cpu_time' => $nodeResourceUsageLogData['cpu_time'][1]['cpu_time'] - $nodeResourceUsageLogData['cpu_time'][0]['cpu_time'],
							'interval' => $nodeResourceUsageLogData['cpu_time'][1]['timestamp'] - $nodeResourceUsageLogData['cpu_time'][0]['timestamp']
						);
					}
				} else {
					exec('sudo cat /proc/stat | grep "cpu " | awk \'{print ""$2"+"$3"+"$4"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeResourceUsageLogCpuTimeNode);
					$nodeResourceUsageLogData['cpu_time_node'][$nodeResourceUsageLogProcessingIntervalIndex] = array(
						'cpu_time' => array_sum(explode('+', $nodeResourceUsageLogCpuTimeNode)),
						'timestamp' => $nodeResourceUsageLogCpuTimeNodeStart
					);

					if (empty($nodeResourceUsageLogData['cpu_time_node'][($nodeResourceUsageLogProcessingIntervalIndex - 1)]) === false) {
						$nodeResourceUsageLogData['cpu_percentage_node'][$nodeResourceUsageLogProcessingIntervalIndex] = array(
							'cpu_time' => $nodeResourceUsageLogData['cpu_time_node'][$nodeResourceUsageLogProcessingIntervalIndex]['cpu_time'] - $nodeResourceUsageLogData['cpu_time_node'][($nodeResourceUsageLogProcessingIntervalIndex - 1)]['cpu_time'],
							'interval' => $nodeResourceUsageLogData['cpu_time_node'][$nodeResourceUsageLogProcessingIntervalIndex]['timestamp'] - $nodeResourceUsageLogData['cpu_time_node'][($nodeResourceUsageLogProcessingIntervalIndex - 1)]['timestamp']
						);
					}

					if (empty($nodeResourceUsageLogData['cpu_percentage_node'][$nodeResourceUsageLogProcessingIntervalIndex]) === false) {
						$nodeResourceUsageLogCpuPercentageNode = $nodeResourceUsageLogData['cpu_percentage_node'][$nodeResourceUsageLogProcessingIntervalIndex];
						$nodeResourceUsageLogData['cpu_percentage_node'][$nodeResourceUsageLogProcessingIntervalIndex] = ($nodeResourceUsageLogData['cpu_time_node'][$nodeResourceUsageLogProcessingIntervalIndex]['cpu_time'] + ($nodeResourceUsageLogData['cpu_time']['interval'] - $nodeResourceUsageLogCpuPercentageNode['interval']) * ($nodeResourceUsageLogCpuPercentageNode['cpu_time'] / $nodeResourceUsageLogCpuPercentageNode['interval'])) / $nodeResourceUsageLogData['cpu_time']['interval'];
					}

					$nodeResourceUsageLogCpuTimeNodeProcessing = $nodeProcessingProcessIds = 0;
					exec('pgrep php', $nodeProcessingProcessIds);

					foreach ($nodeProcessingProcessIds as $nodeProcessingProcessId) {
						$nodeResourceUsageLogCpuTimeNodeProcessingProcess = $nodeResourceUsageLogCpuTimeNodeProcessingProcessStart = microtime();
						exec('bash -c "cat /proc/' . $nodeProcessingProcessId . '/stat" | awk \'{print ""$14"+"$15"+"$16"+"$17""}\' 2>&1', $nodeResourceUsageLogCpuTimeNodeProcessingProcess);
						$nodeResourceUsageLogCpuTimeNodeProcessingProcess = current($nodeResourceUsageLogCpuTimeNodeProcessingProcess);
						$nodeResourceUsageLogData['cpu_time_node_processing'][$nodeResourceUsageLogProcessingIntervalIndex][$nodeProcessingProcessId] = array(
							'cpu_time' => array_sum(explode('+', $nodeResourceUsageLogCpuTimeNodeProcessingProcess)),
							'timestamp' => $nodeResourceUsageLogCpuTimeNodeProcessingProcessStart
						);

						if (empty($nodeResourceUsageLogData['cpu_time_node_processing'][($nodeResourceUsageLogProcessingIntervalIndex - 1)][$nodeProcessingProcessId]) === false) {
							$nodeResourceUsageLogData['cpu_percentage_node_processing'][$nodeResourceUsageLogProcessingIntervalIndex][$nodeProcessingProcessId] = array(
								'cpu_time' => $nodeResourceUsageLogData['cpu_time_node_processing'][$nodeResourceUsageLogProcessingIntervalIndex][$nodeProcessingProcessId]['cpu_time'] - $nodeResourceUsageLogData['cpu_time_node_processing'][($nodeResourceUsageLogProcessingIntervalIndex - 1)][$nodeProcessingProcessId]['cpu_time'],
								'interval' => $nodeResourceUsageLogData['cpu_time_node_processing'][$nodeResourceUsageLogProcessingIntervalIndex][$nodeProcessingProcessId]['timestamp'] - $nodeResourceUsageLogData['cpu_time_node_processing'][($nodeResourceUsageLogProcessingIntervalIndex - 1)][$nodeProcessingProcessId]['timestamp']
							);
						}
					}

					if (empty($nodeResourceUsageLogData['cpu_percentage_node_processing'][$nodeResourceUsageLogProcessingIntervalIndex]) === false) {
						$nodeResourceUsageLogCpuPercentageNodeProcessing = 0;

						foreach ($nodeResourceUsageLogData['cpu_percentage_node_processing'][$nodeResourceUsageLogProcessingIntervalIndex] as $nodeProcessingProcessId => $nodeResourceUsageLogCpuPercentageNodeProcessingProcess) {
							$nodeResourceUsageLogData['cpu_percentage_node_processing'][$nodeResourceUsageLogProcessingIntervalIndex][$nodeProcessingProcessId]['cpu_time'] += ($nodeResourceUsageLogData['cpu_time']['interval'] - $nodeResourceUsageLogCpuPercentageNodeProcessingProcess['interval']) * ($nodeResourceUsageLogCpuPercentageNodeProcessingProcess['cpu_time'] / $nodeResourceUsageLogCpuPercentageNodeProcessingProcess['interval']);
							$nodeResourceUsageLogCpuPercentageNodeProcessing += ($nodeResourceUsageLogData['cpu_percentage_node_processing'][$nodeResourceUsageLogProcessingIntervalIndex][$nodeProcessingProcessId]['cpu_time'] / $nodeResourceUsageLogData['cpu_time']['interval']);
						}

						$nodeResourceUsageLogData['cpu_percentage_node_processing'][$nodeResourceUsageLogProcessingIntervalIndex] = $nodeResourceUsageLogCpuPercentageNodeProcessing;
					}

					// todo: cpu_percentage
					// todo: calculate cpu_percentage_node_usage with remainder until CPU usage for each process type is tracked
					// todo: memory_percentage_node_application
					// todo: memory_percentage_node_processing
					$nodeTransportProtocols = array(
						'tcp',
						'udp'
					);
					exec('bash -c "cat /proc/net/sockstat" | grep "P: " 2>&1', $transportProtocolMemoryUsageLogs);

					foreach ($transportProtocolMemoryUsageLogs as $transportProtocolMemoryUsageLogKey => $transportProtocolMemoryUsageLog) {
						$transportProtocolMemoryUsageLog = (intval(substr($transportProtocolMemoryUsageLog, strpos($transportProtocolMemoryUsageLog, 'mem ') + 4)) * $kernelPageSize) / 1000;
						$nodeResourceUsageLogData['memory_percentage_' . $nodeTransportProtocols[$transportProtocolMemoryUsageKey]][$nodeResourceUsageLogProcessingIntervalIndex][] = ceil(($transportProtocolMemoryUsage / $totalSystemMemory) * 100);
					}

					exec('df -m / | tail -1 | awk \'{print $4}\'  2>&1', $storageCapacityMegabytes);
					exec('df / | tail -1 | awk \'{print $5}\' 2>&1', $storagePercentage);
					$nodeResourceUsageLogData['storage_capacity_megabytes'] = current($storageCapacityMegabytes);
					$nodeResourceUsageLogData['storage_percentage'] = intval(current($storagePercentage));
				}

				$nodeResourceUsageLogProcessingIntervalIndex++;
				sleep(10);
			}

			$nodeResourceUsageLogPercentageKeys = array(
				'cpu_percentage_node',
				'cpu_percentage_node_processing',
				'cpu_percentage_node_usage',
				'memory_percentage_node_processing',
				'memory_percentage_node_usage',
				'memory_percentage_tcp',
				'memory_percentage_udp',
				'storage_percentage'
			);

			foreach ($nodeResourceUsageLogPercentageKeys as $nodeResourceUsageLogPercentageKey) {
				if (empty($nodeResourceUsageLogData[$nodeResourceUsageLogPercentageKey]) === false) {
					rsort($nodeResourceUsageLogData[$nodeResourceUsageLogPercentageKey]);
					$nodeResourceUsageLogData[$nodeResourceUsageLogPercentageKey] = current($nodeResourceUsageLogData[$nodeResourceUsageLogPercentageKey]);
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
