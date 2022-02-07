<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_worker_settings',
		'node_process_resource_usage_logs'
	), $parameters['system_databases'], $response);

	function _processNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response) {
		if (empty($_SERVER['argv'][1]) === true) {
			return $response;
		}

		while (true) {
			$nodeProcessCryptocurrencyBlockchainWorkerSettings = _list(array(
				'data' => array(
					'count',
					'cpu_usage_maximum_percentage',
					'gpu_usage_maximum_percentage',
					'id',
					'node_id',
					'memory_usage_maximum_percentage',
					'node_process_type',
					'unprocessed_count'
				),
				'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_settings'],
				'sort' => array(
					'modified_timestamp' => 'ascending'
				),
				'where' => array(
					'modified_timestamp <' => (time() - 600)
				)
			), $response);

			if (empty($nodeProcessCryptocurrencyBlockchainWorkerSettings) === true) {
				$response['message'] = 'Node process cryptocurrency blockchain worker settings processed successfully.';
				$response['valid_status'] = '1';
				return $response;
			}

			foreach ($nodeProcessCryptocurrencyBlockchainWorkerSettings as $nodeProcessCryptocurrencyBlockchainWorkerSetting) {
				$nodeProcessCryptocurrencyBlockchainResourceUsageLog = _list(array(
					'data' => array(
						'cpu_percentage',
						'gpu_percentage',
						'memory_percentage'
					),
					'in' => $parameters['system_databases']['node_process_resource_usage_logs'],
					'limit' => 1,
					'sort' => array(
						'modified_timestamp' => 'descending'
					),
					'where' => array(
						'node_id' => $nodeProcessCryptocurrencyBlockchainWorkerSetting['node_id'],
						'node_process_type' => $nodeProcessCryptocurrencyBlockchainWorkerSetting['node_process_type']
					)
				), $response);
				$nodeProcessCryptocurrencyBlockchainResourceUsageLog = current($nodeProcessCryptocurrencyBlockchainResourceUsageLog);

				if (
					(($nodeProcessCryptocurrencyBlockchainResourceUsageLog['cpu_percentage'] > $nodeProcessCryptocurrencyBlockchainWorkerSetting['cpu_usage_maximum_percentage']) === true) ||
					(($nodeProcessCryptocurrencyBlockchainResourceUsageLog['gpu_percentage'] > $nodeProcessCryptocurrencyBlockchainWorkerSetting['gpu_usage_maximum_percentage']) === true) ||
					(($nodeProcessCryptocurrencyBlockchainResourceUsageLog['memory_percentage'] > $nodeProcessCryptocurrencyBlockchainWorkerSetting['memory_usage_maximum_percentage']) === true)
				) {
					$nodeProcessCryptocurrencyBlockchainWorkerSetting['unprocessed_count'] = ($nodeProcessCryptocurrencyBlockchainWorkerSetting['count'] - 1);
				} else {
					// todo
				}

				if (isset($nodeProcessCryptocurrencyBlockchainWorkerSetting['unprocessed_count']) === true) {
					_edit(array(
						'data' => array(
							'unprocessed_count' => $nodeProcessCryptocurrencyBlockchainWorkerSetting['count']
						),
						'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_settings'],
						'where' => array(
							'id' => $nodeProcessCryptocurrencyBlockchainWorkerSetting['id']
						)
					), $response);
				}

				// todo
			}
		}
	}

	if (($parameters['action'] === 'process_node_process_cryptocurrency_blockchain_worker_settings') === true) {
		$response = _processNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response);
	}
?>
