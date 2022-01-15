<?php
	$_0 = file_get_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_data.json');
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
		$_4 = hash('sha256', $_0[3] . $_0[4][$_2] . $_0[2] . $_3);
		$_4 = hash('sha256', $_4);

		if (
			(($_4[16] === '0') === true) &&
			(empty($_4[0] . $_4[1] . $_4[2] . $_4[3] . $_4[4] . $_4[5]) === true) &&
			(empty($_4[6] . $_4[7] . $_4[8] . $_4[9] . $_4[10]) === true) &&
			(empty($_4[11] . $_4[12] . $_4[13] . $_4[14] . $_4[15]) === true)
		) {
			if (($_4 < $_0[1]) === true) {
				file_put_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block.txt', $_0[3] . $_0[4][$_2] . $_0[2] . $_3);
				exit;
			}
		}
	}
?>
