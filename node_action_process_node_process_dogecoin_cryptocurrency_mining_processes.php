<?php
	exec('ps -h -o pid -o cmd $(pgrep php) | grep "node_action_process_node_process_dogecoin_cryptocurrency_mining_processes.php" | awk \'{print $1}\'', $nodeProcessDogecoinCryptocurrencyMiningProcessIds);

	if (empty($nodeProcessDogecoinCryptocurrencyMiningProcessIds[1]) === false) {
		exit;
	}

	// todo
?>
