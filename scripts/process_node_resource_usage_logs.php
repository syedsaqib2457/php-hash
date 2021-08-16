<?php
	class ProcessNodeResourceUsageLogs {

		public $parameters;

		public function __construct($parameters) {
			$this->parameters = $parameters;
		}

		protected function _processProcessUsagePercentages($processType) {
			$this->nodeResourceUsageLogData['memory_percentage_process_' . $processType][$this->nodeResourceUsageLogProcessIntervalIndex] = 0;
			$processProcessIdCommand = 'pgrep ';

			if ($processType === 'system') {
				$processProcessIdCommand .= 'php';
			} else {
				$processProcessIdCommand .= $processType;
			}

			exec($processProcessIdCommand, $processProcessIds);
			$nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsageProcess = $this->nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsage;

			foreach ($processProcessIds as $processProcessId) {
				$nodeResourceUsageLogCpuTimeProcess = $nodeResourceUsageLogCpuTimeProcessStart = microtime();
				exec('bash -c "cat /proc/' . $processProcessId . '/stat" | awk \'{print ""$14"+"$15"+"$16"+"$17""}\' 2>&1', $nodeResourceUsageLogCpuTimeProcess);
				$nodeResourceUsageLogCpuTimeProcess = current($nodeResourceUsageLogCpuTimeProcess);
				$this->nodeResourceUsageLogData['cpu_time_process_' . $processType][$this->nodeResourceUsageLogProcessIntervalIndex][$processProcessId] = array(
					'cpu_time' => array_sum(explode('+', $nodeResourceUsageLogCpuTimeProcess)),
					'timestamp' => $nodeResourceUsageLogCpuTimeProcessStart
				);

				if (empty($this->nodeResourceUsageLogData['cpu_time_process_' . $processType][($this->nodeResourceUsageLogProcessIntervalIndex - 1)][$processProcessId]) === false) {
					$this->nodeResourceUsageLogData['cpu_percentage_process_' . $processType][$this->nodeResourceUsageLogProcessIntervalIndex][$processProcessId] = array(
						'cpu_time' => $this->nodeResourceUsageLogData['cpu_time_process_' . $processType][$this->nodeResourceUsageLogProcessIntervalIndex][$processProcessId]['cpu_time'] - $this->nodeResourceUsageLogData['cpu_time_process_' . $processType][($this->nodeResourceUsageLogProcessIntervalIndex - 1)][$processProcessId]['cpu_time'],
						'interval' => $this->nodeResourceUsageLogData['cpu_time_process_' . $processType][$this->nodeResourceUsageLogProcessIntervalIndex][$processProcessId]['timestamp'] - $this->nodeResourceUsageLogData['cpu_time_process_' . $processType][($this->nodeResourceUsageLogProcessIntervalIndex - 1)][$processProcessId]['timestamp']
					);
				}

				$nodeResourceUsageLogMemoryPercentageProcessProcess = 0;
				exec('ps -h -p ' . $processProcessId . ' -o %mem', $nodeResourceUsageLogMemoryPercentageProcessProcess);
				$this->nodeResourceUsageLogData['memory_percentage_process_' . $processType][$this->nodeResourceUsageLogProcessIntervalIndex] += current($nodeResourceUsageLogMemoryPercentageProcessProcess);

				foreach ($this->nodeResourceUsageLogIpVersionSocketUsageFiles as $nodeResourceUsageLogIpVersion => $nodeResourceUsageLogIpVersionSocketUsageFile) {
					$nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsageProcessProcess = 0;
					exec('bash -c "cat /proc/' . $processProcessId . '/net/' . $nodeResourceUsageLogIpVersionSocketUsageFile . '" | grep "P: "', $nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsageProcessProcess);

					foreach ($this->nodeResourceUsageLogTransportProtocols as $nodeResourceUsageLogTransportProtocolKey => $nodeResourceUsageLogTransportProtocol) {
						$nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsageProcess[$nodeResourceUsageLogIpVersion][$nodeResourceUsageLogTransportProtocol] += (intval(substr($nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsageProcessProcess[$nodeResourceUsageLogTransportProtocolKey], strpos($nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsageProcessProcess[$nodeResourceUsageLogTransportProtocolKey], 'mem ') + 4)) * $kernelPageSize) / 1000000;
					}
				}
			}

			foreach ($nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsageProcess as $nodeResourceUsageLogIpVersion => $nodeResourceUsageLogTransportProtocolSocketMemoryUsageProcess) {
				foreach ($this->nodeResourceUsageLogTransportProtocols as $nodeResourceUsageLogTransportProtocol) {
					$this->nodeResourceUsageLogData['memory_percentage_process_' . $processType . '_' . $nodeResourceUsageLogTransportProtocol . '_ip_version_' . $nodeResourceUsageLogIpVersion][$this->nodeResourceUsageLogProcessIntervalIndex] = ceil(($nodeResourceUsageLogTransportProtocolSocketMemoryUsageProcess[$nodeResourceUsageLogTransportProtocol] / $this->nodeResourceUsageLogData['memory_capacity_megabytes']) * 100);
				}
			}

			if (empty($this->nodeResourceUsageLogData['cpu_percentage_process_' . $processType][$this->nodeResourceUsageLogProcessIntervalIndex]) === false) {
				$nodeResourceUsageLogCpuPercentageProcess = 0;

				foreach ($nodeResourceUsageLogData['cpu_percentage_process_' . $processType][$this->nodeResourceUsageLogProcessIntervalIndex] as $processProcessId => $nodeResourceUsageLogCpuPercentageProcessProcess) {
					$this->nodeResourceUsageLogData['cpu_percentage_process_' . $processType][$this->nodeResourceUsageLogProcessIntervalIndex][$processProcessId]['cpu_time'] += ($this->nodeResourceUsageLogData['cpu_capacity_time']['interval'] - $nodeResourceUsageLogCpuPercentageProcessProcess['interval']) * ($nodeResourceUsageLogCpuPercentageProcessProcess['cpu_time'] / $nodeResourceUsageLogCpuPercentageProcessProcess['interval']);
					$nodeResourceUsageLogCpuPercentageProcess += ($this->nodeResourceUsageLogData['cpu_percentage_process_' . $processType][$this->nodeResourceUsageLogProcessIntervalIndex][$processProcessId]['cpu_time'] / $this->nodeResourceUsageLogData['cpu_capacity_time']['interval']);
				}

				$this->nodeResourceUsageLogData['cpu_percentage_process_' . $processType][$this->nodeResourceUsageLogProcessIntervalIndex] = $nodeResourceUsageLogCpuPercentageProcess;
			}

			return;
		}

		public function process() {
			$nodeResourceUsageLogProcessStart = time();
			exec('getconf PAGE_SIZE 2>&1', $kernelPageSize);
			exec('free -m | grep "Mem:" | grep -v free | awk \'{print $2"_"$3}\'', $nodeResourceUsageLogMemoryUsage);
			$kernelPageSize = current($kernelPageSize);
			$nodeResourceUsageLogMemoryUsage = explode('_', current($nodeResourceUsageLogMemoryUsage));
			$this->nodeResourceUsageLogData = array(
				'memory_capacity_megabytes' => $nodeResourceUsageLogMemoryUsage[0],
				'memory_percentage' => ceil($nodeResourceUsageLogMemoryUsage[1] / $nodeResourceUsageLogMemoryUsage[0])
			);
			$this->nodeResourceUsageLogIpVersionSocketUsageFiles = array(
				4 => 'sockstat',
				6 => 'sockstat6'
			);
			$this->nodeResourceUsageLogProcessTypes = array(
				'http_proxy',
				'nameserver',
				'socks_proxy',
				'system'
			);
			$this->nodeResourceUsageLogTransportProtocols = array(
				'tcp',
				'udp'
			);

			while (($nodeResourceUsageLogProcessStart + 540) > time()) {
				$this->nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsage = array(
					4 => array(
						'tcp' => 0,
						'udp' => 0
					),
					6 => array(
						'tcp' => 0,
						'udp' => 0
					)
				);

				if (empty($this->nodeResourceUsageLogProcessIntervalIndex) === true) {
					$this->nodeResourceUsageLogProcessIntervalIndex = 0;
				}

				$nodeResourceUsageLogCpuTime = $nodeResourceUsageLogCpuTimeStart = microtime();

				if (empty($nodeResourceUsageLogData['cpu_capacity_time']['interval']) === true) {
					exec('sudo cat /proc/stat | grep "cpu" 2>&1', $nodeResourceUsageLogCpuTime);
					end($nodeResourceUsageLogCpuTime);
					$this->nodeResourceUsageLogData['cpu_capacity_cores'] = key($nodeResourceUsageLogCpuTime);
					$nodeResourceUsageLogCpuTime = array_shift($nodeResourceUsageLogCpuTime);
					exec('echo ' . $nodeResourceUsageLogCpuTime . ' | awk \'{print ""$2"+"$3"+"$4"+"$5"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeResourceUsageLogCpuTime);
					$nodeResourceUsageLogCpuTime = current($nodeResourceUsageLogCpuTime);
					$this->nodeResourceUsageLogData['cpu_capacity_time'][] = array(
						'cpu_time' => array_sum(explode('+', $nodeResourceUsageLogCpuTime)),
						'timestamp' => $nodeResourceUsageLogCpuTimeStart
					);

					if (empty($this->nodeResourceUsageLogData['cpu_capacity_time'][1]) === false) {
						$this->nodeResourceUsageLogData['cpu_capacity_time'] = array(
							'cpu_time' => $this->nodeResourceUsageLogData['cpu_capacity_time'][1]['cpu_time'] - $this->nodeResourceUsageLogData['cpu_capacity_time'][0]['cpu_time'],
							'interval' => $this->nodeResourceUsageLogData['cpu_capacity_time'][1]['timestamp'] - $this->nodeResourceUsageLogData['cpu_capacity_time'][0]['timestamp']
						);
					}
				} else {
					exec('sudo cat /proc/stat | grep "cpu " | awk \'{print ""$2"+"$3"+"$4"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeResourceUsageLogCpuTime);
					$this->nodeResourceUsageLogData['cpu_time'][$this->nodeResourceUsageLogProcessIntervalIndex] = array(
						'cpu_time' => array_sum(explode('+', $nodeResourceUsageLogCpuTime)),
						'timestamp' => $nodeResourceUsageLogCpuTimeStart
					);

					if (empty($this->nodeResourceUsageLogData['cpu_time'][($this->nodeResourceUsageLogProcessIntervalIndex - 1)]) === false) {
						$this->nodeResourceUsageLogData['cpu_percentage'][$this->nodeResourceUsageLogProcessIntervalIndex] = array(
							'cpu_time' => $this->nodeResourceUsageLogData['cpu_time'][$this->nodeResourceUsageLogProcessIntervalIndex]['cpu_time'] - $this->nodeResourceUsageLogData['cpu_time'][($this->nodeResourceUsageLogProcessIntervalIndex - 1)]['cpu_time'],
							'interval' => $this->nodeResourceUsageLogData['cpu_time'][$this->nodeResourceUsageLogProcessIntervalIndex]['timestamp'] - $this->nodeResourceUsageLogData['cpu_time'][($this->nodeResourceUsageLogProcessIntervalIndex - 1)]['timestamp']
						);
					}

					if (empty($this->nodeResourceUsageLogData['cpu_percentage'][$this->nodeResourceUsageLogProcessIntervalIndex]) === false) {
						$nodeResourceUsageLogCpuPercentage = $this->nodeResourceUsageLogData['cpu_percentage'][$this->nodeResourceUsageLogProcessIntervalIndex];
						$this->nodeResourceUsageLogData['cpu_percentage'][$this->nodeResourceUsageLogProcessIntervalIndex] = ($nodeResourceUsageLogData['cpu_time'][$this->nodeResourceUsageLogProcessIntervalIndex]['cpu_time'] + ($nodeResourceUsageLogData['cpu_capacity_time']['interval'] - $nodeResourceUsageLogCpuPercentage['interval']) * ($nodeResourceUsageLogCpuPercentage['cpu_time'] / $nodeResourceUsageLogCpuPercentage['interval'])) / $nodeResourceUsageLogData['cpu_capacity_time']['interval'];
					}

					foreach ($this->nodeResourceUsageLogProcessTypes as $nodeResourceUsageLogProcessType) {
						$this->_processProcessUsagePercentages($nodeResourceUsageLogProcessType);
					}

					foreach ($this->nodeResourceUsageLogIpVersionSocketUsageFiles as $nodeResourceUsageLogIpVersion => $nodeResourceUsageLogIpVersionSocketUsageFile) {
						$nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsage = 0;
						exec('bash -c "cat /proc/net/' . $nodeResourceUsageLogIpVersionSocketUsageFile . '" | grep "P: " 2>&1', $nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsage);

						foreach ($this->nodeResourceUsageLogTransportProtocols as $nodeResourceUsageLogTransportProtocolKey => $nodeResourceUsageLogTransportProtocol) {
							$nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsage[$nodeResourceUsageLogTransportProtocolKey] = (intval(substr($nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsage[$nodeResourceUsageLogTransportProtocolKey], strpos($nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsage[$nodeResourceUsageLogTransportProtocolKey], 'mem ') + 4)) * $kernelPageSize) / 1000000;
							$this->nodeResourceUsageLogData['memory_percentage_' . $nodeResourceUsageLogTransportProtocol . '_ip_version_' . $nodeResourceUsageLogIpVersion][$this->nodeResourceUsageLogProcessIntervalIndex] = ceil(($nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsage[$nodeResourceUsageLogTransportProtocolKey] / $this->nodeResourceUsageLogData['memory_capacity_megabytes']) * 100);
						}
					}

					exec('df -m / | tail -1 | awk \'{print $4}\'  2>&1', $nodeResourceUsageLogStorageCapacityMegabytes);
					exec('df / | tail -1 | awk \'{print $5}\' 2>&1', $nodeResourceUsageLogStoragePercentage);
					$nodeResourceUsageLogData['storage_capacity_megabytes'] = current($nodeResourceUsageLogStorageCapacityMegabytes);
					$nodeResourceUsageLogData['storage_percentage'] = intval(current($nodeResourceUsageLogStoragePercentage));
				}

				$this->nodeResourceUsageLogProcessIntervalIndex++;
				sleep(10);
			}

			$nodeResourceUsageLogPercentageKeys = array(
				'cpu_percentage',
				'memory_percentage',
				'storage_percentage'
			);

			foreach ($this->nodeResourceUsageLogIpVersionSocketUsageFiles as $nodeResourceUsageLogIpVersion => $nodeResourceUsageLogIpVersionSocketUsageFile) {
				foreach ($this->nodeResourceUsageLogTransportProtocols as $nodeResourceUsageLogTransportProtocolKey => $nodeResourceUsageLogTransportProtocol) {
					foreach ($this->nodeResourceUsageLogProcessTypes as $nodeResourceUsageLogProcessTypes) {
						// todo: prepare data and database structure for node_process_resource_usage_logs and node_resource_usage_logs
					}
				}
			}

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
