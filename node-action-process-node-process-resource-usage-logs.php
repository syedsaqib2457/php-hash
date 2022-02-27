<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _calculateCpuTime($cpuTimeString) {
		$cpuTime = 0;
		$cpuTimeValues = explode('+', $cpuTimeString);

		foreach ($cpuTimeValues as $cpuTimeValue) {
			$cpuTime += substr($cpuTimeValue, -15);
		}

		return $cpuTime;
	}

	function _processNodeProcessResourceUsageLogs($parameters, $response) {
		$parameters['nodeProcessResourceUsageLogProcessTypes'] = array(
			'httpProxy',
			'recursiveDns',
			'socksProxy'
		);
		$nodeProcessResourceUsageLogTimestamp = time();

		while ((($nodeProcessResourceUsageLogTimestamp + 540) > time()) === true) {
			if (empty($parameters['nodeProcessResourceUsageLogProcessIntervalIndex']) === true) {
				$parameters['nodeProcessResourceUsageLogProcessIntervalIndex'] = 0;
			}

			if (empty($parameters['cpuCapacityTime']['interval']) === true) {
				$nodeProcessResourceUsageLogCpuTime = false;
				exec('sudo bash -c "sudo cat /proc/stat" | grep "cpu" 2>&1', $nodeProcessResourceUsageLogCpuTime);
				end($nodeProcessResourceUsageLogCpuTime);
				$nodeProcessResourceUsageLogCpuTime = array_shift($nodeProcessResourceUsageLogCpuTime);
				exec('echo ' . $nodeProcessResourceUsageLogCpuTime . ' | awk \'{print ""$2"+"$3"+"$4"+"$5"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeProcessResourceUsageLogCpuTime);
				$nodeProcessResourceUsageLogCpuTime = current($nodeProcessResourceUsageLogCpuTime);
				$parameters['cpuCapacityTime'][] = array(
					'cpuTime' => _calculateCpuTime($nodeProcessResourceUsageLogCpuTime),
					'timestamp' => microtime(true)
				);

				if (empty($parameters['cpuCapacityTime'][1]) === false) {
					$parameters['cpuCapacityTime'] = array(
						'cpuTime' => $parameters['cpuCapacityTime'][1]['cpuTime'] - $parameters['cpuCapacityTime'][0]['cpuTime'],
						'interval' => $parameters['cpuCapacityTime'][1]['timestamp'] - $parameters['cpuCapacityTime'][0]['timestamp']
					);
				}
			} else {
				foreach ($parameters['nodeProcessResourceUsageLogProcessTypes'] as $nodeProcessResourceUsageLogProcessType) {
					$parameters['data'][$nodeProcessResourceUsageLogProcessType]['memoryPercentage'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']] = 0;
					$processProcessIdCommand = 'pgrep -f ' . $nodeProcessResourceUsageLogProcessType;
					$processProcessIds = false;
					exec($processProcessIdCommand, $processProcessIds);

					foreach ($processProcessIds as $processProcessId) {
						$nodeProcessResourceUsageLogCpuTimeProcess = false;
						exec('sudo bash -c "sudo cat /proc/' . $processProcessId . '/stat" | awk \'{print ""$14"+"$15"+"$16"+"$17""}\' 2>&1', $nodeProcessResourceUsageLogCpuTimeProcess);
						$nodeProcessResourceUsageLogCpuTimeProcess = current($nodeProcessResourceUsageLogCpuTimeProcess);

						$parameters[$nodeProcessResourceUsageLogProcessType]['cpuTime'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']][$processProcessId] = array(
							'cpuTime' => _calculateCpuTime($nodeProcessResourceUsageLogCpuTimeProcess),
							'timestamp' => microtime(true)
						);

						if (empty($parameters[$nodeProcessResourceUsageLogProcessType]['cpuTime'][($parameters['nodeProcessResourceUsageLogProcessIntervalIndex'] - 1)][$processProcessId]) === false) {
							$parameters['data'][$nodeProcessResourceUsageLogProcessType]['cpuPercentage'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']][$processProcessId] = array(
								'cpuTime' => $parameters[$nodeProcessResourceUsageLogProcessType]['cpuTime'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']][$processProcessId]['cpuTime'] - $parameters[$nodeProcessResourceUsageLogProcessType]['cpuTime'][($parameters['nodeProcessResourceUsageLogProcessIntervalIndex'] - 1)][$processProcessId]['cpuTime'],
								'interval' => $parameters[$nodeProcessResourceUsageLogProcessType]['cpuTime'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']][$processProcessId]['timestamp'] - $parameters[$nodeProcessResourceUsageLogProcessType]['cpuTime'][($parameters['nodeProcessResourceUsageLogProcessIntervalIndex'] - 1)][$processProcessId]['timestamp']
							);
						}

						$nodeProcessResourceUsageLogProcessTypeProcessMemoryPercentage = 0;
						exec('ps -h -p ' . $processProcessId . ' -o %mem', $nodeProcessResourceUsageLogProcessTypeProcessMemoryPercentage);
						$parameters['data'][$nodeProcessResourceUsageLogProcessType]['memoryPercentage'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']] += current($nodeProcessResourceUsageLogProcessTypeProcessMemoryPercentage);
					}

					if (empty($parameters['data'][$nodeProcessResourceUsageLogProcessType]['cpuPercentage'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']]) === false) {
						$nodeProcessResourceUsageLogProcessTypeCpuPercentage = 0;

						foreach ($parameters['data'][$nodeProcessResourceUsageLogProcessType]['cpuPercentage'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']] as $processProcessId => $nodeProcessResourceUsageLogProcessTypeProcessCpuPercentage) {
							$parameters['data'][$nodeProcessResourceUsageLogProcessType]['cpuPercentage'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']][$processProcessId]['cpuTime'] += ($parameters['cpuCapacityTime']['interval'] - $nodeProcessResourceUsageLogProcessTypeProcessCpuPercentage['interval']) * ($nodeProcessResourceUsageLogProcessTypeProcessCpuPercentage['cpuTime'] / $nodeProcessResourceUsageLogProcessTypeProcessCpuPercentage['interval']);
							$nodeProcessResourceUsageLogProcessTypeCpuPercentage += ($parameters['data'][$nodeProcessResourceUsageLogProcessType]['cpuPercentage'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']][$processProcessId]['cpuTime'] / $parameters['cpuCapacityTime']['interval']);
						}

						$parameters['data'][$nodeProcessResourceUsageLogProcessType]['cpuPercentage'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']] = $nodeProcessResourceUsageLogProcessTypeCpuPercentage;
					}
				}
			}

			$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']++;
			sleep(10);
		}

		$nodeProcessResourceUsageLogTimestamp = date('Y-m-d H:i', $nodeProcessResourceUsageLogTimestamp);
		$nodeProcessResourceUsageLogTimestamp = substr($nodeProcessResourceUsageLogTimestamp, 0, 15) . '0:00';

		foreach ($parameters['data'] as $nodeProcessResourceUsageLogProcessType => $nodeProcessResourceUsageLogData) {
			$parameters['data'][] = array(
				'cpuPercentage' => max($nodeProcessResourceUsageLogData['cpuPercentage']),
				'createdTimestamp' => strtotime($nodeProcessResourceUsageLogTimestamp),
				'memoryPercentage' => max($nodeProcessResourceUsageLogData['memoryPercentage']),
				'nodeProcessType' => $nodeProcessResourceUsageLogProcessType
			);
			unset($parameters['data'][$nodeProcessResourceUsageLogProcessType]);
		}

		$systemParameters = array(
			'action' => 'add-node-process-resource-usage-logs',
			'data' => $parameters['data'],
			'nodeAuthenticationToken' => $parameters['nodeAuthenticationToken']
		);
		$encodedSystemParameters = json_encode($systemParameters);

		if (empty($encodedSystemParameters) === false) {
			shell_exec('sudo ' . $parameters['binaryFiles']['wget'] . ' -O /usr/local/firewall-security-api/system-action-add-node-process-resource-usage-logs-response.json --no-dns-cache --post-data \'json=' . $encodedSystemParameters . '\' --timeout=10 ' . $parameters['systemEndpointDestinationAddress'] . '/system-endpoint.php');

			if (file_exists('/usr/local/firewall-security-api/system-action-add-node-process-resource-usage-logs-response.json') === true) {
				$systemActionProcessNodeProcessResourceUsageLogResponse = file_get_contents('/usr/local/firewall-security-api/system-action-add-node-process-resource-usage-logs-response.json');
				$systemActionProcessNodeProcessResourceUsageLogResponse = json_decode($systemActionProcessNodeProcessResourceUsageLogResponse, true);

				if (empty($systemActionProcessNodeProcessResourceUsageLogResponse) === false) {
					$response = $systemActionProcessNodeProcessResourceUsageLogResponse;
				}
			}
		}

		return $response;
	}

	if (($parameters['action'] === 'process-node-process-resource-usage-logs') === true) {
		$response = _processNodeProcessResourceUsageLogs($parameters, $response);
	}
?>
