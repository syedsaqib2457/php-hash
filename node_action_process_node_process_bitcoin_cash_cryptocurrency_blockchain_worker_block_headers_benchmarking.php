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
	$_5 = time();

	while (true) {
		$_6 = hrtime(true);
		$_6 = substr($_6, 5, 8);
		$_6 = hex2bin($_6);

		foreach ($_0[0] as $_7) {
			foreach ($_0[5] as $_8) {
				$_9 = hash('sha256', ($_7 . $_8 . $_0[4] . $_6), true);
				$_9 = hash('sha256', $_9);

				if (
					(($_9[48] === '0') === true) &&
					((($_9[63] . $_9[62] . $_9[61] . $_9[60] . $_9[59]) === '00000') === true) &&
					((($_9[58] . $_9[57] . $_9[56] . $_9[55] . $_9[54]) === '00000') === true) &&
					((($_9[53] . $_9[52] . $_9[51] . $_9[50] . $_9[49]) === '00000') === true)
				) {
					continue;
				}
			}
		}

		if ((($_5 + 10) < time()) === true) {
			$_10 = array(
				(time() - $_5),
				$_2,
				$_4
			);
			$_10 = json_encode($_10);
			file_put_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_blockchain_worker_block_headers_benchmarking_data.json', $_10);
			exit;
		}

		$_3++;

		if (($_3 === 1000000) === true) {
			$_3 = 1;
			$_4++;
		}
	}
?>
