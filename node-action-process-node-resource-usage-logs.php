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

	function _processNodeResourceUsageLogs($parameters, $response) {
		exec('sudo bash -c "sudo cat /proc/cpuinfo" | grep "cpu MHz" | awk \'{print $4\'} | head -1 2>&1', $nodeResourceUsageLogCpuCapacityMegahertz);
		$nodeResourceUsageLogCpuCapacityMegahertz = current($nodeResourceUsageLogCpuCapacityMegahertz);
		exec('free -m | grep "Mem:" | grep -v free | awk \'{print $2"_"$3}\'', $nodeResourceUsageLogMemoryUsage);
		$nodeResourceUsageLogMemoryUsage = current($nodeResourceUsageLogMemoryUsage);
		$nodeResourceUsageLogMemoryUsage = explode('_', $nodeResourceUsageLogMemoryUsage);
		$parameters['data'] = array(
			'cpuCapacityMegahertz' => ceil($nodeResourceUsageLogCpuCapacityMegahertz),
			'memoryCapacityMegabytes' => $nodeResourceUsageLogMemoryUsage[0],
			'memoryPercentage' => ceil($nodeResourceUsageLogMemoryUsage[1] / $nodeResourceUsageLogMemoryUsage[0])
		);
		$nodeResourceUsageLogTimestamp = time();

		while ((($nodeResourceUsageLogTimestamp + 540) > time()) === true) {
			if (empty($parameters['nodeResourceUsageLogProcessIntervalIndex']) === true) {
				$parameters['nodeResourceUsageLogProcessIntervalIndex'] = 0;
			}

			if (empty($parameters['cpuCapacityTime']['interval']) === true) {
				$nodeResourceUsageLogCpuTime = false;
				exec('sudo bash -c "sudo cat /proc/stat" | grep "cpu" 2>&1', $nodeResourceUsageLogCpuTime);
				end($nodeResourceUsageLogCpuTime);
				$parameters['data']['cpuCapacityCores'] = key($nodeResourceUsageLogCpuTime);
				$nodeResourceUsageLogCpuTime = array_shift($nodeResourceUsageLogCpuTime);
				exec('echo ' . $nodeResourceUsageLogCpuTime . ' | awk \'{print ""$2"+"$3"+"$4"+"$5"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeResourceUsageLogCpuTime);
				$nodeResourceUsageLogCpuTime = current($nodeResourceUsageLogCpuTime);
				$parameters['cpuCapacityTime'][] = array(
					'cpuTime' => _calculateCpuTime($nodeResourceUsageLogCpuTime),
					'timestamp' => microtime(true)
				);

				if (empty($parameters['cpuCapacityTime'][1]) === false) {
					$parameters['cpuCapacityTime'] = array(
						'cpuTime' => $parameters['cpuCapacityTime'][1]['cpuTime'] - $parameters['cpuCapacityTime'][0]['cpuTime'],
						'interval' => $parameters['cpuCapacityTime'][1]['timestamp'] - $parameters['cpuCapacityTime'][0]['timestamp']
					);
				}
			} else {
				exec('sudo bash -c "sudo cat /proc/stat" | grep "cpu " | awk \'{print ""$2"+"$3"+"$4"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeResourceUsageLogCpuTime);
				$nodeResourceUsageLogCpuTime = current($nodeResourceUsageLogCpuTime);
				$parameters['cpuTime'][$parameters['nodeResourceUsageLogProcessIntervalIndex']] = array(
					'cpuTime' => _calculateCpuTime($nodeResourceUsageLogCpuTime),
					'timestamp' => microtime(true)
				);

				if (empty($parameters['cpuTime'][($parameters['nodeResourceUsageLogProcessIntervalIndex'] - 1)]) === false) {
					$parameters['data']['cpuPercentage'][$parameters['nodeResourceUsageLogProcessIntervalIndex']] = array(
						'cpuTime' => $parameters['cpuTime'][$parameters['nodeResourceUsageLogProcessIntervalIndex']]['cpuTime'] - $parameters['cpuTime'][($parameters['nodeResourceUsageLogProcessIntervalIndex'] - 1)]['cpuTime'],
						'interval' => $parameters['cpuTime'][$parameters['nodeResourceUsageLogProcessIntervalIndex']]['timestamp'] - $parameters['cpuTime'][($parameters['nodeResourceUsageLogProcessIntervalIndex'] - 1)]['timestamp']
					);
				}

				if (empty($parameters['data']['cpuPercentage'][$parameters['nodeResourceUsageLogProcessIntervalIndex']]) === false) {
					$parameters['data']['cpuPercentage'][$parameters['nodeResourceUsageLogProcessIntervalIndex']] = ($parameters['data']['cpuPercentage'][$parameters['nodeResourceUsageLogProcessIntervalIndex']]['cpuTime'] + ($parameters['cpuCapacityTime']['interval'] - $parameters['data']['cpuPercentage'][$parameters['nodeResourceUsageLogProcessIntervalIndex']]['interval']) * ($parameters['data']['cpuPercentage'][$parameters['nodeResourceUsageLogProcessIntervalIndex']]['cpuTime'] / $parameters['data']['cpuPercentage'][$parameters['nodeResourceUsageLogProcessIntervalIndex']]['interval'])) / $parameters['cpuCapacityTime']['interval'];
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
		$parameters['data']['cpuPercentage'] = max($parameters['data']['cpuPercentage']);
		$parameters['data']['createdTimestamp'] = strtotime($nodeResourceUsageLogTimestamp);
		$systemParameters = array(
			'action' => 'add-node-resource-usage-logs',
			'data' => $parameters['data'],
			'nodeAuthenticationToken' => $parameters['nodeAuthenticationToken']
		);
		$encodedSystemParameters = json_encode($systemParameters);

		if (empty($encodedSystemParameters) === false) {
			shell_exec('sudo ' . $parameters['binary_files']['wget'] . ' -O /usr/local/firewall-security-api/system-action-add-node-resource-usage-logs-response.json  --connect-timeout=5 --dns-timeout=5 --no-dns-cache --post-data \'json=' . $encodedSystemParameters . '\' --read-timeout=60 --tries=1 ' . $parameters['systemEndpointDestinationAddress'] . '/system-endpoint.php');

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
