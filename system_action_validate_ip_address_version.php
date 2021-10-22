<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _validateIpAddressVersion($ipAddresses = array(), $allowIpAddressRanges = false) {
		$response = array();

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

				if (empty($response[$ipAddressVersion][$ipAddress]) === true) {
					$validatedIpAddress = _validateIpAddress($ipAddress, $ipAddressVersion, $allowIpAddressRanges);

					if ($validatedIpAddress === false) {
						continue;
					}

					$response[$ipAddressVersion][$validatedIpAddress] = $validatedIpAddress;
				}
			}
		}

		return $response;
	}
?>
