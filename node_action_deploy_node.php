<?php
	if (empty($_SERVER['argv'][2]) === true) {
		echo 'Error deploying node, please try again.' . "\n";
		exit;
	}

	function _killProcessIds($binaryFiles, $processIds) {
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

		if (file_put_contents('/usr/local/nodecompute/node_action_deploy_node_commands.sh', $killProcessCommands) === false) {
			echo 'Error adding kill process ID commands, please try again.' . "\n";
			exit;
		}

		shell_exec('sudo chmod +x /usr/local/nodecompute/node_action_deploy_node_commands.sh');
		shell_exec('cd /usr/local/nodecompute/ && sudo ./node_action_deploy_node_commands.sh');
		return;
	}

	// todo: modify php.ini settings
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
	exec('sudo cat /etc/*-release 2>&1', $imageDetails);

	foreach ($imageDetails as $imageDetailKey => $imageDetail) {
		$imageDetail = explode('=', $imageDetail);
		unset($imageDetails[$imageDetailKey]);

		if (empty($imageDetail[1]) === false) {
			$imageDetailKey = strtolower($imageDetail[0]);
			$imageDetails[$imageDetailKey] = trim($imageDetail[1], '"');
		}
	}

	if (empty($packageSources[$imageDetails['id']][$imageDetails['version_id']]) === true) {
		echo 'Error installing on unsupported ' . $imageDetails['id'] . ' ' . $imageDetails['version_id'] . ' image, please try again.' . "\n";
		exit;
	}

	$packageSources = implode("\n", $packageSources[$imageDetails['id']][$imageDetails['version_id']]);

	if (file_put_contents('/etc/apt/sources.list', $packageSources) === false) {
		echo 'Error adding package sources, please try again.' . "\n";
		exit;
	}

	mkdir('/usr/local/nodecompute/');
	chmod('/usr/local/nodecompute/', 0755);

	if (is_dir('/usr/local/nodecompute/') === false) {
		echo 'Error adding root directory, please try again.' . "\n";
		exit;
	}

	shell_exec('sudo apt-get update');
	exec('fuser -v /var/cache/debconf/config.dat', $lockedProcessIds);
	_killProcessIds($binaryFiles, $lockedProcessIds);
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install apache2 apache2-utils bind9 bind9utils build-essential coreutils cron curl dnsutils net-tools php-curl procps syslinux systemd util-linux');
	shell_exec('sudo /etc/init.d/apache2 stop');
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
			'command' => $uniqueId,
			'name' => 'ip',
			'output' => 'ip help',
			'package' => 'iproute2'
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
	$binaryFiles = array();

	foreach ($binaries as $binary) {
		$commands = array(
			'#!/bin/bash',
			'whereis ' . $binary['name'] . ' | awk \'{ for (i=2; i<=NF; i++) print $i }\' | while read -r binaryFile; do echo $((sudo $binaryFile "' . $binary['command'] . '") 2>&1) | grep -c "' . $binary['output'] . '" && echo $binaryFile && break; done | tail -1'
		);
		$commands = implode("\n", $commands);

		if (file_put_contents('/usr/local/nodecompute/node_action_deploy_node_commands.sh', $commands) === false) {
			echo 'Error adding binary file list commands, please try again.' . "\n";
			exit;
		}

		chmod('/usr/local/nodecompute/node_action_deploy_node_commands.sh', 0755);
		exec('cd /usr/local/nodecompute/ && sudo ./node_action_deploy_node_commands.sh', $binaryFile);
		$binaryFile = current($binaryFile);

		if (empty($binaryFile) === true) {
			shell_exec('sudo apt-get update');
			shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install ' . $binary['package']);
			echo 'Error listing ' . $binary['name'] . ' binary file from the ' . $binary['package'] . ' package, please try again.' . "\n";
			exit;
		}

		$binaryFiles[$binary['name']] = $binaryFile;
	}

	shell_exec('sudo ' . $binaryFiles['sysctl'] . ' -w vm.overcommit_memory=0');
	shell_exec('sudo ' . $binaryFiles['wget'] . ' -O /usr/local/nodecompute/system_action_activate_node_response.json --no-dns-cache --post-data "json={\"action\":\"activate_node\",\"node_authentication_token\":\"' . $_SERVER['argv'][1] . '\"}" --retry-connrefused --timeout=60 --tries=2 ' . $_SERVER['argv'][2] . '/system_endpoint.php');

	if (file_exists('/usr/local/nodecompute/system_action_activate_node_response.json') === false) {
		echo 'Error activating node, please try again.' . "\n";
		exit;
	}

	$systemActionActivateNodeResponse = file_get_contents('/usr/local/nodecompute/system_action_activate_node_response.json');
	$systemActionActivateNodeResponse = json_decode($systemActionActivateNodeResponse, true);

	if (empty($systemActionActivateNodeResponse['message']) === true) {
		echo 'Error activating node, please try again.' . "\n";
		exit;
	}

	echo $systemActionActivateNodeResponse['message'] . "\n";

	if (($systemActionActivateNodeResponse['valid_status'] === '0') === true) {
		exit;
	}

	shell_exec('sudo ' . $binaryFiles['wget'] . ' -O /usr/local/nodecompute/system_action_process_node_response.json --no-dns-cache --post-data "json={\"action\":\"process_node\",\"node_authentication_token\":\"' . $_SERVER['argv'][1] . '\"}" --timeout=600 ' . $_SERVER['argv'][2] . '/system_endpoint.php');

	if (file_exists('/usr/local/nodecompute/system_action_process_node_response.json') === false) {
		echo 'Error processing node, please try again.' . "\n";
		exit;
	}

	$systemActionProcessNodeResponse = file_get_contents('/usr/local/nodecompute/system_action_process_node_response.json');
	$systemActionProcessNodeResponse = json_decode($systemActionProcessNodeResponse, true);

	if (empty($systemActionProcessNodeResponse['message']) === true) {
		echo 'Error processing node, please try again.' . "\n";
		exit;
	}

	echo $systemActionProcessNodeResponse['message'] . "\n";

	if (($systemActionProcessNodeResponse['valid_status'] === '0') === true) {
		exit;
	}

	exec('sudo ' . $binaryFiles['netstat'] . ' -i | grep -v face | awk \'NR==1{print $1}\' 2>&1', $networkInterfaceName);
	$networkInterfaceName = current($networkInterfaceName);

	if (empty($networkInterfaceName) === true) {
		echo 'Error listing network interface name, please try again.' . "\n";
		exit;
	}

	$nodeActionProcessNetworkInterfaceIpAddressesCommands = array(
		'<?php'
	);

	foreach ($systemActionProcessNodeResponse['data']['node_ip_address_versions'] as $nodeIpAddressVersionNetworkMask => $nodeIpAddressVersionNumber) {
		foreach ($systemActionProcessNodeResponse['data']['node_ip_addresses'][$nodeIpAddressVersionNetworkMask] as $nodeIpAddress) {
			$nodeActionProcessNetworkInterfaceIpAddressesCommands[] = 'shell_exec(\'sudo ' . $binaryFiles['ip'] . ' -' . $nodeIpAddressVersionNumber . ' addr add ' . $nodeIpAddress . '/' . $nodeIpAddressVersionNetworkMask . ' dev ' . $networkInterfaceName . '\');';
		}
	}

	$nodeActionProcessNetworkInterfaceIpAddressesCommands = implode("\n", $nodeActionProcessNetworkInterfaceIpAddressesCommands);

	if (file_put_contents('/usr/local/nodecompute/node_action_process_network_interface_ip_addresses.php', $nodeActionProcessNetworkInterfaceIpAddressesCommands) === false) {
		echo 'Error processing network interface IP addresses, please try again.' . "\n";
		exit;
	}

	shell_exec('sudo ' . $binaryFiles['php'] . ' /usr/local/nodecompute/node_action_process_network_interface_ip_addresses.php');
	$recursiveDnsNodeProcessDefaultServiceName = 'named';

	if (is_dir('/etc/default/bind9/') === true) {
		$recursiveDnsNodeProcessDefaultServiceName = 'bind9';
	}

	exec('pgrep ' . $recursiveDnsNodeProcessDefaultServiceName, $recursiveDnsDefaultProcessIds);
	_killProcessIds($binaryFiles, $recursiveDnsDefaultProcessIds);
	shell_exec('sudo mkdir -m 0775 /var/run/named/');
	shell_exec('sudo rm -rf /usr/src/3proxy/ && sudo mkdir -p /usr/src/3proxy/');
	shell_exec('cd /usr/src/3proxy/ && sudo ' . $binaryFiles['wget'] . ' -O 3proxy.tar.gz --no-dns-cache --timeout=60 https://github.com/3proxy/3proxy/archive/refs/tags/0.9.3.tar.gz');
	shell_exec('cd /usr/src/3proxy/ && sudo tar -xvzf 3proxy.tar.gz');
	shell_exec('cd /usr/src/3proxy/*/ && sudo make -f Makefile.Linux');
	shell_exec('cd /usr/src/3proxy/*/ && sudo make -f Makefile.Linux install');
	shell_exec('sudo mkdir -p /var/log/3proxy/');
	$nodeFiles = array(
		'node_endpoint.php',
		'process_node_processes.php',
		'process_node_resource_usage_logs.php',
		'process_node_user_request_logs.php',
		'process_recursive_dns_destination.php'
	);

	foreach ($nodeFiles as $nodeFile) {
		$systemActionDownloadNodeFileContentsParameters = array(
			'action' => 'download_node_file_contents',
			'node_authentication_token' => $_SERVER['argv'][1],
			'where' => array(
				'node_file' => $nodeFile
			)
		);
		$systemActionDownloadNodeFileContentsParameters = json_encode($systemActionDownloadNodeFileContentsParameters);

		if ($systemActionDownloadNodeFileContentsParameters === false) {
			echo 'Error downloading node file contents for ' . $nodeFile . ', please try again.' . "\n";
			exit;
		}

		shell_exec('sudo ' . $binaryFiles['wget'] . ' -O /usr/local/nodecompute/' . $nodeFile . ' --no-dns-cache --post-data \'json=' . $systemActionDownloadNodeFileContentsParameters . '\' --timeout=10 ' . $_SERVER['argv'][2] . '/system_endpoint.php');

		if (file_exists('/usr/local/nodecompute/' . $nodeFile) === false) {
			echo 'Error downloading node file contents for ' . $nodeFile . ', please try again.' . "\n";
			exit;
		}

		$systemActionDownloadNodeFileContentsResponse = file_get_contents('/usr/local/nodecompute/' . $nodeFile);

		if (empty($systemActionDownloadNodeFileContentsResponse)) === true) {
			echo 'Error downloading node file contents for ' . $nodeFile . ', please try again.' . "\n";
			exit;
		}

		$systemActionDownloadNodeFileContentsResponse = json_decode($systemActionDownloadNodeFileContentsResponse, true);

		if (empty($systemActionDownloadNodeFileContentsResponse['message']) === false) {
			echo $systemActionDownloadNodeFileContentsResponse['message'] . "\n";
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
	$crontabCommandIndex = array_search('# nodecompute_processes', $crontabCommands);

	if (is_int($crontabCommandIndex) === true) {
		while (is_int($crontabCommandIndex) === true) {
			unset($crontabCommands[$crontabCommandIndex]);
			$crontabCommandIndex++;

			if (strpos($crontabCommands[$crontabCommandIndex], ' nodecompute_processes') === false) {
				$crontabCommandIndex = false;
			}
		}
	}

	$crontabCommands += array(
		'# nodecompute_processes',
		'@reboot root sudo ' . $binaryFiles['php'] . ' /usr/local/nodecompute/node_endpoint.php process_network_interface_ip_addresses nodecompute_processes'
		'*/10 * * * * root sudo ' . $binaryFiles['php'] . ' /usr/local/nodecompute/node_endpoint.php process_node_process_bitcoin_cash_cryptocurrency_blockchain_blocks nodecompute_processes',
		'*/10 * * * * root sudo ' . $binaryFiles['php'] . ' /usr/local/nodecompute/node_endpoint.php process_node_process_bitcoin_cash_cryptocurrency_blockchain_worker_block_header_data nodecompute_processes',
		'*/10 * * * * root sudo ' . $binaryFiles['php'] . ' /usr/local/nodecompute/node_endpoint.php process_node_process_cryptocurrency_blockchain_worker_settings nodecompute_processes',
		'* * * * * root sudo ' . $binaryFiles['php'] . ' /usr/local/nodecompute/node_endpoint.php process_node_process_node_user_request_logs nodecompute_processes',
		'* * * * * root sudo ' . $binaryFiles['php'] . ' /usr/local/nodecompute/node_endpoint.php process_node_process_resource_usage_logs nodecompute_processes',
		'* * * * * root sudo ' . $binaryFiles['php'] . ' /usr/local/nodecompute/node_endpoint.php process_node_processes nodecompute_processes',
		'* * * * * root sudo ' . $binaryFiles['php'] . ' /usr/local/nodecompute/node_endpoint.php process_node_resource_usage_logs nodecompute_processes',
		'* * * * * root sudo ' . $binaryFiles['php'] . ' /usr/local/nodecompute/node_endpoint.php process_recursive_dns_destination nodecompute_processes',
	);
	$crontabCommands = implode("\n", $crontabCommands);

	if (file_put_contents('/etc/crontab', $crontabCommands) === false) {
		echo 'Error adding crontab commands, please try again.' . "\n";
		exit;
	}

	shell_exec('sudo ' . $binaryFiles['wget'] . ' -O /usr/local/nodecompute/system_action_deploy_node_response.json --no-dns-cache --post-data "json={\"action\":\"deploy_node\",\"node_authentication_token\":\"' . $_SERVER['argv'][1] . '\"}" --timeout=60 ' . $_SERVER['argv'][2] . '/system_endpoint.php');

	if (file_exists('/usr/local/nodecompute/system_action_deploy_node_response.json') === false) {
		echo 'Error deploying node, please try again.' . "\n";
		exit;
	}

	$systemActionDeployNodeResponse = file_get_contents('/usr/local/nodecompute/system_action_deploy_node_response.json');
	$systemActionDeployNodeResponse = json_decode($systemActionDeployNodeResponse, true);

	if (empty($systemActionDeployNodeResponse['message']) === true) {
		echo 'Error deploying node, please try again.' . "\n";
		exit;
	}

	echo $systemActionDeployNodeResponse['message'] . "\n";

	if (($systemActionProcessNodeResponse['valid_status'] === '1') === true) {
		$nodeData = array(
			'authentication_token' => $_SERVER['argv'][1],
			'system_endpoint_destination_address' => $_SERVER['argv'][2],
			'system_version_number' => '1'
		);
		$nodeData = json_encode($nodeData);

		if (file_put_contents('/usr/local/nodecompute/node_data.json', $nodeData) === false) {
			echo 'Error adding node data, please try again.' . "\n";
			exit;
		}

		shell_exec('sudo ' . $binaryFiles['crontab'] . ' /etc/crontab');
	}

	exit;
?>
