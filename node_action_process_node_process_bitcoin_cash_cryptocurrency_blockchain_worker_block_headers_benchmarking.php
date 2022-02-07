<?php
	if (empty($_SERVER['argv'][1]) === true) {
		exit;
	}

	$_0 = file_get_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_blockchain_worker_block_headers_data_1.json');
	$_0 = json_decode($_0, true);

	if ($_0 === false) {
		exit;
	}

	foreach ($_0[0] as $_1 => $_2) {
		$_0[0][$_1] = hex2bin($_2);
	}

	$_1 += 1;
	$_0[4] = hex2bin($_0[4]);

	foreach ($_0[5] as $_2 => $_3) {
		$_0[5][$_1] = hex2bin($_2);
	}

	$_2 += 1;
	$_3 = $_4 = 0;

	while (true) {
		$_5 = hrtime(true);
		$_5 = substr($_5, 5, 8);
		$_5 = hex2bin($_5);

		foreach ($_0[0] as $_6) {
			foreach ($_0[5] as $_7) {
				$_8 = hash('sha256', ($_6 . $_7 . $_0[4] . $_5), true);
				$_8 = hash('sha256', $_8);

				if (
					(($_8[48] === '0') === true) &&
					((($_8[63] . $_8[62] . $_8[61] . $_8[60] . $_8[59]) === '00000') === true) &&
					((($_8[58] . $_8[57] . $_8[56] . $_8[55] . $_8[54]) === '00000') === true) &&
					((($_8[53] . $_8[52] . $_8[51] . $_8[50] . $_8[49]) === '00000') === true)
				) {
					continue;
				}
			}
		}

		$_3++;

		if (($_3 === 10000) === true) {
			$_3 = 0;
			// $_4 += ; // todo
		}
	}
?>
