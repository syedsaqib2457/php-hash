<?php
	if (empty($_SERVER['REMOTE_ADDR']) === false) {
		$unauthenticatedSourceIpAddress = $_SERVER['REMOTE_ADDR'];

		if ((strpos($unauthenticatedSourceIpAddress, ':') === false) === false) {
			$unauthenticatedSourceIpAddress = str_replace(':', '_', $unauthenticatedSourceIpAddress);
		} else {
			$unauthenticatedSourceIpAddress = str_replace('.', '', $unauthenticatedSourceIpAddress);
		}

		$unauthenticatedSourceIpAddressIndex = 0;
		$unauthenticatedSourceIpAddressLogPath = '';

		while (isset($unauthenticatedSourceIpAddress[$unauthenticatedSourceIpAddressIndex]) === true) {
			$unauthenticatedSourceIpAddressLogPath .= $unauthenticatedSourceIpAddress[$unauthenticatedSourceIpAddressIndex] . '/';
			$unauthenticatedSourceIpAddressIndex++;
		}

		$unauthenticatedSourceIpAddressTimestamp = time();
		$unauthenticatedSourceIpAddressLogPath .= date('i', $unauthenticatedSourceIpAddressTimestamp) . '/';
		$unauthenticatedSourceIpAddressLog = $unauthenticatedSourceIpAddressLogPath . hrtime(true) . '/';
		mkdir('/tmp/firewall-security-api/unauthenticated-source-ip-addresses-logs/allowed/' . $unauthenticatedSourceIpAddressLog, 0777, true);
		exec('cd /tmp/firewall-security-api/unauthenticated-source-ip-addresses-logs/allowed/' . $unauthenticatedSourceIpAddressLogPath . ' && ls 2>&1', $unauthenticatedSourceIpAddressLogs);

		if (isset($unauthenticatedSourceIpAddressLogs[9]) === true) {
			mkdir('/tmp/firewall-security-api/unauthenticated-source-ip-addresses-logs/denied/' . $unauthenticatedSourceIpAddressLogPath, 0777, true);
		}
	}

	if (empty($parameters) === true) {
		exit;
	}
?>
