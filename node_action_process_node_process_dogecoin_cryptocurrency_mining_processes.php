<?php
	exec('ps -h -o pid -o cmd $(pgrep php) | grep process_node_process_dogecoin_cryptocurrency_mining_processes | grep -v grep | awk \'{print $1"_"$4}\'', $nodeProcessDogecoinCryptocurrencyMiningProcesses);

	if (empty($nodeProcessDogecoinCryptocurrencyMiningProcesses[1]) === false) {
		$nodeProcessDogecoinCryptocurrencyMiningProcessIds = array();

		foreach ($nodeProcessDogecoinCryptocurrencyMiningProcesses as $nodeProcessDogecoinCryptocurrencyMiningProcess) {
			$nodeProcessDogecoinCryptocurrencyMiningProcess = explode('_', $nodeProcessDogecoinCryptocurrencyMiningProcess);

			if (($nodeProcessDogecoinCryptocurrencyMiningProcess[0] === $parameters['process_id']) === false) {
				$nodeProcessDogecoinCryptocurrencyMiningProcessIds[] = $nodeProcessDogecoinCryptocurrencyMiningProcess[0];
			}

			$nodeProcessDogecoinCryptocurrencyMiningProcessInterval = $nodeProcessDogecoinCryptocurrencyMiningProcess[1];
		}

		// terminate current process if PoW interval changes
		return $response;
	}

	while (true) {
		exec('ps -h -o etime -o pid $(pgrep php) | grep node_action_process_node_process_dogecoin_cryptocurrency_mining_proof_of_work.php | grep -v grep | awk \'{print $1}\'', $nodeProcessDogecoinCryptocurrencyMiningProofOfWorkProcesses);
		// todo
		sleep(1);
	}

	// todo
?>
