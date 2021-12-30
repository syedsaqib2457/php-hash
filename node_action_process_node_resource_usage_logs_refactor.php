<?php
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
		$parameters['memory_percentage'] => ceil($nodeResourceUsageLogMemoryUsage[1] / $nodeResourceUsageLogMemoryUsage[0]);
	}

	if (($parameters['action'] === 'process_node_resource_usage_logs') === true) {
		$response = _processNodeResourceUsageLogs($parameters, $response);
		_output($response);
	}
?>
