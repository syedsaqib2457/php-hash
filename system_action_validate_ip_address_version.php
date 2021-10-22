<?php
	function _validateIpAddressVersion($ipAddresses = array(), $allowIpAddressRanges = false) {
		$validatedIpAddresses = array();

		if (is_array($ipAddresses) === false) {
			$ipAddresses = array(
				$ipAddresses
			);
		}

		$ipAddresses = array_filter($ipAddresses); 

		foreach ($ipAddresses as $ipAddress) {
			$ipAddressVersion = 4;
			$validatedIpAddress = false;

			if (empty($ipAddress) === false) {
				if (
					(strpos($ipAddress, ':') !== false) && 
					(strpos($ipAddress, ':::') === false)
				) {
					$ipAddressVersion = 6;
				}

				if (empty($validatedIpAddresses[$ipAddressVersion][$ipAddress]) === true) {
					$validatedIpAddress = _validateIpAddress($ipAddress, $ipAddressVersion, $allowIpAddressRanges);

					if ($validatedIpAddress === false) {
						continue;
					}

					$validatedIpAddresses[$ipAddressVersion][$validatedIpAddress] = $validatedIpAddress;
				}
			}
		}

		$response = $validatedIpAddresses;
		return $response;
	}
?>
