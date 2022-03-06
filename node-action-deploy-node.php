<?php
	if (empty($_SERVER['argv'][2]) === true) {
		header('Content-Type: text/plain');
		echo file_get_contents('/var/www/firewall-security-api/node-action-deploy-node.php');
		exit;
	}

	function _killProcessIds($binaryFiles, $processIds) {
		$killProcessCommands = array(
			'#!/bin/bash'
		);
		$processIdsParts = array();
		$processIdsPartsKey = 0;

		foreach ($processIds as $processIdsKey => $processId) {
			if ((($processIdsKey % 10) === 0) === true) {
				$processIdsPartsKey++;
				$processIdsParts[$processIdsPartsKey] = '';
			}

			$processIdsParts[$processIdsPartsKey] .= $processId . ' ';
		}

		foreach ($processIdsParts as $processIdsPart) {
			$killProcessCommands[] = 'sudo ' . $binaryFiles['kill'] . ' -9 ' . $processIdsPart;
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

		if (file_put_contents('/usr/local/firewall-security-api/node-action-deploy-node-commands.sh', $killProcessCommands) === false) {
			echo 'Error adding kill process ID commands, please try again.' . "\n";
			exit;
		}

		shell_exec('sudo chmod +x /usr/local/firewall-security-api/node-action-deploy-node-commands.sh');
		shell_exec('cd /usr/local/firewall-security-api/ && sudo ./node-action-deploy-node-commands.sh');
		unlink('/usr/local/firewall-security-api/node-action-deploy-node-commands.sh');
		return;
	}

	$packageSources = array(
		'debian' => array(
			'10' => array(
				'deb http://deb.debian.org/debian buster main',
				'deb-src http://deb.debian.org/debian buster main',
				'deb http://deb.debian.org/debian buster-updates main',
				'deb-src http://deb.debian.org/debian buster-updates main',
				'deb http://security.debian.org/debian-security/ buster/updates main',
				'deb-src http://security.debian.org/debian-security/ buster/updates main'
			),
			'11' => array(
				'deb http://deb.debian.org/debian bullseye main',
				'deb-src http://deb.debian.org/debian bullseye main',
				'deb http://deb.debian.org/debian bullseye-updates main',
				'deb-src http://deb.debian.org/debian bullseye-updates main',
				'deb http://security.debian.org/debian-security/ bullseye-security main',
				'deb-src http://security.debian.org/debian-security/ bullseye-security main'
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
		echo 'Error installing on unsupported ' . ucwords($imageDetails['id']) . ' ' . $imageDetails['version_id'] . ' image, please try again.' . "\n";
		exit;
	}

	$packageSources = implode("\n", $packageSources[$imageDetails['id']][$imageDetails['version_id']]);
	file_put_contents('/etc/apt/sources.list', $packageSources);

	if (
		(is_dir('/etc/php/7.3/') === false) &&
		(is_dir('/etc/php/7.4/') === false)
	) {
		shell_exec('sudo rm -rf /etc/php/ /usr/bin/php* /usr/lib/php/ /var/lib/php/');
		echo 'Error downloading PHP, please try again.' . "\n";
		exit;
	}

	if (is_dir('/usr/local/firewall-security-api/') === true) {
		shell_exec('sudo rm -rf /usr/local/firewall-security-api/');
	}

	mkdir('/usr/local/firewall-security-api/');
	chmod('/usr/local/firewall-security-api/', 0755);

	if (is_dir('/usr/local/firewall-security-api/') === false) {
		echo 'Error adding root directory, please try again.' . "\n";
		exit;
	}

	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install procps');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install systemd');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install systemd-sysv');
	$uniqueId = '_' . uniqid();
	$binaries = array(
		array(
			'command' => '-' . $uniqueId,
			'name' => 'kill',
			'output' => 'invalid ',
			'package' => 'procps'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'telinit',
			'output' => 'invalid ',
			'package' => 'systemd-sysv'
		)
	);
	$binaryFiles = array();

	foreach ($binaries as $binary) {
		$commands = array(
			'#!/bin/bash',
			'whereis ' . $binary['name'] . ' | awk \'{ for (i=2; i<=NF; i++) print $i }\' | while read -r binaryFile; do echo $((sudo $binaryFile "' . $binary['command'] . '") 2>&1) | grep -c "' . $binary['output'] . '" && echo $binaryFile && break; done | tail -1'
		);
		$commands = implode("\n", $commands);
		file_put_contents('/usr/local/firewall-security-api/node-action-deploy-node-commands.sh', $commands);
		chmod('/usr/local/firewall-security-api/node-action-deploy-node-commands.sh', 0755);
		exec('cd /usr/local/firewall-security-api/ && sudo ./node-action-deploy-node-commands.sh', $binaryFile);
		$binaryFile = current($binaryFile);
		unlink('/usr/local/firewall-security-api/node-action-deploy-node-commands.sh');

		if (empty($binaryFile) === true) {
			shell_exec('sudo apt-get update');
			shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install ' . $binary['package']);
			echo 'Error listing ' . $binary['name'] . ' binary file from the ' . $binary['package'] . ' package, please try again.' . "\n";
			exit;
		}

		$binaryFiles[$binary['name']] = $binaryFile;
	}

	exec('fuser -v /var/cache/debconf/config.dat', $lockedProcessIds);
	_killProcessIds($binaryFiles, $lockedProcessIds);
	shell_exec('sudo apt-get update');
	$lockedProcessIds = false;
	exec('fuser -v /var/cache/debconf/config.dat', $lockedProcessIds);
	_killProcessIds($binaryFiles, $lockedProcessIds);
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install apache2');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install apache2-utils');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install bind9');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install bind9utils');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install build-essential');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install coreutils');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install cron');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install curl');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install dnsutils');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install iproute2');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install ipset');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install net-tools');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php-curl');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install procps');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install syslinux');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install systemd');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install systemd-sysv');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install util-linux');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y purge conntrack');
	shell_exec('sudo /etc/init.d/apache2 stop');
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
			'output' => 'invalid ',
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
			'output' => 'invalid ',
			'package' => 'procps'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'netstat',
			'output' => 'invalid ',
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
			'output' => 'unrecognized ',
			'package' => 'systemd'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'sleep',
			'output' => 'invalid ',
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
			'output' => 'invalid ',
			'package' => 'systemd'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'telinit',
			'output' => 'invalid ',
			'package' => 'systemd-sysv'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'timeout',
			'output' => 'invalid ',
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
		$commands = array(
			'#!/bin/bash',
			'whereis ' . $binary['name'] . ' | awk \'{ for (i=2; i<=NF; i++) print $i }\' | while read -r binaryFile; do echo $((sudo $binaryFile "' . $binary['command'] . '") 2>&1) | grep -c "' . $binary['output'] . '" && echo $binaryFile && break; done | tail -1'
		);
		$commands = implode("\n", $commands);
		file_put_contents('/usr/local/firewall-security-api/node-action-deploy-node-commands.sh', $commands);
		chmod('/usr/local/firewall-security-api/node-action-deploy-node-commands.sh', 0755);
		exec('cd /usr/local/firewall-security-api/ && sudo ./node-action-deploy-node-commands.sh', $binaryFile);
		$binaryFile = current($binaryFile);
		unlink('/usr/local/firewall-security-api/node-action-deploy-node-commands.sh');

		if (empty($binaryFile) === true) {
			shell_exec('sudo apt-get update');
			shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install ' . $binary['package']);
			echo 'Error listing ' . $binary['name'] . ' binary file from the ' . $binary['package'] . ' package, please try again.' . "\n";
			exit;
		}

		$binaryFiles[$binary['name']] = $binaryFile;
	}

	shell_exec('sudo ' . $binaryFiles['sysctl'] . ' -w vm.overcommit_memory=0');
	$phpSettings = array(
		'allow_url_fopen = On',
		'allow_url_include = Off',
		'auto_append_file =',
		'auto_globals_jit = On',
		'auto_prepend_file =',
		'bcmath.scale = 0',
		'cli_server.color = Off',
		'default_charset = "UTF-8"',
		'default_mimetype = "text/html"',
		'default_socket_timeout = -1',
		'disable_classes =',
		'disable_functions = pcntl_alarm,pcntl_fork,pcntl_waitpid,pcntl_wait,pcntl_wifexited,pcntl_wifstopped,pcntl_wifsignaled,pcntl_wifcontinued,pcntl_wexitstatus,pcntl_wtermsig,pcntl_wstopsig,pcntl_signal,pcntl_signal_get_handler,pcntl_signal_dispatch,pcntl_get_last_error,pcntl_strerror,pcntl_sigprocmask,pcntl_sigwaitinfo,pcntl_sigtimedwait,pcntl_exec,pcntl_getpriority,pcntl_setpriority,pcntl_async_signals,pcntl_unshare,',
		'display_errors = Off',
		'display_startup_errors = Off',
		'doc_root =',
		'enable_dl = Off',
		'engine = On',
		'error_reporting = 0',
		'expose_php = Off',
		'implicit_flush = Off',
		'ignore_repeated_errors = Off',
		'ignore_repeated_source = Off',
		'ldap.max_links = -1',
		'log_errors = Off',
		'mail.add_x_header = Off',
		'max_execution_time = -1',
		'max_input_time = -1',
		'memory_limit = -1',
		'mysqli.allow_persistent = On',
		'mysqli.max_persistent = -1',
		'mysqli.default_host =',
		'mysqli.default_port = 3306',
		'mysqli.default_pw =',
		'mysqli.default_socket =',
		'mysqli.default_user =',
		'mysqli.max_links = -1',
		'mysqli.reconnect = Off',
		'mysqlnd.collect_memory_statistics = Off',
		'mysqlnd.collect_statistics = On',
		'output_buffering = 4096',
		'pdo_mysql.default_socket =',
		'post_max_size = 0',
		'precision = 14',
		'register_argc_argv = Off',
		'report_memleaks = Off',
		'request_order = "GP"',
		'serialize_precision = -1',
		'session.auto_start = 0',
		'session.cache_expire = 180',
		'session.cache_limiter = nocache',
		'session.cookie_domain =',
		'session.cookie_httponly =',
		'session.cookie_lifetime = 0',
		'session.cookie_path = /',
		'session.cookie_samesite =',
		'session.gc_divisor = 1000',
		'session.gc_maxlifetime = 1440',
		'session.gc_probability = 0',
		'session.name = PHPSESSID',
		'session.referer_check =',
		'session.save_handler = files',
		'session.serialize_handler = php',
		'session.sid_bits_per_character = 5',
		'session.sid_length = 26',
		'session.trans_sid_tags = "a=href,area=href,frame=src,form="',
		'session.use_cookies = 1',
		'session.use_only_cookies = 1',
		'session.use_strict_mode = 0',
		'session.use_trans_sid = 0',
		'short_open_tag = Off',
		'smtp_port = 25',
		'tidy.clean_output = Off',
		'unserialize_callback_func =',
		'user_dir =',
		'variables_order = "GPCS"',
		'zend.assertions = -1',
		'zend.enable_gc = On',
		'zend.exception_ignore_args = On',
		'zlib.output_compression = Off'
	);
	$phpSettings = implode("\n", $phpSettings);
	$phpVersion = '7.3';

	if (is_dir('/etc/php/7.4/') === true) {
		$phpVersion = '7.4';
	}

	file_put_contents('/etc/php/' . $phpVersion . '/cli/php.ini', $phpSettings);
	$systemActionListSystemSettingsParameters = array(
		'action' => 'listSystemSettings',
		'data' => array(
			'key',
			'value'
		),
		'nodeAuthenticationToken' => $_SERVER['argv'][1],
		'where' => array(
			'key' => array(
				'endpointDestinationIpAddress',
				'endpointDestinationIpAddressType',
				'endpointDestinationIpAddressVersionNumber',
				'endpointDestinationPortNumber',
				'endpointDestinationSubdirectory'
			)
		)
	);
	$systemActionListSystemSettingsParameters = json_encode($systemActionListSystemSettingsParameters);

	if (file_exists('/usr/local/firewall-security-api/system-action-list-system-settings-response.json') === true) {
		unlink('/usr/local/firewall-security-api/system-action-list-system-settings-response.json');
	}

	shell_exec('sudo ' . $binaryFiles['wget'] . ' -O /usr/local/firewall-security-api/system-action-list-system-settings-response.json --connect-timeout=5 --dns-timeout=5 --no-dns-cache --post-data \'json=' . $systemActionListSystemSettingsParameters . '\' --read-timeout=60 --tries=1 ' . $_SERVER['argv'][2] . '/system-endpoint.php');

	if (file_exists('/usr/local/firewall-security-api/system-action-list-system-settings-response.json') === false) {
		echo 'Error listing system settings, please try again.' . "\n";
		exit;
	}

	$systemActionListSystemSettingsResponse = file_get_contents('/usr/local/firewall-security-api/system-action-list-system-settings-response.json');
	$systemActionListSystemSettingsResponse = json_decode($systemActionListSystemSettingsResponse, true);

	if (empty($systemActionListSystemSettingsResponse['data']) === true) {
		echo 'Error listing system settings, please try again.' . "\n";
		exit;
	}

	$nodeSettingsData = array();

	foreach ($systemActionListSystemSettingsResponse['data'] as $systemActionListSystemSettingsResponseData) {
		$nodeSettingsData['system' . ucwords($systemActionListSystemSettingsResponseData['key'])] = $systemActionListSystemSettingsResponseData['value'];
	}

	$systemActionActivateNodeParameters = array(
		'action' => 'activateNode',
		'nodeAuthenticationToken' => $_SERVER['argv'][1]
	);
	$systemActionActivateNodeParameters = json_encode($systemActionActivateNodeParameters);

	if (file_exists('/usr/local/firewall-security-api/system-action-activate-node-response.json') === true) {
		unlink('/usr/local/firewall-security-api/system-action-activate-node-response.json');
	}

	shell_exec('sudo ' . $binaryFiles['wget'] . ' -O /usr/local/firewall-security-api/system-action-activate-node-response.json --connect-timeout=5 --dns-timeout=5 --no-dns-cache --post-data \'json=' . $systemActionActivateNodeParameters . '\' --read-timeout=60 --tries=1 ' . $nodeSettingsData['systemEndpointDestinationIpAddress'] . ':' . $nodeSettingsData['systemEndpointDestinationPortNumber'] . '/' . $nodeSettingsData['systemEndpointDestinationSubdirectory'] . '/system-endpoint.php');

	if (file_exists('/usr/local/firewall-security-api/system-action-activate-node-response.json') === false) {
		echo 'Error activating node, please try again.' . "\n";
		exit;
	}

	$systemActionActivateNodeResponse = file_get_contents('/usr/local/firewall-security-api/system-action-activate-node-response.json');
	$systemActionActivateNodeResponse = json_decode($systemActionActivateNodeResponse, true);

	if (empty($systemActionActivateNodeResponse['message']) === true) {
		echo 'Error activating node, please try again.' . "\n";
		exit;
	}

	echo $systemActionActivateNodeResponse['message'] . "\n";

	if (($systemActionActivateNodeResponse['validatedStatus'] === '0') === true) {
		exit;
	}

	$systemActionProcessNodeParameters = array(
		'action' => 'processNode',
		'nodeAuthenticationToken' => $_SERVER['argv'][1]
	);
	$systemActionProcessNodeParameters = json_encode($systemActionProcessNodeParameters);

	if (file_exists('/usr/local/firewall-security-api/system-action-process-node-response.json') === true) {
		unlink('/usr/local/firewall-security-api/system-action-process-node-response.json');
	}

	shell_exec('sudo ' . $binaryFiles['wget'] . ' -O /usr/local/firewall-security-api/system-action-process-node-response.json  --connect-timeout=5 --dns-timeout=5 --no-dns-cache --post-data \'json=' . $systemActionProcessNodeParameters . '\' --read-timeout=600 --tries=1 ' . $nodeSettingsData['systemEndpointDestinationIpAddress'] . ':' . $nodeSettingsData['systemEndpointDestinationPortNumber'] . '/' . $nodeSettingsData['systemEndpointDestinationSubdirectory'] . '/system-endpoint.php');

	if (file_exists('/usr/local/firewall-security-api/system-action-process-node-response.json') === false) {
		echo 'Error processing node, please try again.' . "\n";
		exit;
	}

	$systemActionProcessNodeResponse = file_get_contents('/usr/local/firewall-security-api/system-action-process-node-response.json');
	$systemActionProcessNodeResponse = json_decode($systemActionProcessNodeResponse, true);

	if (empty($systemActionProcessNodeResponse['message']) === true) {
		echo 'Error processing node, please try again.' . "\n";
		exit;
	}

	echo $systemActionProcessNodeResponse['message'] . "\n";

	if (($systemActionProcessNodeResponse['validatedStatus'] === '0') === true) {
		exit;
	}

	exec('sudo ' . $binaryFiles['netstat'] . ' -i | grep -v face | awk \'NR==1{print $1}\' 2>&1', $networkInterfaceName);
	$networkInterfaceName = current($networkInterfaceName);

	if (empty($networkInterfaceName) === true) {
		echo 'Error listing network interface name, please try again.' . "\n";
		exit;
	}

	$nodeActionProcessNetworkInterfaceIpAddressesCommands = array(
		'<?php',
		'if (empty($parameters) === true) {',
		'exit;',
		'}'
	);

	foreach ($systemActionProcessNodeResponse['data']['nodeIpAddressVersionNumbers'] as $nodeIpAddressVersionNetworkMask => $nodeIpAddressVersionNumber) {
		foreach ($systemActionProcessNodeResponse['data']['nodeIpAddresses'][$nodeIpAddressVersionNumber] as $nodeIpAddress) {
			$nodeActionProcessNetworkInterfaceIpAddressesCommands[] = 'shell_exec(\'sudo ' . $binaryFiles['ip'] . ' -' . $nodeIpAddressVersionNumber . ' addr add ' . $nodeIpAddress . '/' . $nodeIpAddressVersionNetworkMask . ' dev ' . $networkInterfaceName . '\');';
			shell_exec('sudo ' . $binaryFiles['ip'] . ' -' . $nodeIpAddressVersionNumber . ' addr add ' . $nodeIpAddress . '/' . $nodeIpAddressVersionNetworkMask . ' dev ' . $networkInterfaceName);
		}
	}

	$nodeActionProcessNetworkInterfaceIpAddressesCommands = implode("\n", $nodeActionProcessNetworkInterfaceIpAddressesCommands);
	file_put_contents('/usr/local/firewall-security-api/node-action-process-network-interface-ip-addresses.php', $nodeActionProcessNetworkInterfaceIpAddressesCommands);
	$recursiveDnsNodeProcessDefaultServiceName = 'named';

	if (is_dir('/etc/default/bind9/') === true) {
		$recursiveDnsNodeProcessDefaultServiceName = 'bind9';
	}

	exec('pgrep ' . $recursiveDnsNodeProcessDefaultServiceName, $recursiveDnsDefaultProcessIds);
	_killProcessIds($binaryFiles, $recursiveDnsDefaultProcessIds);
	shell_exec('sudo mkdir -m 0775 /var/run/named/');
	shell_exec('sudo rm -rf /usr/src/3proxy/ && sudo mkdir -p /usr/src/3proxy/');
	shell_exec('cd /usr/src/3proxy/ && sudo ' . $binaryFiles['wget'] . ' -O 3proxy.tar.gz --connect-timeout=5 --dns-timeout=5 --no-dns-cache --read-timeout=60 --tries=1 https://github.com/3proxy/3proxy/archive/refs/tags/0.9.3.tar.gz');
	shell_exec('cd /usr/src/3proxy/ && sudo tar -xvzf 3proxy.tar.gz');
	shell_exec('cd /usr/src/3proxy/*/ && sudo make -f Makefile.Linux');
	shell_exec('cd /usr/src/3proxy/*/ && sudo make -f Makefile.Linux install');
	shell_exec('sudo mkdir -p /var/log/3proxy/');
	$nodeFiles = array(
		'node-action-process-node-process-node-user-request-logs.php',
		'node-action-process-node-process-resource-usage-logs.php',
		'node-action-process-node-processes.php',
		'node-action-process-node-resource-usage-logs.php',
		'node-action-process-recursive-dns-destination.php',
		'node-endpoint.php'
	);

	foreach ($nodeFiles as $nodeFile) {
		if (file_exists('/usr/local/firewall-security-api/' . $nodeFile) === false) {
			$systemActionDownloadNodeFileParameters = array(
				'action' => 'downloadNodeFile',
				'nodeAuthenticationToken' => $_SERVER['argv'][1],
				'where' => array(
					'nodeFile' => $nodeFile
				)
			);
			$systemActionDownloadNodeFileParameters = json_encode($systemActionDownloadNodeFileParameters);
			shell_exec('sudo ' . $binaryFiles['wget'] . ' -O /usr/local/firewall-security-api/' . $nodeFile . ' --connect-timeout=5 --dns-timeout=5 --no-dns-cache --post-data \'json=' . $systemActionDownloadNodeFileParameters . '\' --read-timeout=60 --tries=1 ' . $nodeSettingsData['systemEndpointDestinationIpAddress'] . ':' . $nodeSettingsData['systemEndpointDestinationPortNumber'] . '/' . $nodeSettingsData['systemEndpointDestinationSubdirectory'] . '/system-endpoint.php');

			if (file_exists('/usr/local/firewall-security-api/' . $nodeFile) === false) {
				echo 'Error downloading node file ' . $nodeFile . ', please try again.' . "\n";
				exit;
			}

			$systemActionDownloadNodeFileResponse = file_get_contents('/usr/local/firewall-security-api/' . $nodeFile);

			if (empty($systemActionDownloadNodeFileResponse) === true) {
				echo 'Error downloading node file ' . $nodeFile . ', please try again.' . "\n";
				exit;
			}

			$systemActionDownloadNodeFileResponse = json_decode($systemActionDownloadNodeFileResponse, true);

			if (empty($systemActionDownloadNodeFileResponse['message']) === false) {
				echo $systemActionDownloadNodeFileResponse['message'] . "\n";
				exit;
			}
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
	$crontabCommandIndex = array_search('# firewall-security-api-node-processes', $crontabCommands);

	if (is_int($crontabCommandIndex) === true) {
		while (is_int($crontabCommandIndex) === true) {
			unset($crontabCommands[$crontabCommandIndex]);
			$crontabCommandIndex++;

			if (strpos($crontabCommands[$crontabCommandIndex], ' firewall-security-api-node-processes') === false) {
				$crontabCommandIndex = false;
			}
		}
	}

	$crontabCommands[] = '# firewall-security-api-node-processes';
	$crontabCommands[] = '@reboot root sudo ' . $binaryFiles['php'] . ' /usr/local/firewall-security-api/node-endpoint.php process-network-interface-ip-addresses firewall-security-api-node-processes';
	$crontabCommands[] = '* * * * * root sudo ' . $binaryFiles['php'] . ' /usr/local/firewall-security-api/node-endpoint.php process-node-process-node-user-request-logs firewall-security-api-node-processes';
	$crontabCommands[] = '* * * * * root sudo ' . $binaryFiles['php'] . ' /usr/local/firewall-security-api/node-endpoint.php process-node-process-resource-usage-logs firewall-security-api-node-processes';
	$crontabCommands[] = '* * * * * root sudo ' . $binaryFiles['php'] . ' /usr/local/firewall-security-api/node-endpoint.php process-node-processes firewall-security-api-node-processes';
	$crontabCommands[] = '* * * * * root sudo ' . $binaryFiles['php'] . ' /usr/local/firewall-security-api/node-endpoint.php process-node-resource-usage-logs firewall-security-api-node-processes';
	$crontabCommands[] = '* * * * * root sudo ' . $binaryFiles['timeout'] . ' 600 ' . $binaryFiles['php'] . ' /usr/local/firewall-security-api/node-endpoint.php process-recursive-dns-destination firewall-security-api-node-processes';
	$crontabCommands[] = '';
	$crontabCommands = implode("\n", $crontabCommands);
	file_put_contents('/etc/crontab', $crontabCommands);
	$systemActionDeployNodeParameters = array(
		'action' => 'deployNode',
		'nodeAuthenticationToken' => $_SERVER['argv'][1]
	);
	$systemActionDeployNodeParameters = json_encode($systemActionDeployNodeParameters);

	if (file_exists('/usr/local/firewall-security-api/system-action-deploy-node-response.json') === true) {
		unlink('/usr/local/firewall-security-api/system-action-deploy-node-response.json');
	}

	shell_exec('sudo ' . $binaryFiles['wget'] . ' -O /usr/local/firewall-security-api/system-action-deploy-node-response.json --connect-timeout=5 --dns-timeout=5 --no-dns-cache --post-data \'json=' . $systemActionDeployNodeParameters . '\' --read-timeout=60 --tries=1 ' . $nodeSettingsData['systemEndpointDestinationIpAddress'] . ':' . $nodeSettingsData['systemEndpointDestinationPortNumber'] . '/' . $nodeSettingsData['systemEndpointDestinationSubdirectory'] . '/system-endpoint.php');

	if (file_exists('/usr/local/firewall-security-api/system-action-deploy-node-response.json') === false) {
		echo 'Error deploying node, please try again.' . "\n";
		exit;
	}

	$systemActionDeployNodeResponse = file_get_contents('/usr/local/firewall-security-api/system-action-deploy-node-response.json');
	$systemActionDeployNodeResponse = json_decode($systemActionDeployNodeResponse, true);

	if (empty($systemActionDeployNodeResponse['message']) === true) {
		echo 'Error deploying node, please try again.' . "\n";
		exit;
	}

	if (($systemActionProcessNodeResponse['validatedStatus'] === '1') === true) {
		$nodeSettingsData['authenticationToken'] = $_SERVER['argv'][1];
		$nodeSettingsData['systemVersionNumber'] = '1';
		$nodeSettingsData = json_encode($nodeSettingsData);
		file_put_contents('/usr/local/firewall-security-api/node-settings-data.json', $nodeSettingsData);
		shell_exec('sudo ' . $binaryFiles['crontab'] . ' /etc/crontab');
	}

	echo $systemActionDeployNodeResponse['message'] . "\n";
	exit;
?>
