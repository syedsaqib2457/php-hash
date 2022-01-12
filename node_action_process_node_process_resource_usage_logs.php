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
		$parameters['node_resource_usage_log_process_types'] = array(
			'http_proxy',
			'recursive_dns',
			'socks_proxy',
			'system'
		);
		$nodeResourceUsageLogProcessTimestamp = time();

		while ((($nodeResourceUsageLogProcessTimestamp + 540) > time()) === true) {
			if (empty($parameters['node_resource_usage_log_process_interval_index']) === true) {
				$parameters['node_resource_usage_log_process_interval_index'] = 0;
			}

			if (empty($parameters['data']['cpu_capacity_time']['interval']) === true) {
				$nodeResourceUsageLogCpuTime = false;
				exec('sudo bash -c "sudo cat /proc/stat" | grep "cpu" 2>&1', $nodeResourceUsageLogCpuTime);
				end($nodeResourceUsageLogCpuTime);
				$nodeResourceUsageLogCpuTime = array_shift($nodeResourceUsageLogCpuTime);
				exec('echo ' . $nodeResourceUsageLogCpuTime . ' | awk \'{print ""$2"+"$3"+"$4"+"$5"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeResourceUsageLogCpuTime);
				$nodeResourceUsageLogCpuTime = current($nodeResourceUsageLogCpuTime);
				$parameters['data']['cpu_capacity_time'][] = array(
					'cpu_time' => _calculateCpuTime($nodeResourceUsageLogCpuTime),
					'timestamp' => microtime(true)
				);

				if (empty($parameters['data']['cpu_capacity_time'][1]) === false) {
					$parameters['data']['cpu_capacity_time'] = array(
						'cpu_time' => $parameters['data']['cpu_capacity_time'][1]['cpu_time'] - $parameters['data']['cpu_capacity_time'][0]['cpu_time'],
						'interval' => $parameters['data']['cpu_capacity_time'][1]['timestamp'] - $parameters['data']['cpu_capacity_time'][0]['timestamp']
					);
				}
			} else {
				foreach ($parameters['node_resource_usage_log_process_types'] as $nodeResourceUsageLogProcessType) {
					$parameters['data']['memory_percentage_process_' . $processType][$parameters['node_resource_usage_log_process_interval_index']] = 0;
					$processProcessIdCommand = 'pgrep ';

					if ($processType === 'system') {
						$processProcessIdCommand .= 'php';
					} else {
						$processProcessIdCommand .= $processType;
					}

					exec($processProcessIdCommand, $processProcessIds);

					foreach ($processProcessIds as $processProcessId) {
						$nodeResourceUsageLogCpuTimeProcess = false;
						exec('sudo bash -c "sudo cat /proc/' . $processProcessId . '/stat" | awk \'{print ""$14"+"$15"+"$16"+"$17""}\' 2>&1', $nodeResourceUsageLogCpuTimeProcess);
						$nodeResourceUsageLogCpuTimeProcess = current($nodeResourceUsageLogCpuTimeProcess);

						$parameters['data']['cpu_time_process_' . $processType][$parameters['node_resource_usage_log_process_interval_index']][$processProcessId] = array(
							'cpu_time' => _calculateCpuTime($nodeResourceUsageLogCpuTimeProcess),
							'timestamp' => microtime(true)
						);

						if (empty($parameters['data']['cpu_time_process_' . $processType][($parameters['node_resource_usage_log_process_interval_index'] - 1)][$processProcessId]) === false) {
							$parameters['data']['cpu_percentage_process_' . $processType][$parameters['node_resource_usage_log_process_interval_index']][$processProcessId] = array(
								'cpu_time' => $parameters['data']['cpu_time_process_' . $processType][$parameters['node_resource_usage_log_process_interval_index']][$processProcessId]['cpu_time'] - $parameters['data']['cpu_time_process_' . $processType][($parameters['node_resource_usage_log_process_interval_index'] - 1)][$processProcessId]['cpu_time'],
								'interval' => $parameters['data']['cpu_time_process_' . $processType][$parameters['node_resource_usage_log_process_interval_index']][$processProcessId]['timestamp'] - $parameters['data']['cpu_time_process_' . $processType][($parameters['node_resource_usage_log_process_interval_index'] - 1)][$processProcessId]['timestamp']
							);
						}

						$nodeResourceUsageLogMemoryPercentageProcessProcess = 0;
						exec('ps -h -p ' . $processProcessId . ' -o %mem', $nodeResourceUsageLogMemoryPercentageProcessProcess);
						$parameters['data']['memory_percentage_process_' . $processType][$parameters['node_resource_usage_log_process_interval_index']] += current($nodeResourceUsageLogMemoryPercentageProcessProcess);
					}

					if (empty($parameters['data']['cpu_percentage_process_' . $processType][$parameters['node_resource_usage_log_process_interval_index']]) === false) {
						$nodeResourceUsageLogCpuPercentageProcess = 0;

						foreach ($parameters['data']['cpu_percentage_process_' . $processType][$parameters['node_resource_usage_log_process_interval_index']] as $processProcessId => $nodeResourceUsageLogCpuPercentageProcessProcess) {
							$parameters['data']['cpu_percentage_process_' . $processType][$parameters['node_resource_usage_log_process_interval_index']][$processProcessId]['cpu_time'] += ($parameters['data']['cpu_capacity_time']['interval'] - $nodeResourceUsageLogCpuPercentageProcessProcess['interval']) * ($nodeResourceUsageLogCpuPercentageProcessProcess['cpu_time'] / $nodeResourceUsageLogCpuPercentageProcessProcess['interval']);
							$nodeResourceUsageLogCpuPercentageProcess += ($parameters['data']['cpu_percentage_process_' . $processType][$parameters['node_resource_usage_log_process_interval_index']][$processProcessId]['cpu_time'] / $parameters['data']['cpu_capacity_time']['interval']);
						}

						$parameters['data']['cpu_percentage_process_' . $processType][$parameters['node_resource_usage_log_process_interval_index']] = $nodeResourceUsageLogCpuPercentageProcess;
					}
				}
			}

			$parameters['node_resource_usage_log_process_interval_index']++;
			sleep(10);
		}

		$nodeResourceUsageLogProcessTimestamp = date('Y-m-d H:i', $nodeResourceUsageLogProcessTimestamp);
		$nodeResourceUsageLogProcessTimestamp = substr($nodeResourceUsageLogProcessTimestamp, 0, 15) . '0:00';
		$nodeResourceUsageLogProcessTimestamp = strtotime($nodeResourceUsageLogProcessTimestamp);
		$parameters['data']['node_process_resource_usage_logs'] = array();

		foreach ($parameters['node_resource_usage_log_process_types'] as $nodeResourceUsageLogProcessType) {
			$parameters['data']['node_process_resource_usage_logs'][$nodeResourceUsageLogProcessType] = array(
				'created' => $nodeResourceUsageLogCreated,
				'node_process_type' => $nodeResourceUsageLogProcessType
			);

			if (
				(isset($parameters['data']['node_process_resource_usage_logs']['cpu_percentage_process_' . $nodeResourceUsageLogProcessType]) === false) &&
				(isset($parameters['data']['cpu_percentage_process_' . $nodeResourceUsageLogProcessType]) === true)
			) {
				$parameters['data']['node_process_resource_usage_logs'][$nodeResourceUsageLogProcessType]['cpu_percentage'] = max($parameters['data']['cpu_percentage_process_' . $nodeResourceUsageLogProcessType]);
			}

			if (
				(isset($parameters['data']['node_process_resource_usage_logs']['memory_percentage_process_' . $nodeResourceUsageLogProcessType]) === false) &&
				(isset($parameters['data']['memory_percentage_process_' . $nodeResourceUsageLogProcessType]) === true)
			) {
				$parameters['data']['node_process_resource_usage_logs'][$nodeResourceUsageLogProcessType]['memory_percentage'] = max($parameters['data']['memory_percentage_process_' . $nodeResourceUsageLogProcessType]);
			}
		}

		$nodeResourceUsageLogs = array_intersect_key($parameters['data'], array(
			'node_process_resource_usage_logs' => true
		));
		$nodeResourceUsageLogs = json_encode($nodeResourceUsageLogs);

		if (file_put_contents('/usr/local/ghostcompute/node_resource_usage_logs.json', $nodeResourceUsageLogs) === false) {
			$response['message'] = 'Error adding node resource usage logs, please try again.' . "\n";
			return $response;
		}

		$systemParameters = array(
			'action' => 'add_node_resource_usage_logs',
			'node_authentication_token' => $parameters['node_authentication_token']
		);
		$encodedSystemParameters = json_encode($systemParameters);

		if ($encodedSystemParameters === false) {
			$response['message'] = 'Error processing node resource usage logs, please try again.' . "\n";
			return $response;
		}

		exec('sudo ' . $parameters['binary_files']['curl'] . ' -s --form "data=@/usr/local/ghostcompute/node_resource_usage_logs.json" --form-string \'json=' . $encodedSystemParameters . '\' ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php 2>&1', $processNodeResourceUsageLogsResponse);
		$processNodeResourceUsageLogsResponse = current($processNodeResourceUsageLogsResponse);
		$processNodeResourceUsageLogsResponse = json_decode($processNodeResourceUsageLogsResponse, true);
		return $processNodeResourceUsageLogsResponse;
	}

	if (($parameters['action'] === 'process_node_process_resource_usage_logs') === true) {
		$response = _processNodeProcessResourceUsageLogs($parameters, $response);
		_output($response);
	}
?>
