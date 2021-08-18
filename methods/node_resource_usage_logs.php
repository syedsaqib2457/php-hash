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

			$nodeResourceUsageLogProcessTypes = array(
				'http_proxy',
				'nameserver',
				'socks_proxy',
				'system'
			);
			$nodeResourceUsageLogKeys = array(
				'node_process_resource_usage_logs' => array(
					'cpu_percentage',
					'memory_percentage',
					'memory_percentage_tcp_ip_version_4',
					'memory_percentage_tcp_ip_version_6',
					'memory_percentage_udp_ip_version_4',
					'memory_percentage_udp_ip_version_6',
					'node_process_type'
				),
				'node_resource_usage_logs' => array(
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
				)
			);
			$nodeResourceUsageLogs = json_decode(file_get_contents($_FILES['data']['tmp_name']), true);

			$response['status_valid'] = (
				(
					(empty($nodeResourceUsageLogs['node_process_resource_usage_logs']) === false) &&
					(is_array(current($nodeResourceUsageLogs['node_process_resource_usage_logs'])) === true)
				) &&
				(
					(empty($nodeResourceUsageLogs['node_resource_usage_logs']) === false) &&
					(is_array(current($nodeResourceUsageLogs['node_resource_usage_logs'])) === false)
				)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			// todo: search for existing records within same 10 minute interval
			// todo: save created timestamp string as Y-m-d H:i0:00
			$existingNodeProcessResourceUsageLogs = $this->fetch(array(
				'fields' => array(
					'id',
					'node_id',
					'node_process_type'
				),
				'from' => 'node_process_resource_usage_logs',
				'where' => array(
					'created >' => date('Y-m-d H:i:s', strtotime('-10 minutes')),
					'node_id' => ($nodeId = $parameters['user']['node_id'])
				)
			));

			if ($existingNodeProcessResourceUsageLogs === false) {
				return $response;
			}

			foreach ($nodeResourceUsageLogs['node_process_resource_usage_logs'] as $nodeProcessResourceUsageLogKey => $nodeProcessResourceUsageLog) {
				unset($nodeResourceUsageLogs['node_process_resource_usage_logs'][$nodeProcessResourceUsageLogKey]['created']);
				unset($nodeResourceUsageLogs['node_process_resource_usage_logs'][$nodeProcessResourceUsageLogKey]['node_id']);
			}

			unset($nodeResourceUsageLogs['node_resource_usage_logs']['created']);
			unset($nodeResourceUsageLogs['node_resource_usage_logs']['modified']);

			if (empty($existingNodeProcessResourceUsageLogs) === false) {
				foreach ($existingNodeProcessResourceUsageLogs as $existingNodeProcessResourceUsageLog) { 
					$nodeResourceUsageLogs['node_process_resource_usage_logs'][$existingNodeProcessResourceUsageLog['node_process_type']]['node_id'] = $nodeId;
				}
			}

			$nodeProcessResourceUsageLogData = array();

			foreach ($nodeResourceUsageLogs['node_process_resource_usage_logs'] as $nodeProcessResourceUsageLog) {
				if (
					(empty($nodeProcessResourceUsageLog['node_process_type']) === true) ||
					(in_array($nodeProcessResourceUsageLog['node_process_type'], $nodeResourceUsageLogProcessTypes) === false)
				) {
					$response['message'] = 'Invalid node process type, please try again.';
					return $response;
				}

				foreach ($nodeProcessResourceUsageLog as $nodeProcessResourceUsageLogKey => $nodeProcessResourceUsageLogValue) {
					if (
						(strpos($nodeProcessResourceUsageLogKey, '_percentage') !== false) &&
						(is_numeric($nodeProcessResourceUsageLogValue) === false)
					) {
						$response['message'] = 'Invalid node process resource usage logs, please try again.';
						return $response;
					}
				}

				$nodeProcessResourceUsageLogData[] = array_intersect_key($nodeProcessResourceUsageLog, array(
					'cpu_percentage' => true,
					'created' => true,
					'memory_percentage' => true,
					'memory_percentage_tcp_ip_version_4' => true,
					'memory_percentage_tcp_ip_version_6' => true,
					'memory_percentage_udp_ip_version_4' => true,
					'memory_percentage_udp_ip_version_6' => true,
					'node_id' => true,
					'node_process_type' => true
				));
			}

			// ..

			$nodeProcessResourceUsageLogDataSaved = $this->save(array(
				'data' => $nodeProcessResourceUsageLogData,
				'to' => 'node_process_resource_usage_logs'
			));
			$nodeResourceUsageLogDataSaved = $this->save(array(
				'data' => array_intersect_key($nodeResourceUsageLogs['node_resource_usage_logs'] array(
					'cpu_capacity_cores' => true,
					'cpu_capacity_megahertz' => true,
					'cpu_percentage' => true,
					'created' => true,
					'id' => true,
					'memory_capacity_megabytes' => true,
					'memory_percentage' => true,
					'memory_percentage_tcp_ip_version_4' => true,
					'memory_percentage_tcp_ip_version_6' => true,
					'memory_percentage_udp_ip_version_4' => true,
					'memory_percentage_udp_ip_version_6' => true,
					'storage_capacity_megabytes' => true,
					'storage_percentage' => true
				)),
				'to' => 'node_resource_usage_logs'
			));
			$response['status_valid'] = (
				($nodeProcessResourceUsageLogDataSaved === true) &&
				($nodeResourceUsageLogDataSaved === true)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['message'] = 'Node resource usage logs added successfully.';
			return $response;
		}

	}

	if (empty($system->parameters) === false) {
		$nodeResourceUsageLogMethods = new NodeResourceUsageLogMethods();
		$data = $nodeResourceUsageLogMethods->route($system->parameters);
	}
?>
