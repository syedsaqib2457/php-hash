<?php
	function _killProcessIds($binaryFiles, $nodeAction, $processId, $processIds) {
		$killProcessCommands = array(
			'#!/bin/bash'
		);
		$processIdParts = array_chunk($processIds, 10);

		foreach ($processIdParts as $processIdPart) {
			$processIdPart = implode(' ', $processIdPart);
			$killProcessCommands[] = 'sudo ' . $binaryFiles['kill'] . ' -9 ' . $processIdPart;
		}

		$killProcessCommands[] = 'sudo ' . $binaryFiles['kill'] . ' -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk \'{print $1}\')';
		$killProcessCommands[] = 'sudo ' . $binaryFiles['telinit'] . ' u';
		$killProcessCommands = implode("\n", $killProcessCommands);
		file_put_contents('/usr/local/cloud_node_automation_api/node_action_' . $nodeAction . '_kill_process_commands_' . $processId . '.sh', $killProcessCommands);
		shell_exec('sudo chmod +x /usr/local/cloud_node_automation_api/node_action_' . $nodeAction . '_kill_process_commands_' . $processId . '.sh');
		shell_exec('cd /usr/local/cloud_node_automation_api/ && sudo ./node_action_' . $nodeAction . '_kill_process_commands_' . $processId . '.sh');
		unlink('/usr/local/cloud_node_automation_api/node_action_' . $nodeAction . '_kill_process_commands_' . $processId . '.sh');
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

	$nodeSettingsData = file_get_contents('/usr/local/cloud_node_automation_api/node_settings_data.json');
	$nodeSettingsData = json_decode($nodeSettingsData, true);

	if ($nodeSettingsData === false) {
		$response['message'] = 'Error listing node settings data, please try again.';
		_output($response);
	}

	$parameters = array(
		'action' => $_SERVER['argv'][1],
		'binary_files' => array(),
		'node_authentication_token' => $nodeSettingsData['authentication_token'],
		'process_id' => getmypid(),
		'system_endpoint_destination_ip_address' => $nodeSettingsData['system_endpoint_destination_ip_address'],
		'system_endpoint_destination_ip_address_type' => $nodeSettingsData['system_endpoint_destination_ip_address_type'],
		'system_endpoint_destination_ip_address_version_number' => $nodeSettingsData['system_endpoint_destination_ip_address_version_number'],
		'system_version_number' => $nodeSettingsData['system_version_number']
	);

	if ($parameters['process_id'] === false) {
		$response['message'] = 'Error listing process ID, please try again.';
		_output($response);
	}

	$uniqueId = '_' . uniqid();
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
			'command' => '_' . $uniqueId,
			'name' => 'curl',
			'output' => 'Could not resolve host',
			'package' => 'curl'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'dig',
			'output' => 'dig -h',
			'package' => 'dnsutils'
		),
		array(
			'command' => $uniqueId,
			'name' => 'ifconfig',
			'output' => 'interface',
			'package' => 'net-tools'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'kill',
			'output' => 'invalid signal',
			'package' => 'procps'
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
			'command' => '-' . $uniqueId,
			'name' => 'sleep',
			'output' => 'invalid option',
			'package' => 'coreutils'
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
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'timeout',
			'output' => 'invalid option',
			'package' => 'coreutils'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'wget',
			'output' => 'unable to resolve host address',
			'package' => 'wget'
		)
	);
	$nodeAction = strval($parameters['action']);

	foreach ($binaries as $binary) {
		$binaryFileListCommands = array(
			'#!/bin/bash',
			'whereis ' . $binary['name'] . ' | awk \'{ for (i=2; i<=NF; i++) print $i }\' | while read -r binaryFile; do echo $((sudo $binaryFile "' . $binary['command'] . '") 2>&1) | grep -c "' . $binary['output'] . '" && echo $binaryFile && break; done | tail -1'
		);
		$binaryFileListCommands = implode("\n", $binaryFileListCommands);
		
		if (file_put_contents('/usr/local/cloud_node_automation_api/node_action_' . $nodeAction . '_binary_file_list_commands.sh', $binaryFileListCommands) === false) {
			$response['message'] = 'Error adding binary file list commands, please try again.';
			_output($response);
		}

		chmod('/usr/local/cloud_node_automation_api/node_action_' . $nodeAction . '_binary_file_list_commands.sh', 0755);
		unset($binaryFile);
		exec('cd /usr/local/cloud_node_automation_api/ && sudo ./node_action_' . $nodeAction . '_binary_file_list_commands.sh', $binaryFile);
		$binaryFile = current($binaryFile);

		if (empty($binaryFile) === true) {
			shell_exec('sudo apt-get update');
			shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install ' . $binary['package']);
			$response['message'] = 'Error listing ' . $binary['name'] . ' binary file from the ' . $binary['package'] . ' package, please try again.';
			_output($response);
		}

		$parameters['binary_files'][$binary['name']] = $binaryFile;
	}

	unlink('/usr/local/cloud_node_automation_api/node_action_' . $nodeAction . '_binary_file_list_commands.sh');

	if (in_array($nodeAction, array(
		'process_network_interface_ip_addresses',
		'process_recursive_dns_destination'
	)) === false) {
		if (($nodeAction === 'process_node_processes') === false) {
			exec('ps -h -o pid -o cmd $(pgrep php) | grep "node_endpoint.php node_action_' . $nodeAction . '" | awk \'{print $1}\'', $nodeActionProcessIds);

			if (empty($nodeActionProcessIds[1]) === false) {
				exit;
			}
		}

		$systemParameters = array(
			'action' => 'list_system_settings',
			'node_authentication_token' => $parameters['node_authentication_token']
		);
		$encodedSystemParameters = json_encode($systemParameters);

		if ($encodedSystemParameters === false) {
			$response['message'] = 'Error listing system settings, please try again.';
			_output($response);
		}

		shell_exec('sudo ' . $parameters['binary_files']['wget'] . ' -O /usr/local/cloud_node_automation_api/system_action_list_system_settings_response.json --no-dns-cache --post-data \'json=' . $encodedSystemParameters . '\' --timeout=60 ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php');

		if (file_exists('/usr/local/cloud_node_automation_api/system_action_list_system_settings_response.json') === false) {
			$response['message'] = 'Error listing system settings, please try again.';
			_output($response);
		}

		$systemSettingsResponse = file_get_contents('/usr/local/cloud_node_automation_api/system_action_list_system_settings_response.json');
		$systemSettingsResponse = json_decode($systemSettingsResponse, true);

		if ($systemSettingsResponse === false) {
			$response['message'] = 'Error listing system settings, please try again.';
			_output($response);
		}

		if (($parameters['system_version'] < $systemSettingsResponse['version']) === true) {
			$systemFiles = json_decode($systemSettingsResponse['files'], true);

			foreach ($systemFiles as $systemFile) {
				// todo: kill existing $systemFile process
				// todo: update system file
			}
		}

		// todo: update system_endpoint_destination_address if changed
	}

	$nodeAction = str_replace('_', '', $nodeAction);

	if (
		(ctype_alnum($nodeAction) === false) ||
		(file_exists('/usr/local/cloud_node_automation_api/node_action_' . $nodeAction . '.php') === false)
	) {
		$response['message'] = 'Invalid node endpoint request action, please try again.';
		_output($response);
	}

	require_once('/usr/local/cloud_node_automation_api/node_action_' . $nodeAction . '.php');
	_output($response);
?>
