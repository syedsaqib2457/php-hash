<?php
	$extend = true;
	require_once($system->settings['base_path'] . '/methods/system.php');

	class NodeResourceUsageLogMethods extends SystemMethods {

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding node resource usage logs, please try again.',
				'status_valid' = (
					(empty($_FILES['data']['tmp_name']) === false) &&
					(empty($parameters['user']['endpoint']) === false)
				)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$nodeProcessResourceUsageLogKeys(); // ..
			$nodeResourceUsageLogKeys = array(
				'cpu_capacity_cores',
				'cpu_capacity_megahertz',
				'cpu_percentage',
				'memory_capacity_megabytes',
				'memory_percentage',
				'memory_percentage_tcp_ip_version_4',
				'memory_percentage_tcp_ip_version_6',
				'memory_percentage_udp_ip_version_4',
				'memory_percentage_udp_ip_version_6',
				'storage_capacity_megabytes',
				'storage_percentage'
			);
			$nodeResourceUsageLogTables = array(
				'node_process_resource_usage_logs',
				'node_resource_usage_logs'
			);
			$nodeResourceUsageLogs = json_decode(file_get_contents($_FILES['data']['tmp_name']), true);

			// todo: validate keys + tables so 1 compromised node doesn't affect system data
			// todo: transfer process-specific resource usage columns to node_process_resource_usage_logs
			// todo: parse data for node_resource_usage_logs and node_process_resource_usage_logs
			// todo: update row if row already exists for 10-minute interval (request bytes and request count update separately)
			// ..

			$response['message'] = 'Node resource usage logs added successfully.';
			return $response;
		}

	}

	if (empty($system->parameters) === false) {
		$nodeResourceUsageLogMethods = new NodeResourceUsageLogMethods();
		$data = $nodeResourceUsageLogMethods->route($system->parameters);
	}
?>
