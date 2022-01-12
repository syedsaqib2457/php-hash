<?php
	function _calculateCpuTime($cpuTimeString) {
		$cpuTime = 0;
		$cpuTimeValues = explode('+', $cpuTimeString);

		foreach ($cpuTimeValues as $cpuTimeValue) {
			$cpuTime += substr($cpuTimeValue, -15);
		}

		return $cpuTime;
	}

	function _processNodeProcessResourceUsageLogs($parameters, $response) {
		$parameters['node_process_resource_usage_log_process_types'] = array(
			'http_proxy',
			'php',
			'recursive_dns',
			'socks_proxy'
		);
		$nodeProcessResourceUsageLogTimestamp = time();

		while ((($nodeProcessResourceUsageLogTimestamp + 540) > time()) === true) {
			if (empty($parameters['node_resource_usage_log_process_interval_index']) === true) {
				$parameters['node_resource_usage_log_process_interval_index'] = 0;
			}

			if (empty($parameters['cpu_capacity_time']['interval']) === true) {
				$nodeProcessResourceUsageLogCpuTime = false;
				exec('sudo bash -c "sudo cat /proc/stat" | grep "cpu" 2>&1', $nodeProcessResourceUsageLogCpuTime);
				end($nodeProcessResourceUsageLogCpuTime);
				$nodeProcessResourceUsageLogCpuTime = array_shift($nodeProcessResourceUsageLogCpuTime);
				exec('echo ' . $nodeProcessResourceUsageLogCpuTime . ' | awk \'{print ""$2"+"$3"+"$4"+"$5"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeProcessResourceUsageLogCpuTime);
				$nodeProcessResourceUsageLogCpuTime = current($nodeProcessResourceUsageLogCpuTime);
				$parameters['cpu_capacity_time'][] = array(
					'cpu_time' => _calculateCpuTime($nodeProcessResourceUsageLogCpuTime),
					'timestamp' => microtime(true)
				);

				if (empty($parameters['cpu_capacity_time'][1]) === false) {
					$parameters['cpu_capacity_time'] = array(
						'cpu_time' => $parameters['cpu_capacity_time'][1]['cpu_time'] - $parameters['cpu_capacity_time'][0]['cpu_time'],
						'interval' => $parameters['cpu_capacity_time'][1]['timestamp'] - $parameters['cpu_capacity_time'][0]['timestamp']
					);
				}
			} else {
				foreach ($parameters['node_process_resource_usage_log_process_types'] as $nodeResourceUsageLogProcessType) {
					$parameters['data'][$nodeResourceUsageLogProcessType]['memory_percentage'][$parameters['node_resource_usage_log_process_interval_index']] = 0;
					$processProcessIdCommand = 'pgrep ' . $nodeResourceUsageLogProcessType;
					$processProcessIds = false;
					exec($processProcessIdCommand, $processProcessIds);

					foreach ($processProcessIds as $processProcessId) {
						$nodeProcessResourceUsageLogCpuTimeProcess = false;
						exec('sudo bash -c "sudo cat /proc/' . $processProcessId . '/stat" | awk \'{print ""$14"+"$15"+"$16"+"$17""}\' 2>&1', $nodeProcessResourceUsageLogCpuTimeProcess);
						$nodeProcessResourceUsageLogCpuTimeProcess = current($nodeProcessResourceUsageLogCpuTimeProcess);

						$parameters[$nodeResourceUsageLogProcessType]['cpu_time'][$parameters['node_resource_usage_log_process_interval_index']][$processProcessId] = array(
							'cpu_time' => _calculateCpuTime($nodeProcessResourceUsageLogCpuTimeProcess),
							'timestamp' => microtime(true)
						);

						if (empty($parameters[$nodeResourceUsageLogProcessType]['cpu_time'][($parameters['node_resource_usage_log_process_interval_index'] - 1)][$processProcessId]) === false) {
							$parameters['data'][$nodeResourceUsageLogProcessType]['cpu_percentage'][$parameters['node_resource_usage_log_process_interval_index']][$processProcessId] = array(
								'cpu_time' => $parameters[$nodeResourceUsageLogProcessType]['cpu_time'][$parameters['node_resource_usage_log_process_interval_index']][$processProcessId]['cpu_time'] - $parameters[$nodeResourceUsageLogProcessType]['cpu_time'][($parameters['node_resource_usage_log_process_interval_index'] - 1)][$processProcessId]['cpu_time'],
								'interval' => $parameters[$nodeResourceUsageLogProcessType]['cpu_time'][$parameters['node_resource_usage_log_process_interval_index']][$processProcessId]['timestamp'] - $parameters[$nodeResourceUsageLogProcessType]['cpu_time'][($parameters['node_resource_usage_log_process_interval_index'] - 1)][$processProcessId]['timestamp']
							);
						}

						$nodeResourceUsageLogMemoryPercentageProcessProcess = 0;
						exec('ps -h -p ' . $processProcessId . ' -o %mem', $nodeResourceUsageLogMemoryPercentageProcessProcess);
						$parameters['data'][$nodeResourceUsageLogProcessType]['memory_percentage'][$parameters['node_resource_usage_log_process_interval_index']] += current($nodeResourceUsageLogMemoryPercentageProcessProcess);
					}

					if (empty($parameters['data'][$nodeResourceUsageLogProcessType]['cpu_percentage'][$parameters['node_resource_usage_log_process_interval_index']]) === false) {
						$nodeResourceUsageLogCpuPercentageProcess = 0;

						foreach ($parameters['data'][$nodeResourceUsageLogProcessType]['cpu_percentage'][$parameters['node_resource_usage_log_process_interval_index']] as $processProcessId => $nodeResourceUsageLogCpuPercentageProcessProcess) {
							$parameters['data'][$nodeResourceUsageLogProcessType]['cpu_percentage'][$parameters['node_resource_usage_log_process_interval_index']][$processProcessId]['cpu_time'] += ($parameters['cpu_capacity_time']['interval'] - $nodeResourceUsageLogCpuPercentageProcessProcess['interval']) * ($nodeResourceUsageLogCpuPercentageProcessProcess['cpu_time'] / $nodeResourceUsageLogCpuPercentageProcessProcess['interval']);
							$nodeResourceUsageLogCpuPercentageProcess += ($parameters['data'][$nodeResourceUsageLogProcessType]['cpu_percentage'][$parameters['node_resource_usage_log_process_interval_index']][$processProcessId]['cpu_time'] / $parameters['cpu_capacity_time']['interval']);
						}

						$parameters['data'][$nodeResourceUsageLogProcessType]['cpu_percentage'][$parameters['node_resource_usage_log_process_interval_index']] = $nodeResourceUsageLogCpuPercentageProcess;
					}
				}
			}

			$parameters['node_resource_usage_log_process_interval_index']++;
			sleep(10);
		}

		$nodeProcessResourceUsageLogTimestamp = date('Y-m-d H:i', $nodeProcessResourceUsageLogTimestamp);
		$nodeProcessResourceUsageLogTimestamp = substr($nodeProcessResourceUsageLogTimestamp, 0, 15) . '0:00';
		$nodeProcessResourceUsageLogTimestamp = strtotime($nodeProcessResourceUsageLogTimestamp);

		foreach ($parameters['data'] as $nodeProcessResourceUsageLogProcessType => $nodeProcessResourceUsageLogData) {
			$parameters['data'][] = array(
				'cpu_percentage' = max($nodeProcessResourceUsageLogData['cpu_percentage']),
				'created_timestamp' => $nodeProcessResourceUsageLogTimestamp,
				'memory_percentage' = max($nodeProcessResourceUsageLogData['memory_percentage']),
				'node_process_type' => $nodeProcessResourceUsageLogProcessType
			);
			unset($parameters['data'][$nodeProcessResourceUsageLogProcessType]);
		}

		$systemParameters = array(
			'action' => 'add_node_process_resource_usage_logs',
			'data' => $parameters['data'],
			'node_authentication_token' => $parameters['node_authentication_token']
		);
		$encodedSystemParameters = json_encode($systemParameters);

		if (empty($encodedSystemParameters) === false) {
			shell_exec('sudo ' . $parameters['binary_files']['wget'] . ' -O /usr/local/ghostcompute/system_action_add_node_process_resource_usage_logs_response.json --no-dns-cache --post-data \'json=' . $encodedSystemParameters . '\' --timeout=10 ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php');

			if (file_exists('/usr/local/ghostcompute/system_action_add_node_process_resource_usage_logs_response.json') === true) {
				$systemActionProcessNodeProcessResourceUsageLogResponse = file_get_contents('/usr/local/ghostcompute/system_action_add_node_process_resource_usage_logs_response.json');
				$systemActionProcessNodeProcessResourceUsageLogResponse = json_decode($systemActionProcessNodeResponse, true);

				if (empty($systemActionProcessNodeProcessResourceUsageLogResponse) === false) {
					$response = $systemActionProcessNodeProcessResourceUsageLogResponse;
				}
			}
		}

		return $response;
	}

	if (($parameters['action'] === 'process_node_process_resource_usage_logs') === true) {
		$response = _processNodeProcessResourceUsageLogs($parameters, $response);
		_output($response);
	}
?>
