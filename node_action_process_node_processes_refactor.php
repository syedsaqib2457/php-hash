<?php
	function _killProcessIds($processIds, $telinitBinaryFile) {
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

		if (file_exists('/tmp/commands.sh') === true) {
			unlink('/tmp/commands.sh');
		}

		file_put_contents('/tmp/commands.sh', implode("\n", $commands));
		chmod('/tmp/commands.sh', 0755);
		shell_exec('cd /tmp/ && sudo ./commands.sh');
		unlink('/tmp/commands.sh');
		return;
	}

	$parameters['ip_address_versions'] = array(
		4 => array(
			'interface_type' => 'inet',
			'network_mask' => 32
		),
		6 => array(
			'interface_type' => 'inet6',
			'network_mask' => 128
		)
	);
	exec('getconf PAGE_SIZE 2>&1', $kernelPageSize);
	$parameters['kernel_page_size'] = current($kernelPageSize);
	exec('free -b | grep "Mem:" | grep -v free | awk \'{print $2}\'', $memoryCapacityBytes);
	$parameters['memory_capacity_bytes'] = current($memoryCapacityBytes);
	$parameters['node_process_type_firewall_rule_set_index'] = 0;
	// todo
?>
