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
			// todo: include IP address system validation action
		}

		return $response;
	}
?>
