<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _validateHostname($hostname, $allowIpAddress = false) {
		$response = false;
		// todo: no request schemes for hostname validation
		// todo: validate special domain cases return true for valid domains with TLDs such as .be

		if (
			(empty($hostname) === false) &&
			((filter_var('http://' . $hostname, FILTER_SANITIZE_URL) === filter_var('http://' . $hostname, FILTER_VALIDATE_URL)) === true)
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
