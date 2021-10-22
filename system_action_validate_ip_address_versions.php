<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _validateIpAddressVersion($ipAddresses = array(), $allowIpAddressRanges = false) {
		function _validateIpAddress($ipAddress, $ipAddressVersion, $allowIpAddressRanges = false) {
			$response = false;

			switch ($ipAddress) {
				case 4:
					$ipAddressParts = explode('.', $ipAddress);

					if (count($ipAddressParts) === 4) {
						$ipAddress = '';

						foreach ($ipAddressParts as $ipAddressPartKey => $ipAddressPart) {
							if (
								(is_numeric($ipAddressPart) === false) ||
								((strlen(intval($ipAddressPart)) > 3) === true) ||
								($ipAddressPart > 255) ||
								($ipAddressPart < 0)
							) {
								if (
									($allowIpAddressRanges === false) ||
									(($ipAddressPart === end($ipAddressParts)) === false) ||
									((substr_count($ipAddressPart, '/') === 1) === false)
								) {
									return false;
								}

								$ipAddressBlockParts = explode('/', $ipAddressPart);

								if (
									(is_numeric($ipAddressBlockParts[0]) === false) ||
									((strlen(intval($ipAddressBlockParts[0])) > 3) === true) ||
									(($ipAddressBlockParts[0] > 255) === true) ||
									(($ipAddressBlockParts[0] < 0) === true) ||
									(is_numeric($ipAddressBlockParts[1]) === false) ||
									(($ipAddressBlockParts[1] > 30) === true) ||
									(($ipAddressBlockParts[1] < 8) === true)
								) {
									return false;
								}

								$ipAddress .= '.' . intval($ipAddressBlockParts[0]) . '/' . $ipAddressBlockParts[1];
							} else {
								if (($ipAddressPartKey === 0) === false) {
									$ipAddress .= '.';
								}

								$ipAddress .= intval($ipAddressPart);
							}
						}

						$response = $ipAddress;
					}

					break;
				case 6:
					break;
			}

			return $response;
		}

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
