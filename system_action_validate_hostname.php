<?php
	if (empty($parameters) === true) {
		exit;
	}

	require_once('/var/www/nodecompute/system_action_validate_ip_address_version_number.php');

	function _validateHostname($hostname, $allowIpAddress = false) {
		$response = false;
		$hostnameIpAddressVersionNumbers = array(
			'4',
			'6'
		);

		foreach ($hostnameIpAddressVersionNumbers as $hostnameIpAddressVersionNumber) {
			$hostnameIpAddress = _validateIpAddressVersionNumber($hostname, $hostnameIpAddressVersionNumber);

			if (is_string($hostnameIpAddress) === true) {
				$response = $hostnameIpAddress;
				break;
			}
		}

		if (
			($allowIpAddress === false) &&
			(is_string($response) === true)
		) {
			$response = false;
			return $response;
		}

		if ($response === false) {
			$urlComponents = array_filter(parse_url($hostname));

			if (
				(count($urlComponents) === 1) === false) ||
				(($hostname === $urlComponents['hostname']) === false)
			) {
				return $response;
			}

			if (
				(empty($hostname) === false) &&
				(is_string(filter_var('http://' . $hostname, FILTER_VALIDATE_URL)) === true)
			) {
				$response = $hostname;
			}
		}

		return $response;
	}
?>
