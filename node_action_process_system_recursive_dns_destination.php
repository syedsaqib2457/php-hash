<?php
	function _processNodeSystemRecursiveDnsDestination($parameters, $response) {
		exec('ps -h -o pid -o cmd $(pgrep php) | grep "node_endpoint.php node_action_process_system_recursive_dns_destination" | awk \'{print $1}\'', $nodeSystemRecursiveDnsDestinationProcessIds);
		$nodeSystemRecursiveDnsDestinationProcessIds = array_diff($nodeSystemRecursiveDnsDestinationProcessIds, array(
			getmypid()
		));

		if (empty($nodeSystemRecursiveDnsDestinationProcessIds) === false) {
			_killProcessIds($parameters['binary_files'], $nodeSystemRecursiveDnsDestinationProcessIds, $response);
		}

		while (true) {
			shell_exec('sudo cp /usr/local/ghostcompute/resolv.conf /etc/resolv.conf');
			usleep(200000);
		}
	}

	if (($parameters['action'] === 'process_system_recursive_dns_destination') === true) {
		_processNodeSystemRecursiveDnsDestination($parameters, $response);
	}
?>
