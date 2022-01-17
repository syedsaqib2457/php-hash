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
		$_4 = hash('sha256', ($_0[3] . $_0[4][$_2] . $_0[2] . $_3), true);
		$_4 = hash('sha256', $_4);
		// todo: validate hash data formatting using real .dat blocks (hex or dec)
		// todo: create 1 process for each CPU core with incremented timestamps instead of random timestamps from an array
		// todo: recreate block header more frequently for extranonce

		if (
			(($_4[12] === '0') === true) &&
			((($_4[0] . $_4[1] . $_4[2] . $_4[3] . $_4[4] . $_4[5]) === '000000') === true) &&
			((($_4[6] . $_4[7] . $_4[8] . $_4[9] . $_4[10] . $_4[11]) === '000000') === true)
		) {
			if (($_4 < $_0[1]) === true) {
				$_3 = hex2bin($_3);
				$_3 = strrev($_3);
				$_3 = bin2hex($_3);
				file_put_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.txt', ($_0[3] . $_0[4][$_2] . $_0[2] . $_3));
				exit;
			}
		}
	}
?>
