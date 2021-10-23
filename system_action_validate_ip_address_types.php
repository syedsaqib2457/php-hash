<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _validateIpAddressTypes($ipAddressVersionIpAddresses) {
		$response = $ipAddressVersionIpAddresses;
		$reservedIpAddresses = array(
			4 => array(
				array(
					'block' => '0.0.0.0/8',
					'count' => 16777216,
					'range_start' => array(
						'integer' => 0,
						'string' => '0.0.0.0'
					),
					'range_stop' => array(
						'integer' => 16777215,
						'string' => '0.255.255.255'
					),
					'usage' => 'current_network'
				),
				array(
					'block' => '10.0.0.0/8',
					'count' => 16777216,
					'range_start' => array(
						'integer' => 167772160,
						'string' => '10.0.0.0'
					),
					'range_stop' => array(
						'integer' => 184549375,
						'string' => '10.255.255.255'
					),
					'usage' => 'private_network'
				),
				array(
					'block' => '100.64.0.0/10',
					'count' => 4194304,
					'range_start' => array(
						'integer' => 1681915904,
						'string' => '100.64.0.0'
					),
					'range_stop' => array(
						'integer' => 1686110207,
						'string' => '100.127.255.255'
					),
					'usage' => 'private_network'
				),
				array(
					'block' => '127.0.0.0/8',
					'count' => 16777216,
					'range_start' => array(
						'integer' => 2130706432,
						'string' => '127.0.0.0'
					),
					'range_stop' => array(
						'integer' => 2147483647,
						'string' => '127.255.255.255'
					),
					'usage' => 'loopback'
				),
				array(
					'block' => '172.16.0.0/12',
					'count' => 1048576,
					'range_start' => array(
						'integer' => 2886729728,
						'string' => '172.16.0.0'
					),
					'range_stop' => array(
						'integer' => 2887778303,
						'string' => '172.31.255.255'
					),
					'usage' => 'private_network'
				),
				array(
					'block' => '192.0.0.0/24',
					'count' => 256,
					'range_start' => array(
						'integer' => 3221225472,
						'string' => '192.0.0.0'
					),
					'range_stop' => array(
						'integer' => 3221225727,
						'string' => '192.0.0.255'
					),
					'usage' => 'ietf_protocol_assignments'
				),
				array(
					'block' => '192.0.2.0/24',
					'count' => 256,
					'range_start' => array(
						'integer' => 3221225984,
						'string' => '192.0.2.0'
					),
					'range_stop' => array(
						'integer' => 3221226239,
						'string' => '192.0.2.255'
					),
					'usage' => 'documentation'
				),
				// todo: add remaining range details for IPv6
				// 3227017984 => 3227018239,
				// 3232235520 => 3232301055,
				// 3323068416 => 3323199487,
				// 3325256704 => 3325256959,
				// 3405803776 => 3405804031,
				// 3758096384 => 4026531839,
				// 4026531840 => 4294967294,
				// 4294967295 => 4294967295
			), 6 => array(
				// todo: add remaining range details for IPv6
				// '0000:0000:0000:0000:0000:0000:0000:0000',
				// '0000:0000:0000:0000:0000:0000:0000:0001',
				// '0000:0000:0000:0000:0000:ffff:y',
				// '0000:0000:0000:0000:ffff:0000:y',
				// '0064:ff9b:0000:0000:0000:0000:y',
				// '0100:0000:0000:0000:x:x:x:x',
				// 'fe80:0000:0000:0000:x:x:x:x',
				// '2001:0000:x:x:x:x:x:x',
				// '2001:0db8:x:x:x:x:x:x',
				// '2001:002x:x:x:x:x:x:x',
				// '2002:x:x:x:x:x:x:x',
				// 'fcx:x:x:x:x:x:x:x',
				// 'fdx:x:x:x:x:x:x:x',
				// 'ffx:x:x:x:x:x:x:x'
			)
		);
	}

	$ipAddressVersions = array(
		4,
		6
	);

	foreach ($ipAddressVersions as $ipAddressVersion) {
		if (empty($ipAddressVersionIpAddresses) === false) {
			foreach ($ipAddressVersionIpAddresses[$ipAddressVersion] as $ipAddress) {
				$response[$ipAddressVersion][$ipAddress] = array(
					'type' => 'public',
					'usage' => 'public_network'
				);

				foreach ($reservedIpAddresses as $reservedIpAddress) {
					
				}
			}
		}
	}

	return $response;
?>
