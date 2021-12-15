<?php
	if (empty($_SERVER['argv'][2]) === true) {
		echo 'Error deploying node, please try again.' . "\n";
		exit;
	}

	function _executeCommands($commands) {
		foreach ($commands as $command) {
			if (
				(empty($command) === false) &&
				(is_string($command) === true)
			) {
				echo shell_exec($command);
			}
		}

		return;
	}

	$supportedOperatingSystems = array(
		'debian' => array(
			'9' => array(
				'sources' => array(
					'aptitude' => array(
						'contents' => array(
							'deb http://deb.debian.org/debian stretch main',
							'deb-src http://deb.debian.org/debian stretch main',
							'deb http://deb.debian.org/debian stretch-updates main',
							'deb-src http://deb.debian.org/debian stretch-updates main',
							'deb http://security.debian.org/debian-security/ stretch/updates main',
							'deb-src http://security.debian.org/debian-security/ stretch/updates main'
						),
						'path' => '/etc/apt/sources.list'
					)
				)
			),
			'10' => array(
				'sources' => array(
					'aptitude' => array(
						'contents' => array(
							'deb http://deb.debian.org/debian buster main',
							'deb-src http://deb.debian.org/debian buster main',
							'deb http://deb.debian.org/debian buster-updates main',
							'deb-src http://deb.debian.org/debian buster-updates main',
							'deb http://security.debian.org/debian-security/ buster/updates main',
							'deb-src http://security.debian.org/debian-security/ buster/updates main'
						),
						'path' => '/etc/apt/sources.list'
					)
				)
			)
		),
		'ubuntu' => array(
			'18.04' => array(
				'sources' => array(
					'aptitude' => array(
						'contents' => array(
							'deb http://archive.ubuntu.com/ubuntu bionic main',
							'deb http://archive.ubuntu.com/ubuntu bionic-updates main',
							'deb http://archive.ubuntu.com/ubuntu bionic-backports main',
							'deb http://security.ubuntu.com/ubuntu bionic-security main',
							'deb-src http://archive.ubuntu.com/ubuntu bionic main',
							'deb-src http://archive.ubuntu.com/ubuntu bionic-backports main',
							'deb-src http://archive.ubuntu.com/ubuntu bionic-updates main',
							'deb-src http://security.ubuntu.com/ubuntu bionic-security main'
						),
						'path' => '/etc/apt/sources.list'
					)
				)
			),
			'20.04' => array(
				'sources' => array(
					'aptitude' => array(
						'contents' => array(
							'deb http://archive.ubuntu.com/ubuntu focal main',
							'deb http://archive.ubuntu.com/ubuntu focal-updates main',
							'deb http://archive.ubuntu.com/ubuntu focal-backports main',
							'deb http://security.ubuntu.com/ubuntu focal-security main',
							'deb-src http://archive.ubuntu.com/ubuntu focal main',
							'deb-src http://archive.ubuntu.com/ubuntu focal-backports main',
							'deb-src http://archive.ubuntu.com/ubuntu focal-updates main',
							'deb-src http://security.ubuntu.com/ubuntu focal-security main'
						),
						'path' => '/etc/apt/sources.list'
					)
				)
			)
		)
	);
	exec('sudo cat /etc/*-release 2>&1', $operatingSystemDetails);

	foreach ($operatingSystemDetails as $operatingSystemDetailKey => $operatingSystemDetail) {
		$operatingSystemDetail = explode('=', $operatingSystemDetail);

		if (empty($operatingSystemDetail[1]) === false) {
			$operatingSystemDetails[strtolower($operatingSystemDetail[0])] = trim($operatingSystemDetail[1], '"');
		}

		unset($operatingSystemDetails[$operatingSystemDetailKey]);
	}

	if (empty($supportedOperatingSystems[$operatingSystemDetails['id']][$operatingSystemDetails['version_id']]) === true) {
		echo 'Error detecting a supported operating system, please try again.' . "\n";
		exit;
	}

	$operatingSystemConfiguration = $supportedOperatingSystems[$operatingSystemDetails['id']][$operatingSystemDetails['version_id']];

	if (
		(file_exists($operatingSystemConfiguration['sources']['aptitude']['path']) === false) ||
		(file_put_contents($operatingSystemConfiguration['sources']['aptitude']['path'], implode("\n", $operatingSystemConfiguration['sources']['aptitude']['contents'])) === false)
	) {
		echo 'Error updating package sources, please try again.' . "\n";
		exit;
	}

	$commands = array(
		'sudo apt-get update',
		'sudo DEBIAN_FRONTEND=noninteractive apt-get -y install procps systemd'
	);
	_executeCommands($commands);
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
	$binaryFiles = array();

	foreach ($binaries as $binary) {
		$commands = array(
			'#!/bin/bash',
			'whereis ' . $binary['name'] . ' | awk \'{ for (i=2; i<=NF; i++) print $i }\' | while read -r binaryFile; do echo $((sudo $binaryFile "' . $binary['command'] . '") 2>&1) | grep -c "' . $binary['output'] . '" && echo $binaryFile && break; done | tail -1'
		);
		$commandsFile = '/tmp/commands.sh';

		if (file_exists($commandsFile) === true) {
			unlink($commandsFile);
		}

		file_put_contents($commandsFile, implode("\n", $commands));
		chmod($commandsFile, 0755);
		exec('cd /tmp/ && sudo ./' . basename($commandsFile), $binaryFile);
		$binaryFile = current($binaryFile);
		unlink($commandsFile);

		if (empty($binaryFile) === true) {
			shell_exec('sudo apt-get update');
			shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install ' . $binary['package']);
			echo 'Error listing ' . $binary['name'] . ' binary file from the ' . $binary['package'] . ' package, please try again.' . "\n";
			exit;
		}

		$binaryFiles[$binary['name']] = $binaryFile;
	}

	$nodeAuthenticationToken = $_SERVER['argv'][1];
	$systemActionActivateNodeResponseFile = '/tmp/system_action_activate_node_response.json';
	$systemEndpointDestinationAddress = $_SERVER['argv'][2];
	$wgetParameters = '--no-dns-cache --retry-connrefused --timeout=60 --tries=2';
	$commands = array(
		'sudo ' . $binaryFiles['sysctl'] . ' -w vm.overcommit_memory=0',
		'sudo wget -O ' . $systemActionActivateNodeResponseFile . ' ' . $wgetParameters . ' --post-data "json={\"action\":\"activate_node\",\"node_authentication_token\":\"' . $nodeAuthenticationToken . '\"}" ' . $systemEndpointDestinationAddress . '/system_endpoint.php'
	);
	_executeCommands($commands);

	if (file_exists($systemActionActivateNodeResponseFile) === false) {
		echo 'Error activating node, please try again.' . "\n";
		exit;
	}

	$systemActionActivateNodeResponse = json_decode(file_get_contents($systemActionActivateNodeResponseFile), true);
	unlink($systemActionActivateNodeResponseFile);

	if (empty($systemActionActivateNodeResponse['message']) === true) {
		echo 'Error activating node, please try again.' . "\n";
		exit;
	}

	echo $systemActionActivateNodeResponse['message'] . "\n";

	if (($systemActionActivateNodeResponse['valid_status'] === '0') === true) {
		exit;
	}

	// todo
?>
