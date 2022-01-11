<?php
	function _calculateCpuTime($nodeResourceUsageLogCpuTimeString) {
		$nodeResourceUsageLogCpuTime = 0;
		$nodeResourceUsageLogCpuTimeValues = explode('+', $nodeResourceUsageLogCpuTimeString);

		foreach ($nodeResourceUsageLogCpuTimeValues as $nodeResourceUsageLogCpuTimeValue) {
			$nodeResourceUsageLogCpuTime += substr($nodeResourceUsageLogCpuTimeValue, -15);
		}

		return $nodeResourceUsageLogCpuTime;
	}

	function _processNodeResourceUsageLogs($parameters, $response) {
		exec('sudo bash -c "sudo cat /proc/cpuinfo" | grep "cpu MHz" | awk \'{print $4\'} | head -1 2>&1', $nodeResourceUsageLogCpuCapacityMegahertz);
		$nodeResourceUsageLogCpuCapacityMegahertz = current($nodeResourceUsageLogCpuCapacityMegahertz);
		$parameters['cpu_capacity_megahertz'] = ceil($nodeResourceUsageLogCpuCapacityMegahertz);
		exec('getconf PAGE_SIZE 2>&1', $kernelPageSize);
		$parameters['kernel_page_size'] = current($kernelPageSize);
		exec('free -m | grep "Mem:" | grep -v free | awk \'{print $2"_"$3}\'', $nodeResourceUsageLogMemoryUsage);
		$nodeResourceUsageLogMemoryUsage = current($nodeResourceUsageLogMemoryUsage);
		$nodeResourceUsageLogMemoryUsage = explode('_', $nodeResourceUsageLogMemoryUsage);
		$parameters['memory_capacity_megabytes'] = $nodeResourceUsageLogMemoryUsage[0];
		$parameters['memory_percentage'] = ceil($nodeResourceUsageLogMemoryUsage[1] / $nodeResourceUsageLogMemoryUsage[0]);
		$parameters['node_resource_usage_log_ip_address_versions'] = array(
			'4',
			'6'
		);
		$parameters['node_resource_usage_log_process_types'] = array(
			'http_proxy',
			'recursive_dns',
			'socks_proxy',
			'system'
		);
		$nodeResourceUsageLogProcessStart = time();

		while ((($nodeResourceUsageLogProcessStart + 540) > time()) === true) {
			if (empty($parameters['node_resource_usage_log_process_interval_index']) === true) {
				$parameters['node_resource_usage_log_process_interval_index'] = 0;
			}

			$nodeResourceUsageLogCpuTime = $nodeResourceUsageLogCpuTimeStart = microtime(true);

			if (empty($parameters['data']['cpu_capacity_time']['interval']) === true) {
				exec('sudo bash -c "sudo cat /proc/stat" | grep "cpu" 2>&1', $nodeResourceUsageLogCpuTime);
				end($nodeResourceUsageLogCpuTime);
				$parameters['data']['cpu_capacity_cores'] = key($nodeResourceUsageLogCpuTime);
				$nodeResourceUsageLogCpuTime = array_shift($nodeResourceUsageLogCpuTime);
				exec('echo ' . $nodeResourceUsageLogCpuTime . ' | awk \'{print ""$2"+"$3"+"$4"+"$5"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeResourceUsageLogCpuTime);
				$nodeResourceUsageLogCpuTime = current($nodeResourceUsageLogCpuTime);
				$parameters['data']['cpu_capacity_time'][] = array(
					'cpu_time' => _calculateCpuTime($nodeResourceUsageLogCpuTime),
					'timestamp' => $nodeResourceUsageLogCpuTimeStart
				);

				if (empty($parameters['data']['cpu_capacity_time'][1]) === false) {
					$parameters['data']['cpu_capacity_time'] = array(
						'cpu_time' => $parameters['data']['cpu_capacity_time'][1]['cpu_time'] - $parameters['data']['cpu_capacity_time'][0]['cpu_time'],
						'interval' => $parameters['data']['cpu_capacity_time'][1]['timestamp'] - $parameters['data']['cpu_capacity_time'][0]['timestamp']
					);
				}
			} else {
				exec('sudo bash -c "sudo cat /proc/stat" | grep "cpu " | awk \'{print ""$2"+"$3"+"$4"+"$6"+"$7"+"$8"+"$9"+"$10"+"$11""}\' 2>&1', $nodeResourceUsageLogCpuTime);
				$nodeResourceUsageLogCpuTime = current($nodeResourceUsageLogCpuTime);
				$parameters['data']['cpu_time'][$parameters['node_resource_usage_log_process_interval_index']] = array(
					'cpu_time' => _calculateCpuTime($nodeResourceUsageLogCpuTime),
					'timestamp' => $nodeResourceUsageLogCpuTimeStart
				);

				if (empty($parameters['data']['cpu_time'][($parameters['node_resource_usage_log_process_interval_index'] - 1)]) === false) {
					$parameters['data']['cpu_percentage'][$parameters['node_resource_usage_log_process_interval_index']] = array(
						'cpu_time' => $parameters['data']['cpu_time'][$parameters['node_resource_usage_log_process_interval_index']]['cpu_time'] - $parameters['data']['cpu_time'][($parameters['node_resource_usage_log_process_interval_index'] - 1)]['cpu_time'],
						'interval' => $parameters['data']['cpu_time'][$parameters['node_resource_usage_log_process_interval_index']]['timestamp'] - $parameters['data']['cpu_time'][($parameters['node_resource_usage_log_process_interval_index'] - 1)]['timestamp']
					);
				}

				if (empty($parameters['data']['cpu_percentage'][$parameters['node_resource_usage_log_process_interval_index']]) === false) {
					$parameters['data']['cpu_percentage'][$parameters['node_resource_usage_log_process_interval_index']] = ($parameters['data']['cpu_percentage'][$parameters['node_resource_usage_log_process_interval_index']]['cpu_time'] + ($parameters['data']['cpu_capacity_time']['interval'] - $parameters['data']['cpu_percentage'][$parameters['node_resource_usage_log_process_interval_index']]['interval']) * ($parameters['data']['cpu_percentage'][$parameters['node_resource_usage_log_process_interval_index']]['cpu_time'] / $parameters['data']['cpu_percentage'][$parameters['node_resource_usage_log_process_interval_index']]['interval'])) / $parameters['data']['cpu_capacity_time']['interval'];
				}

				foreach ($parameters['node_resource_usage_log_process_types'] as $nodeResourceUsageLogProcessType) {
					$parameters = _processProcessUsagePercentages($parameters, $nodeResourceUsageLogProcessType);
				}

				exec('df -m / | tail -1 | awk \'{print $4}\'  2>&1', $nodeResourceUsageLogStorageCapacityMegabytes);
				$parameters['data']['storage_capacity_megabytes'] = current($nodeResourceUsageLogStorageCapacityMegabytes);
				exec('df / | tail -1 | awk \'{print $5}\' 2>&1', $nodeResourceUsageLogStoragePercentage);
				$parameters['data']['storage_percentage'] = current($nodeResourceUsageLogStoragePercentage);
			}

			$parameters['node_resource_usage_log_process_interval_index']++;
			sleep(10);
		}

		$nodeResourceUsageLogProcessStart = date('Y-m-d H:i', $nodeResourceUsageLogProcessStart);
		$nodeResourceUsageLogCreated = substr($nodeResourceUsageLogProcessStart, 0, 15) . '0:00';
		$parameters['data']['node_process_resource_usage_logs'] = array();
		$parameters['data']['node_resource_usage_log'] = array(
			'cpu_percentage' => max($parameters['data']['cpu_percentage']),
			'created_timestamp' => $nodeResourceUsageLogCreated,
			'storage_capacity_megabytes' => $parameters['data']['storage_capacity_megabytes'],
			'storage_percentage' => $parameters['data']['storage_percentage']
		);

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
			'node_process_resource_usage_logs' => true,
			'node_resource_usage_log' => true
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

	function _processProcessUsagePercentages($parameters, $nodeResourceUsageLogProcessType) {
		$parameters['data']['memory_percentage_process_' . $processType][$parameters['node_resource_usage_log_process_interval_index']] = 0;
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
			$nodeResourceUsageLogCpuTimeProcess = current($nodeResourceUsageLogCpuTimeProcess);

			$parameters['data']['cpu_time_process_' . $processType][$parameters['node_resource_usage_log_process_interval_index']][$processProcessId] = array(
				'cpu_time' => _calculateCpuTime($nodeResourceUsageLogCpuTimeProcess),
				'timestamp' => $nodeResourceUsageLogCpuTimeProcessStart
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

		return $parameters;
	}

	if (($parameters['action'] === 'process_node_resource_usage_logs') === true) {
		$response = _processNodeResourceUsageLogs($parameters, $response);
		_output($response);
	}
?>
