<?php
	_processNodeProcessDogecoinCryptocurrencyMiningProcesses($parameters, $response) {
		exec('ps -h -o pid -o cmd $(pgrep php) | grep process_node_process_dogecoin_cryptocurrency_mining_processes | grep -v grep | awk \'{print $1"_"$4}\'', $nodeProcessDogecoinCryptocurrencyMiningProcesses);

		if (empty($nodeProcessDogecoinCryptocurrencyMiningProcesses[1]) === false) {
			$nodeProcessDogecoinCryptocurrencyMiningProcessIds = array();

			foreach ($nodeProcessDogecoinCryptocurrencyMiningProcesses as $nodeProcessDogecoinCryptocurrencyMiningProcess) {
				$nodeProcessDogecoinCryptocurrencyMiningProcess = explode('_', $nodeProcessDogecoinCryptocurrencyMiningProcess);

				if (($nodeProcessDogecoinCryptocurrencyMiningProcess[0] === $parameters['process_id']) === false) {
					$nodeProcessDogecoinCryptocurrencyMiningProcessIds[] = $nodeProcessDogecoinCryptocurrencyMiningProcess[0];
				}

				if (
					(empty($nodeProcessDogecoinCryptocurrencyMiningProcessInterval) === false) &&
					(($nodeProcessDogecoinCryptocurrencyMiningProcessInterval === $nodeProcessDogecoinCryptocurrencyMiningProcess[1]) === false) {
				) {
					_killProcessIds($parameters['binary_files'], $parameters['action'], $parameters['process_id'], $nodeProcessDogecoinCryptocurrencyMiningProcessIds);
					$response['message'] = 'Node process Dogecoin cryptocurrency mining processes processed successfully.';
					return $response;
				}

				$nodeProcessDogecoinCryptocurrencyMiningProcessInterval = $nodeProcessDogecoinCryptocurrencyMiningProcess[1];
			}

			$response['message'] = 'Node process Dogecoin cryptocurrency mining processes processed successfully.';
			return $response;
		}

		while (true) {
			$nodeProcessDogecoinCryptocurrencyMiningNextBlockHeight = file_get_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_next_block_height.dat');

			if ($nodeProcessDogecoinCryptocurrencyMiningNextBlockHeight === false) {
				$response['message'] = 'Error listing node process Dogecoin cryptocurrency mining block height, please try again.';
				return $response;
			}

			$nodeProcessDogecoinCryptocurrencyMiningBlockHeaderProcessIds = false;
			exec('ps -h -o pid -o cmd $(pgrep php) | grep node_action_process_node_process_dogecoin_cryptocurrency_mining_block_header.php | grep -v grep | grep -v _' . $nodeProcessDogecoinCryptocurrencyMiningNextBlockHeight . ' | awk \'{print $1}\'', $nodeProcessDogecoinCryptocurrencyMiningBlockHeaderProcessIds);

			if (empty($nodeProcessDogecoinCryptocurrencyMiningBlockHeaderProcessIds) === false) {
				_killProcessIds($parameters['binary_files'], $parameters['action'], $parameters['process_id'], $nodeProcessDogecoinCryptocurrencyMiningBlockHeaderProcessIds);
			}

			exec('ps -h -o etimes -o pid -o cmd $(pgrep php) | grep node_action_process_node_process_dogecoin_cryptocurrency_mining_block_header.php | grep -v grep | awk \'{print $1"_"$2}\'', $nodeProcessDogecoinCryptocurrencyMiningBlockHeaderProcesses);
			$nodeProcessDogecoinCryptocurrencyMiningBlockHeaderProcessIds = array();

			foreach ($nodeProcessDogecoinCryptocurrencyMiningBlockHeaderProcesses as $nodeProcessDogecoinCryptocurrencyMiningBlockHeaderProcess) {
				$nodeProcessDogecoinCryptocurrencyMiningBlockHeaderProcess = explode('_', $nodeProcessDogecoinCryptocurrencyMiningBlockHeaderProcess);

				if (($nodeProcessDogecoinCryptocurrencyMiningBlockHeaderProcess[0] > $nodeProcessDogecoinCryptocurrencyMiningProcessInterval) === true) {
					$nodeProcessDogecoinCryptocurrencyMiningBlockHeaderProcessIds[] = $nodeProcessDogecoinCryptocurrencyMiningBlockHeaderProcess[1];
				}
			}

			if (empty($nodeProcessDogecoinCryptocurrencyMiningBlockHeaderProcessIds) === false) {
				_killProcessIds($parameters['binary_files'], $parameters['action'], $parameters['process_id'], $nodeProcessDogecoinCryptocurrencyMiningBlockHeaderProcessIds);
			}

			sleep(1);
		}
	}

	if (($parameters['action'] === 'process_node_process_dogecoin_cryptocurrency_mining_processes') === true) {
		_processNodeProcessDogecoinCryptocurrencyMiningProcesses($parameters, $response);
	}
?>
