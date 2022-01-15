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
					return $response;
				}

				$nodeProcessDogecoinCryptocurrencyMiningProcessInterval = $nodeProcessDogecoinCryptocurrencyMiningProcess[1];
			}

			return $response;
		}

		while (true) {
			exec('ps -h -o etime -o pid -o cmd $(pgrep php) | grep node_action_process_node_process_dogecoin_cryptocurrency_mining_proof_of_work.php | grep -v grep | awk \'{print $1"_"$4"_"$6}\'', $nodeProcessDogecoinCryptocurrencyMiningProofOfWorkProcesses);

			foreach ($nodeProcessDogecoinCryptocurrencyMiningProofOfWorkProcesses as $nodeProcessDogecoinCryptocurrencyMiningProofOfWorkProcess) {
				$nodeProcessDogecoinCryptocurrencyMiningProofOfWorkProcess = explode('_', $nodeProcessDogecoinCryptocurrencyMiningProofOfWorkProcess);
				// todo: terminate proof of work processes exceeding interval
			}

			sleep(1);
		}
	}

	if (($parameters['action'] === 'process_node_process_dogecoin_cryptocurrency_mining_processes') === true) {
		_processNodeProcessDogecoinCryptocurrencyMiningProcesses($parameters, $response);
	}
?>
