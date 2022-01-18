<?php
	if (file_exists('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.txt') === true) {
		exit;
	}

	$_0 = file_get_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.json');
	$_0 = json_decode($_0, true);

	if ($_0 === false) {
		exit;
	}

	end($_0[4]);
	$_1 = key($_0[4]);

	while (true) {
		$_2 = mt_rand(0, $_1);
		$_3 = random_bytes(4);
		$_3 = bin2hex($_3);
		$_4 = hex2bin($_0[2] . $_0[4][$_2] . $_0[1] . $_3);
		$_5 = hash('sha256', $4, true);
		$_5 = hash('sha256', $_5);
		// todo: create function to increment timestamp + nonce hex strings instead of using random bytes or converting to decimal
		// todo: recreate block header more frequently for extranonce

		if (
			(($_5[12] === '0') === true) &&
			((($_5[0] . $_5[1] . $_5[2] . $_5[3] . $_5[4] . $_5[5]) === '000000') === true) &&
			((($_5[6] . $_5[7] . $_5[8] . $_5[9] . $_5[10] . $_5[11]) === '000000') === true)
		) {
			if (($_5 < $_0[0]) === true) {
				file_put_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.txt', ($_0[2] . $_0[4][$_2] . $_0[1] . $_3));
				exit;
			}
		}
	}
?>
