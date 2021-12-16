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

	if (empty($_SERVER['argv'][2]) === true) {
		echo 'Error deploying node, please try again.' . "\n";
		exit;
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

	shell_exec('sudo apt-get update');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install procps systemd');
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
			'name' => 'ip',
			'output' => 'ip help',
			'package' => 'iproute2'
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
	$systemEndpointDestinationAddress = $_SERVER['argv'][2];
	$wgetParameters = '--no-dns-cache --retry-connrefused --timeout=60 --tries=2';
	shell_exec('sudo ' . $binaryFiles['sysctl'] . ' -w vm.overcommit_memory=0');
	shell_exec('sudo wget -O /tmp/system_action_activate_node_response.json ' . $wgetParameters . ' --post-data "json={\"action\":\"activate_node\",\"node_authentication_token\":\"' . $nodeAuthenticationToken . '\"}" ' . $systemEndpointDestinationAddress . '/system_endpoint.php');

	if (file_exists('/tmp/system_action_activate_node_response.json') === false) {
		echo 'Error activating node, please try again.' . "\n";
		exit;
	}

	$systemActionActivateNodeResponse = json_decode(file_get_contents('/tmp/system_action_activate_node_response.json'), true);
	unlink('/tmp/system_action_activate_node_response.json');

	if (empty($systemActionActivateNodeResponse['message']) === true) {
		echo 'Error activating node, please try again.' . "\n";
		exit;
	}

	echo $systemActionActivateNodeResponse['message'] . "\n";

	if (($systemActionActivateNodeResponse['valid_status'] === '0') === true) {
		exit;
	}

	shell_exec('sudo wget -O /tmp/system_action_process_node_response.json ' . $wgetParameters . ' --post-data "json={\"action\":\"process\",\"node_authentication_token\":\"' . $nodeAuthenticationToken . '\"}" ' . $systemEndpointDestinationAddress . '/system_endpoint.php');

	if (file_exists('/tmp/system_action_process_node_response.json') === false) {
		echo 'Error processing node, please try again.' . "\n";
		exit;
	}

	$systemActionProcessNodeResponse = json_decode(file_get_contents('/tmp/node_process_response.json'), true);
	unlink('/tmp/system_action_process_node_response.json');

	if (empty($systemActionProcessNodeResponse['message']) === true) {
		echo 'Error processing node, please try again.' . "\n";
		exit;
	}

	echo $systemActionProcessNodeResponse['message'] . "\n";

	if (($systemActionProcessNodeResponse['valid_status'] === '0') === true) {
		exit;
	}

	exec('fuser -v /var/cache/debconf/config.dat', $lockedProcessIds);
	$parameters['process_ids'] = $lockedProcessIds;
	_killProcessIds($parameters);
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install apache2 apache2-utils bind9 bind9utils build-essential cron curl dnsutils net-tools php-curl syslinux systemd util-linux');
	shell_exec('sudo /etc/init.d/apache2 stop');

	if (is_dir('/usr/local/ghostcompute/') === true) {
		rmdir('/usr/local/ghostcompute/');
	}

	mkdir('/usr/local/ghostcompute/');
	chmod('/usr/local/ghostcompute/', 0755);

	if (is_dir('/usr/local/ghostcompute/') === false) {
		echo 'Error adding root directory, please try again.' . "\n";
		exit;
	}

	exec('sudo ' . $parameters['binary_files']['netstat'] . ' -i | grep -v face | awk \'NR==1{print $1}\' 2>&1', $nodeNetworkInterfaceName);
	$nodeNetworkInterfaceName = current($nodeNetworkInterfaceName);

	if (empty($nodeNetworkInterfaceName) === true) {
		echo 'Error detecting node network interface, please try again.' . "\n";
		exit;
	}

	$nodeActionAddNodeNetworkInterfaceIpAddressFileContents = array();

	foreach ($systemActionProcessNodeResponse['data']['node_ip_address_versions'] as $nodeIpAddressVersionNetworkMask => $nodeIpAddressVersion) {
		foreach ($systemActionProcessNodeResponse['data']['node_ip_addresses'][$nodeIpAddressVersionNetworkMask] as $nodeIpAddress) {
			$nodeActionAddNodeNetworkInterfaceIpAddressFileContents[] = 'shell_exec(\'sudo ' . $parameters['binary_files']['ip'] . ' -' . $nodeIpAddressVersion . ' addr add ' . $nodeIpAddress . '/' . $nodeIpAddressVersionNetworkMask . ' dev ' . $nodeNetworkInterfaceName . '\');';
		}
	}

	$nodeActionAddNodeNetworkInterfaceIpAddressFileContents = '<?php shell_exec(\'' . implode('\'); shell_exec(\'', $nodeActionAddNodeNetworkInterfaceIpAddressFileContents) . '\'); ?>';
	file_put_contents('/usr/local/ghostcompute/node_action_add_node_network_interface_ip_addresses.php', $nodeActionAddNodeNetworkInterfaceIpAddressFileContents);

	if (file_get_contents('/usr/local/ghostcompute/node_action_add_node_network_interface_ip_addresses.php') === false) {
		echo 'Error adding network interface IP addresses, please try again.' . "\n";
		exit;
	}

	shell_exec('sudo ' . $parameters['binary_files']['php'] . ' /usr/local/ghostcompute/node_action_add_node_network_interface_ip_addresses.php');
	// todo: add recursive DNS config
	// todo: add proxy config
	$crontabCommands = array(
		'# [Start]',
		'* * * * * root sudo ' . $parameters['binary_files']['php'] . ' /usr/local/ghostcompute/node_action_process_node_processes.php',
		'@reboot root sudo ' . $parameters['binary_files']['php'] . ' /usr/local/ghostcompute/node_action_add_node_network_interface_ip_addresses.php',
		'# [Stop]'
	);

	if (file_exists('/etc/crontab') === false) {
		echo 'Error listing crontab file contents, please try again.' . "\n";
		exit;
	}

	$crontabFileContents = file_get_contents('/etc/crontab');

	if ($crontabFileContents === false) {
		echo 'Error listing crontab file contents, please try again.' . "\n";
		exit;
	}

	$crontabFileContents = explode("\n", $crontabFileContents);
	// todo: delete existing crontab commands before appending
	$crontabFileContents = array_merge($crontabFileContents, $crontabCommands);
	// todo
?>
