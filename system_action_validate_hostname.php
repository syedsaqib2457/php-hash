<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _validateHostname($hostname, $allowIpAddress = false) {
		$response = false;
		$urlComponents = array_filter(path_info($hostname));

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

		if (
			($response === false) &&
			($allowIpAddress === true)
		) {
			require_once('/var/www/ghostcompute/system_action_validate_ip_address_version.php');
			$hostnameIpAddressVersions = array(
				4,
				6
			);

			foreach ($hostnameIpAddressVersions as $hostnameIpAddressVersion) {
				$hostnameIpAddress = _validateIpAddressVersion($hostname, $hostnameIpAddressVersion);

				if (is_string($hostnameIpAddress) === true) {
					$response = $hostnameIpAddress;
					break;
				}
			}
		}

		return $response;
	}
?>
