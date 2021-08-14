<?php
	class ProcessNodeResourceUsageLogs {

		public $parameters;

		public function __construct($parameters) {
			$this->parameters = $parameters;
		}

		public function process() {
			// todo: create rule to allocate more processes if nameserver or proxy cpu percentage exceeds X

			$nodeResourceUsageLogNodeProcessSystemStart = time();
			exec('getconf PAGE_SIZE 2>&1', $kernelPageSize);
			exec('free | grep -v free | awk \'NR==1{print $2}\'', $totalSystemMemory);
			$kernelPageSize = current($kernelPageSize);
			$totalSystemMemory = current($totalSystemMemory);
			$nodeResourceUsageLogData = array(
				'memory_capacity_megabytes' => ($totalSystemMemory / 1000)
			);

			while (($nodeResourceUsageLogNodeProcessSystemStart + 540) > time()) {
				if (empty($nodeResourceUsageLogProcessSystemIntervalIndex) === true) {
					$nodeResourceUsageLogProcessSystemIntervalIndex = 0;
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
					$nodeResourceUsageLogData['cpu_time_node'][$nodeResourceUsageLogProcessSystemIntervalIndex] = array(
						'cpu_time' => array_sum(explode('+', $nodeResourceUsageLogCpuTimeNode)),
						'timestamp' => $nodeResourceUsageLogCpuTimeNodeStart
					);

					if (empty($nodeResourceUsageLogData['cpu_time_node'][($nodeResourceUsageLogProcessSystemIntervalIndex - 1)]) === false) {
						$nodeResourceUsageLogData['cpu_percentage_node'][$nodeResourceUsageLogProcessSystemIntervalIndex] = array(
							'cpu_time' => $nodeResourceUsageLogData['cpu_time_node'][$nodeResourceUsageLogProcessSystemIntervalIndex]['cpu_time'] - $nodeResourceUsageLogData['cpu_time_node'][($nodeResourceUsageLogProcessSystemIntervalIndex - 1)]['cpu_time'],
							'interval' => $nodeResourceUsageLogData['cpu_time_node'][$nodeResourceUsageLogProcessSystemIntervalIndex]['timestamp'] - $nodeResourceUsageLogData['cpu_time_node'][($nodeResourceUsageLogProcessSystemIntervalIndex - 1)]['timestamp']
						);
					}

					if (empty($nodeResourceUsageLogData['cpu_percentage_node'][$nodeResourceUsageLogProcessingIntervalIndex]) === false) {
						$nodeResourceUsageLogCpuPercentageNode = $nodeResourceUsageLogData['cpu_percentage_node'][$nodeResourceUsageLogProcessSystemIntervalIndex];
						$nodeResourceUsageLogData['cpu_percentage_node'][$nodeResourceUsageLogProcessSystemIntervalIndex] = ($nodeResourceUsageLogData['cpu_time_node'][$nodeResourceUsageLogProcessSystemIntervalIndex]['cpu_time'] + ($nodeResourceUsageLogData['cpu_time']['interval'] - $nodeResourceUsageLogCpuPercentageNode['interval']) * ($nodeResourceUsageLogCpuPercentageNode['cpu_time'] / $nodeResourceUsageLogCpuPercentageNode['interval'])) / $nodeResourceUsageLogData['cpu_time']['interval'];
					}

					$nodeResourceUsageLogCpuTimeNodeProcessSystem = $nodeProcessSystemProcessIds = 0;
					exec('pgrep php', $nodeProcessSystemProcessIds);

					foreach ($nodeProcessSystemProcessIds as $nodeProcessSystemProcessId) {
						$nodeResourceUsageLogCpuTimeNodeProcessSystemProcessProcess = $nodeResourceUsageLogCpuTimeNodeProcessSystemProcessStart = microtime();
						exec('bash -c "cat /proc/' . $nodeProcessSystemProcessId . '/stat" | awk \'{print ""$14"+"$15"+"$16"+"$17""}\' 2>&1', $nodeResourceUsageLogCpuTimeNodeProcessSystemProcess);
						$nodeResourceUsageLogCpuTimeNodeProcessSystemProcess = current($nodeResourceUsageLogCpuTimeNodeProcessSystemProcess);
						$nodeResourceUsageLogData['cpu_time_node_process_system'][$nodeResourceUsageLogProcessSystemIntervalIndex][$nodeProcessSystemProcessId] = array(
							'cpu_time' => array_sum(explode('+', $nodeResourceUsageLogCpuTimeNodeProcessSystemProcess)),
							'timestamp' => $nodeResourceUsageLogCpuTimeNodeProcessSystemProcessStart
						);

						if (empty($nodeResourceUsageLogData['cpu_time_node_process_system'][($nodeResourceUsageLogProcessingIntervalIndex - 1)][$nodeProcessingProcessId]) === false) {
							$nodeResourceUsageLogData['cpu_percentage_node_process_system'][$nodeResourceUsageLogProcessSystemIntervalIndex][$nodeProcessSystemProcessId] = array(
								'cpu_time' => $nodeResourceUsageLogData['cpu_time_node_process_system'][$nodeResourceUsageLogProcessSystemIntervalIndex][$nodeProcessSystemProcessId]['cpu_time'] - $nodeResourceUsageLogData['cpu_time_node_processing'][($nodeResourceUsageLogProcessSystemIntervalIndex - 1)][$nodeProcessSystemProcessId]['cpu_time'],
								'interval' => $nodeResourceUsageLogData['cpu_time_node_process_system'][$nodeResourceUsageLogProcessSystemIntervalIndex][$nodeProcessSystemProcessId]['timestamp'] - $nodeResourceUsageLogData['cpu_time_node_process_system'][($nodeResourceUsageLogProcessSystemIntervalIndex - 1)][$nodeProcessSystemProcessId]['timestamp']
							);
						}
					}

					if (empty($nodeResourceUsageLogData['cpu_percentage_node_process_system'][$nodeResourceUsageLogProcessingIntervalIndex]) === false) {
						$nodeResourceUsageLogCpuPercentageNodeProcessSystem = 0;

						foreach ($nodeResourceUsageLogData['cpu_percentage_node_process_system'][$nodeResourceUsageLogProcessSystemIntervalIndex] as $nodeProcessSystemProcessId => $nodeResourceUsageLogCpuPercentageNodeProcessSystemProcess) {
							$nodeResourceUsageLogData['cpu_percentage_node_process_system'][$nodeResourceUsageLogProcessSystemIntervalIndex][$nodeProcessSystemProcessId]['cpu_time'] += ($nodeResourceUsageLogData['cpu_time']['interval'] - $nodeResourceUsageLogCpuPercentageNodeProcessSystemProcess['interval']) * ($nodeResourceUsageLogCpuPercentageNodeProcessSystemProcess['cpu_time'] / $nodeResourceUsageLogCpuPercentageNodeProcessSystemProcess['interval']);
							$nodeResourceUsageLogCpuPercentageNodeProcessSystem += ($nodeResourceUsageLogData['cpu_percentage_node_process_system'][$nodeResourceUsageLogProcessSystemIntervalIndex][$nodeProcessingProcessId]['cpu_time'] / $nodeResourceUsageLogData['cpu_time']['interval']);
						}

						$nodeResourceUsageLogData['cpu_percentage_node_process_system'][$nodeResourceUsageLogProcessSystemIntervalIndex] = $nodeResourceUsageLogCpuPercentageNodeProcessSystem;
					}

					// todo: calculate cpu_percentage_node_application with each proxy / dns process
					// todo: memory_percentage_node_application
					// todo: memory_percentage_node_processing
					$nodeTransportProtocols = array(
						'tcp',
						'udp'
					);
					exec('bash -c "cat /proc/net/sockstat" | grep "P: " 2>&1', $transportProtocolMemoryUsageLogs);

					foreach ($transportProtocolMemoryUsageLogs as $transportProtocolMemoryUsageLogKey => $transportProtocolMemoryUsageLog) {
						$transportProtocolMemoryUsageLog = (intval(substr($transportProtocolMemoryUsageLog, strpos($transportProtocolMemoryUsageLog, 'mem ') + 4)) * $kernelPageSize) / 1000;
						$nodeResourceUsageLogData['memory_percentage_' . $nodeTransportProtocols[$transportProtocolMemoryUsageKey]][$nodeResourceUsageLogProcessSystemIntervalIndex][] = ceil(($transportProtocolMemoryUsage / $totalSystemMemory) * 100);
					}

					exec('df -m / | tail -1 | awk \'{print $4}\'  2>&1', $storageCapacityMegabytes);
					exec('df / | tail -1 | awk \'{print $5}\' 2>&1', $storagePercentage);
					$nodeResourceUsageLogData['storage_capacity_megabytes'] = current($storageCapacityMegabytes);
					$nodeResourceUsageLogData['storage_percentage'] = intval(current($storagePercentage));
				}

				$nodeResourceUsageLogProcessSystemIntervalIndex++;
				sleep(10);
			}

			$nodeResourceUsageLogPercentageKeys = array(
				'cpu_percentage_node',
				'cpu_percentage_node_process_http_proxy',
				'cpu_percentage_node_process_socks_proxy',
				'cpu_percentage_node_process_nameserver',
				'cpu_percentage_node_process_system',
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
