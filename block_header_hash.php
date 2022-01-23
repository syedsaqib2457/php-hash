<?php
	// sha256 file to optimize hashing specifically for block header bytes
	// todo: remove this file and place code in https://github.com/nodecompute/nodecompute/blob/main/node_action_process_node_process_bitcoin_cash_cryptocurrency_mining_block_header.php in compressed format with no comments

	function _0($_0, $_1, $_2) {
		foreach ($_0 as $_3) {
			$_4 = $_1;

			for ($_5 = 0; $_5 < 16; $_5 += 4) {
				$_6 = (ord($_3[$_5]) << 24) + (ord($_3[($_5 + 1)]) << 16) + (ord($_3[($_5 + 2)]) << 8) + (ord($_3[($_5 + 3)]));
				
				// todo
			}
		}

		// todo
	}

	function _1($_0, $_1) {
		return ($_0 << (32 - $_1)) & 4294967295);
	}

	$_0 = hex2bin('010000000000000000000000000000000000000000000fffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff'); // version + current block header hash + merkle root hash
	$_0 = str_split($_0, 64);
	$_1 = "\x80\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\2\x80";
	$_2 = array(
		1779033703,
		3144134277,
		1013904242,
		2773480762,
		1359893119,
		2600822924,
		528734635,
		1541459225
	);
	$_3 = array(
		1116352408,
		1899447441,
		3049323471,
		3921009573,
		961987163,
		1508970993,
		2453635748,
		2870763221,
		3624381080,
		310598401,
		607225278,
		1426881987,
		1925078388,
		2162078206,
		2614888103,
		3248222580,
		3835390401,
		4022224774,
		264347078,
		604807628,
		770255983,
		1249150122,
		1555081692,
		1996064986,
		2554220882,
		2821834349,
		2952996808,
		3210313671,
		3336571891,
		3584528711,
		113926993,
		338241895,
		666307205,
		773529912,
		1294757372,
		1396182291,
		1695183700,
		1986661051,
		2177026350,
		2456956037,
		2730485921,
		2820302411,
		3259730800,
		3345764771,
		3516065817,
		3600352804,
		4094571909,
		275423344,
		430227734,
		506948616,
		659060556,
		883997877,
		958139571,
		1322822218,
		1537002063,
		1747873779,
		1955562222,
		2024104815,
		2227730452,
		2361852424,
		2428436474,
		2756734187,
		3204031479,
		3329325298
	);

	while (true) { // file execution time is limited instead of wasting resources checking conditions on each loop iteration
		$_0[1] .= '111111112222222233333333'; // timestamp + bits + nonce placeholders
		$_0[1] .= $_1; // fast 512-bit padding for each iteration
		$_4 = _0($_0, $_2, $_3);
	}
?>
