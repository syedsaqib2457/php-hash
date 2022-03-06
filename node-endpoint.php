<?php
	function _killProcessIds($binaryFiles, $currentProcessId, $nodeAction, $processIds) {
		$killProcessCommands = array(
			'#!/bin/bash'
		);
		$processIdParts = array();
		$processIdPartsKey = 1;

		foreach ($processIds as $processIdKey => $processId) {
			if ((($processIdKey % 10) === 0) === true) {
				$processIdPartsKey++;
				$processIdParts[$processIdPartsKey] = '';
			}

			$processIdParts[$processIdPartsKey] .= $processId . ' ';
		}

		foreach ($processIdParts as $processIdPart) {
			$killProcessCommands[] = 'sudo ' . $binaryFiles['kill'] . ' -9 ' . $processIdPart;
		}

		$processIds = false;
		exec('ps -o ppid -o stat | grep Z | grep -v grep | awk \'{print $1}\'', $processIds);

		if (empty($processIds) === false) {
			$processIdsParts = '';

			foreach ($processIds as $processId) {
				$processIdsParts .= ' ' . $processId;
			}

			$killProcessCommands[] = 'sudo ' . $binaryFiles['kill'] . ' -9' . $processIdsParts;
			$killProcessCommands[] = 'sudo ' . $binaryFiles['telinit'] . ' u';
		}

		$killProcessCommands = implode("\n", $killProcessCommands);
		file_put_contents('/usr/local/firewall-security-api/node-action-' . $nodeAction . '-kill-process-commands-' . $currentProcessId . '.sh', $killProcessCommands);
		shell_exec('sudo chmod +x /usr/local/firewall-security-api/node-action-' . $nodeAction . '-kill-process-commands-' . $currentProcessId . '.sh');
		shell_exec('cd /usr/local/firewall-security-api/ && sudo ./node-action-' . $nodeAction . '-kill-process-commands-' . $currentProcessId . '.sh');
		unlink('/usr/local/firewall-security-api/node-action-' . $nodeAction . '-kill-process-commands-' . $currentProcessId . '.sh');
		return;
	}

	function _output($response) {
		echo json_encode($response);
		exit;
	}

	$response = array(
		'authenticatedStatus' => '1',
		'data' => array(),
		'message' => 'Invalid node endpoint request, please try again.',
		'validStatus' => '0'
	);

	if (empty($_SERVER['argv'][1]) === true) {
		_output($response);
	}

	$nodeSettingsData = file_get_contents('/usr/local/firewall-security-api/node-settings-data.json');
	$nodeSettingsData = json_decode($nodeSettingsData, true);

	if ($nodeSettingsData === false) {
		$response['message'] = 'Error listing node settings data, please try again.';
		_output($response);
	}

	$parameters = array(
		'action' => $_SERVER['argv'][1],
		'binaryFiles' => array(),
		'nodeAuthenticationToken' => $nodeSettingsData['authenticationToken'],
		'processId' => getmypid(),
		'systemEndpointDestination' => $nodeSettingsData['systemEndpointDestinationIpAddress'] . ':' . $nodeSettingsData['systemEndpointDestinationPortNumber'] . '/' . $nodeSettingsData['systemEndpointDestinationSubdirectory'],
		'systemEndpointDestinationIpAddress' => $nodeSettingsData['systemEndpointDestinationIpAddress'],
		'systemEndpointDestinationIpAddressType' => $nodeSettingsData['systemEndpointDestinationIpAddressType'],
		'systemEndpointDestinationIpAddressVersionNumber' => $nodeSettingsData['systemEndpointDestinationIpAddressVersionNumber'],
		'systemEndpointDestinationPortNumber' => $nodeSettingsData['systemEndpointDestinationPortNumber'],
		'systemEndpointDestinationSubdirectory' => $nodeSettingsData['systemEndpointDestinationSubdirectory'],
		'systemVersionNumber' => $nodeSettingsData['systemVersionNumber']
	);

	if ($parameters['processId'] === false) {
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
			'output' => 'invalid',
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
			'command' => $uniqueId,
			'name' => 'ip',
			'output' => 'ip help',
			'package' => 'iproute2'
		),
		array(
			'command' => '-h',
			'name' => 'ip6tables-restore',
			'output' => 'tables-restore',
			'package' => 'iptables'
		),
		array(
			'command' => $uniqueId,
			'name' => 'ipset',
			'output' => 'unknown ',
			'package' => 'ipset'
		),
		array(
			'command' => '-h',
			'name' => 'iptables-restore',
			'output' => 'tables-restore',
			'package' => 'iptables'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'kill',
			'output' => 'invalid',
			'package' => 'procps'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'netstat',
			'output' => 'invalid',
			'package' => 'net-tools'
		),
		array(
			'command' => '-v',
			'name' => 'php',
			'output' => 'PHP',
			'package' => 'php'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'prlimit',
			'output' => 'invalid',
			'package' => 'util-linux'
		),
		array(
			'command' => $uniqueId,
			'name' => 'service',
			'output' => 'unrecognized',
			'package' => 'systemd'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'sleep',
			'output' => 'invalid',
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
			'output' => 'invalid',
			'package' => 'systemd'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'telinit',
			'output' => 'invalid',
			'package' => 'systemd-sysv'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'timeout',
			'output' => 'invalid',
			'package' => 'coreutils'
		),
		array(
			'command' => $uniqueId,
			'name' => 'wget',
			'output' => 'unable to resolve host address',
			'package' => 'wget'
		)
	);

	foreach ($binaries as $binary) {
		$binaryFileListCommands = array(
			'#!/bin/bash',
			'whereis ' . $binary['name'] . ' | awk \'{ for (i=2; i<=NF; i++) print $i }\' | while read -r binaryFile; do echo $((sudo $binaryFile "' . $binary['command'] . '") 2>&1) | grep -c "' . $binary['output'] . '" && echo $binaryFile && break; done | tail -1'
		);
		$binaryFileListCommands = implode("\n", $binaryFileListCommands);
		file_put_contents('/usr/local/firewall-security-api/node-action-' . $parameters['action'] . '-binary-file-list-commands.sh', $binaryFileListCommands);
		chmod('/usr/local/firewall-security-api/node-action-' . $parameters['action'] . '-binary-file-list-commands.sh', 0755);
		unset($binaryFile);
		exec('cd /usr/local/firewall-security-api/ && sudo ./node-action-' . $parameters['action'] . '-binary-file-list-commands.sh', $binaryFile);
		$binaryFile = current($binaryFile);
		unlink('/usr/local/firewall-security-api/node-action-' . $parameters['action'] . '-binary-file-list-commands.sh');

		if (empty($binaryFile) === true) {
			shell_exec('sudo apt-get update');
			shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install ' . $binary['package']);
			$response['message'] = 'Error listing ' . $binary['name'] . ' binary file from the ' . $binary['package'] . ' package, please try again.';
			_output($response);
		}

		$parameters['binaryFiles'][$binary['name']] = $binaryFile;
	}

	if (
		(($parameters['action'] === 'process-network-interface-ip-addresses') === false) &&
		(($parameters['action'] === 'process-recursive-dns-destination') === false)
	) {
		if (($parameters['action'] === 'process-node-processes') === false) {
			exec('ps -h -o pid -o cmd $(pgrep php) | grep "node-endpoint.php node-action-' . $parameters['action'] . '" | awk \'{print $1}\'', $nodeActionProcessIds);

			if (empty($nodeActionProcessIds[1]) === false) {
				exit;
			}
		}

		$systemParameters = array(
			'action' => 'listSystemSettings',
			'nodeAuthenticationToken' => $parameters['nodeAuthenticationToken']
		);
		$encodedSystemParameters = json_encode($systemParameters);

		if ($encodedSystemParameters === false) {
			$response['message'] = 'Error listing system settings, please try again.';
			_output($response);
		}

		shell_exec('sudo ' . $parameters['binaryFiles']['wget'] . ' -O /usr/local/firewall-security-api/system-action-list-system-settings-response.json --connect-timeout=5 --dns-timeout=5 --no-dns-cache --post-data \'json=' . $encodedSystemParameters . '\' --read-timeout=60 --tries=1 ' . $nodeSettingsData['systemEndpointDestinationIpAddress'] . ':' . $nodeSettingsData['systemEndpointDestinationPortNumber'] . '/' . $nodeSettingsData['systemEndpointDestinationSubdirectory'] . '/system-endpoint.php');

		if (file_exists('/usr/local/firewall-security-api/system-action-list-system-settings-response.json') === false) {
			$response['message'] = 'Error listing system settings, please try again.';
			_output($response);
		}

		$systemSettingsResponse = file_get_contents('/usr/local/firewall-security-api/system-action-list-system-settings-response.json');
		$systemSettingsResponse = json_decode($systemSettingsResponse, true);

		if ($systemSettingsResponse === false) {
			$response['message'] = 'Error listing system settings, please try again.';
			_output($response);
		}

		if (($parameters['systemVersion'] < $systemSettingsResponse['version']) === true) {
			$systemFiles = json_decode($systemSettingsResponse['files'], true);

			foreach ($systemFiles as $systemFile) {
				// todo: kill existing $systemFile process
				// todo: update system file
			}
		}

		// todo: update system_endpoint_destination_address if changed
	}

	if ((strpos($parameters['action'], '/') === false) === false) {
		$response['message'] = 'Invalid node action, please try again.';
		_output($response);
	}

	if (file_exists('/usr/local/firewall-security-api/node-action-' . $parameters['action'] . '.php') === false) {
		$response['message'] = 'Error listing node action file, please try again.';
		_output($response);
	}

	require_once('/usr/local/firewall-security-api/node-action-' . $parameters['action'] . '.php');
	_output($response);
?>
