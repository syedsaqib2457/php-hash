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
		$_3 = hrtime(true);
		$_3 = substr($_3, 6, 8);
		$_4 = (timestamp() - $_0[2]);
		$_5 = hex2bin($_0[3] . $_4 . $_0[1] . $_3);
		$_6 = hash('sha256', $5, true);
		$_6 = hash('sha256', $_6);

		if (
			(($_6[12] === '0') === true) &&
			((($_6[0] . $_6[1] . $_6[2] . $_6[3] . $_6[4] . $_6[5]) === '000000') === true) &&
			((($_6[6] . $_6[7] . $_6[8] . $_6[9] . $_6[10] . $_6[11]) === '000000') === true)
		) {
			if (($_5 < $_0[0]) === true) {
				file_put_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.txt', ($_0[3] . $_4 . $_0[1] . $_3));
				exit;
			}
		}
	}
?>
