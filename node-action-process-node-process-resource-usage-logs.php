<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _calculateProcessorTime($processorTimeString) {
		$processorTime = 0;
		$processorTimeValues = explode('+', $processorTimeString);

		foreach ($processorTimeValues as $processorTimeValue) {
			$processorTime += substr($processorTimeValue, -15);
		}

		return $processorTime;
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

			if (empty($parameters['processorCapacityTime']['interval']) === true) {
				$nodeProcessResourceUsageLogProcessorTime = false;
				exec('sudo bash -c "sudo cat /proc/stat" | grep "cpu" 2>&1', $nodeProcessResourceUsageLogProcessorTime);
				end($nodeProcessResourceUsageLogProcessorTime);
				$nodeProcessResourceUsageLogProcessorTime = array_shift($nodeProcessResourceUsageLogProcessorTime);
				exec('echo ' . $nodeProcessResourceUsageLogProcessorTime . ' | awk \'{print ""$2"+"$3"+"$4"+"$5"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeProcessResourceUsageLogProcessorTime);
				$nodeProcessResourceUsageLogProcessorTime = current($nodeProcessResourceUsageLogProcessorTime);
				$parameters['processorCapacityTime'][] = array(
					'processorTime' => _calculateProcessorTime($nodeProcessResourceUsageLogProcessorTime),
					'timestamp' => microtime(true)
				);

				if (empty($parameters['processorCapacityTime'][1]) === false) {
					$parameters['processorCapacityTime'] = array(
						'interval' => $parameters['processorCapacityTime'][1]['timestamp'] - $parameters['processorCapacityTime'][0]['timestamp'],
						'processorTime' => $parameters['processorCapacityTime'][1]['processorTime'] - $parameters['processorCapacityTime'][0]['processorTime']
					);
				}
			} else {
				foreach ($parameters['nodeProcessResourceUsageLogProcessTypes'] as $nodeProcessResourceUsageLogProcessType) {
					$parameters['data'][$nodeProcessResourceUsageLogProcessType]['memoryPercentage'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']] = 0;
					$processProcessIdCommand = 'pgrep -f ' . $nodeProcessResourceUsageLogProcessType;
					$processProcessIds = false;
					exec($processProcessIdCommand, $processProcessIds);

					foreach ($processProcessIds as $processProcessId) {
						$nodeProcessResourceUsageLogProcessorTimeProcess = false;
						exec('sudo bash -c "sudo cat /proc/' . $processProcessId . '/stat" | awk \'{print ""$14"+"$15"+"$16"+"$17""}\' 2>&1', $nodeProcessResourceUsageLogProcessorTimeProcess);
						$nodeProcessResourceUsageLogProcessorTimeProcess = current($nodeProcessResourceUsageLogProcessorTimeProcess);

						$parameters[$nodeProcessResourceUsageLogProcessType]['processorTime'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']][$processProcessId] = array(
							'processorTime' => _calculateProcessorTime($nodeProcessResourceUsageLogProcessorTimeProcess),
							'timestamp' => microtime(true)
						);

						if (empty($parameters[$nodeProcessResourceUsageLogProcessType]['processorTime'][($parameters['nodeProcessResourceUsageLogProcessIntervalIndex'] - 1)][$processProcessId]) === false) {
							$parameters['data'][$nodeProcessResourceUsageLogProcessType]['processorPercentage'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']][$processProcessId] = array(
								'interval' => $parameters[$nodeProcessResourceUsageLogProcessType]['processorTime'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']][$processProcessId]['timestamp'] - $parameters[$nodeProcessResourceUsageLogProcessType]['processorTime'][($parameters['nodeProcessResourceUsageLogProcessIntervalIndex'] - 1)][$processProcessId]['timestamp'],
								'processorTime' => $parameters[$nodeProcessResourceUsageLogProcessType]['processorTime'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']][$processProcessId]['processorTime'] - $parameters[$nodeProcessResourceUsageLogProcessType]['processorTime'][($parameters['nodeProcessResourceUsageLogProcessIntervalIndex'] - 1)][$processProcessId]['processorTime']
							);
						}

						$nodeProcessResourceUsageLogProcessTypeProcessMemoryPercentage = 0;
						exec('ps -h -p ' . $processProcessId . ' -o %mem', $nodeProcessResourceUsageLogProcessTypeProcessMemoryPercentage);
						$parameters['data'][$nodeProcessResourceUsageLogProcessType]['memoryPercentage'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']] += current($nodeProcessResourceUsageLogProcessTypeProcessMemoryPercentage);
					}

					if (empty($parameters['data'][$nodeProcessResourceUsageLogProcessType]['processorPercentage'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']]) === false) {
						$nodeProcessResourceUsageLogProcessTypeProcessorPercentage = 0;

						foreach ($parameters['data'][$nodeProcessResourceUsageLogProcessType]['processorPercentage'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']] as $processProcessId => $nodeProcessResourceUsageLogProcessTypeProcessProcessorPercentage) {
							$parameters['data'][$nodeProcessResourceUsageLogProcessType]['processorPercentage'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']][$processProcessId]['processorTime'] += ($parameters['processorCapacityTime']['interval'] - $nodeProcessResourceUsageLogProcessTypeProcessProcessorPercentage['interval']) * ($nodeProcessResourceUsageLogProcessTypeProcessProcessorPercentage['processorTime'] / $nodeProcessResourceUsageLogProcessTypeProcessProcessorPercentage['interval']);
							$nodeProcessResourceUsageLogProcessTypeProcessorPercentage += ($parameters['data'][$nodeProcessResourceUsageLogProcessType]['processorPercentage'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']][$processProcessId]['processorTime'] / $parameters['processorCapacityTime']['interval']);
						}

						$parameters['data'][$nodeProcessResourceUsageLogProcessType]['processorPercentage'][$parameters['nodeProcessResourceUsageLogProcessIntervalIndex']] = $nodeProcessResourceUsageLogProcessTypeProcessorPercentage;
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
				'createdTimestamp' => strtotime($nodeProcessResourceUsageLogTimestamp),
				'memoryPercentage' => max($nodeProcessResourceUsageLogData['memoryPercentage']),
				'nodeProcessType' => $nodeProcessResourceUsageLogProcessType,
				'processorPercentage' => max($nodeProcessResourceUsageLogData['processorPercentage'])
			);
			unset($parameters['data'][$nodeProcessResourceUsageLogProcessType]);
		}

		$systemParameters = array(
			'action' => 'addNodeProcessResourceUsageLogs',
			'data' => $parameters['data'],
			'nodeAuthenticationToken' => $parameters['nodeAuthenticationToken']
		);
		$encodedSystemParameters = json_encode($systemParameters);

		if (empty($encodedSystemParameters) === false) {
			shell_exec('sudo ' . $parameters['binaryFiles']['wget'] . ' -O /usr/local/firewall-security-api/system-action-add-node-process-resource-usage-logs-response.json --connect-timeout=5 --dns-timeout=5 --no-dns-cache --post-data \'json=' . $encodedSystemParameters . '\' --read-timeout=60 --tries=1 ' . $parameters['systemEndpointDestination'] . '/system-endpoint.php');

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
