<?php
	function _killProcessIds($processIds) {
		$commands = array(
			'#!/bin/bash',
			'whereis telinit | awk \'{ for (i=2; i<=NF; i++) print $i }\' | while read -r binaryFile; do echo $((sudo $binaryFile "_' . uniqid() . time() . '") 2>&1) | grep -c "single" && echo $binaryFile && break; done | tail -1'
		);
		$commandsFile = '/tmp/system_recursive_dns_destination_commands.sh';

		if (file_exists($commandsFile) === true) {
			unlink($commandsFile);
		}

		file_put_contents($commandsFile, implode("\n", $commands));
		chmod($commandsFile, 0755);
		exec('cd /tmp/ && sudo ./' . basename($commandsFile), $binaryFile);
		$telinitBinaryFile = current($telinitBinaryFile);
		unlink($commandsFile);

		if (empty($telinitBinaryFile) === true) {
			shell_exec('sudo apt-get update');
			shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install systemd');
			echo 'Error listing telinit binary file, please try again.' . "\n";
			exit;
		}

		$commands = array(
			'#!/bin/bash'
		);
		$processIdParts = array_chunk($processIds, 10);

		foreach ($processIdParts as $processIds) {
			$commands[] = 'sudo kill -9 ' . implode(' ', $processIds);
		}

		$commands = array_merge($commands, array(
			'sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk \'{print $1}\')',
			'sudo ' . $telinitBinaryFile . ' u'
		));
		$commandsFile = '/tmp/system_recursive_dns_destination_commands.sh';

		if (file_exists($commandsFile) === true) {
			unlink($commandsFile);
		}

		file_put_contents($commandsFile, implode("\n", $commands));
		chmod($commandsFile, 0755);
		shell_exec('cd /tmp/ && sudo ./' . basename($commandsFile));
		unlink($commandsFile);
		return;
	}

	_processNodeSystemRecursiveDnsDestination() {
		exec('ps -h -o pid -o cmd $(pgrep php) | grep "process.php node_system_recursive_dns_destination" | awk \'{print $1}\'', $nodeSystemRecursiveDnsDestinationProcessIds);
		$nodeSystemRecursiveDnsDestinationProcessIds = array_diff($nodeSystemRecursiveDnsDestinationProcessIds, array(
			getmypid()
		));

		if (empty($nodeSystemRecursiveDnsDestinationProcessIds) === false) {
			_killProcessIds($nodeSystemRecursiveDnsDestinationProcessIds);
		}

		while (true) {
			shell_exec('sudo cp /usr/local/ghostcompute/resolv.conf /etc/resolv.conf');
			usleep(200000);
		}
	}

	if (($parameters['action'] === 'process_system_recursive_dns_destination') === true) {
		$response = _processNodeSystemRecursiveDnsDestination();
		_output($response);
	}
?>
