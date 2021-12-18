<?php
	function _killProcessIds($parameters) {
		$commands = array(
			'#!/bin/bash'
		);
		$processIdParts = array_chunk($parameters['process_ids'], 10);

		foreach ($processIdParts as $processIds) {
			$processIds = implode(' ', $processIds);
			$commands[] = 'sudo kill -9 ' . $processIds;
		}

		$commands[] = 'sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk \'{print $1}\')';
		$commands[] = 'sudo ' . $parameters['binary_files']['telinit'] . ' u';
		$commands = implode("\n", $commands);
		$filePutContentsResponse = file_put_contents('/usr/local/ghostcompute/node_action_deploy_node_commands.sh', $commands);

		if (empty($filePutContentsResponse) === true) {
			echo 'Error adding kill process ID commands, please try again.' . "\n";
			exit;
		}

		shell_exec('sudo chmod +x /usr/local/ghostcompute/node_action_deploy_node_commands.sh');
		shell_exec('cd /usr/local/ghostcompute/ && sudo ./node_action_deploy_node_commands.sh');
		return;
	}

	if (empty($_SERVER['argv'][2]) === true) {
		echo 'Error deploying node, please try again.' . "\n";
		exit;
	}

	$packageSources = array(
		'debian' => array(
			'9' => array(
				'deb http://deb.debian.org/debian stretch main',
				'deb-src http://deb.debian.org/debian stretch main',
				'deb http://deb.debian.org/debian stretch-updates main',
				'deb-src http://deb.debian.org/debian stretch-updates main',
				'deb http://security.debian.org/debian-security/ stretch/updates main',
				'deb-src http://security.debian.org/debian-security/ stretch/updates main'
			),
			'10' => array(
				'deb http://deb.debian.org/debian buster main',
				'deb-src http://deb.debian.org/debian buster main',
				'deb http://deb.debian.org/debian buster-updates main',
				'deb-src http://deb.debian.org/debian buster-updates main',
				'deb http://security.debian.org/debian-security/ buster/updates main',
				'deb-src http://security.debian.org/debian-security/ buster/updates main'
			)
		),
		'ubuntu' => array(
			'18.04' => array(
				'deb http://archive.ubuntu.com/ubuntu bionic main',
				'deb http://archive.ubuntu.com/ubuntu bionic-updates main',
				'deb http://archive.ubuntu.com/ubuntu bionic-backports main',
				'deb http://security.ubuntu.com/ubuntu bionic-security main',
				'deb-src http://archive.ubuntu.com/ubuntu bionic main',
				'deb-src http://archive.ubuntu.com/ubuntu bionic-backports main',
				'deb-src http://archive.ubuntu.com/ubuntu bionic-updates main',
				'deb-src http://security.ubuntu.com/ubuntu bionic-security main'
			),
			'20.04' => array(
				'deb http://archive.ubuntu.com/ubuntu focal main',
				'deb http://archive.ubuntu.com/ubuntu focal-updates main',
				'deb http://archive.ubuntu.com/ubuntu focal-backports main',
				'deb http://security.ubuntu.com/ubuntu focal-security main',
				'deb-src http://archive.ubuntu.com/ubuntu focal main',
				'deb-src http://archive.ubuntu.com/ubuntu focal-backports main',
				'deb-src http://archive.ubuntu.com/ubuntu focal-updates main',
				'deb-src http://security.ubuntu.com/ubuntu focal-security main'
			)
		)
	);
	exec('sudo cat /etc/*-release 2>&1', $operatingSystemDetails);

	foreach ($operatingSystemDetails as $operatingSystemDetailKey => $operatingSystemDetail) {
		$operatingSystemDetail = explode('=', $operatingSystemDetail);
		unset($operatingSystemDetails[$operatingSystemDetailKey]);

		if (empty($operatingSystemDetail[1]) === false) {
			$operatingSystemDetailKey = strtolower($operatingSystemDetail[0]);
			$operatingSystemDetails[$operatingSystemDetailKey] = trim($operatingSystemDetail[1], '"');
		}
	}

	if (empty($packageSources[$operatingSystemDetails['id']][$operatingSystemDetails['version_id']]) === true) {
		echo 'Error detecting a supported operating system, please try again.' . "\n";
		exit;
	}

	$packageSources = implode("\n", $packageSources[$operatingSystemDetails['id']][$operatingSystemDetails['version_id']]);
	$filePutContentsResponse = file_put_contents('/etc/apt/sources.list', $packageSources);

	if (empty($filePutContentsResponse) === true) {
		echo 'Error updating package sources, please try again.' . "\n";
		exit;
	}

	mkdir('/usr/local/ghostcompute/');
	chmod('/usr/local/ghostcompute/', 0755);

	if (is_dir('/usr/local/ghostcompute/') === false) {
		echo 'Error adding root directory, please try again.' . "\n";
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
			'command' => '_' . $uniqueId,
			'name' => 'curl',
			'output' => 'Could not resolve host',
			'package' => 'curl'
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
		$commands = implode("\n", $commands);
		$filePutContentsResponse = file_put_contents('/usr/local/ghostcompute/node_action_deploy_node_commands.sh', $commands);

		if (empty($filePutContentsResponse) === true) {
			echo 'Error adding binary file list commands, please try again.' . "\n";
			exit;
		}

		chmod('/usr/local/ghostcompute/node_action_deploy_node_commands.sh', 0755);
		exec('cd /usr/local/ghostcompute/ && sudo ./node_action_deploy_node_commands.sh', $binaryFile);
		$binaryFile = current($binaryFile);

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
	shell_exec('sudo ' . $binaryFiles['sysctl'] . ' -w vm.overcommit_memory=0');
	shell_exec('sudo wget -O /usr/local/ghostcompute/system_action_activate_node_response.json --no-dns-cache --post-data "json={\"action\":\"activate_node\",\"node_authentication_token\":\"' . $nodeAuthenticationToken . '\"}" --retry-connrefused --timeout=60 --tries=2 ' . $systemEndpointDestinationAddress . '/system_endpoint.php');

	if (file_exists('/usr/local/ghostcompute/system_action_activate_node_response.json') === false) {
		echo 'Error activating node, please try again.' . "\n";
		exit;
	}

	$systemActionActivateNodeResponse = file_get_contents('/usr/local/ghostcompute/system_action_activate_node_response.json');
	$systemActionActivateNodeResponse = json_decode($systemActionActivateNodeResponse, true);

	if (empty($systemActionActivateNodeResponse['message']) === true) {
		echo 'Error activating node, please try again.' . "\n";
		exit;
	}

	echo $systemActionActivateNodeResponse['message'] . "\n";

	if (($systemActionActivateNodeResponse['valid_status'] === '0') === true) {
		exit;
	}

	shell_exec('sudo wget -O /usr/local/ghostcompute/system_action_process_node_response.json --no-dns-cache --post-data "json={\"action\":\"process_node\",\"node_authentication_token\":\"' . $nodeAuthenticationToken . '\"}" --timeout=600 ' . $systemEndpointDestinationAddress . '/system_endpoint.php');

	if (file_exists('/usr/local/ghostcompute/system_action_process_node_response.json') === false) {
		echo 'Error processing node, please try again.' . "\n";
		exit;
	}

	$systemActionProcessNodeResponse = file_get_contents('/usr/local/ghostcompute/system_action_process_node_response.json');
	$systemActionProcessNodeResponse = json_decode($systemActionProcessNodeResponse, true);

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
	exec('sudo ' . $parameters['binary_files']['netstat'] . ' -i | grep -v face | awk \'NR==1{print $1}\' 2>&1', $nodeNetworkInterfaceName);
	$nodeNetworkInterfaceName = current($nodeNetworkInterfaceName);

	if (empty($nodeNetworkInterfaceName) === true) {
		echo 'Error detecting node network interface, please try again.' . "\n";
		exit;
	}

	$nodeActionProcessNodeNetworkInterfaceIpAddressFileContents = array();

	foreach ($systemActionProcessNodeResponse['data']['node_ip_address_versions'] as $nodeIpAddressVersionNetworkMask => $nodeIpAddressVersion) {
		foreach ($systemActionProcessNodeResponse['data']['node_ip_addresses'][$nodeIpAddressVersionNetworkMask] as $nodeIpAddress) {
			$nodeActionProcessNodeNetworkInterfaceIpAddressFileContents[] = 'shell_exec(\'sudo ' . $parameters['binary_files']['ip'] . ' -' . $nodeIpAddressVersion . ' addr add ' . $nodeIpAddress . '/' . $nodeIpAddressVersionNetworkMask . ' dev ' . $nodeNetworkInterfaceName . '\');';
		}
	}

	// todo: add interface IPs in JSON file with node_action_process_node_network_interface_ip_addresses.php file executed in crontab every 5 minutes because binary path listing may fail on @reboot
	$nodeActionProcessNodeNetworkInterfaceIpAddressFileContents = '<?php shell_exec(\'' . implode('\'); shell_exec(\'', $nodeActionProcessNodeNetworkInterfaceIpAddressFileContents) . '\'); ?>';
	$nodeActionProcessNodeNetworkInterfaceIpAddressFileContentsResponse = file_put_contents('/usr/local/ghostcompute/node_action_process_node_network_interface_ip_addresses.php', $nodeActionProcessNodeNetworkInterfaceIpAddressFileContents);

	if (empty($nodeActionProcessNodeNetworkInterfaceIpAddressFileContentsResponse) === true) {
		echo 'Error processing network interface IP addresses, please try again.' . "\n";
		exit;
	}

	shell_exec('sudo ' . $parameters['binary_files']['php'] . ' /usr/local/ghostcompute/node_action_process_node_network_interface_ip_addresses.php');
	$recursiveDnsNodeProcessDefaultServiceName = 'named';

	if (is_dir('/etc/default/bind9') === true) {
		$recursiveDnsNodeProcessDefaultServiceName = 'bind9';
	}

	exec('pgrep ' . $recursiveDnsNodeProcessDefaultServiceName, $recursiveDnsDefaultProcessIds);
	$parameters['process_ids'] = $recursiveDnsDefaultProcessIds;
	_killProcessIds($parameters);
	shell_exec('sudo mkdir -m 0775 /var/run/named');
	shell_exec('sudo rm -rf /usr/src/3proxy/ && sudo mkdir -p /usr/src/3proxy/');
	shell_exec('cd /usr/src/3proxy/ && sudo wget -O 3proxy.tar.gz --no-dns-cache --timeout=60 https://github.com/3proxy/3proxy/archive/refs/tags/0.9.3.tar.gz');
	shell_exec('cd /usr/src/3proxy/ && sudo tar -xvzf 3proxy.tar.gz');
	shell_exec('cd /usr/src/3proxy/*/ && sudo make -f Makefile.Linux && sudo make -f Makefile.Linux install');
	shell_exec('sudo mkdir -p /var/log/3proxy');
	$nodeActions = array(
		'process_node_processes',
		'process_node_resource_usage_logs',
		'process_node_user_blockchain_mining',
		'process_node_user_request_logs',
		'process_node_system_recursive_dns_destination'
	);

	foreach ($nodeActions as $nodeAction) {
		shell_exec('sudo wget -O /usr/local/ghostcompute/node_action_' . $nodeAction . '.php --no-dns-cache --post-data "json={\"action\":\"download_node_action_file_contents\",\"node_authentication_token\":\"' . $nodeAuthenticationToken . '\",\"where\":{\"node_action\":\"' . $nodeAction . '\"}}" --timeout=60 ' . $systemEndpointDestinationAddress . '/system_endpoint.php');

		if (file_exists('/usr/local/ghostcompute/node_action_' . $nodeAction . '.php') === false) {
			echo 'Error downloading node action file contents, please try again.' . "\n";
			exit;
		}

		$nodeActionFileContentsResponse = file_get_contents('node_action_' . $nodeAction . '.php');

		if (empty($nodeActionFileContentsResponse)) === true) {
			echo 'Error downloading node action file contents, please try again.' . "\n";
			exit;
		}

		$nodeActionFileContentsResponse = json_decode($nodeActionFileContentsResponse, true);

		if (empty($nodeActionFileContentsResponse['message']) === false) {
			echo $nodeActionFileContentsResponse['message'] . "\n";
			exit;
		}
	}

	if (file_exists('/etc/crontab') === false) {
		echo 'Error listing crontab commands, please try again.' . "\n";
		exit;
	}

	$crontabCommands = file_get_contents('/etc/crontab');

	if (empty($crontabCommands) === true) {
		echo 'Error listing crontab commands, please try again.' . "\n";
		exit;
	}

	$crontabCommands = explode("\n", $crontabCommands);
	$crontabCommandIndex = array_search('# ghostcompute', $crontabCommands);

	if (is_int($crontabCommandIndex) === true) {
		while (is_int($crontabCommandIndex) === true) {
			unset($crontabCommands[$crontabCommandIndex]);
			$crontabCommandIndex++;

			if (strpos($crontabCommands[$crontabCommandIndex], 'ghostcompute') === false) {
				$crontabCommandIndex = false;
			}
		}
	}

	$crontabCommands += array(
		'# ghostcompute',
		'* * * * * root sudo ' . $parameters['binary_files']['php'] . ' /usr/local/ghostcompute/node_endpoint.php /usr/local/ghostcompute/process_node_processes',
		'* * * * * root sudo ' . $parameters['binary_files']['php'] . ' /usr/local/ghostcompute/node_endpoint.php /usr/local/ghostcompute/process_node_resource_usage_logs',
		// todo: add process_node_user_blockchain_mining with parameters
		'* * * * * root sudo ' . $parameters['binary_files']['php'] . ' /usr/local/ghostcompute/node_endpoint.php /usr/local/ghostcompute/process_node_user_request_logs',
		'* * * * * root sudo ' . $parameters['binary_files']['php'] . ' /usr/local/ghostcompute/node_endpoint.php /usr/local/ghostcompute/process_node_system_recursive_dns_destination',
		'@reboot root sudo ' . $parameters['binary_files']['php'] . ' /usr/local/ghostcompute/node_endpoint.php /usr/local/ghostcompute/process_node_network_interface_ip_addresses'
	);
	$crontabCommands = implode("\n", $crontabCommands);
	$filePutContentsResponse = file_put_contents('/etc/crontab', $crontabCommands);

	if (empty($filePutContentsResponse) === true) {
		echo 'Error adding crontab commands, please try again.' . "\n";
		exit;
	}

	shell_exec('sudo wget -O /usr/local/ghostcompute/system_action_deploy_node_response.json --no-dns-cache --post-data "json={\"action\":\"deploy_node\",\"node_authentication_token\":\"' . $nodeAuthenticationToken . '\"}" --timeout=60 ' . $systemEndpointDestinationAddress . '/system_endpoint.php');

	if (file_exists('/usr/local/ghostcompute/system_action_deploy_node_response.json') === false) {
		echo 'Error deploying node, please try again.' . "\n";
		exit;
	}

	$systemActionDeployNodeResponse = json_decode(file_get_contents('/usr/local/ghostcompute/system_action_deploy_node_response.json'), true);

	if (empty($systemActionDeployNodeResponse['message']) === true) {
		echo 'Error deploying node, please try again.' . "\n";
		exit;
	}

	echo $systemActionDeployNodeResponse['message'] . "\n";

	if (($systemActionProcessNodeResponse['valid_status'] === '1') === true) {
		shell_exec('sudo ' . $parameters['binary_files']['crontab'] . ' /etc/crontab');
	}

	exit;
?>
