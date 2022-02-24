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

		if (file_put_contents('/usr/local/cloud_node_automation_api/node_action_deploy_node_commands.sh', $killProcessCommands) === false) {
			echo 'Error adding kill process ID commands, please try again.' . "\n";
			exit;
		}

		shell_exec('sudo chmod +x /usr/local/cloud_node_automation_api/node_action_deploy_node_commands.sh');
		shell_exec('cd /usr/local/cloud_node_automation_api/ && sudo ./node_action_deploy_node_commands.sh');
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
		),
		'ubuntu' => array(
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

	if (
		(is_dir('/etc/php/7.3/') === false) &&
		(is_dir('/etc/php/7.4/') === false)
	) {
		shell_exec('sudo rm -rf /etc/php/ /usr/bin/php* /usr/lib/php/ /var/lib/php/');
		echo 'Error downloading PHP, please try again.' . "\n";
		exit;
	}

	mkdir('/usr/local/cloud_node_automation_api/');
	chmod('/usr/local/cloud_node_automation_api/', 0755);

	if (is_dir('/usr/local/cloud_node_automation_api/') === false) {
		echo 'Error adding root directory, please try again.' . "\n";
		exit;
	}

	exec('fuser -v /var/cache/debconf/config.dat', $lockedProcessIds);
	_killProcessIds($binaryFiles, $lockedProcessIds);
	shell_exec('sudo apt-get update');
	exec('fuser -v /var/cache/debconf/config.dat', $lockedProcessIds);
	_killProcessIds($binaryFiles, $lockedProcessIds);
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install apache2 apache2-utils bind9 bind9utils build-essential coreutils cron curl dnsutils net-tools php-curl procps syslinux systemd util-linux');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y purge conntrack');
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
			'command' => $uniqueId,
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

		if (file_put_contents('/usr/local/cloud_node_automation_api/node_action_deploy_node_commands.sh', $commands) === false) {
			echo 'Error adding binary file list commands, please try again.' . "\n";
			exit;
		}

		chmod('/usr/local/cloud_node_automation_api/node_action_deploy_node_commands.sh', 0755);
		exec('cd /usr/local/cloud_node_automation_api/ && sudo ./node_action_deploy_node_commands.sh', $binaryFile);
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
		'action' => 'list_system_settings',
		'data' => array(
			'name',
			'value'
		),
		'node_authentication_token' => $_SERVER['argv'][1],
		'where' => array(
			'name' => array(
				'endpoint_destination_ip_address',
				'endpoint_destination_ip_address_type',
				'endpoint_destination_ip_address_version_number'
			)
		)
	);
	$systemActionListSystemSettingsParameters = json_encode($systemActionListSystemSettingsParameters);
	unlink('/usr/local/cloud_node_automation_api/system_action_list_system_settings_response.json');
	shell_exec('sudo ' . $binaryFiles['wget'] . ' -O /usr/local/cloud_node_automation_api/system_action_list_system_settings_response.json --connect-timeout=5 --dns-timeout=5 --no-dns-cache --post-data \'json=' . $systemActionListSystemSettingsParameters . '\' --read-timeout=60 --tries=1 ' . $_SERVER['argv'][2] . '/system_endpoint.php');

	if (file_exists('/usr/local/cloud_node_automation_api/system_action_list_system_settings_response.json') === false) {
		echo 'Error listing system settings, please try again.' . "\n";
		exit;
	}

	$systemActionListSystemSettingsResponse = file_get_contents('/usr/local/cloud_node_automation_api/system_action_list_system_settings_response.json');
	$systemActionListSystemSettingsResponse = json_decode($systemActionListSystemSettingsResponse, true);

	if (empty($systemActionListSystemSettingsResponse['data']) === true) {
		echo 'Error listing system settings, please try again.' . "\n";
		exit;
	}

	$nodeSettingsData = array();

	foreach ($systemActionListSystemSettingsResponse['data'] as $systemActionListSystemSettingsResponseData) {
		$nodeSettingsData['system_' . $systemActionListSystemSettingsResponseData['name']] = $systemActionListSystemSettingsResponseData['value'];
	}

	$systemActionActivateNodeParameters = array(
		'action' => 'activate_node',
		'node_authentication_token' => $_SERVER['argv'][1]
	);
	$systemActionActivateNodeParameters = json_encode($systemActionActivateNodeParameters);
	unlink('/usr/local/cloud_node_automation_api/system_action_activate_node_response.json');
	shell_exec('sudo ' . $binaryFiles['wget'] . ' -O /usr/local/cloud_node_automation_api/system_action_activate_node_response.json --connect-timeout=5 --dns-timeout=5 --no-dns-cache --post-data \'json=' . $systemActionActivateNodeParameters . '\' --read-timeout=60 --tries=1 ' . $nodeSettingsData['system_endpoint_destination_ip_address'] . '/system_endpoint.php');

	if (file_exists('/usr/local/cloud_node_automation_api/system_action_activate_node_response.json') === false) {
		echo 'Error activating node, please try again.' . "\n";
		exit;
	}

	$systemActionActivateNodeResponse = file_get_contents('/usr/local/cloud_node_automation_api/system_action_activate_node_response.json');
	$systemActionActivateNodeResponse = json_decode($systemActionActivateNodeResponse, true);

	if (empty($systemActionActivateNodeResponse['message']) === true) {
		echo 'Error activating node, please try again.' . "\n";
		exit;
	}

	echo $systemActionActivateNodeResponse['message'] . "\n";

	if (($systemActionActivateNodeResponse['valid_status'] === '0') === true) {
		exit;
	}

	$systemActionProcessNodeParameters = array(
		'action' => 'process_node',
		'node_authentication_token' => $_SERVER['argv'][1]
	);
	$systemActionProcessNodeParameters = json_encode($systemActionProcessNodeParameters);
	unlink('/usr/local/cloud_node_automation_api/system_action_process_node_response.json');
	shell_exec('sudo ' . $binaryFiles['wget'] . ' -O /usr/local/cloud_node_automation_api/system_action_process_node_response.json  --connect-timeout=5 --dns-timeout=5 --no-dns-cache --post-data \'json=' . $systemActionProcessNodeParameters . '\' --read-timeout=600 --tries=1 ' . $nodeSettingsData['system_endpoint_destination_ip_address'] . '/system_endpoint.php');

	if (file_exists('/usr/local/cloud_node_automation_api/system_action_process_node_response.json') === false) {
		echo 'Error processing node, please try again.' . "\n";
		exit;
	}

	$systemActionProcessNodeResponse = file_get_contents('/usr/local/cloud_node_automation_api/system_action_process_node_response.json');
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
		'<?php',
		'if (empty($parameters) === true) {',
		'exit;',
		'}'
	);

	foreach ($systemActionProcessNodeResponse['data']['node_ip_address_versions'] as $nodeIpAddressVersionNetworkMask => $nodeIpAddressVersionNumber) {
		foreach ($systemActionProcessNodeResponse['data']['node_ip_addresses'][$nodeIpAddressVersionNetworkMask] as $nodeIpAddress) {
			$nodeActionProcessNetworkInterfaceIpAddressesCommands[] = 'shell_exec(\'sudo ' . $binaryFiles['ip'] . ' -' . $nodeIpAddressVersionNumber . ' addr add ' . $nodeIpAddress . '/' . $nodeIpAddressVersionNetworkMask . ' dev ' . $networkInterfaceName . '\');';
			shell_exec('sudo ' . $binaryFiles['ip'] . ' -' . $nodeIpAddressVersionNumber . ' addr add ' . $nodeIpAddress . '/' . $nodeIpAddressVersionNetworkMask . ' dev ' . $networkInterfaceName);
		}
	}

	$nodeActionProcessNetworkInterfaceIpAddressesCommands = implode("\n", $nodeActionProcessNetworkInterfaceIpAddressesCommands);

	if (file_put_contents('/usr/local/cloud_node_automation_api/node_action_process_network_interface_ip_addresses.php', $nodeActionProcessNetworkInterfaceIpAddressesCommands) === false) {
		echo 'Error processing network interface IP addresses, please try again.' . "\n";
		exit;
	}

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
		'node_action_process_node_process_node_user_request_logs.php',
		'node_action_process_node_process_resource_usage_logs.php',
		'node_action_process_node_processes.php',
		'node_action_process_node_resource_usage_logs.php',
		'node_action_process_recursive_dns_destination.php',
		'node_endpoint.php'
	);

	foreach ($nodeFiles as $nodeFile) {
		if (file_exists('/usr/local/cloud_node_automation_api/' . $nodeFile) === false) {
			$systemActionDownloadNodeFileParameters = array(
				'action' => 'download_node_file',
				'node_authentication_token' => $_SERVER['argv'][1],
				'where' => array(
					'node_file' => $nodeFile
				)
			);
			$systemActionDownloadNodeFileParameters = json_encode($systemActionDownloadNodeFileParameters);
			shell_exec('sudo ' . $binaryFiles['wget'] . ' -O /usr/local/cloud_node_automation_api/' . $nodeFile . ' --connect-timeout=5 --dns-timeout=5 --no-dns-cache --post-data \'json=' . $systemActionDownloadNodeFileParameters . '\' --read-timeout=60 --tries=1 ' . $nodeSettingsData['system_endpoint_destination_ip_address'] . '/system_endpoint.php');

			if (file_exists('/usr/local/cloud_node_automation_api/' . $nodeFile) === false) {
				echo 'Error downloading node file ' . $nodeFile . ', please try again.' . "\n";
				exit;
			}

			$systemActionDownloadNodeFileResponse = file_get_contents('/usr/local/cloud_node_automation_api/' . $nodeFile);

			if (empty($systemActionDownloadNodeFileResponse)) === true) {
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
	$crontabCommandIndex = array_search('# cloud_node_automation_api_node_processes', $crontabCommands);

	if (is_int($crontabCommandIndex) === true) {
		while (is_int($crontabCommandIndex) === true) {
			unset($crontabCommands[$crontabCommandIndex]);
			$crontabCommandIndex++;

			if (strpos($crontabCommands[$crontabCommandIndex], ' cloud_node_automation_api_node_processes') === false) {
				$crontabCommandIndex = false;
			}
		}
	}

	$crontabCommands += array(
		'# cloud_node_automation_api_node_processes',
		'@reboot root sudo ' . $binaryFiles['php'] . ' /usr/local/cloud_node_automation_api/node_endpoint.php process_network_interface_ip_addresses cloud_node_automation_api_node_processes',
		'* * * * * root sudo ' . $binaryFiles['php'] . ' /usr/local/cloud_node_automation_api/node_endpoint.php process_node_process_node_user_request_logs cloud_node_automation_api_node_processes',
		'* * * * * root sudo ' . $binaryFiles['php'] . ' /usr/local/cloud_node_automation_api/node_endpoint.php process_node_process_resource_usage_logs cloud_node_automation_api_node_processes',
		'* * * * * root sudo ' . $binaryFiles['php'] . ' /usr/local/cloud_node_automation_api/node_endpoint.php process_node_processes cloud_node_automation_api_node_processes',
		'* * * * * root sudo ' . $binaryFiles['php'] . ' /usr/local/cloud_node_automation_api/node_endpoint.php process_node_resource_usage_logs cloud_node_automation_api_node_processes',
		'* * * * * root sudo ' . $binaryFiles['php'] . ' /usr/local/cloud_node_automation_api/node_endpoint.php process_recursive_dns_destination cloud_node_automation_api_node_processes',
	);
	$crontabCommands = implode("\n", $crontabCommands);

	if (file_put_contents('/etc/crontab', $crontabCommands) === false) {
		echo 'Error adding crontab commands, please try again.' . "\n";
		exit;
	}

	$systemActionDeployNodeParameters = array(
		'action' => 'deploy_node',
		'node_authentication_token' => $_SERVER['argv'][1]
	);
	$systemActionDeployNodeParameters = json_encode($systemActionDeployNodeParameters);
	unlink('/usr/local/cloud_node_automation_api/system_action_deploy_node_response.json');
	shell_exec('sudo ' . $binaryFiles['wget'] . ' -O /usr/local/cloud_node_automation_api/system_action_deploy_node_response.json --connect-timeout=5 --dns-timeout=5 --no-dns-cache --post-data \'json=' . $systemActionDeployNodeParameters . '\' --read-timeout=60 --tries=1 ' . $nodeSettingsData['system_endpoint_destination_ip_address'] . '/system_endpoint.php');

	if (file_exists('/usr/local/cloud_node_automation_api/system_action_deploy_node_response.json') === false) {
		echo 'Error deploying node, please try again.' . "\n";
		exit;
	}

	$systemActionDeployNodeResponse = file_get_contents('/usr/local/cloud_node_automation_api/system_action_deploy_node_response.json');
	$systemActionDeployNodeResponse = json_decode($systemActionDeployNodeResponse, true);

	if (empty($systemActionDeployNodeResponse['message']) === true) {
		echo 'Error deploying node, please try again.' . "\n";
		exit;
	}

	echo $systemActionDeployNodeResponse['message'] . "\n";

	if (($systemActionProcessNodeResponse['valid_status'] === '1') === true) {
		$nodeSettingsData['authentication_token'] = $_SERVER['argv'][1];
		$nodeSettingsData['system_version_number'] = '1';
		$nodeSettingsData = json_encode($nodeSettingsData);

		if (file_put_contents('/usr/local/cloud_node_automation_api/node_settings_data.json', $nodeSettingsData) === false) {
			echo 'Error adding node settings data, please try again.' . "\n";
			exit;
		}

		shell_exec('sudo ' . $binaryFiles['crontab'] . ' /etc/crontab');
	}

	exit;
?>
