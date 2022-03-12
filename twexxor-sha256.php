<?php
	function _0($_0, $_1) {
		if ($_0 < 0) {
			$_0 = (($_0 & 0x7FFFFFFF) + 0x80000000);
		}

		if ($_1 < 0) {
			$_1 = (($_1 & 0x7FFFFFFF) + 0x80000000);
		}

		$_0 += $_1;

		while ($_0 >= 0x100000000) {
			$_0 -= 0x100000000;
		}

		return $_0;
	}

	function _1($_0, $_1) {
		return _2($_0, $_1) | ($_0 << (32 - $_1)) & 0xFFFFFFFF;
	}

	function _2($_0, $_1) {
		if (($_0 < 0) === true) {
			$_0 &= 0x7FFFFFFF;
			return ($_0 >> $_1) | (0x40000000 >> ($_1 - 1));
		}

		return $_0 >> $_1;
	}

	function _3($_0) {
		return _1($_0, 6) ^ _1($_0, 11) ^ _1($_0, 25);
	}

	function _4($_0, $_1, $_2) {
		return ($_0 & $_1) ^ ((~$_0) & $_2);
	}

	function _5($_0) {
		return _1($_0, 2) ^ _1($_0, 13) ^ _1($_0, 22);
	}

	function _6($_0, $_1, $_2) {
		return ($_0 & $_1) ^ (($_0 & $_2) ^ ($_1 & $_2));
	}

	function _7($_0) {
		return _1($_0, 7) ^ _1($_0, 18) ^ _2($_0, 3);
	}

	function _8($_0) {
		return _1($_0, 17) ^ _1($_0, 19) ^ _2($_0, 10);
	}

	function twexxorSha256($_0) {
		$_1 = dechex(strlen($_0) * 8);
		$_2 = strlen($_1);

		if (($_2 & 1) === 1) {
			$_1 = '0' . $_1;
		}

		$_1 = hex2bin($_1);
		$_2 = (8 - strlen($_1));

		if ($_2 < 0) {
			return false;
		}

		while ($_2 !== 0) {
			$_1 = "\0" . $_1;
			$_2--;
		}

		$_0 .= "\x80";
		$_0 = str_split($_0, 64);
		end($_0);
		$_2 = key($_0);
		$_3 = 64 - strlen($_0[$_2]);

		if ($_3 < 8) {
			while ($_3 !== 0) {
				$_0[$_2] .= "\0";
				$_3--;
			}

			$_2++;
			$_0[$_2] = '';
			$_3 = 64;
		}

		while ($_3 !== 8) {
			$_0[$_2] .= "\0";
			$_3--;
		}

		$_0[$_2] .= $_1;
		$_1 = array(0x6A09E667, 0xBB67AE85, 0x3C6EF372, 0xA54FF53A, 0x510E527F, 0x9B05688C, 0x1F83D9AB, 0x5BE0CD19);
		$_2 = array(0x428A2F98, 0x71374491, 0xB5C0FBCF, 0xE9B5DBA5, 0x3956C25B, 0x59F111F1, 0x923F82A4, 0xAB1C5ED5, 0xD807AA98, 0x12835B01, 0x243185BE, 0x550C7DC3, 0x72BE5D74, 0x80DEB1FE, 0x9BDC06A7, 0xC19BF174, 0xE49B69C1, 0xEFBE4786, 0xFC19DC6, 0x240CA1CC, 0x2DE92C6F, 0x4A7484AA, 0x5CB0A9DC, 0x76F988DA, 0x983E5152, 0xA831C66D, 0xB00327C8, 0xBF597FC7, 0xC6E00BF3, 0xD5A79147, 0x6CA6351, 0x14292967, 0x27B70A85, 0x2E1B2138, 0x4D2C6DFC, 0x53380D13, 0x650A7354, 0x766A0ABB, 0x81C2C92E, 0x92722C85, 0xA2BFE8A1, 0xA81A664B, 0xC24B8B70, 0xC76C51A3, 0xD192E819, 0xD6990624, 0xF40E3585, 0x106AA070, 0x19A4C116, 0x1E376C08, 0x2748774C, 0x34B0BCB5, 0x391C0CB3, 0x4ED8AA4A, 0x5B9CCA4F, 0x682E6FF3, 0x748F82EE, 0x78A5636F, 0x84C87814, 0x8CC70208, 0x90BEFFFA, 0xA4506CEB, 0xBEF9A3F7, 0xC67178F2);

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

		return sprintf('%08x', $_1[0]) . sprintf('%08x', $_1[1]) . sprintf('%08x', $_1[2]) . sprintf('%08x', $_1[3]) . sprintf('%08x', $_1[4]) . sprintf('%08x', $_1[5]) . sprintf('%08x', $_1[6]) . sprintf('%08x', $_1[7]);
	}
?>
