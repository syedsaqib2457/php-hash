<?php
	if (
		(empty($_SERVER['argv'][1]) === true) ||
		($_SERVER['argv'][1] === 'STATIC_IP_ADDRESS')
	) {
		echo 'Error deploying system with STATIC_IP_ADDRESS, please try again.' . "\n";
		exit;
	}

	function applyCommands($commands) {
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

	function fetchBinaryFile($binary) {
		$commands = array(
			'#!/bin/bash',
			'whereis ' . $binary['name'] . ' | awk \'{ for (i=2; i<=NF; i++) print $i }\' | while read -r binaryFile; do echo $((sudo $binaryFile "' . $binary['command'] . '") 2>&1) | grep -c "' . $binary['output'] . '" && echo $binaryFile && break; done | tail -1'
		);
		$commandsFile = '/tmp/commands.sh';

		if (file_exists($commandsFile) === true) {
			unlink($commandsFile);
		}

		file_put_contents($commandsFile, implode("\n", $commands));
		shell_exec('sudo chmod +x ' . $commandsFile);
		exec('cd /tmp/ && sudo ./' . basename($commandsFile), $binaryFile);
		$binaryFile = current($binaryFile);
		unlink($commandsFile);

		if (empty($binaryFile) === true) {
			echo 'Error fetching required binary file, please try again.' . "\n";
			shell_exec('sudo apt-get update');
			shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install ' . $binary['package']);
			exit;
		}

		return $binaryFile;
	}

	function fetchSshPorts() {
		$sshPorts = array();

		if (file_exists('/etc/ssh/sshd_config') === true) {
			exec('grep "Port " /etc/ssh/sshd_config | grep -v "#" | awk \'{print $2}\' 2>&1', $sshPorts);

			foreach ($sshPorts as $sshPortKey => $sshPort) {
				if (
					(strlen($sshPort) > 5) ||
					(is_numeric($sshPort) === false)
				) {
					unset($sshPorts[$sshPortKey]);
				}
			}
		}

		return $sshPorts;
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
		'sudo kill -9 $(fuser -v /var/cache/debconf/config.dat)',
		'sudo apt-get update',
		'sudo DEBIAN_FRONTEND=noninteractive apt-get -y install apache2 bind9 bind9utils cron curl iptables net-tools php-curl php-mysqli procps syslinux systemd util-linux',
		'sudo DEBIAN_FRONTEND=noninteractive apt-get -y install gnupg'
	);
	applyCommands($commands);

	if (function_exists('mysqli_query') === false) {
		exit;
	}

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
			'command' => '-h',
			'name' => 'apachectl',
			'output' => 'Options:',
			'package' => 'apache2'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'crontab',
			'output' => 'invalid option',
			'package' => 'cron'
		),
		array(
			'command' => '-h',
			'name' => 'iptables-restore',
			'output' => 'tables-restore ',
			'package' => 'iptables'
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
		)
	);
	$binaryFiles = array();

	foreach ($binaries as $binary) {
		$binaryFiles[$binary['name']] = fetchBinaryFile($binary);
	}

	$kernelOptions = array(
		'fs.aio-max-nr = 1000000000',
		'fs.file-max = 1000000000',
		'fs.nr_open = 1000000000',
		'fs.pipe-max-size = 10000000',
		'fs.suid_dumpable = 0',
		'kernel.core_uses_pid = 1',
		'kernel.hung_task_timeout_secs = 2',
		'kernel.io_delay_type = 3',
		'kernel.kptr_restrict = 2',
		'kernel.msgmax = 65535',
		'kernel.msgmnb = 65535',
		'kernel.printk = 7 7 7 7',
		'kernel.sem = 404 256000 64 2048',
		'kernel.shmmni = 32767',
		'kernel.sysrq = 0',
		'kernel.threads-max = 1000000000',
		'net.core.default_qdisc = fq',
		'net.core.dev_weight = 100000',
		'net.core.netdev_max_backlog = 1000000',
		'net.core.somaxconn = 1000000000',
		'net.ipv4.conf.all.accept_redirects = 0',
		'net.ipv4.conf.all.accept_source_route = 0',
		'net.ipv4.conf.all.arp_ignore = 1',
		'net.ipv4.conf.all.bootp_relay = 0',
		'net.ipv4.conf.all.forwarding = 0',
		'net.ipv4.conf.all.rp_filter = 1',
		'net.ipv4.conf.all.secure_redirects = 0',
		'net.ipv4.conf.all.send_redirects = 0',
		'net.ipv4.conf.all.log_martians = 0',
		'net.ipv4.icmp_echo_ignore_all = 0',
		'net.ipv4.icmp_echo_ignore_broadcasts = 0',
		'net.ipv4.icmp_ignore_bogus_error_responses = 1',
		'net.ipv4.ip_forward = 0',
		'net.ipv4.ip_local_port_range = 1024 65000',
		'net.ipv4.ipfrag_high_thresh = 64000000',
		'net.ipv4.ipfrag_low_thresh = 32000000',
		'net.ipv4.ipfrag_time = 10',
		'net.ipv4.neigh.default.gc_interval = 50',
		'net.ipv4.neigh.default.gc_stale_time = 10',
		'net.ipv4.neigh.default.gc_thresh1 = 32',
		'net.ipv4.neigh.default.gc_thresh2 = 1024',
		'net.ipv4.neigh.default.gc_thresh3 = 2048',
		'net.ipv4.route.gc_timeout = 2',
		'net.ipv4.tcp_adv_win_scale = 2',
		'net.ipv4.tcp_congestion_control = htcp',
		'net.ipv4.tcp_fastopen = 2',
		'net.ipv4.tcp_fin_timeout = 2',
		'net.ipv4.tcp_keepalive_intvl = 2',
		'net.ipv4.tcp_keepalive_probes = 2',
		'net.ipv4.tcp_keepalive_time = 2',
		'net.ipv4.tcp_low_latency = 1',
		'net.ipv4.tcp_max_orphans = 100000',
		'net.ipv4.tcp_max_syn_backlog = 1000000',
		'net.ipv4.tcp_max_tw_buckets = 100000000',
		'net.ipv4.tcp_moderate_rcvbuf = 1',
		'net.ipv4.tcp_no_metrics_save = 1',
		'net.ipv4.tcp_orphan_retries = 0',
		'net.ipv4.tcp_retries2 = 1',
		'net.ipv4.tcp_rfc1337 = 0',
		'net.ipv4.tcp_sack = 0',
		'net.ipv4.tcp_slow_start_after_idle = 0',
		'net.ipv4.tcp_syn_retries = 2',
		'net.ipv4.tcp_synack_retries = 2',
		'net.ipv4.tcp_syncookies = 0',
		'net.ipv4.tcp_thin_linear_timeouts = 1',
		'net.ipv4.tcp_timestamps = 1',
		'net.ipv4.tcp_tw_reuse = 0',
		'net.ipv4.tcp_window_scaling = 1',
		'net.ipv4.udp_rmem_min = 1',
		'net.ipv4.udp_wmem_min = 1',
		'net.ipv6.conf.all.accept_redirects = 0',
		'net.ipv6.conf.all.accept_source_route = 0',
		'net.ipv6.conf.all.disable_ipv6 = 0',
		'net.ipv6.conf.all.forwarding = 0',
		'net.ipv6.ip6frag_high_thresh = 64000000',
		'net.ipv6.ip6frag_low_thresh = 32000000',
		'vm.dirty_background_ratio = 10',
		'vm.dirty_expire_centisecs = 10',
		'vm.dirty_ratio = 10',
		'vm.dirty_writeback_centisecs = 100',
		'vm.max_map_count = 1000000',
		'vm.mmap_min_addr = 4096',
		'vm.overcommit_memory = 0',
		'vm.swappiness = 0'
	);
	file_put_contents('/etc/sysctl.conf', implode("\n", $kernelOptions));
	shell_exec('sudo ' . $binaryFiles['sysctl'] . ' -p');
	exec('getconf PAGE_SIZE 2>&1', $kernelPageSize);
	exec('free | grep -v free | awk \'NR==1{print $2}\'', $totalSystemMemory);
	$kernelPageSize = current($kernelPageSize);
	$totalSystemMemory = current($totalSystemMemory);

	if (
		is_numeric($kernelPageSize) &&
		is_numeric($totalSystemMemory)
	) {
		$maximumMemoryBytes = ceil($totalSystemMemory * 0.34);
		$maximumMemoryPages = ceil($maximumMemoryBytes / $kernelPageSize);
		$dynamicKernelOptions = array(
			'kernel.shmall' => floor($totalSystemMemory / $kernelPageSize),
			'kernel.shmmax' => $totalSystemMemory,
			'net.core.optmem_max' => ($defaultSocketBufferMemoryBytes = ceil($maximumMemoryBytes * 0.10)),
			'net.core.rmem_default' => $defaultSocketBufferMemoryBytes,
			'net.core.rmem_max' => ($defaultSocketBufferMemoryBytes * 2),
			'net.ipv4.tcp_mem' => $maximumMemoryPages . ' ' . $maximumMemoryPages . ' ' . $maximumMemoryPages,
			'net.ipv4.tcp_rmem' => 1 . ' ' . $defaultSocketBufferMemoryBytes . ' ' . ($defaultSocketBufferMemoryBytes * 2)
		);
		$dynamicKernelOptions += array(
			'net.ipv4.tcp_wmem' => $dynamicKernelOptions['net.ipv4.tcp_rmem'],
			'net.ipv4.udp_mem' => $dynamicKernelOptions['net.ipv4.tcp_mem'],
			'net.core.wmem_default' => $dynamicKernelOptions['net.core.rmem_default'],
			'net.core.wmem_max' => $dynamicKernelOptions['net.core.rmem_max']
		);

		foreach ($dynamicKernelOptions as $dynamicKernelOptionKey => $dynamicKernelOptionValue) {
			shell_exec('sudo ' . $binaryFiles['sysctl'] . ' -w ' . $dynamicKernelOptionKey . '="' . $dynamicKernelOptionValue . '"');
		}
	}

	$commands = array(
		'sudo rm -rf ' . ($systemPath = '/var/www/' . ($url = $_SERVER['argv'][1])),
		'sudo mkdir -p ' . $systemPath,
		'sudo ' . $binaryFiles['systemctl'] . ' start apache2'
	);
	applyCommands($commands);
	$virtualHostContents = array(
		'<VirtualHost *:80>',
		'ServerAlias ' . $url,
		'ServerName ' . $url,
		'DocumentRoot ' . $systemPath . '/source',
		'<Directory ' . $systemPath . '/source' . '>',
		'Allow from all',
		'Options FollowSymLinks',
		'AllowOverride All',
		'</Directory>',
		'</VirtualHost>'
	);
	file_put_contents('/etc/apache2/sites-available/' . $url . '.conf', implode("\n", $virtualHostContents));
	$commands = array(
		'cd /etc/apache2/sites-available && sudo ' . $binaryFiles['a2ensite'] . ' ' . $url,
		'cd /etc/apache2/mods-available && sudo ' . $binaryFiles['a2enmod'] . ' rewrite.load',
		'sudo ' . $binaryFiles['apachectl'] . ' graceful',
		'sudo apt-get update',
		'sudo ' . $binaryFiles['systemctl'] . ' stop mysql',
		'sudo DEBIAN_FRONTEND=noninteractive apt-get -y purge mysql-server mysql-client mysql-common mysql-server-core-* mysql-client-core-*',
		'sudo rm -rf /etc/mysql /var/lib/mysql /var/log/mysql',
		'sudo DEBIAN_FRONTEND=noninteractive apt-get -y autoremove',
		'sudo DEBIAN_FRONTEND=noninteractive apt-get -y autoclean',
		'cd /tmp && sudo wget -O mysql_apt_config.deb ' . ($wgetParameters = '--no-dns-cache --retry-connrefused --timeout=60 --tries=2') . ' https://dev.mysql.com/get/mysql-apt-config_0.8.13-1_all.deb',
	);
	applyCommands($commands);

	if (file_exists('/tmp/mysql_apt_config.deb') === false) {
		echo 'Error downloading MySQL source file, please try again.' . "\n";
		exit;
	}

	$commands = array(
		'cd /tmp && sudo DEBIAN_FRONTEND=noninteractive dpkg -i mysql_apt_config.deb',
		'sudo add-apt-repository -y universe',
		'sudo apt-get update',
		'sudo DEBIAN_FRONTEND=noninteractive apt-get -y install libmecab2',
		'sudo DEBIAN_FRONTEND=noninteractive apt-get --fix-broken -y install mysql-common mysql-client mysql-community-server-core mysql-community-client mysql-community-client-core mysql-community-server mysql-community-client-plugins mysql-server'
	);
	applyCommands($commands);

	if (file_exists('/etc/mysql/mysql.conf.d/mysqld.cnf') === false) {
		echo 'Error installing MySQL, please try again.' . "\n";
		exit;
	}

	$mysqlConfigurationContents = array(
		'[mysqld]',
		'bind-address = 127.0.0.1',
		'datadir = /var/lib/mysql',
		'default-authentication-plugin = mysql_native_password',
		'log-error = /var/log/mysql/error.log',
		'pid-file = /var/run/mysqld/mysqld.pid',
		'socket = /var/run/mysqld/mysqld.sock'
	);
	file_put_contents('/etc/mysql/mysql.conf.d/mysqld.cnf', implode("\n", $mysqlConfigurationContents));
	$commands = array(
		'sudo ' . $binaryFiles['service'] . ' mysql restart',
		'sudo mysql -u root -p"password" -e "DELETE FROM mysql.user WHERE User=\'\'; DELETE FROM mysql.user WHERE User=\'root\' AND Host NOT IN (\'localhost\', \'127.0.0.1\', \'::1\');"',
		'sudo mysql -u root -p"password" -e "DROP USER \'root\'@\'localhost\'; CREATE USER \'root\'@\'localhost\' IDENTIFIED BY \'password\'; GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\' WITH GRANT OPTION; FLUSH PRIVILEGES;"',
		'sudo ' . $binaryFiles['service'] . ' mysql restart',
		'sudo apt-get update',
		'sudo rm -rf ' . $systemPath . '/*',
		'cd ' . $systemPath . ' && sudo wget -O overlord.tar.gz ' . $wgetParameters . ' https://github.com/williamstaffordparsons/overlord/archive/refs/heads/develop.tar.gz'
	);
	applyCommands($commands);

	if (file_exists($systemPath . '/overlord.tar.gz') === false) {
		echo 'Error downloading system files, please try again.' . "\n";
		exit;
	}

	$commands = array(
		'cd ' . $systemPath . ' && sudo tar -xvzf overlord.tar.gz && cd overlord-develop && mv .* * ../',
		'cd ' . $systemPath . ' && sudo rm -rf overlord.tar.gz overlord-develop'
	);
	applyCommands($commands);

	if (file_exists($systemPath . '/license.txt') === false) {
		echo 'Error extracting system files, please try again.' . "\n";
		exit;
	}

	$keyCharacters = str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz01234567890123456789', 4));
	$keyStart = 'x' . substr($keyCharacters, 0, rand(17, 34));
	$keyStop = 'x' . substr($keyCharacters, 34, rand(17, 34));
	file_put_contents($systemPath . '/source/keys.php', "<?php \$keys = array('start' => '" . $keyStart . "', 'stop' => '" . $keyStop . "'); ?>");
	$crontabFile = '/etc/crontab';

	if (file_exists($crontabFile) === true) {
		$crontabFileContents = file_get_contents($crontabFile);
	}

	$crontabCommands = array(
		'# [Start]',
		'* * * * * root sudo ' . $binaryFiles['php'] . ' ' . $systemPath . '/source/interfaces/command/interface.php system processRequestLogs',
		'@reboot root sudo ' . $binaryFiles['crontab'] . ' ' . $crontabFile,
		'# [Stop]'
	);

	if (
		(file_exists($crontabFile) === false) ||
		(boolval($crontabFileContents) === false)
	) {
		echo 'Error fetching crontab contents, please try again.' . "\n";
		exit;
	}

	$crontabFileContents = explode("\n", $crontabFileContents);

	while (array_search('# [Start]', $crontabFileContents) !== false) {
		$startCrontabFileContents = array_search('# [Start]', $crontabFileContents);
		$stopCrontabFileContents = array_search('# [Stop]', $crontabFileContents);

		if (
			($stopCrontabFileContents !== false) &&
			($stopCrontabFileContents > $startCrontabFileContents)
		) {
			foreach (range($startCrontabFileContents, $stopCrontabFileContents) as $crontabContentLineIndex) {
				unset($crontabFileContents[$crontabContentLineIndex]);
			}
		}
	}

	$crontabFileContents = array_merge($crontabFileContents, $crontabCommands);
	file_put_contents($crontabFile, implode("\n", $crontabFileContents));
	$commands = array(
		'sudo ' . $binaryFiles['crontab'] . ' ' . $crontabFile,
	);
	applyCommands($commands);
	$firewallRules = array(
		'*filter',
		':INPUT ACCEPT [0:0]',
		':FORWARD ACCEPT [0:0]',
		':OUTPUT ACCEPT [0:0]',
		'-A INPUT -p icmp -m hashlimit --hashlimit-above 1/second --hashlimit-burst 2 --hashlimit-htable-gcinterval 100000 --hashlimit-htable-expire 10000 --hashlimit-mode srcip --hashlimit-name icmp --hashlimit-srcmask 32 -j DROP'
	);

	if (
		(empty($sshPorts) === false) &&
		(is_array($sshPorts) === true)
	) {
		foreach ($sshPorts as $sshPort) {
			$firewallRules[] = '-A INPUT -p tcp --dport ' . $sshPort . ' -m hashlimit --hashlimit-above 1/minute --hashlimit-burst 10 --hashlimit-htable-gcinterval 600000 --hashlimit-htable-expire 60000 --hashlimit-mode srcip --hashlimit-name ssh --hashlimit-srcmask 32 -j DROP';
		}
	}

	$firewallRules[] = 'COMMIT';
	$firewallRuleParts = array_chunk($firewallRules, 1000);
	$firewallRulesFile = '/tmp/firewall';

	if (file_exists($firewallRulesFile)) {
		unlink($firewallRulesFile);
	}

	touch($firewallRulesFile);

	foreach ($firewallRuleParts as $firewallRulePart) {
		$saveFirewallRules = implode("\n", $firewallRulePart);
		shell_exec('sudo echo "' . $saveFirewallRules . '" >> ' . $firewallRulesFile);
	}

	shell_exec('sudo ' . $binaryFiles['iptables-restore'] . ' < ' . $firewallRulesFile);
	sleep(1 * count($firewallRuleParts));
	shell_exec('sudo rm /tmp/firewall');
	require_once('/var/www/' . $url . '/source/system.php');
	$database = mysqli_connect($system->settings['database']['hostname'], $system->settings['database']['username'], $system->settings['database']['password']);
	$queries = array();

	if ($database === false) {
		echo 'Error: ' . mysqli_connect_error() . '.';
		exit;
	}

	mysqli_query($database, 'CREATE DATABASE IF NOT EXISTS `' . $system->settings['database']['name'] . '` CHARSET utf8');
	$database = mysqli_connect($system->settings['database']['hostname'], $system->settings['database']['username'], $system->settings['database']['password'], $system->settings['database']['name']);

	if ($database === false) {
		echo 'Error: ' . mysqli_connect_error() . '.';
		exit;
	}

	foreach ($system->settings['database']['structure'] as $tableName => $columns) {
		$columnKey = key($columns);
		$columns = array_merge($columns, array(
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'type' => 'DATETIME'
			),
			'modified' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'type' => 'DATETIME'
			)
		));

		if (empty($columnKey) === false) {
			$columnDefault = '';
			$columnNull = 'NULL';

			if (isset($columns[$columnKey]['default']) === true) {
				$columnDefault = ' DEFAULT ' . $columns[$columnKey]['default'];
			}

			if (empty($columns[$columnKey]['null']) === true) {
				$columnNull = 'NOT NULL';
			}

			$queries[] = 'CREATE TABLE IF NOT EXISTS `' . $tableName . '` (`' . $columnKey . '` ' . $columns[$columnKey]['type'] . ' ' . $columnNull . $columnDefault . ');';

			foreach ($columns as $columnName => $columnStructure) {
				$columnDefault = '';
				$columnNull = 'NULL';

				if (isset($columnStructure['default']) === true) {
					$columnDefault = ' DEFAULT ' . $columnStructure['default'];
				}

				if (empty($columnStructure['null']) === true) {
					$columnNull = 'NOT NULL';
				}

				$queryActions = array(
					'add' => 'ADD `' . $columnName . '`',
					'change' => 'CHANGE `' . $columnName . '` `' . $columnName . '`'
				);
				$query = 'ALTER TABLE `' . $tableName . '` ' . $queryActions['change'] . ' ' . $columnStructure['type'] . ' ' . $columnNull . $columnDefault;

				if (
					($columnName !== $columnKey) &&
					(mysqli_query($database, $query) === false)
				) {
					$queries[] = str_replace($queryActions['change'], $queryActions['add'], $query);
				}

				if (!empty($columnStructure['primary_key'])) {
					$queries[$columnName . $system->settings['keys']['start'] . $tableName] = 'ALTER TABLE `' . $tableName . '` ADD PRIMARY KEY(`' . $columnName . '`)';

					if (!empty($columnStructure['auto_increment'])) {
						$queries[] = $query . ' AUTO_INCREMENT';
					}
				}

				if (!empty($columnStructure['index'])) {
					$queries[$columnName . $system->settings['keys']['start'] . $tableName] = 'ALTER TABLE `' . $tableName . '` ADD INDEX(`' . $columnName . '`)';
				}
			}
		}
	}

	foreach ($queries as $queryKey => $query) {
		if (
			(is_numeric($queryKey) === false) &&
			(
				(strpos($query, 'ADD INDEX') !== false) ||
				(strpos($query, 'ADD PRIMARY KEY') !== false)
			)
		) {
			$queryKey = explode($system->settings['keys']['start'], $queryKey);

			if (empty(mysqli_query($database, 'SHOW KEYS FROM `' . $queryKey[1] . '` WHERE Column_name=\'' . $queryKey[0] . '\'')->num_rows) === false) {
				continue;
			}
		}

		$queryResult = mysqli_query($database, $query);

		if ($queryResult === false) {
			echo 'Error executing database query, please try again.';
			exit;
		}
	}

	if (empty(mysqli_query($database, 'SELECT `id` FROM `users` LIMIT 1')->num_rows) === false) {
		echo 'System already installed.' . "\n";
		exit;
	}

	$settingDataFields = array(
		'created',
		'id',
		'modified',
		'value'
	);
	$settingDataValues = array(
		"'" . date('Y-m-d H:i:s', time()) . "'",
		"'keys'",
		"'" . date('Y-m-d H:i:s', time()) . "'",
		"'" . sha1($system->settings['keys']['start'] . $system->settings['keys']['stop']) . "'"
	);
	$userDataFields = array(
		'authentication_password',
		'created',
		'id',
		'modified',
	);
	$userDataValues = array(
		"'" . ($password = 'x' . substr(str_shuffle('abcdefghijklmnopqrstuvwxyz01234567890123456789'), 0, 14)) . "'",
		"'" . date('Y-m-d H:i:s', time()) . "'",
		1,
		"'" . date('Y-m-d H:i:s', time()) . "'"
	);
	mysqli_query($database, 'INSERT IGNORE INTO `settings` (`' . implode('`, `', $settingDataFields) . '`) VALUES (' . implode(', ', $settingDataValues) . ')');
	mysqli_query($database, 'INSERT IGNORE INTO `users` (`' . implode('`, `', $userDataFields) . '`) VALUES (' . implode(', ', $userDataValues) . ')');

	if (
		(empty(mysqli_query($database, 'SELECT `id` FROM `settings`')->num_rows) === true) ||
		(empty(mysqli_query($database, 'SELECT `id` FROM `users`')->num_rows) === true)
	) {
		echo 'Error creating system user, please try again.' . "\n";
		exit;
	}

	echo 'System installed successfully.' . "\n";
	echo 'You can now log in at ' . $url . ' with this password:' . "\n";
	echo 'Password: ' . $password . "\n";
	shell_exec('sudo rm /tmp/deploy_system.php');
	exit;
?>
