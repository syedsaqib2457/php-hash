<?php
	exec('ps -h -o pid -o cmd $(pgrep php) | grep "node_action_process_node_process_dogecoin_cryptocurrency_mining_processes.php" | awk \'{print $1}\'', $nodeProcessDogecoinCryptocurrencyMiningProcessIds);

	if (empty($nodeProcessDogecoinCryptocurrencyMiningProcessIds[1]) === false) {
		exit;
	}

	while (true) {
		exec('ps -h -o etime $(pgrep php) | grep "node_action_process_node_process_dogecoin_cryptocurrency_mining_proof_of_work.php" | awk \'{print $1}\'', $nodeProcessDogecoinCryptocurrencyMiningProofOfWorkProcessIds);
		// todo
		sleep(1);
	}

	// todo
?>
