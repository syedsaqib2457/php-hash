<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _processRecursiveDnsDestination($parameters, $response) {
		exec('ps -h -o pid -o cmd $(pgrep php) | grep "node_endpoint.php node_action_process_recursive_dns_destination" | grep -v grep | awk \'{print $1}\'', $recursiveDnsDestinationProcessIds);
		$recursiveDnsDestinationProcessIds = array_diff($recursiveDnsDestinationProcessIds, array(
			$parameters['process_id']
		));

		if (empty($recursiveDnsDestinationProcessIds) === false) {
			_killProcessIds($parameters['binary_files'], $parameters['action'], $parameters['process_id'], $recursiveDnsDestinationProcessIds);
		}

		while (true) {
			shell_exec('sudo cp /usr/local/nodecompute/resolv.conf /etc/resolv.conf');
			usleep(200000);
		}
	}

	if (($parameters['action'] === 'process_recursive_dns_destination') === true) {
		_processRecursiveDnsDestination($parameters, $response);
	}
?>
