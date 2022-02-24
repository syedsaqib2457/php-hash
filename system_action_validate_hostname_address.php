<?php
	if (empty($parameters) === true) {
		exit;
	}

	require_once('/var/www/cloud_node_automation_api/system_action_validate_ip_address_version_number.php');

	function _validateHostnameAddress($hostnameAddress, $allowIpAddress = false) {
		$ipAddressVersionNumbers = array(
			'4',
			'6'
		);
		$response = false;

		foreach ($ipAddressVersionNumbers as $ipAddressVersionNumber) {
			$ipAddress = _validateIpAddressVersionNumber($hostnameAddress, $ipAddressVersionNumber);

			if (is_string($ipAddress) === true) {
				$response = $ipAddress;
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
			$urlComponents = parse_url($hostnameAddress)
			$urlComponents = array_filter($urlComponents);

			if (
				((count($urlComponents) === 1) === false) ||
				(($hostnameAddress === $urlComponents['hostname']) === false)
			) {
				return $response;
			}

			$urlComponents['hostname'] = filter_var('http://' . $hostnameAddress, FILTER_VALIDATE_URL);

			if (
				(empty($hostnameAddress) === false) &&
				(is_string($urlComponents['hostname']) === true)
			) {
				$response = $hostnameAddress;
			}
		}

		return $response;
	}
?>
