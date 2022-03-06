<?php
	if (empty($_SERVER['REMOTE_ADDR']) === false) {
		$sourceIpAddress = $_SERVER['REMOTE_ADDR'];

		if ((strpos($sourceIpAddress, ':') === false) === false) {
			$sourceIpAddress = str_replace(':', '_', $sourceIpAddress);
		} else {
			$sourceIpAddress = str_replace('.', '', $sourceIpAddress);
		}

		$sourceIpAddressIndex = 0;
		$sourceIpAddressPath = '';

		while (isset($sourceIpAddress[$sourceIpAddressIndex]) === true) {
			$sourceIpAddressPath .= $sourceIpAddress[$sourceIpAddressIndex] . '/';
			$sourceIpAddressIndex++;
		}

		$timestamp = time();
		$sourceIpAddressPath .= date('i', $timestamp) . '/';
		$sourceIpAddressLogsPath = $sourceIpAddressPath . hrtime(true) . '/';
		mkdir('/tmp/firewall-security-api/unauthorized-source-ip-addresses-logs/allowed/' . $sourceIpAddressLogsPath, 0777, true);
		exec('cd /tmp/firewall-security-api/unauthorized-source-ip-addresses-logs/allowed/' . $sourceIpAddressPath . ' && ls 2>&1', $sourceIpAddressLogs);

		if (isset($sourceIpAddressLogs[9]) === true) {
			mkdir('/tmp/firewall-security-api/unauthorized-source-ip-addresses-logs/denied/' . $sourceIpAddressPath, 0777, true);
		}
	}

	exit;
?>
