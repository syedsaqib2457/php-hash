<?php
	if (file_exists('/usr/local/ghostcompute/node_process_bitcoin_cash_cryptocurrency_mining_block_header.dat') === true) {
		exit;
	}

	$_0 = file_get_contents('/usr/local/ghostcompute/node_process_bitcoin_cash_cryptocurrency_mining_block_header.json');
	$_0 = json_decode($_0, true);

	if ($_0 === false) {
		exit;
	}

	$_0[1] = hex2bin($_0[1]);
	$_0[4] = hex2bin($_0[4]);
	$_1 = range($_0[2], $_0[3]);

	foreach ($_1 as $_2 => $_3) {
		$_3 = dechex($_3);
		$_3 = str_pad($_3, 8, '0', STR_PAD_LEFT);
		$_3 = hex2bin($_3);
		$_1[$_2] = strrev($_3);
	}

	while (true) {
		$_2 = hrtime(true);
		$_2 = substr($_2, 4, 8);
		$_2 = hex2bin($_2);

		foreach ($_1 as $_3) {
			$_4 = hash('sha256', ($_0[4] . $_3 . $_0[1] . $_2), true);
			$_4 = hash('sha256', $_4);

			if (
				(($_4[46] === '0') === true) &&
				((($_4[63] . $_4[62] . $_4[61] . $_4[60] . $_4[59]) === '000000') === true) &&
				((($_4[58] . $_4[57] . $_4[56] . $_4[55] . $_4[54] . $_4[53]) === '000000') === true) &&
				((($_4[52] . $_4[51] . $_4[50] . $_4[49] . $_4[48] . $_4[47]) === '000000') === true)
			) {
				$_4 = hex2bin($_4);
				$_4 = strrev($_4);
				$_4 = bin2hex($_4);

				if (($_4 < $_0[0]) === true) {
					$_4 = bin2hex($_0[4] . $_3 . $_0[1] . $_2);
					file_put_contents('/usr/local/ghostcompute/node_process_bitcoin_cash_cryptocurrency_mining_block_header.dat', $_4);
					exit;
				}
			}
		}
	}
?>
