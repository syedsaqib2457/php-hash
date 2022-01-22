<?php
	// sha256 file to optimize hashing specifically for block header bytes
	// todo: remove this file and place code in https://github.com/nodecompute/nodecompute/blob/main/node_action_process_node_process_bitcoin_cash_cryptocurrency_mining_block_header.php in compressed format

	function hashcash($string) {
		// todo: add 2 string parameters to prevent str_split overhead since there are always 1024 bits
		$string .= "\x80\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\2\x80";
		// todo
	}

	$blockHeaderString = hex2bin('010000000000000000000000000000000000000000000fffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff');
	$response = hashcash($blockHeaderString);
?>
