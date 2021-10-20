<?php
	if (empty($_SERVER['argv'][3]) === true) {
		// php node_action_process_node_user_blockchain_mining.php [type] [wallet_address or public_key from node_user authentication_username] 10
			// build block header, manage indexed sections, etc
	} else {
		// php node_action_process_node_user_blockchain_mining.php [type] [block_header] [min_nonce] [max_nonce] [leading_zero_count] [process_index]
			// mine indexed section for pseudo-threading

		switch ($_SERVER['argv'][0]) {
			case: 'bitcoin':
				// process hashing for indexed nonce range, then exit
				break;
		}

		exit;
	}

	// when a valid hash is found, send block hash value by starting node_process_blockchain_building with successful hash as a parameter, log response + hash details to /usr/src/ghostcompute

	// todo: start this file in background for each blockchain_type from node_process_processes.php
	// todo: install/remove CLI files for blockchain types in node_process_processes.php with binary paths if non-existing
	// todo: retrieve block header values for mining based on 1-minute crontab interval for process_node_processes, restart process_blockchain_building.php when prev block changes from value stored in cache
	// todo: split nonce min-max values into processes, pass details as params to node_process_blockchain_mining.php
		// try different micro-optimizations (for vs foreach vs while) (multiple hash attempts in single iteration for multiples of increment), (alternatives to hash_hmac), (custom binary sha256 hashing), etc
			// only use variables when absolutely required, unset unused variables + flush + gc
			// validate (block < header) using leading 0 count with an additional 0 (improves performance while a successful block with have a better chance of being the best block)
				// use php's ($hash[$index] === 0)
			// avoid nested loops for mining only
		// allow user-defined maximum CPU % and maximum MEM % for each blockchain_type
		// log number of parts optimal for user-defined maximum CPU % and maximum MEM % to allocate for mining (allows safe deployment on existing under-utilized machines without overloading existing processes)
			// start with low number of processes, increase until maximum % is reached
		// try submitting blocks with leading zero nonces to see if it fails validation
?>
