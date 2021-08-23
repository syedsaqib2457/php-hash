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
			$this->nodeResourceUsageLogIpVersions = array(
				4,
				6
			);
			$this->nodeResourceUsageLogProcessTypes = array(
				'http_proxy',
				'recursive_dns',
				'socks_proxy',
				'system'
			);
			$this->parameters = $parameters;
			exec('getconf PAGE_SIZE 2>&1', $kernelPageSize);
			$this->kernelPageSize = current($kernelPageSize);
		}

		protected function _calculateCpuTime($nodeResourceUsageLogCpuTimeString) {
			$nodeResourceUsageLogCpuTime = 0;
			$nodeResourceUsageLogCpuTimeValues = explode('+', $nodeResourceUsageLogCpuTimeString);

			foreach ($nodeResourceUsageLogCpuTimeValues as $nodeResourceUsageLogCpuTimeValue) {
				$nodeResourceUsageLogCpuTime += substr($nodeResourceUsageLogCpuTimeValue, -15);
			}

			return $nodeResourceUsageLogCpuTime;
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

			foreach ($processProcessIds as $processProcessId) {
				$nodeResourceUsageLogCpuTimeProcess = $nodeResourceUsageLogCpuTimeProcessStart = microtime(true);
				exec('sudo bash -c "sudo cat /proc/' . $processProcessId . '/stat" | awk \'{print ""$14"+"$15"+"$16"+"$17""}\' 2>&1', $nodeResourceUsageLogCpuTimeProcess);

				$this->nodeResourceUsageLogData['cpu_time_process_' . $processType][$this->nodeResourceUsageLogProcessIntervalIndex][$processProcessId] = array(
					'cpu_time' => $this->_calculateCpuTime(current($nodeResourceUsageLogCpuTimeProcess)),
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
					$this->nodeResourceUsageLogData['cpu_capacity_time'][] = array(
						'cpu_time' => $this->_calculateCpuTime(current($nodeResourceUsageLogCpuTime)),
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
					$this->nodeResourceUsageLogData['cpu_time'][$this->nodeResourceUsageLogProcessIntervalIndex] = array(
						'cpu_time' => $this->_calculateCpuTime(current($nodeResourceUsageLogCpuTime)),
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

					exec('df -m / | tail -1 | awk \'{print $4}\'  2>&1', $nodeResourceUsageLogStorageCapacityMegabytes);
					exec('df / | tail -1 | awk \'{print $5}\' 2>&1', $nodeResourceUsageLogStoragePercentage);
					$this->nodeResourceUsageLogData['storage_capacity_megabytes'] = current($nodeResourceUsageLogStorageCapacityMegabytes);
					$this->nodeResourceUsageLogData['storage_percentage'] = intval(current($nodeResourceUsageLogStoragePercentage));
				}

				$this->nodeResourceUsageLogProcessIntervalIndex++;
				sleep(mt_rand(4, 10));
			}

			$nodeResourceUsageLogCreated = substr(date('Y-m-d H:i', $nodeResourceUsageLogProcessStart), 0, 15) . '0:00';
			$nodeResourceUsageLogData = array(
				'node_process_resource_usage_logs' => array(),
				'node_resource_usage_logs' => array(
					'cpu_percentage' => max($this->nodeResourceUsageLogData['cpu_percentage']),
					'created' => $nodeResourceUsageLogCreated
				)
			);

			foreach ($this->nodeResourceUsageLogProcessTypes as $nodeResourceUsageLogProcessType) {
				$nodeResourceUsageLogData['node_process_resource_usage_logs'][$nodeResourceUsageLogProcessType] = array(
					'created' => $nodeResourceUsageLogCreated,
					'node_process_type' => $nodeResourceUsageLogProcessType
				);

				if (
					(isset($nodeResourceUsageLogData['node_process_resource_usage_logs']['cpu_percentage_process_' . $nodeResourceUsageLogProcessType]) === false) &&
					(isset($this->nodeResourceUsageLogData['cpu_percentage_process_' . $nodeResourceUsageLogProcessType]) === true)
				) {
					$nodeResourceUsageLogData['node_process_resource_usage_logs'][$nodeResourceUsageLogProcessType]['cpu_percentage'] = max($this->nodeResourceUsageLogData['cpu_percentage_process_' . $nodeResourceUsageLogProcessType]);
				}

				if (
					(isset($nodeResourceUsageLogData['node_process_resource_usage_logs']['memory_percentage_process_' . $nodeResourceUsageLogProcessType]) === false) &&
					(isset($this->nodeResourceUsageLogData['memory_percentage_process_' . $nodeResourceUsageLogProcessType]) === true)
				) {
					$nodeResourceUsageLogData['node_process_resource_usage_logs'][$nodeResourceUsageLogProcessType]['memory_percentage'] = max($this->nodeResourceUsageLogData['memory_percentage_process_' . $nodeResourceUsageLogProcessType]);
				}
			}

			$nodeResourceUsageLogFile = '/tmp/node_resource_usage_logs';

			if (
				(
					(file_exists($nodeResourceUsageLogFile) === false) ||
					(unlink($nodeResourceUsageLogFile) === true)
				) &&
				(file_put_contents($nodeResourceUsageLogFile, json_encode(nodeResourceUsageLogData)) === true)
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
