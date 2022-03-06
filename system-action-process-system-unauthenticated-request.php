<?php
	if (empty($_SERVER['REMOTE_ADDR']) === false) {
		$systemEndpointDestinationUnauthenticatedRequestLimitRulePerMinuteCount = 10;

		if (is_dir('/var/www/firewall-security-api/unauthorized-source-ip-addresses-logs/') === false) {
			shell_exec('cd /var/www/firewall-security-api/ && sudo mkdir -p unauthorized-source-ip-addresses-logs/allowed/ unauthorized-source-ip-addresses-logs/denied/');
		}

		$sourceIpAddress = $_SERVER['REMOTE_ADDR'];

		if ((strpos($sourceIpAddress, ':') === false) === false) {
			$sourceIpAddress = str_replace(':', '_', $sourceIpAddress);
		} else {
			$sourceIpAddress = str_replace('.', '', $sourceIpAddress);
		}

		$sourceIpAddressIndex = 0;
		$sourceIpAddressPath = '/';

		while (isset($sourceIpAddress[$sourceIpAddressIndex]) === true) {
			$sourceIpAddressPath .= $sourceIpAddress[$sourceIpAddressIndex] . '/';
			$sourceIpAddressIndex++;
		}

		// todo
	}

	exit;
?>
