<?php
	if (
		(empty($_SERVER['argv'][1]) === true) ||
		(file_exists('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_worker_block_header.dat') === true)
	) {
		exit;
	}

	$_0 = file_get_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_blockchain_worker_block_headers_' . $_SERVER['argv'][1] . '.json');
	$_0 = json_decode($_0, true);
	// todo: add increment count for hashes per second performance

	if ($_0 === false) {
		exit;
	}

	foreach ($_0[0] as $_1 => $_2) {
		$_0[0][$_1] = hex2bin($_2);
	}

	foreach ($_0[3] as $_1 => $_2) {
		$_0[3][$_1] = hex2bin($_2);
	}

	while (true) {
		$_1 = hrtime(true);
		$_1 = substr($_1, 5, 9);
		$_1 = hex2bin($_1);

		foreach ($_0[0] as $_2) {
			foreach ($_0[3] as $_3) {
				$_4 = hash('sha256', ($_2 . $_3 . $_0[2] . $_1), true);
				$_4 = hash('sha256', $_4);

				if (
					(($_4[48] === '0') === true) &&
					((($_4[63] . $_4[62] . $_4[61] . $_4[60] . $_4[59]) === '00000') === true) &&
					((($_4[58] . $_4[57] . $_4[56] . $_4[55] . $_4[54]) === '00000') === true) &&
					((($_4[53] . $_4[52] . $_4[51] . $_4[50] . $_4[49]) === '00000') === true)
				) {
					$_4 = hex2bin($_4);
					$_4 = strrev($_4);
					$_4 = bin2hex($_4);

					if (($_4 < $_0[1]) === true) {
						$_4 = bin2hex($_2 . $_3 . $_0[2] . $_1);
						file_put_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_worker_block_header.dat', $_4);
						exit;
					}
				}
			}
		}
	}
?>
