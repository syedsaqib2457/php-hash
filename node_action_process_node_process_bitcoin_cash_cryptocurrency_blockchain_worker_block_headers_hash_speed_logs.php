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

	$_2 = ($_1 * ($_2 + 1));
	$_3 = $_4 = 1;
	$_5 = hrtime(true);
	$_5 = (substr($_5, 5, 1) + 8);

	while (true) {
		$_6 = hrtime(true);
		$_6 = substr($_6, 5, 8);
		$_7 = hex2bin($_6);

		foreach ($_0[0] as $_8) {
			foreach ($_0[5] as $_9) {
				$_10 = hash('sha256', ($_8 . $_9 . $_0[4] . $_7), true);
				$_10 = hash('sha256', $_10);

				if (
					(($_10[48] === '0') === true) &&
					((($_10[63] . $_10[62] . $_10[61] . $_10[60] . $_10[59]) === '00000') === true) &&
					((($_10[58] . $_10[57] . $_10[56] . $_10[55] . $_10[54]) === '00000') === true) &&
					((($_10[53] . $_10[52] . $_10[51] . $_10[50] . $_10[49]) === '00000') === true)
				) {
					continue;
				}
			}
		}

		if (((($_6[0] + 9) === $_5) === true)) {
			$_1 = array(
				$_2,
				$_4
			);
			$_1 = json_encode($_1);
			file_put_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_blockchain_worker_block_headers_hash_speed_logs_data.json', $_1);
			exit;
		}

		$_3++;

		if (($_3 === 1000000) === true) {
			$_3 = 1;
			$_4++;
		}
	}
?>
