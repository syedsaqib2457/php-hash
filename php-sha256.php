<?php
	function _sha256($_0) {
		if (function_exists('_0') === false) {
			function _0($_0, $_1) {
				if (($_0 < 0) === true) {
					$_0 = (($_0 & 2147483647) + 2147483648);
				}

				if (($_1 < 0) === true) {
					$_1 = (($_1 & 2147483647) + 2147483648);
				}

				$_0 = ($_0 + $_1);

				while (($_0 >= 4294967296) === true) {
					$_0 -= 4294967296;
				}

				return $_0;
			}

			function _1($_0, $_1) {
				return (_2($_0, $_1) | ($_0 << (32 - $_1)) & 4294967295);
			}

			function _2($_0, $_1) {
				if (($_0 < 0) === true) {
					$_0 &= 2147483647;
					return (($_0 >> $_1) | (1073741824 >> ($_1 - 1)));
				}

				return ($_0 >> $_1);
			}

			function _3($_0) {
				return (_1($_0, 6) ^ _1($_0, 11) ^ _1($_0, 25));
			}

			function _4($_0, $_1, $_2) {
				return (($_0 & $_1) ^ ((~$_0) & $_2));
			}

			function _5($_0) {
				return (_1($_0, 2) ^ _1($_0, 13) ^ _1($_0, 22));
			}

			function _6($_0, $_1, $_2) {
				return (($_0 & $_1) ^ (($_0 & $_2) ^ ($_1 & $_2)));
			}

			function _7($_0) {
				return (_1($_0, 7) ^ _1($_0, 18) ^ _2($_0, 3));
			}

			function _8($_0) {
				return (_1($_0, 17) ^ _1($_0, 19) ^ _2($_0, 10));
			}
		}

		$_1 = (strlen($_0) * 8);
		$_1 = dechex($_1);
		$_2 = strlen($_1);

		if (boolval($_2 & 1) === true) {
			$_1 = '0' . $_1;
			$_2++;
		}

		$_1 = hex2bin($_1);
		$_2 = (8 - strlen($_1));

		if (($_2 < 0) === true) {
			return false;
		}

		while (($_2 === 0) === false) {
			$_1 = "\0" . $_1;
			$_2--;
		}

		$_0 .= "\x80";
		$_0 = str_split($_0, 64);
		end($_0);
		$_2 = key($_0);
		$_3 = (64 - strlen($_0[$_2]));

		if (($_3 < 8) === true) {
			while (($_3 === 0) === false) {
				$_0[$_2] .= "\0";
				$_3--;
			}

			$_2++;
			$_0[$_2] = '';
			$_3 = 64;
		}

		while (($_3 === 8) === false) {
			$_0[$_2] .= "\0";
			$_3--;
		}

		$_0[$_2] .= $_1;
		$_1 = array(
			1779033703, 3144134277, 1013904242, 2773480762, 1359893119, 2600822924, 528734635, 1541459225
		);
		$_2 = array(
			1116352408, 1899447441, 3049323471, 3921009573, 961987163, 1508970993, 2453635748, 2870763221,
			3624381080, 310598401, 607225278, 1426881987, 1925078388, 2162078206, 2614888103, 3248222580,
			3835390401, 4022224774, 264347078, 604807628, 770255983, 1249150122, 1555081692, 1996064986,
			2554220882, 2821834349, 2952996808, 3210313671, 3336571891, 3584528711, 113926993, 338241895,
			666307205, 773529912, 1294757372, 1396182291, 1695183700, 1986661051, 2177026350, 2456956037,
			2730485921, 2820302411, 3259730800, 3345764771, 3516065817, 3600352804, 4094571909, 275423344,
			430227734, 506948616, 659060556, 883997877, 958139571, 1322822218, 1537002063, 1747873779,
			1955562222, 2024104815, 2227730452, 2361852424, 2428436474, 2756734187, 3204031479, 3329325298
		);

		foreach ($_0 as $_3) {
			$_4 = $_1;
			$_5 = array();

			for ($_6 = 0; $_6 < 16; $_6++) {
				$_7 = _3($_4[4]);
				$_8 = _0($_4[7], $_7);
				$_9 = _4($_4[4], $_4[5], $_4[6]);
				$_8 = _0($_8, $_9);
				$_8 = _0($_8, $_2[$_6]);
				$_10 = ($_6 * 4);
				$_5[$_6] = (ord($_3[$_10]) << 24) + (ord($_3[($_10 + 1)]) << 16) + (ord($_3[($_10 + 2)]) << 8) + (ord($_3[($_10 + 3)]));
				$_8 = _0($_8, $_5[$_6]);
				$_11 = _5($_4[0]);
				$_12 = _6($_4[0], $_4[1], $_4[2]);
				$_13 = _0($_11, $_12);
				$_4[7] = $_4[6];
				$_4[6] = $_4[5];
				$_4[5] = $_4[4];
				$_4[4] = _0($_4[3], $_8);
				$_4[3] = $_4[2];
				$_4[2] = $_4[1];
				$_4[1] = $_4[0];
				$_4[0] = _0($_8, $_13);
			}

			for ($_6; $_6 < 64; $_6++) {
				$_7 = _7($_5[(($_6 + 1) & 15)]);
				$_8 = _8($_5[(($_6 + 14) & 15)]);
				$_9 = ($_6 & 15);
				$_5[$_9] = _0($_5[$_9], $_7);
				$_5[$_9] = _0($_5[$_9], $_8);
				$_5[$_9] = _0($_5[$_9], $_5[(($_6 + 9) & 15)]);
				$_10 = _3($_4[4]);
				$_11 = _0($_4[7], $_10);
				$_12 = _4($_4[4], $_4[5], $_4[6]);
				$_11 = _0($_11, $_12);
				$_11 = _0($_11, $_2[$_6]);
				$_11 = _0($_11, $_5[$_9]);
				$_13 = _5($_4[0]);
				$_14 = _6($_4[0], $_4[1], $_4[2]);
				$_15 = _0($_13, $_14);
				$_4[7] = $_4[6];
				$_4[6] = $_4[5];
				$_4[5] = $_4[4];
				$_4[4] = _0($_4[3], $_11);
				$_4[3] = $_4[2];
				$_4[2] = $_4[1];
				$_4[1] = $_4[0];
				$_4[0] = _0($_11, $_15);
			}

			foreach ($_1 as $_3 => $_5) {
				$_1[$_3] = _0($_1[$_3], $_4[$_3]);
			}
		}

		return sprintf("%08x%08x%08x%08x%08x%08x%08x%08x", $_1[0], $_1[1], $_1[2], $_1[3], $_1[4], $_1[5], $_1[6], $_1[7]);
	}
?>
