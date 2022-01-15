<?php
	// todo: manage process execution by block height with another process to bypass PHP.ini restrictions (set_time_limit doesn't always work)
	// php node_action_process_node_process_dogecoin_cryptocurrency_mining_proof_of_work.php [block_height] [arbitrary random string to allow duplicate processes]
	// todo: list mining block data from file
	// todo: random 4-byte nonce if nonce range is 00000000ffffffff
	// todo: numeric indexes for mining block data to optimize duplicate processes

	$_0 = file_get_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_data.json');
	$_0 = json_decode($_0, true);

	if ($_0 === false) {
		exit;
	}

	end($_0[4]);
	$_1 = key($_0[4]);

	while (true) {
		$_2 = mt_rand(0, $_1);
		$_3 = hash('sha256', $_0[3] . $_0[4][$_2] . $_0[2]);
		$_3 = hash('sha256', $_3); // add random bytes nonce

		if (
			(($_3[16] === '0') === true) &&
			(empty($_3[0] . $_3[1] . $_3[2] . $_3[3] . $_3[4] . $_3[5]) === true) &&
			(empty($_3[6] . $_3[7] . $_3[8] . $_3[9] . $_3[10]) === true) &&
			(empty($_3[11] . $_3[12] . $_3[13] . $_3[14] . $_3[15]) === true)
		) {
			if (($_3 < $_0[1]) === true) {
				// todo: save valid block header with concatenated nonce + bits to a file for submitblock
				exit;
			}
		}
	}
?>
