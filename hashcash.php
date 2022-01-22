<?php
	// sha256 file to optimize hashing specifically for block header bytes
	// todo: remove this file and place code in https://github.com/nodecompute/nodecompute/blob/main/node_action_process_node_process_bitcoin_cash_cryptocurrency_mining_block_header.php in compressed format

	function blockHeaderHash($blockHeaderString, $blockHeaderHashStringPadding) {
		// bit counts are static 640 for block header + 512 for concatenated merkle root hashes
		$hexidecimalHashConstants = array(
			'6a09e667',
			'bb67ae85',
			'3c6ef372',
			'a54ff53a',
			'510e527f',
			'9b05688c',
			'1f83d9ab',
			'5be0cd19'
		);
		// todo: add 2 string parameters to prevent str_split overhead since there are always 1024 bits
		$blockHeaderString .= $blockHeaderHashStringPadding;
		// todo
	}

	$blockHeaderString = hex2bin('010000000000000000000000000000000000000000000fffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff');
	$blockHeaderHashStringPadding = "\x80\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\2\x80";
	$response = blockHeaderHash($blockHeaderString, $blockHeaderHashStringPadding);
?>
