<?php
	class ProcessNodeResourceUsageLogs {

		public $parameters;

		public function __construct($parameters) {
			$this->parameters = $parameters;
		}

		protected function _fetchProcessUsagePercentages($processName) {
			$response = array();
			// ..
			return $response;
		}

		public function process() {
			$nodeResourceUsageLogProcessSystemStart = time();
			exec('getconf PAGE_SIZE 2>&1', $kernelPageSize);
			exec('free | grep -v free | awk \'NR==1{print $2}\'', $totalSystemMemory);
			$kernelPageSize = current($kernelPageSize);
			$totalSystemMemory = current($totalSystemMemory);
			$nodeResourceUsageLogData = array(
				'memory_capacity_megabytes' => ($totalSystemMemory / 1000)
			);

			while (($nodeResourceUsageLogProcessSystemStart + 540) > time()) {
				if (empty($nodeResourceUsageLogProcessSystemIntervalIndex) === true) {
					$nodeResourceUsageLogProcessSystemIntervalIndex = 0;
				}

				$nodeResourceUsageLogCpuTime = $nodeResourceUsageLogCpuTimeStart = microtime();

				if (empty($nodeResourceUsageLogData['cpu_capacity_time']['interval']) === true) {
					exec('sudo cat /proc/stat | grep "cpu" 2>&1', $nodeResourceUsageLogCpuTime);
					end($nodeResourceUsageLogCpuTime);
					$nodeResourceUsageLogData['cpu_capacity_cores'] = key($nodeResourceUsageLogCpuTime);
					$nodeResourceUsageLogCpuTime = array_shift($nodeResourceUsageLogCpuTime);
					exec('echo ' . $nodeResourceUsageLogCpuTime . ' | awk \'{print ""$2"+"$3"+"$4"+"$5"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeResourceUsageLogCpuTime);
					$nodeResourceUsageLogCpuTime = current($nodeResourceUsageLogCpuTime);
					$nodeResourceUsageLogData['cpu_capacity_time'][] = array(
						'cpu_time' => array_sum(explode('+', $nodeResourceUsageLogCpuTime)),
						'timestamp' => $nodeResourceUsageLogCpuTimeStart
					);

					if (empty($nodeResourceUsageLogData['cpu_capacity_time'][1]) === false) {
						$nodeResourceUsageLogData['cpu_capacity_time'] = array(
							'cpu_time' => $nodeResourceUsageLogData['cpu_capacity_time'][1]['cpu_time'] - $nodeResourceUsageLogData['cpu_capacity_time'][0]['cpu_time'],
							'interval' => $nodeResourceUsageLogData['cpu_capacity_time'][1]['timestamp'] - $nodeResourceUsageLogData['cpu_capacity_time'][0]['timestamp']
						);
					}
				} else {
					exec('sudo cat /proc/stat | grep "cpu " | awk \'{print ""$2"+"$3"+"$4"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeResourceUsageLogCpuTime);
					$nodeResourceUsageLogData['cpu_time'][$nodeResourceUsageLogProcessSystemIntervalIndex] = array(
						'cpu_time' => array_sum(explode('+', $nodeResourceUsageLogCpuTime)),
						'timestamp' => $nodeResourceUsageLogCpuTimeStart
					);

					if (empty($nodeResourceUsageLogData['cpu_time'][($nodeResourceUsageLogProcessSystemIntervalIndex - 1)]) === false) {
						$nodeResourceUsageLogData['cpu_percentage'][$nodeResourceUsageLogProcessSystemIntervalIndex] = array(
							'cpu_time' => $nodeResourceUsageLogData['cpu_time'][$nodeResourceUsageLogProcessSystemIntervalIndex]['cpu_time'] - $nodeResourceUsageLogData['cpu_time'][($nodeResourceUsageLogProcessSystemIntervalIndex - 1)]['cpu_time'],
							'interval' => $nodeResourceUsageLogData['cpu_time'][$nodeResourceUsageLogProcessSystemIntervalIndex]['timestamp'] - $nodeResourceUsageLogData['cpu_time'][($nodeResourceUsageLogProcessSystemIntervalIndex - 1)]['timestamp']
						);
					}

					if (empty($nodeResourceUsageLogData['cpu_percentage'][$nodeResourceUsageLogProcessingIntervalIndex]) === false) {
						$nodeResourceUsageLogCpuPercentage = $nodeResourceUsageLogData['cpu_percentage'][$nodeResourceUsageLogProcessSystemIntervalIndex];
						$nodeResourceUsageLogData['cpu_percentage'][$nodeResourceUsageLogProcessSystemIntervalIndex] = ($nodeResourceUsageLogData['cpu_time'][$nodeResourceUsageLogProcessSystemIntervalIndex]['cpu_time'] + ($nodeResourceUsageLogData['cpu_capacity_time']['interval'] - $nodeResourceUsageLogCpuPercentage['interval']) * ($nodeResourceUsageLogCpuPercentage['cpu_time'] / $nodeResourceUsageLogCpuPercentage['interval'])) / $nodeResourceUsageLogData['cpu_capacity_time']['interval'];
					}

					$nodeIpVersionSocketMemory = array(
						4 => array(
							'tcp' => 0,
							'udp' => 0
						),
						6 => array(
							'tcp' => 0,
							'udp' => 0
						)
					);
					$nodeIpVersionSocketUsageLogFiles = array(
						4 => 'sockstat',
						6 => 'sockstat6'
					);
					$nodeTransportProtocols = array(
						'tcp',
						'udp'
					);

					todo: use _fetchProcessUsagePercentages() for process cpu and memory usage percentages

					// todo: http_proxy process IDs
					$nodeIpVersionSocketMemoryProcessHttpProxy = $nodeIpVersionSocketMemory;
					// todo: socks_proxy process IDs
					$nodeIpVersionSocketMemoryProcessSocksProxy = $nodeIpVersionSocketMemory;
					// todo: recursive_dns process IDs
					$nodeIpVersionSocketMemoryProcessRecursiveDns = $nodeIpVersionSocketMemory;
					// todo: verify /proc/[pid]/net/sockstat works for single processes
					$nodeResourceUsageLogCpuTimeProcessSystem = $nodeProcessSystemProcessIds = 0;
					exec('pgrep php', $systemProcessProcessIds);
					$nodeIpVersionSocketMemoryProcessSystem = $nodeIpVersionSocketMemory;

					foreach ($systemProcessProcessIds as $systemProcessProcessId) {
						$nodeResourceUsageLogCpuTimeProcessSystemProcess = $nodeResourceUsageLogCpuTimeProcessSystemProcessStart = microtime();
						exec('bash -c "cat /proc/' . $systemProcessProcessId . '/stat" | awk \'{print ""$14"+"$15"+"$16"+"$17""}\' 2>&1', $nodeResourceUsageLogCpuTimeProcessSystemProcess);
						$nodeResourceUsageLogCpuTimeProcessSystemProcess = current($nodeResourceUsageLogCpuTimeProcessSystemProcess);
						$nodeResourceUsageLogData['cpu_time_process_system'][$nodeResourceUsageLogProcessSystemIntervalIndex][$systemProcessProcessId] = array(
							'cpu_time' => array_sum(explode('+', $nodeResourceUsageLogCpuTimeProcessSystemProcess)),
							'timestamp' => $nodeResourceUsageLogCpuTimeProcessSystemProcessStart
						);

						if (empty($nodeResourceUsageLogData['cpu_time_process_system'][($nodeResourceUsageLogProcessSystemIntervalIndex - 1)][$systemProcessProcessId]) === false) {
							$nodeResourceUsageLogData['cpu_percentage_process_system'][$nodeResourceUsageLogProcessSystemIntervalIndex][$systemProcessProcessId] = array(
								'cpu_time' => $nodeResourceUsageLogData['cpu_time_process_system'][$nodeResourceUsageLogProcessSystemIntervalIndex][$systemProcessProcessId]['cpu_time'] - $nodeResourceUsageLogData['cpu_time_processing'][($nodeResourceUsageLogProcessSystemIntervalIndex - 1)][$systemProcessProcessId]['cpu_time'],
								'interval' => $nodeResourceUsageLogData['cpu_time_process_system'][$nodeResourceUsageLogProcessSystemIntervalIndex][$systemProcessProcessId]['timestamp'] - $nodeResourceUsageLogData['cpu_time_process_system'][($nodeResourceUsageLogProcessSystemIntervalIndex - 1)][$systemProcessProcessId]['timestamp']
							);
						}

						// todo: use ps -p [pid] -o %mem for system process total memory usage

						foreach ($nodeIpVersionSocketUsageLogFiles as $nodeIpVersion => $nodeIpVersionSocketUsageLogFile) {
							$nodeResourceUsageLogMemoryUsageProcessSystem = 0;
							exec('bash -c "cat /proc/' . $systemProcessProcessId . '/net/' . $nodeIpVersionSocketUsageLogFile . '" | grep "P: "', $nodeResourceUsageLogMemoryUsageProcessSystem);

							foreach ($nodeTransportProtocols as $nodeTransportProtocolKey => $nodeTransportProtocol) {
								$nodeIpVersionSocketMemoryProcessSystem[$nodeIpVersion][$nodeTransportProtocol] += (intval(substr($nodeResourceUsageLogMemoryUsageProcessSystem[$nodeTransportProtocolKey], strpos($nodeResourceUsageLogMemoryUsageProcessSystem[$nodeTransportProtocolKey], 'mem ') + 4)) * $kernelPageSize) / 1000;
							}
						}
					}

					foreach ($nodeIpVersionSocketMemoryProcessSystem as $nodeIpVersion => $nodeIpVersionSocketMemoryUsageLogs) {
						foreach ($nodeIpVersionSocketMemoryUsageLogs as $nodeTransportProtocol => $nodeIpVersionSocketMemoryUsageLog) {
							$nodeResourceUsageLogData['memory_percentage_process_system_' . $nodeTransportProtocol . '_ip_version_' . $nodeIpVersion][$nodeResourceUsageLogProcessSystemIntervalIndex] = ceil(($nodeIpVersionSocketMemoryUsageLog / $totalSystemMemory) * 100);
						}
					}

					if (empty($nodeResourceUsageLogData['cpu_percentage_process_system'][$nodeResourceUsageLogProcessingIntervalIndex]) === false) {
						$nodeResourceUsageLogCpuPercentageProcessSystem = 0;

						foreach ($nodeResourceUsageLogData['cpu_percentage_process_system'][$nodeResourceUsageLogProcessSystemIntervalIndex] as $systemProcessProcessId => $nodeResourceUsageLogCpuPercentageProcessSystemProcess) {
							$nodeResourceUsageLogData['cpu_percentage_process_system'][$nodeResourceUsageLogProcessSystemIntervalIndex][$systemProcessProcessId]['cpu_time'] += ($nodeResourceUsageLogData['cpu_capacity_time']['interval'] - $nodeResourceUsageLogCpuPercentageProcessSystemProcess['interval']) * ($nodeResourceUsageLogCpuPercentageProcessSystemProcess['cpu_time'] / $nodeResourceUsageLogCpuPercentageProcessSystemProcess['interval']);
							$nodeResourceUsageLogCpuPercentageProcessSystem += ($nodeResourceUsageLogData['cpu_percentage_process_system'][$nodeResourceUsageLogProcessSystemIntervalIndex][$systemProcessProcessId]['cpu_time'] / $nodeResourceUsageLogData['cpu_capacity_time']['interval']);
						}

						$nodeResourceUsageLogData['cpu_percentage_process_system'][$nodeResourceUsageLogProcessSystemIntervalIndex] = $nodeResourceUsageLogCpuPercentageProcessSystem;
					}

					// todo: use /proc/meminfo for total memory usage

					foreach ($nodeIpVersionSocketUsageLogFiles as $nodeIpVersion => $nodeIpVersionSocketUsageLogFile) {
						$nodeResourceUsageLogMemoryUsage = 0;
						exec('bash -c "cat /proc/net/' . $nodeIpVersionSocketUsageLogFile . '" | grep "P: " 2>&1', $nodeResourceUsageLogMemoryUsage);

						foreach ($nodeTransportProtocols as $nodeTransportProtocolKey => $nodeTransportProtocol) {
							$nodeResourceUsageLogMemoryUsage = (intval(substr($nodeResourceUsageLogMemoryUsage[$nodeTransportProtocolKey], strpos($nodeResourceUsageLogMemoryUsage[$nodeTransportProtocolKey], 'mem ') + 4)) * $kernelPageSize) / 1000;
							$nodeResourceUsageLogData['memory_percentage_' . $nodeTransportProtocol . '_ip_version_' . $nodeIpVersion][$nodeResourceUsageLogProcessSystemIntervalIndex] = ceil(($nodeResourceUsageLogMemoryUsage / $totalSystemMemory) * 100);
						}
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
				'cpu_percentage',
				'cpu_percentage_process_http_proxy',
				'cpu_percentage_process_socks_proxy',
				'cpu_percentage_process_nameserver',
				'cpu_percentage_process_system',
				// ..
				'memory_percentage_process_system_tcp_ip_version_4',
				'memory_percentage_process_system_tcp_ip_version_6',
				'memory_percentage_process_system_udp_ip_version_4',
				'memory_percentage_process_system_udp_ip_version_6',
				'memory_percentage_tcp_ip_version_4',
				'memory_percentage_tcp_ip_version_6',
				'memory_percentage_udp_ip_version_4',
				'memory_percentage_udp_ip_version_6',
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
