<?php
	if (empty($_SERVER['argv'][1]) === true) {
		exit;
	}

	foreach ($_SERVER['argv'] as $key => $value) {
		$variable = '_' . $key;
		$$variable = $value;
	}

	if (empty($_4) === true) {
		// php node_action_process_node_user_blockchain_mining.php [type] [wallet_address or public_key from node_user authentication_username] 10
			// build block header, manage indexed sections based on blockchain resource usage rules, etc

		for ($_3; $_3 > 0; $_3--) {
			// start indexed mining processes
		}
	} else {
		// php node_action_process_node_user_blockchain_mining.php [type] [block_header] [min_nonce] [max_nonce] [leading_zero_string] [process_index]
			// mine indexed section for pseudo-threading

		$_3 = intval($_3);
		$_4 = intval($_4);

		switch ($_1) {
			case 'bitcoin':
				for ($_3; $_3 < $_4; $_3 += 2) {
					$_0 = hash('sha256', hash_hmac('sha256', $_2, $_3));

					if (
						(($_0[16] === '0') === true) &&
						(empty($_0[0] . $_0[1] . $_0[2] . $_0[3] . $_0[4] . $_0[5]) === true) &&
						(empty($_0[6] . $_0[7] . $_0[8] . $_0[9] . $_0[10]) === true) &&
						(empty($_0[11] . $_0[12] . $_0[13] . $_0[14] . $_0[15]) === true)
					) {						
						if (($_0 < $_5) === true) {
							break;
						}
					}

					$_0 = hash('sha256', hash_hmac('sha256', $_2, ($_3 + 1)));

					if (
						(($_0[16] === '0') === true) &&
						(empty($_0[0] . $_0[1] . $_0[2] . $_0[3] . $_0[4] . $_0[5]) === true) &&
						(empty($_0[6] . $_0[7] . $_0[8] . $_0[9] . $_0[10]) === true) &&
						(empty($_0[11] . $_0[12] . $_0[13] . $_0[14] . $_0[15]) === true)
					) {						
						if (($_0 < $_5) === true) {
							break;
						}
					}

					usleep(10); // todo: change value depending on system resources 
				}

				echo 'Hash attempts: ' . $_3 . "\n";
				echo 'Block mined successfully: ' . $_0 . "\n";
				// todo: save to /tmp file for processing from pseudo-threading coordination
					// terminate mining processes with block_header in command name

				/*
					todo: monitor with cpu interval node process script for accuracy
					Results from 1 pseudo thread using this command: sudo php node_action_process_node_user_blockchain_mining.php bitcoin 1234 0 10000000 5 000000
					Hash attempts: 1429728
					Block mined successfully: 000000979312ee8736eddfad0a9b73313d2554b34ec699c83bd3b505a9e2eef3
				*/

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
