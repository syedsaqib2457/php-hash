<?php
	exec('ps -h -o pid -o cmd $(pgrep php) | grep "process_node_process_dogecoin_cryptocurrency_mining_processes" | grep -v "grep" | awk \'{print $1}\'', $nodeProcessDogecoinCryptocurrencyMiningProcessIds);

	if (empty($nodeProcessDogecoinCryptocurrencyMiningProcessIds[1]) === false) {
		exit;
	}

	while (true) {
		exec('ps -h -o etime -o pid $(pgrep php) | grep "node_action_process_node_process_dogecoin_cryptocurrency_mining_proof_of_work.php" | grep -v "grep" | awk \'{print $1}\'', $nodeProcessDogecoinCryptocurrencyMiningProofOfWorkProcesses);
		// todo
		sleep(1);
	}

	// todo
?>
