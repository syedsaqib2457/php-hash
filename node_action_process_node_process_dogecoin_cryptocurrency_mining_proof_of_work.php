<?php
	/* foreach ($_SERVER['argv'] as $key => $value) {
		$variable = '_' . $key;
		$$variable = $value;
	} */

	// todo: manage process execution by block height with another process to bypass PHP.ini restrictions (set_time_limit doesn't always work)
	// php node_action_process_node_process_dogecoin_cryptocurrency_mining_proof_of_work.php [block_height] [arbitrary random string to allow duplicate processes]
	// todo: list mining block data from file
	// todo: random 4-byte nonce if nonce range is 00000000ffffffff
	// todo: numeric indexes for mining block data to optimize duplicate processes

	while (true) {
		$_0 = hash('sha256', hash('sha256', $_1 . $_2)); // concatenate timestamps

		if (
			(($_0[16] === '0') === true) &&
			(empty($_0[0] . $_0[1] . $_0[2] . $_0[3] . $_0[4] . $_0[5]) === true) &&
			(empty($_0[6] . $_0[7] . $_0[8] . $_0[9] . $_0[10]) === true) &&
			(empty($_0[11] . $_0[12] . $_0[13] . $_0[14] . $_0[15]) === true)
		) {
			if (($_0 < $_4) === true) { // $_4 is target hash
				// todo: save valid block header with concatenated nonce + bits to a file for submitblock
				exit;
			}
		}
	}
?>
