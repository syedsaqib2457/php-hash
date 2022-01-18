<?php
	if (file_exists('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.dat') === true) {
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
		$_1 = hex2bin($_1);

		foreach ($_0[3] as $_2) {
			$_3 = hash('sha256', ($_0[2] . $_2 . $_0[1] . $_1), true);
			$_3 = hash('sha256', $_3);

			if (
				(($_3[12] === '0') === true) &&
				((($_3[0] . $_3[1] . $_3[2] . $_3[3] . $_3[4] . $_3[5]) === '000000') === true) &&
				((($_3[6] . $_3[7] . $_3[8] . $_3[9] . $_3[10] . $_3[11]) === '000000') === true)
			) {
				$_3 = hex2bin($_3);
				$_3 = strrev($_3);
				$_3 = bin2hex($_3);

				if (($_3 < $_0[0]) === true) {
					file_put_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.dat', ($_0[2] . $_2 . $_0[1] . $_1));
					exit;
				}
			}
		}
	}
?>
