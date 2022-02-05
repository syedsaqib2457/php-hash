<?php
	if (
		(empty($_SERVER['argv'][1]) === true) ||
		(file_exists('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_worker_valid_block_header_data.json') === true)
	) {
		exit;
	}

	$_0 = file_get_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_blockchain_worker_block_headers_' . $_SERVER['argv'][1] . '.json');
	$_0 = json_decode($_0, true);
	// todo: add node_action_process_node_process_bitcoin_cash_cryptocurrency_blockchain_worker_block_header_benchmarking.php for estimating hashes per second performance with increment count
	// todo: add process to send node_process_bitcoin_cash_cryptocurrency_worker_valid_block_header_data to cryptocurrency blockchain node
	// todo: add process to add received node_process_bitcoin_cash_cryptocurrency_worker_valid_block_header_data block with cli

	if ($_0 === false) {
		exit;
	}

	foreach ($_0[0] as $_1 => $_2) {
		$_0[0][$_1] = hex2bin($_2);
	}

	$_0[3] = hex2bin($_0[3]);

	foreach ($_0[4] as $_1 => $_2) {
		$_0[4][$_1] = hex2bin($_2);
	}

	while (true) {
		$_1 = hrtime(true);
		$_1 = substr($_1, 5, 8);
		$_1 = hex2bin($_1);

		foreach ($_0[0] as $_2) {
			foreach ($_0[4] as $_3) {
				$_4 = hash('sha256', ($_2 . $_3 . $_0[3] . $_1), true);
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

					if (($_4 < $_0[2]) === true) {
						$_4 = bin2hex($_2 . $_3 . $_0[3] . $_1);
						$_4 = array(
							$_4,
							$_0[1]
						);
						file_put_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_worker_valid_block_header_data.json', $_4);
						exit;
					}
				}
			}
		}
	}
?>
