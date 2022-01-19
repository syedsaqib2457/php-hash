<?php
	if (file_exists('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.dat') === true) {
		exit;
	}

	$_0 = file_get_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.json');
	$_0 = json_decode($_0, true);

	if ($_0 === false) {
		exit;
	}

	$_0[1] = hex2bin($_0[1]);
	$_0[2] = hex2bin($_0[2]);

	while (true) {
		$_1 = hrtime(true);
		$_1 = substr($_1, 4, 8);
		$_1 = hex2bin($_1);

		foreach ($_0[3] as $_2) {
			$_3 = hash('sha256', ($_0[2] . $_2 . $_0[1] . $_1), true);
			$_3 = hash('sha256', $_3);

			if (
				(($_3[51] === '0') === true) &&
				((($_3[63] . $_3[62] . $_3[61] . $_3[60] . $_3[59] . $_3[58]) === '000000') === true) &&
				((($_3[57] . $_3[56] . $_3[55] . $_3[54] . $_3[53] . $_3[52]) === '000000') === true)
			) {
				$_3 = hex2bin($_3);
				$_3 = strrev($_3);
				$_3 = bin2hex($_3);

				if (($_3 < $_0[0]) === true) {
					$_3 = bin2hex($_0[2] . $_2 . $_0[1] . $_1);
					file_put_contents('/usr/local/ghostcompute/node_process_dogecoin_cryptocurrency_mining_block_header.dat', $_3);
					exit;
				}
			}
		}
	}
?>
