<?php
	$extend = true;
	require_once($system->settings['base_path'] . '/methods/system.php');

	class NodeResourceUsageLogMethods extends SystemMethods {

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding node resource usage logs, please try again.',
				'status_valid' => (empty($parameters['user']['node_id']) === false)
			);

			if ($response['status_valid'] === false) {
				$this->_logUnauthorizedRequest();
				return $response;
			}

			$response['status_valid'] = (
				(empty($_FILES['data']['tmp_name']) === false) &&
				(empty($parameters['user']['endpoint']) === false)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$nodeResourceUsageLogProcessTypes = array(
				'http_proxy',
				'recursive_dns',
				'socks_proxy',
				'system'
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

			$existingNodeProcessResourceUsageLogs = $this->fetch(array(
				'fields' => array(
					'id',
					'node_id',
					'node_process_type'
				),
				'from' => 'node_process_resource_usage_logs',
				'where' => array(
					'created >=' => date('Y-m-d H:i:s', strtotime('-10 minutes')),
					'node_id' => ($nodeId = $parameters['user']['node_id'])
				)
			));
			$existingNodeResourceUsageLog = $this->fetch(array(
				'fields' => array(
					'id'
				),
				'from' => 'node_resource_usage_logs',
				'where' => array(
					'created >=' => date('Y-m-d H:i:s', strtotime('-10 minutes')),
					'node_id' => $nodeId
				)
			));

			if (
				($existingNodeProcessResourceUsageLogs === false) ||
				($existingNodeResourceUsageLogs === false)
			) {
				return $response;
			}

			if (empty($existingNodeProcessResourceUsageLogs) === false) {
				foreach ($existingNodeProcessResourceUsageLogs as $existingNodeProcessResourceUsageLog) { 
					$nodeResourceUsageLogs['node_process_resource_usage_logs'][$existingNodeProcessResourceUsageLog['node_process_type']]['id'] = $existingNodeProcessResourceUsageLog['id'];
				}
			}

			if (empty($existingNodeResourceUsageLog) === false) {
				$nodeResourceUsageLogs['node_resource_usage_log']['id'] = $existingNodeResourceUsageLog['id'];
			}

			$nodeProcessResourceUsageLogData = array();

			foreach ($nodeResourceUsageLogs['node_process_resource_usage_logs'] as $nodeProcessResourceUsageLog) {
				$nodeProcessResourceUsageLog['node_id'] = $nodeId;

				if (
					(strtotime($nodeProcessResourceUsageLog['created']) === false) ||
					(substr($nodeProcessResourceUsageLog['created'], -4) !== '0:00')
				) {
					$response['message'] = 'Invalid node process resource usage log created date, please try again.';
					return $response;
				}

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
					'id' => true,
					'memory_percentage' => true,
					'node_id' => true,
					'node_process_type' => true
				));
			}

			$nodeProcessResourceUsageLogs['node_resource_usage_log']['node_id'] = $nodeId;

			foreach ($nodeResourceUsageLogs['node_resource_usage_log'] as $nodeResourceUsageLogKey => $nodeResourceUsageLogValue) {
				if (
					($nodeResourceUsageLogKey === 'created') &&
					(
						(strtotime($nodeResourceUsageLogValue) === false) ||
						(substr($nodeResourceUsageLogValue, -4) !== '0:00')
					)
				) {
					$response['message'] = 'Invalid node resource usage log created date, please try again.';
					return $response;
				}

				if (
					(
						(strpos($nodeResourceUsageLogKey, '_capacity') !== false) ||
						(strpos($nodeResourceUsageLogKey, '_percentage') !== false)
					) &&
					(is_numeric($nodeResourceUsageLogValue) === false)
				) {
					$response['message'] = 'Invalid node resource usage logs, please try again.';
					return $response;
				}
			}

			$nodeProcessResourceUsageLogsSaved = $this->save(array(
				'data' => $nodeProcessResourceUsageLogData,
				'to' => 'node_process_resource_usage_logs'
			));
			$nodeResourceUsageLogsSaved = $this->save(array(
				'data' => array_intersect_key($nodeProcessResourceUsageLogs['node_resource_usage_log'], array(
					'cpu_capacity_cores' => true,
					'cpu_capacity_megahertz' => true,
					'cpu_percentage' => true,
					'created' => true,
					'id' => true,
					'memory_capacity_megabytes' => true,
					'memory_percentage' => true,
					'node_id' => true,
					'storage_capacity_megabytes' => true,
					'storage_percentage' => true
				)),
				'to' => 'node_resource_usage_logs'
			));
			$response['status_valid'] = (
				($nodeProcessResourceUsageLogsSaved === true) &&
				($nodeResourceUsageLogsSaved === true)
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
