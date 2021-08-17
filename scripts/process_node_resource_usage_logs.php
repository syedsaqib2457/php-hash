<?php
	class ProcessNodeResourceUsageLogs {

		public $parameters;

		public function __construct($parameters) {
			exec('sudo bash -c "sudo cat /proc/cpuinfo" | grep "cpu MHz" | awk \'{print $4\'} | head -1 2>&1', $nodeResourceUsageLogCpuCapacityMegahertz);
			exec('free -m | grep "Mem:" | grep -v free | awk \'{print $2"_"$3}\'', $nodeResourceUsageLogMemoryUsage);
			$nodeResourceUsageLogMemoryUsage = explode('_', current($nodeResourceUsageLogMemoryUsage));
			$this->nodeResourceUsageLogData = array(
				'cpu_capacity_megahertz' => ceil(current($nodeResourceUsageLogCpuCapacityMegahertz)),
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
			$this->parameters = $parameters;
			exec('getconf PAGE_SIZE 2>&1', $kernelPageSize);
			$this->kernelPageSize = current($kernelPageSize);
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
				$nodeResourceUsageLogCpuTimeProcess = $nodeResourceUsageLogCpuTimeProcessStart = microtime(true);
				exec('sudo bash -c "sudo cat /proc/' . $processProcessId . '/stat" | awk \'{print ""$14"+"$15"+"$16"+"$17""}\' 2>&1', $nodeResourceUsageLogCpuTimeProcess);
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
					exec('sudo bash -c "sudo cat /proc/' . $processProcessId . '/net/' . $nodeResourceUsageLogIpVersionSocketUsageFile . '" | grep ": inuse" | head -2', $nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsageProcessProcess);

					foreach ($this->nodeResourceUsageLogTransportProtocols as $nodeResourceUsageLogTransportProtocolKey => $nodeResourceUsageLogTransportProtocol) {
						if (strpos($nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsageProcessProcess[$nodeResourceUsageLogTransportProtocolKey], 'mem ') !== false) {
							$nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsageProcess[$nodeResourceUsageLogIpVersion][$nodeResourceUsageLogTransportProtocol] += (intval(substr($nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsageProcessProcess[$nodeResourceUsageLogTransportProtocolKey], strpos($nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsageProcessProcess[$nodeResourceUsageLogTransportProtocolKey], 'mem ') + 4)) * $this->kernelPageSize) / 1000000;
						}
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

				foreach ($this->nodeResourceUsageLogData['cpu_percentage_process_' . $processType][$this->nodeResourceUsageLogProcessIntervalIndex] as $processProcessId => $nodeResourceUsageLogCpuPercentageProcessProcess) {
					$this->nodeResourceUsageLogData['cpu_percentage_process_' . $processType][$this->nodeResourceUsageLogProcessIntervalIndex][$processProcessId]['cpu_time'] += ($this->nodeResourceUsageLogData['cpu_capacity_time']['interval'] - $nodeResourceUsageLogCpuPercentageProcessProcess['interval']) * ($nodeResourceUsageLogCpuPercentageProcessProcess['cpu_time'] / $nodeResourceUsageLogCpuPercentageProcessProcess['interval']);
					$nodeResourceUsageLogCpuPercentageProcess += ($this->nodeResourceUsageLogData['cpu_percentage_process_' . $processType][$this->nodeResourceUsageLogProcessIntervalIndex][$processProcessId]['cpu_time'] / $this->nodeResourceUsageLogData['cpu_capacity_time']['interval']);
				}

				$this->nodeResourceUsageLogData['cpu_percentage_process_' . $processType][$this->nodeResourceUsageLogProcessIntervalIndex] = $nodeResourceUsageLogCpuPercentageProcess;
			}

			return;
		}

		public function process() {
			$nodeResourceUsageLogProcessStart = time();

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

				$nodeResourceUsageLogCpuTime = $nodeResourceUsageLogCpuTimeStart = microtime(true);

				if (empty($this->nodeResourceUsageLogData['cpu_capacity_time']['interval']) === true) {
					exec('sudo bash -c "sudo cat /proc/stat" | grep "cpu" 2>&1', $nodeResourceUsageLogCpuTime);
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
					exec('sudo bash -c "sudo cat /proc/stat" | grep "cpu " | awk \'{print ""$2"+"$3"+"$4"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeResourceUsageLogCpuTime);
					$nodeResourceUsageLogCpuTime = current($nodeResourceUsageLogCpuTime);
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
						$this->nodeResourceUsageLogData['cpu_percentage'][$this->nodeResourceUsageLogProcessIntervalIndex] = ($this->nodeResourceUsageLogData['cpu_percentage'][$this->nodeResourceUsageLogProcessIntervalIndex]['cpu_time'] + ($this->nodeResourceUsageLogData['cpu_capacity_time']['interval'] - $this->nodeResourceUsageLogData['cpu_percentage'][$this->nodeResourceUsageLogProcessIntervalIndex]['interval']) * ($this->nodeResourceUsageLogData['cpu_percentage'][$this->nodeResourceUsageLogProcessIntervalIndex]['cpu_time'] / $this->nodeResourceUsageLogData['cpu_percentage'][$this->nodeResourceUsageLogProcessIntervalIndex]['interval'])) / $this->nodeResourceUsageLogData['cpu_capacity_time']['interval'];
					}

					foreach ($this->nodeResourceUsageLogProcessTypes as $nodeResourceUsageLogProcessType) {
						$this->_processProcessUsagePercentages($nodeResourceUsageLogProcessType);
					}

					foreach ($this->nodeResourceUsageLogIpVersionSocketUsageFiles as $nodeResourceUsageLogIpVersion => $nodeResourceUsageLogIpVersionSocketUsageFile) {
						$nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsage = 0;
						exec('bash -c "cat /proc/net/' . $nodeResourceUsageLogIpVersionSocketUsageFile . '" | grep ": inuse" | head -2 2>&1', $nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsage);

						foreach ($this->nodeResourceUsageLogTransportProtocols as $nodeResourceUsageLogTransportProtocolKey => $nodeResourceUsageLogTransportProtocol) {
							$this->nodeResourceUsageLogData['memory_percentage_' . $nodeResourceUsageLogTransportProtocol . '_ip_version_' . $nodeResourceUsageLogIpVersion][$this->nodeResourceUsageLogProcessIntervalIndex] = 0;

							if (strpos($nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsage[$nodeResourceUsageLogTransportProtocolKey], 'mem ') === false) {
								$nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsage[$nodeResourceUsageLogTransportProtocolKey] = (intval(substr($nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsage[$nodeResourceUsageLogTransportProtocolKey], strpos($nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsage[$nodeResourceUsageLogTransportProtocolKey], 'mem ') + 4)) * $this->kernelPageSize) / 1000000;
								$this->nodeResourceUsageLogData['memory_percentage_' . $nodeResourceUsageLogTransportProtocol . '_ip_version_' . $nodeResourceUsageLogIpVersion][$this->nodeResourceUsageLogProcessIntervalIndex] = ceil(($nodeResourceUsageLogIpVersionTransportProtocolSocketMemoryUsage[$nodeResourceUsageLogTransportProtocolKey] / $this->nodeResourceUsageLogData['memory_capacity_megabytes']) * 100);
							}
						}
					}

					exec('df -m / | tail -1 | awk \'{print $4}\'  2>&1', $nodeResourceUsageLogStorageCapacityMegabytes);
					exec('df / | tail -1 | awk \'{print $5}\' 2>&1', $nodeResourceUsageLogStoragePercentage);
					$this->nodeResourceUsageLogData['storage_capacity_megabytes'] = current($nodeResourceUsageLogStorageCapacityMegabytes);
					$this->nodeResourceUsageLogData['storage_percentage'] = intval(current($nodeResourceUsageLogStoragePercentage));
				}

				$this->nodeResourceUsageLogProcessIntervalIndex++;
				sleep(mt_rand(4, 10));
			}

			$nodeResourceUsageLogPercentageKeys = array(
				'cpu_percentage'
			);

			foreach ($this->nodeResourceUsageLogIpVersionSocketUsageFiles as $nodeResourceUsageLogIpVersion => $nodeResourceUsageLogIpVersionSocketUsageFile) {
				foreach ($this->nodeResourceUsageLogTransportProtocols as $nodeResourceUsageLogTransportProtocolKey => $nodeResourceUsageLogTransportProtocol) {
					$nodeResourceUsageLogPercentageKeys[] = 'memory_percentage_' . $nodeResourceUsageLogTransportProtocol . '_ip_version_' . $nodeResourceUsageLogIpVersion;

					foreach ($this->nodeResourceUsageLogProcessTypes as $nodeResourceUsageLogProcessType) {
						$nodeResourceUsageLogPercentageKeys['cpu_percentage_process_' . $nodeResourceUsageLogProcessType] = 'cpu_percentage_process_' . $nodeResourceUsageLogProcessType;
						$nodeResourceUsageLogPercentageKeys['memory_percentage_process_' . $nodeResourceUsageLogProcessType] = 'memory_percentage_process_' . $nodeResourceUsageLogProcessType;
						$nodeResourceUsageLogPercentageKeys[] = 'memory_percentage_process_' . $nodeResourceUsageLogProcessType . '_' . $nodeResourceUsageLogTransportProtocol . '_ip_version_' . $nodeResourceUsageLogIpVersion;
					}
				}
			}

			foreach ($nodeResourceUsageLogPercentageKeys as $nodeResourceUsageLogPercentageKey) {
				if (empty($this->nodeResourceUsageLogData[$nodeResourceUsageLogPercentageKey]) === false) {
					rsort($this->nodeResourceUsageLogData[$nodeResourceUsageLogPercentageKey]);
					$this->nodeResourceUsageLogData[$nodeResourceUsageLogPercentageKey] = current($this->nodeResourceUsageLogData[$nodeResourceUsageLogPercentageKey]);
				}
			}

			unset($this->nodeResourceUsageLogData['cpu_capacity_time']);
			unset($this->nodeResourceUsageLogData['cpu_time']);
			unset($this->nodeResourceUsageLogData['cpu_time_process_system']);
			$nodeResourceUsageLogFile = '/tmp/node_resource_usage_logs';

			if (
				(
					file_exists($nodeResourceUsageLogFile) === false ||
					unlink($nodeResourceUsageLogFile) === true
				) &&
				(file_put_contents($nodeResourceUsageLogFile, json_encode($this->nodeResourceUsageLogData)) === true)
			) {
				exec('sudo curl -s --form "data=@' . $nodeResourceUsageLogFile . '" --form-string "json={\"action\":\"archive\"}" ' . $this->parameters['system_url'] . '/endpoint/node-resource-usage-logs 2>&1', $response);
				$response = json_decode(current($response), true);
				// todo: store interval data if system_url fails and retry
			}
		}

	}

	$parameters = array(
		'system_url' => '127.0.0.1'
	);
	$processNodeResourceUsageLogs = new ProcessNodeResourceUsageLogs($parameters);
	$processNodeResourceUsageLogs->process();
?>
