<?php
	function _killProcessIds($parameters) {
		$commands = array(
			'#!/bin/bash'
		);
		$processIdParts = array_chunk($parameters['process_ids'], 10);

		foreach ($processIdParts as $processIds) {
			$commands[] = 'sudo kill -9 ' . implode(' ', $processIds);
		}

		$commands[] = 'sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk \'{print $1}\')';
		$commands[] = 'sudo ' . $parameters['binary_files']['telinit'] . ' u';

		if (file_exists('/tmp/commands.sh') === true) {
			unlink('/tmp/commands.sh');
		}

		file_put_contents('/tmp/commands.sh', implode("\n", $commands));
		shell_exec('sudo chmod +x /tmp/commands.sh');
		shell_exec('cd /tmp/ && sudo ./commands.sh');
		unlink('/tmp/commands.sh');
		return;
	}

	function _output($response) {
		echo json_encode($response);
		exit;
	}

	$response = array(
		'authenticated_status' => '1',
		'data' => array(),
		'message' => 'Invalid node endpoint request, please try again.',
		'valid_status' => '0'
	);

	if (empty($_SERVER['argv'][1]) === true) {
		_output($response);
	}

	if (file_exists('/usr/local/ghostcompute/node_data.json') === false) {
		$response['message'] = 'Node must be redeployed because node data file is missing, please try again.';
		_output($response);
	}

	$nodeData = json_decode(file_get_contents('/usr/local/ghostcompute/node_data.json'), true);

	if ($nodeData === false) {
		$response['message'] = 'Error listing node data, please try again.';
		_output($response);
	}

	if (
		(empty($nodeData['authentication_token']) === true) ||
		(is_string($nodeData['authentication_token']) === false) ||
		(empty($nodeData['system_endpoint_destination_address']) === true) ||
		(is_string($nodeData['system_endpoint_destination_address']) === false) ||
		(isset($nodeData['system_version']) === false) ||
		(is_numeric($nodeData['system_version']) === false)
	) {
		$response['message'] = 'Node must be redeployed because node data is invalid, please try again.';
		_output($response);
	}

	$parameters = array(
		'action' => $_SERVER['argv'][1],
		'binary_files' => array(),
		'node_authentication_token' => $nodeData['authentication_token'],
		'system_endpoint_destination_address' => $nodeData['system_endpoint_destination_address'],
		'system_version' => $nodeData['system_version']
	);
	$uniqueId = '_' . uniqid() . time();
	$binaries = array(
		array(
			'command' => $uniqueId,
			'name' => 'a2enmod',
			'output' => 'Module ' . $uniqueId,
			'package' => 'apache2'
		),
		array(
			'command' => $uniqueId,
			'name' => 'a2ensite',
			'output' => 'Site ' . $uniqueId,
			'package' => 'apache2'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'crontab',
			'output' => 'invalid option',
			'package' => 'cron'
		),
		array(
			'command' => $uniqueId,
			'name' => 'ifconfig',
			'output' => 'interface',
			'package' => 'net-tools'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'netstat',
			'output' => 'invalid option',
			'package' => 'net-tools'
		),
		array(
			'command' => '-v',
			'name' => 'php',
			'output' => 'PHP ',
			'package' => 'php'
		),
		array(
			'command' => $uniqueId,
			'name' => 'service',
			'output' => 'unrecognized service',
			'package' => 'systemd'
		),
		array(
			'command' => $uniqueId,
			'name' => 'sysctl',
			'output' => 'cannot',
			'package' => 'procps'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'systemctl',
			'output' => 'invalid option',
			'package' => 'systemd'
		),
		array(
			'command' => $uniqueId,
			'name' => 'telinit',
			'output' => 'single',
			'package' => 'systemd'
		)
	);

	foreach ($binaries as $binary) {
		$commands = array(
			'#!/bin/bash',
			'whereis ' . $binary['name'] . ' | awk \'{ for (i=2; i<=NF; i++) print $i }\' | while read -r binaryFile; do echo $((sudo $binaryFile "' . $binary['command'] . '") 2>&1) | grep -c "' . $binary['output'] . '" && echo $binaryFile && break; done | tail -1'
		);

		if (file_exists('/tmp/commands.sh') === true) {
			unlink('/tmp/commands.sh');
		}

		file_put_contents('/tmp/commands.sh', implode("\n", $commands));
		chmod('/tmp/commands.sh', 0755);
		exec('cd /tmp/ && sudo ./commands.sh', $binaryFile);
		$binaryFile = current($binaryFile);
		unlink('/tmp/commands.sh');

		if (empty($binaryFile) === true) {
			shell_exec('sudo apt-get update');
			shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install ' . $binary['package']);
			echo 'Error listing ' . $binary['name'] . ' binary file from the ' . $binary['package'] . ' package, please try again.' . "\n";
			exit;
		}

		$parameters['binary_files'][$binary['name']] = $binaryFile;
	}

	if (in_array(strval($parameters['action']), array(
		'process_node_processes',
		'process_node_resource_usage_logs',
		'process_node_user_blockchain_mining',
		'process_node_user_request_logs'
	)) === true) {
		$systemSettingsFile = '/tmp/' . $parameters['action'] . '_system_settings.json';
		shell_exec('sudo wget -O ' . $systemSettingsFile . ' --no-dns-cache --post-data "json={\"action\":\"list_system_settings\",\"node_authentication_token\":\"' . $parameters['node_authentication_token'] . '\"}" --retry-connrefused --timeout=10 --tries=2 ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php');

		if (file_exists($systemSettingsFile) === false) {
			$response['message'] = 'Error listing system settings, please try again.';
			_output($response);
		}

		$systemSettings = json_decode(file_get_contents($systemSettingsFile), true);

		if ($systemSettings === false) {
			$response['message'] = 'Error listing system settings, please try again.';
			_output($response);
		}

		if (($parameters['system_version'] < $systemSettings['version']) === true) {
			$systemFiles = json_decode($systemSettings['files'], true);

			foreach ($systemFiles as $systemFile) {
				// todo: kill existing $systemFile process
				// todo: update system file
			}
		}

		// todo: update system_endpoint_destination_address if changed
	}

	if (
		(ctype_alnum(str_replace('_', '', $parameters['action'])) === false) ||
		(file_exists('/usr/local/ghostcompute/node_action_' . $parameters['action'] . '.php') === false)
	) {
		$response['message'] = 'Invalid node endpoint request action, please try again.';
		_output($response);
	}

	require_once('/usr/local/ghostcompute/node_action_' . $parameters['action'] . '.php');
	_output($response);
?>
