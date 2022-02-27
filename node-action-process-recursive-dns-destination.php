<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _processRecursiveDnsDestination($parameters, $response) {
		exec('ps -h -o pid -o cmd $(pgrep php) | grep "node-endpoint.php node-action-process-recursive-dns-destination" | grep -v grep | awk \'{print $1}\'', $recursiveDnsDestinationProcessIds);
		$recursiveDnsDestinationProcessIds = array_diff($recursiveDnsDestinationProcessIds, array(
			$parameters['processId']
		));

		if (empty($recursiveDnsDestinationProcessIds) === false) {
			_killProcessIds($parameters['binaryFiles'], $parameters['processId'], $parameters['action'], $recursiveDnsDestinationProcessIds);
		}

		while (true) {
			shell_exec('sudo cp /usr/local/firewall-security-api/resolv.conf /etc/resolv.conf');
			usleep(200000);
		}
	}

	if (($parameters['action'] === 'process-recursive-dns-destination') === true) {
		_processRecursiveDnsDestination($parameters, $response);
	}
?>
