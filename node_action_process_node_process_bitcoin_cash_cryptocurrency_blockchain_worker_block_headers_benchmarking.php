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

	$_0[4] = hex2bin($_0[4]);

	foreach ($_0[5] as $_1 => $_2) {
		$_0[5][$_1] = hex2bin($_2);
	}

	$_1 = 0;

	while (true) {
		$_2 = hrtime(true);
		$_2 = substr($_2, 5, 8);
		$_2 = hex2bin($_2);

		foreach ($_0[0] as $_3) {
			foreach ($_0[5] as $_4) {
				$_5 = hash('sha256', ($_3 . $_4 . $_0[4] . $_2), true);
				$_5 = hash('sha256', $_5);

				if (
					(($_5[48] === '0') === true) &&
					((($_5[63] . $_5[62] . $_5[61] . $_5[60] . $_5[59]) === '00000') === true) &&
					((($_5[58] . $_5[57] . $_5[56] . $_5[55] . $_5[54]) === '00000') === true) &&
					((($_5[53] . $_5[52] . $_5[51] . $_5[50] . $_5[49]) === '00000') === true)
				) {
					continue;
				}
			}
		}

		// todo
	}
?>
