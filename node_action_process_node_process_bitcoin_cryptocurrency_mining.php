<?php
	// todo: manage crontab execution without node endpoint from process_node_processes
	// todo: figure out all the confusing block creation RPC shit from the BIP documentation without an existing PHP library
	// todo: use different timestamps for each node process process instead of index
	// todo: span entire nonce range in maximum 5 seconds with staggered sleep() instead of costly x < y in for loop

	if (empty($_SERVER['argv'][1]) === true) {
		exit;
	}

	foreach ($_SERVER['argv'] as $key => $value) {
		$variable = '_' . $key;
		$$variable = $value;
	}

	sleep($_4);
	set_time_limit(5);

	// php node_action_process_node_process_bitcoin_cryptocurrency_mining.php [block_header] [min_nonce] [max_nonce] [target_string] [sleep_interval]
		// mine indexed section for pseudo-threading

	$_2 = intval($_2);
	$_3 = intval($_3);

	for ($_2; true; $_2 += 2) {
		$_0 = hash('sha256', hash_hmac('sha256', $_1, $_2));

		if (
			(($_0[16] === '0') === true) &&
			(empty($_0[0] . $_0[1] . $_0[2] . $_0[3] . $_0[4] . $_0[5]) === true) &&
			(empty($_0[6] . $_0[7] . $_0[8] . $_0[9] . $_0[10]) === true) &&
			(empty($_0[11] . $_0[12] . $_0[13] . $_0[14] . $_0[15]) === true)
		) {
			if (($_0 < $_4) === true) {
				break;
			}
		}

		$_0 = hash('sha256', hash_hmac('sha256', $_1, ($_2 + 1)));

		if (
			(($_0[16] === '0') === true) &&
			(empty($_0[0] . $_0[1] . $_0[2] . $_0[3] . $_0[4] . $_0[5]) === true) &&
			(empty($_0[6] . $_0[7] . $_0[8] . $_0[9] . $_0[10]) === true) &&
			(empty($_0[11] . $_0[12] . $_0[13] . $_0[14] . $_0[15]) === true)
		) {
			if (($_0 < $_4) === true) {
				break;
			}
		}
	}

	echo 'Block mined successfully: ' . $_0 . "\n";
	// todo: save hash to /tmp file for processing
?>
