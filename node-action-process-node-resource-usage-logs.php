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

	function _processNodeResourceUsageLogs($parameters, $response) {
		exec('sudo bash -c "sudo cat /proc/cpuinfo" | grep "cpu MHz" | awk \'{print $4\'} | head -1 2>&1', $nodeResourceUsageLogProcessorCapacityMegahertz);
		$nodeResourceUsageLogProcessorCapacityMegahertz = current($nodeResourceUsageLogProcessorCapacityMegahertz);
		exec('free -m | grep "Mem:" | grep -v free | awk \'{print $2"_"$3}\'', $nodeResourceUsageLogMemoryUsage);
		$nodeResourceUsageLogMemoryUsage = current($nodeResourceUsageLogMemoryUsage);
		$nodeResourceUsageLogMemoryUsage = explode('_', $nodeResourceUsageLogMemoryUsage);
		$parameters['data'] = array(
			'memoryCapacityMegabytes' => $nodeResourceUsageLogMemoryUsage[0],
			'memoryPercentage' => ceil($nodeResourceUsageLogMemoryUsage[1] / $nodeResourceUsageLogMemoryUsage[0]),
			'processorCapacityMegahertz' => ceil($nodeResourceUsageLogProcessorCapacityMegahertz)
		);
		$nodeResourceUsageLogTimestamp = time();

		while ((($nodeResourceUsageLogTimestamp + 540) > time()) === true) {
			if (empty($parameters['nodeResourceUsageLogProcessIntervalIndex']) === true) {
				$parameters['nodeResourceUsageLogProcessIntervalIndex'] = 0;
			}

			if (empty($parameters['processorCapacityTime']['interval']) === true) {
				$nodeResourceUsageLogProcessorTime = false;
				exec('sudo bash -c "sudo cat /proc/stat" | grep "cpu" 2>&1', $nodeResourceUsageLogProcessorTime);
				end($nodeResourceUsageLogProcessorTime);
				$parameters['data']['processorCapacityCores'] = key($nodeResourceUsageLogProcessorTime);
				$nodeResourceUsageLogProcessorTime = array_shift($nodeResourceUsageLogProcessorTime);
				exec('echo ' . $nodeResourceUsageLogProcessorTime . ' | awk \'{print ""$2"+"$3"+"$4"+"$5"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeResourceUsageLogProcessorTime);
				$nodeResourceUsageLogProcessorTime = current($nodeResourceUsageLogProcessorTime);
				$parameters['processorCapacityTime'][] = array(
					'processorTime' => _calculateProcessorTime($nodeResourceUsageLogProcessorTime),
					'timestamp' => microtime(true)
				);

				if (empty($parameters['processorCapacityTime'][1]) === false) {
					$parameters['processorCapacityTime'] = array(
						'interval' => $parameters['processorCapacityTime'][1]['timestamp'] - $parameters['processorCapacityTime'][0]['timestamp'],
						'processorTime' => $parameters['processorCapacityTime'][1]['processorTime'] - $parameters['processorCapacityTime'][0]['processorTime']
					);
				}
			} else {
				exec('sudo bash -c "sudo cat /proc/stat" | grep "cpu " | awk \'{print ""$2"+"$3"+"$4"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeResourceUsageLogProcessorTime);
				$nodeResourceUsageLogProcessorTime = current($nodeResourceUsageLogProcessorTime);
				$parameters['processorTime'][$parameters['nodeResourceUsageLogProcessIntervalIndex']] = array(
					'processorTime' => _calculateProcessorTime($nodeResourceUsageLogProcessorTime),
					'timestamp' => microtime(true)
				);

				if (empty($parameters['processorTime'][($parameters['nodeResourceUsageLogProcessIntervalIndex'] - 1)]) === false) {
					$parameters['data']['processorPercentage'][$parameters['nodeResourceUsageLogProcessIntervalIndex']] = array(
						'interval' => $parameters['processorTime'][$parameters['nodeResourceUsageLogProcessIntervalIndex']]['timestamp'] - $parameters['processorTime'][($parameters['nodeResourceUsageLogProcessIntervalIndex'] - 1)]['timestamp'],
						'processorTime' => $parameters['processorTime'][$parameters['nodeResourceUsageLogProcessIntervalIndex']]['processorTime'] - $parameters['processorTime'][($parameters['nodeResourceUsageLogProcessIntervalIndex'] - 1)]['processorTime']
					);
				}

				if (empty($parameters['data']['processorPercentage'][$parameters['nodeResourceUsageLogProcessIntervalIndex']]) === false) {
					$parameters['data']['processorPercentage'][$parameters['nodeResourceUsageLogProcessIntervalIndex']] = ($parameters['data']['processorPercentage'][$parameters['nodeResourceUsageLogProcessIntervalIndex']]['processorTime'] + ($parameters['processorCapacityTime']['interval'] - $parameters['data']['processorPercentage'][$parameters['nodeResourceUsageLogProcessIntervalIndex']]['interval']) * ($parameters['data']['processorPercentage'][$parameters['nodeResourceUsageLogProcessIntervalIndex']]['processorTime'] / $parameters['data']['processorPercentage'][$parameters['nodeResourceUsageLogProcessIntervalIndex']]['interval'])) / $parameters['processorCapacityTime']['interval'];
				}

				exec('df -m / | tail -1 | awk \'{print $4}\'  2>&1', $nodeResourceUsageLogStorageCapacityMegabytes);
				$parameters['data']['storageCapacityMegabytes'] = current($nodeResourceUsageLogStorageCapacityMegabytes);
				exec('df / | tail -1 | awk \'{print $5}\' 2>&1', $nodeResourceUsageLogStoragePercentage);
				$parameters['data']['storagePercentage'] = current($nodeResourceUsageLogStoragePercentage);
			}

			$parameters['nodeResourceUsageLogProcessIntervalIndex']++;
			sleep(10);
		}

		$nodeResourceUsageLogTimestamp = date('Y-m-d H:i', $nodeResourceUsageLogTimestamp);
		$nodeResourceUsageLogTimestamp = substr($nodeResourceUsageLogTimestamp, 0, 15) . '0:00';
		$parameters['data']['createdTimestamp'] = strtotime($nodeResourceUsageLogTimestamp);
		$parameters['data']['processorPercentage'] = max($parameters['data']['processorPercentage']);
		$systemParameters = array(
			'action' => 'addNodeResourceUsageLogs',
			'data' => $parameters['data'],
			'nodeAuthenticationToken' => $parameters['nodeAuthenticationToken']
		);
		$encodedSystemParameters = json_encode($systemParameters);

		if (empty($encodedSystemParameters) === false) {
			shell_exec('sudo ' . $parameters['binary_files']['wget'] . ' -O /usr/local/firewall-security-api/system-action-add-node-resource-usage-logs-response.json  --connect-timeout=5 --dns-timeout=5 --no-dns-cache --post-data \'json=' . $encodedSystemParameters . '\' --read-timeout=60 --tries=1 ' . $parameters['systemEndpointDestination'] . '/system-endpoint.php');

			if (file_exists('/usr/local/firewall-security-api/system-action-add-node-resource-usage-logs-response.json') === true) {
				$systemActionProcessNodeResourceUsageLogResponse = file_get_contents('/usr/local/firewall-security-api/system-action-add-node-resource-usage-logs-response.json');
				$systemActionProcessNodeResourceUsageLogResponse = json_decode($systemActionProcessNodeResourceUsageLogResponse, true);

				if (empty($systemActionProcessNodeResourceUsageLogResponse) === false) {
					$response = $systemActionProcessNodeResourceUsageLogResponse;
				}
			}
		}

		return $response;
	}

	if (($parameters['action'] === 'process-node-resource-usage-logs') === true) {
		$response = _processNodeResourceUsageLogs($parameters, $response);
	}
?>
