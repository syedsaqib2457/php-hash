<?php
	if (file_exists('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.txt') === true) {
		exit;
	}

	$_0 = file_get_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.json');
	$_0 = json_decode($_0, true);

	if ($_0 === false) {
		exit;
	}

	while (true) {
		$_1 = hrtime(true);
		$_1 = substr($_1, 5, 8);
		$_2 = (timestamp() - $_0[2]); // encode to hex
		$_3 = hex2bin($_0[3] . $_1 . $_0[1] . $_2);
		$_4 = hash('sha256', $_3, true);
		$_4 = hash('sha256', $_4);

		if (
			(($_4[12] === '0') === true) &&
			((($_4[0] . $_4[1] . $_4[2] . $_4[3] . $_4[4] . $_4[5]) === '000000') === true) &&
			((($_4[6] . $_4[7] . $_4[8] . $_4[9] . $_4[10] . $_4[11]) === '000000') === true)
		) {
			if (($_4 < $_0[0]) === true) {
				file_put_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.txt', ($_0[3] . $_1 . $_0[1] . $_2));
				exit;
			}
		}
	}
?>
