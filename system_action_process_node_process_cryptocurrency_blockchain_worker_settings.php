<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_blockchain_worker_block_headers',
		'node_process_cryptocurrency_blockchain_worker_hash_speed_logs',
		'node_process_cryptocurrency_blockchain_worker_settings',
		'node_process_resource_usage_logs',
		'node_resource_usage_logs'
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
					'node_process_type',
					'memory_usage_maximum_percentage'
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
				$nodeProcessCryptocurrencyBlockchainNodeProcessResourceUsageLog = _list(array(
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
				$nodeProcessCryptocurrencyBlockchainNodeProcessResourceUsageTypeLogs = current($nodeProcessCryptocurrencyBlockchainNodeProcessResourceUsageLog);
				$nodeProcessCryptocurrencyBlockchainNodeResourceUsageLog = _list(array(
					'data' => array(
						'cpu_percentage',
						'gpu_percentage',
						'memory_percentage'
					),
					'in' => $parameters['system_databases']['node_resource_usage_logs'],
					'limit' => 1,
					'sort' => array(
						'modified_timestamp' => 'descending'
					),
					'where' => array(
						'node_id' => $nodeProcessCryptocurrencyBlockchainWorkerSetting['node_id'],
						'node_process_type' => $nodeProcessCryptocurrencyBlockchainWorkerSetting['node_process_type']
					)
				), $response);
				$nodeProcessCryptocurrencyBlockchainNodeResourceUsageLog = current($nodeProcessCryptocurrencyBlockchainNodeResourceUsageLog);
				$nodeProcessCryptocurrencyBlockchainWorkerBlockHeadersPerWorkerCount = ceil($nodeProcessCryptocurrencyBlockchainWorkerBlockHeadersCount / $nodeProcessCryptocurrencyBlockchainWorkerSetting['count']);
				$nodeProcessCryptocurrencyBlockchainWorkerSettingResourceUsageMaximums = array(
					'cpu_percentage' => $nodeProcessCryptocurrencyBlockchainWorkerSetting['cpu_usage_maximum_percentage'],
					'gpu_percentage' => $nodeProcessCryptocurrencyBlockchainWorkerSetting['gpu_usage_maximum_percentage'],
					'memory_percentage' => $nodeProcessCryptocurrencyBlockchainWorkerSetting['memory_usage_maximum_percentage']
				);

				foreach ($nodeProcessCryptocurrencyBlockchainNodeProcessResourceUsageTypeLogs as $nodeProcessCryptocurrencyBlockchainNodeProcessResourceUsageType => $nodeProcessCryptocurrencyBlockchainNodeProcessResourceUsageTypeLog) {
					if (
						(($nodeProcessCryptocurrencyBlockchainNodeProcessResourceUsageTypeLog > $nodeProcessCryptocurrencyBlockchainWorkerSettingResourceUsageMaximums[$nodeProcessCryptocurrencyBlockchainNodeProcessResourceUsageType]) === true) ||
						(($nodeProcessCryptocurrencyBlockchainNodeResourceUsageLog[$nodeProcessCryptocurrencyBlockchainNodeProcessResourceUsageType] > 90) === true)
					) {
						$nodeProcessCryptocurrencyBlockchainWorkerSetting['count'] -= $nodeProcessCryptocurrencyBlockchainWorkerBlockHeadersPerWorkerCount;

						if (($nodeProcessCryptocurrencyBlockchainWorkerSetting['count'] < 1) === true) {
							$nodeProcessCryptocurrencyBlockchainWorkerSetting['count'] = 1;
							$nodeProcessCryptocurrencyBlockchainWorkerBlockHeadersPerWorkerCount = max(1, ($nodeProcessCryptocurrencyBlockchainWorkerBlockHeadersPerWorkerCount - 1));
						}

						_edit(array(
							'data' => array(
								'count' => $nodeProcessCryptocurrencyBlockchainWorkerSetting['count']
							),
							'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_settings'],
							'where' => array(
								'id' => $nodeProcessCryptocurrencyBlockchainWorkerSetting['id']
							)
						), $response);
						unset($nodeProcessCryptocurrencyBlockchainWorkerSetting['id']);
						$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders = _list(array(
							'data' => array(
								'id'
							),
							'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_block_headers'],
							'limit' => $nodeProcessCryptocurrencyBlockchainWorkerBlockHeadersPerWorkerCount,
							'where' => array(
								'node_id' => $nodeProcessCryptocurrencyBlockchainWorkerSetting['node_id'],
								'node_process_type' => $nodeProcessCryptocurrencyBlockchainWorkerSetting['node_process_type']
							)
						), $response);

						foreach ($nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders as $nodeProcessCryptocurrencyBlockchainWorkerBlockHeader) {
							$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderIds[] = $nodeProcessCryptocurrencyBlockchainWorkerBlockHeader['id'];
						}

						_delete(array(
							'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_block_headers'],
							'where' => array(
								'id' => $nodeProcessCryptocurrencyBlockchainWorkerBlockHeaderIds
							)
						), $response);
						break;
					}
				}

				if (empty($nodeProcessCryptocurrencyBlockchainWorkerSetting['id']) === false) {
					$nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders = _list(array(
						'data' => array(
							'node_id',
							'node_node_id',
							'node_process_type',
							'public_key_script'
						),
						'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_block_headers'],
						'limit' => $nodeProcessCryptocurrencyBlockchainWorkerBlockHeadersPerWorkerCount,
						'where' => array(
							'node_id' => $nodeProcessCryptocurrencyBlockchainWorkerSetting['node_id'],
							'node_process_type' => $nodeProcessCryptocurrencyBlockchainWorkerSetting['node_process_type']
						)
					), $response);
					_save(array(
					 	'data' => $nodeProcessCryptocurrencyBlockchainWorkerBlockHeaders,
					 	'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_block_headers']
					), $response);
					_edit(array(
						'data' => array(
							'count' => ($nodeProcessCryptocurrencyBlockchainWorkerSetting['count'] + $nodeProcessCryptocurrencyBlockchainWorkerBlockHeadersPerWorkerCount)
						),
						'in' => $parameters['system_databases']['node_process_cryptocurrency_blockchain_worker_settings'],
						'where' => array(
							'id' => $nodeProcessCryptocurrencyBlockchainWorkerSetting['id']
						)
					), $response);
				}
			}
		}
	}

	if (($parameters['action'] === 'process_node_process_cryptocurrency_blockchain_worker_settings') === true) {
		$response = _processNodeProcessCryptocurrencyBlockchainWorkerSettings($parameters, $response);
	}
?>
