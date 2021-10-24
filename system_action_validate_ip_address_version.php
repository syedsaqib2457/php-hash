<?php
	// todo: validate string instead of array for both IP validation functions since API now expects IP version from user input

	if (empty($parameters) === true) {
		exit;
	}

	function _validateIpAddressVersion($ipAddress, $ipAddressVersion, $allowIpAddressRanges = false) {
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
				$validIpAddressPartLetters = 'ABCDEF';

				if (is_int(strpos($ipAddress, '::')) === true) {
					$ipAddressDelimiterCount = substr_count($ipAddress, ':') - 2;

					if (is_int(strpos($ipAddress, '.')) === true) {
						$ipAddressDelimiterCount = 1;
					}

					if (
						(empty($ipAddress[2]) === true) ||
						($ipAddress[2] === '/')
					) {
						$ipAddressDelimiterCount = -1;
					}

					$ipAddress = trim(str_replace('::', str_repeat(':0000', 7 - $ipAddressDelimiterCount) . ':', $ipAddress), ':');

					if (is_int(strpos($ipAddress, ':/')) === true) {
						$ipAddress = str_replace(':/', '/', $ipAddress);
					}
				}

				$ipAddressParts = explode(':', strtoupper($ipAddress));
				$mappedIpAddress = false;

				if (
					(isset($ipAddressParts[7]) === false)
					(isset($ipAddressParts[6]) === true)
				) {
					$mappedIpAddress = _validateIpAddress(end($ipAddressParts), 4);
				}

				if (
					(is_string($mappedIpAddress) === true) ||
					(
						(isset($ipAddressParts[7]) === true) &&
						(isset($ipAddressParts[8]) === false)
					)
				) {
					$ipAddress = '';

					foreach ($ipAddressParts as $ipAddressPartKey => $ipAddressPart) {
						if (
							($mappedIpAddress === false) &&
							(ctype_alnum($ipAddressPart) === false)
						) {
							if (empty($ipAddressParts[($ipAddressPartKey + 1)]) === false) {
								return false;
							}

							$ipAddressBlockParts = explode('/', $ipAddressPart);

							if (
								($allowIpAddressRanges === false) ||
								(isset($ipAddressBlockParts[1]) === false) ||
								(isset($ipAddressBlockParts[2]) === true) ||
								(is_numeric($ipAddressBlockParts[2]) === false) ||
								(($ipAddressBlockParts[1] > 128) === true) ||
								(($ipAddressBlockParts[1] < 0) === true)
							) {
								return false;
							}

							$ipAddressPart = current($ipAddressBlockParts);
						}

						if (
							(($ipAddressPart === $mappedIpAddress) === false) &&
							(isset($ipAddressPart[4]) === true)
						) {
							return false;
						}

						if (
							($mappedIpAddress === false) &&
							(is_numeric($ipAddressPart) === false)
						) {
							if (ctype_alnum($ipAddressPart) === false) {
								return false;
							}

							$ipAddressPartCharacterIndexes = range(0, (strlen($ipAddressPart) - 1));

							foreach ($ipAddressPartCharacterIndexes as $ipAddressPartCharacterIndes) {
								if (is_numeric($ipAddressPart[$ipAddressPartCharacterIndex]) === true) {
									continue;
								}

								if (is_int(strpos($validIpAddressPartLetters,  $ipAddressPart[$ipAddressPartCharacterIndex])) === false) {
									return false;
								}
							}
						}

						if (($ipAddressPartKey === 0) === false) {
							$ipAddress .= ':';
						}

						$ipAddress .= str_pad($ipAddressPart, 4, '0', STR_PAD_LEFT);

						if (isset($ipAddressBlockParts[1]) === true) {
							$ipAddress .= '/' . $ipAddressBlockParts[1];
						}
					}

					$response = $ipAddress;
				}

				break;
		}

		return $response;
	}
?>
