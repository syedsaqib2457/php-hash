<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _validateIpAddressType($ipAddress, $ipAddressVersionNumber) {
		$response = 'public_network';
		$reservedIpAddressVersions = array(
			'4' => array(
				'current_network' => array(
					array(
						'start' => '0.0.0.0',
						'stop' => '0.255.255.255',
					)
				),
				'documentation' => array(
					array(
						'start' => '192.0.2.0',
						'stop' => '192.0.2.255'
					),
					array(
						'start' => '198.51.100.0',
						'stop' => '198.51.100.255'
					),
					array(
						'start' => '203.0.113.0',
						'stop' => '203.0.113.255'
					)
				),
				'ietf_protocol_assignments' => array(
					array(
						'start' => '192.0.0.0',
						'stop' => '192.0.0.255'
					)
				),
				'internet' => array(
					array(
						'start' => '192.88.99.0',
						'stop' => '192.88.99.255'
					),
					array(
						'start' => '224.0.0.0',
						'stop' => '239.255.255.255'
					),
					array(
						'start' => '240.0.0.0',
						'stop' => '255.255.255.254'
					)
				),
				'loopback' => array(
					array(
						'start' => '127.0.0.0',
						'stop' => '127.255.255.255'
					)
				),
				'private_network' => array(
					array(
						'start' => '10.0.0.0',
						'stop' => '10.255.255.255',
					),
					array(
						'start' => '100.64.0.0',
						'stop' => '100.127.255.255',
					),
					array(
						'start' => '172.16.0.0',
						'stop' => '172.31.255.255',
					),
					array(
						'start' => '192.168.0.0',
						'stop' => '192.168.255.255'
					),
					array(
						'start' => '198.18.0.0',
						'stop' => '198.19.255.255'
					),
					array(
						'start' => '255.255.255.255',
						'stop' => '255.255.255.255'
					)
				)
			),
			'6' => array(
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

		foreach ($reservedIpAddressVersions[$ipAddressVersionNumber] as $reservedIpAddressType => $reservedIpAddressRanges) {
			foreach ($reservedIpAddressRanges as $reservedIpAddressRange) {
				if (
					(($ipAddress <= $reservedIpAddressRange['stop']) === true) &&
					(($ipAddress >= $reservedIpAddressRange['start']) === true)
				) {
					$response = $reservedIpAddressType;
					return $response;
				}
			}
		}

		return $response;
	}
?>
