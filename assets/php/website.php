<?php
	if (
		empty($_SERVER['argv'][1]) ||
		$_SERVER['argv'][1] === 'STATIC_IP_ADDRESS'
	) {
		echo 'Error: STATIC_IP_ADDRESS should be the static public IPv4 address of the server.' . "\n";
		exit;
	}

	function applyCommands($commands) {
		foreach ($commands as $command) {
			if (
				!empty($command) &&
				is_string($command)
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

		if (file_exists($commandsFile)) {
			unlink($commandsFile);
		}

		file_put_contents($commandsFile, implode("\n", $commands));
		shell_exec('sudo chmod +x ' . $commandsFile);
		exec('cd /tmp/ && sudo ./' . basename($commandsFile), $binaryFile);
		$binaryFile = current($binaryFile);
		unlink($commandsFile);

		if (empty($binaryFile)) {
			echo 'Error: Binary file for ' . $binary['name'] . ' not found, please run the install script again.' . "\n";
			shell_exec('sudo apt-get update');
			shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install ' . $binary['package']);
			exit;
		}

		return $binaryFile;
	}

	function fetchSshPorts() {
		$sshPorts = array();

		if (file_exists('/etc/ssh/sshd_config')) {
			exec('grep "Port " /etc/ssh/sshd_config | grep -v "#" | awk \'{print $2}\' 2>&1', $sshPorts);

			foreach ($sshPorts as $sshPortKey => $sshPort) {
				if (
					strlen($sshPort) > 5 ||
					!is_numeric($sshPort)
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

		if (!empty($operatingSystemDetail[1])) {
			$operatingSystemDetails[strtolower($operatingSystemDetail[0])] = trim($operatingSystemDetail[1], '"');
		}

		unset($operatingSystemDetails[$operatingSystemDetailKey]);
	}

	if (empty($supportedOperatingSystems[$operatingSystemDetails['id']][$operatingSystemDetails['version_id']])) {
		echo 'Error: Unsupported operating system ' . $operatingSystemDetails['pretty_name'] . "\n";
		exit;
	}

	$operatingSystemConfiguration = $supportedOperatingSystems[$operatingSystemDetails['id']][$operatingSystemDetails['version_id']];

	if (
		!file_exists($operatingSystemConfiguration['sources']['aptitude']['path']) ||
		!file_put_contents($operatingSystemConfiguration['sources']['aptitude']['path'], implode("\n", $operatingSystemConfiguration['sources']['aptitude']['contents']))
	) {
		echo 'Error: Unable to update package sources at ' . $operatingSystemConfiguration['sources']['aptitude']['path'] . '.' . "\n";
		exit;
	}

	$commands = array(
		'sudo kill -9 $(fuser -v /var/cache/debconf/config.dat)',
		'sudo apt-get update',
		'sleep 1',
		'sudo DEBIAN_FRONTEND=noninteractive apt-get -y install apache2 bind9 bind9utils cron curl iptables net-tools php-curl php-mysqli procps syslinux systemd util-linux',
		'sleep 1',
		'sudo DEBIAN_FRONTEND=noninteractive apt-get -y install gnupg',
		'sleep 1'
	);
	applyCommands($commands);
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
		'net.netfilter.nf_conntrack_max = 1000000000',
		'net.netfilter.nf_conntrack_tcp_loose = 0',
		'net.netfilter.nf_conntrack_tcp_timeout_close = 10',
		'net.netfilter.nf_conntrack_tcp_timeout_close_wait = 10',
		'net.netfilter.nf_conntrack_tcp_timeout_established = 10',
		'net.netfilter.nf_conntrack_tcp_timeout_fin_wait = 10',
		'net.netfilter.nf_conntrack_tcp_timeout_last_ack = 10',
		'net.netfilter.nf_conntrack_tcp_timeout_syn_recv = 10',
		'net.netfilter.nf_conntrack_tcp_timeout_syn_sent = 10',
		'net.netfilter.nf_conntrack_tcp_timeout_time_wait = 10',
		'net.nf_conntrack_max = 1000000000',
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
		'sudo rm -rf ' . ($websitePath = '/var/www/' . ($url = $_SERVER['argv'][1])),
		'sudo mkdir -p ' . $websitePath,
		'sudo ' . $binaryFiles['systemctl'] . ' start apache2'
	);
	applyCommands($commands);
	$virtualHostContents = array(
		'<VirtualHost *:80>',
		'ServerAlias ' . $url,
		'ServerName ' . $url,
		'DocumentRoot ' . $websitePath,
		'<Directory ' . $websitePath . '>',
		'Allow from all',
		'Options FollowSymLinks',
		'AllowOverride All',
		'</Directory>',
		'</VirtualHost>'
	);
	file_put_contents('/etc/apache2/sites-available/' . $url . '.conf', implode("\n", $virtualHostContents));
	$commands = array(
		'sleep 1',
		'cd /etc/apache2/sites-available && sudo ' . $binaryFiles['a2ensite'] . ' ' . $url,
		'sleep 1',
		'cd /etc/apache2/mods-available && sudo ' . $binaryFiles['a2enmod'] . ' rewrite.load',
		'sleep 1',
		'sudo ' . $binaryFiles['apachectl'] . ' graceful',
		'sleep 1',
		'sudo apt-get update',
		'sleep 1',
		'sudo ' . $binaryFiles['systemctl'] . ' stop mysql',
		'sleep 1',
		'sudo DEBIAN_FRONTEND=noninteractive apt-get -y purge mysql-server mysql-client mysql-common mysql-server-core-* mysql-client-core-*',
		'sleep 1',
		'sudo rm -rf /etc/mysql /var/lib/mysql /var/log/mysql',
		'sudo DEBIAN_FRONTEND=noninteractive apt-get -y autoremove',
		'sleep 1',
		'sudo DEBIAN_FRONTEND=noninteractive apt-get -y autoclean',
		'sleep 1',
		'cd /tmp && sudo wget -O mysql_apt_config.deb ' . ($wgetParameters = '--no-dns-cache --retry-connrefused --timeout=60 --tries=2') . ' https://dev.mysql.com/get/mysql-apt-config_0.8.13-1_all.deb',
	);
	applyCommands($commands);

	if (!file_exists('/tmp/mysql_apt_config.deb')) {
		echo 'Error: Unable to download MySQL source file.' . "\n";
		exit;
	}

	$commands = array(
		'cd /tmp && sudo DEBIAN_FRONTEND=noninteractive dpkg -i mysql_apt_config.deb',
		'sleep 1',
		'sudo add-apt-repository -y universe',
		'sleep 1',
		'sudo apt-get update',
		'sleep 1',
		'sudo DEBIAN_FRONTEND=noninteractive apt-get -y install libmecab2',
		'sleep 1',
		'sudo DEBIAN_FRONTEND=noninteractive apt-get --fix-broken -y install mysql-common mysql-client mysql-community-server-core mysql-community-client mysql-community-client-core mysql-community-server mysql-community-client-plugins mysql-server'
	);
	applyCommands($commands);

	if (!file_exists('/etc/mysql/mysql.conf.d/mysqld.cnf')) {
		echo 'Error: Unable to install MySQL, please try again.' . "\n";
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
		'sleep 1',
		'sudo mysql -u root -p"password" -e "DELETE FROM mysql.user WHERE User=\'\'; DELETE FROM mysql.user WHERE User=\'root\' AND Host NOT IN (\'localhost\', \'127.0.0.1\', \'::1\');"',
		'sleep 1',
		'sudo mysql -u root -p"password" -e "DROP USER \'root\'@\'localhost\'; CREATE USER \'root\'@\'localhost\' IDENTIFIED BY \'password\'; GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\' WITH GRANT OPTION; FLUSH PRIVILEGES;"',
		'sleep 1',
		'sudo ' . $binaryFiles['service'] . ' mysql restart',
		'sleep 1',
		'sudo apt-get update',
		'sleep 1',
		'sudo rm -rf ' . $websitePath . '/*',
		'cd ' . $websitePath . ' && sudo wget -O overlord.tar.gz ' . $wgetParameters . ' https://github.com/williamstaffordparsons/overlord/archive/refs/heads/master.tar.gz'
	);
	applyCommands($commands);

	if (!file_exists($websitePath . '/overlord.tar.gz')) {
		echo 'Error: Unable to download website files.' . "\n";
		exit;
	}

	$commands = array(
		'cd ' . $websitePath . ' && sudo tar -xvzf overlord.tar.gz && cd overlord-master && mv .* * ../',
		'cd ' . $websitePath . ' && sudo rm -rf overlord.tar.gz overlord-master'
	);
	applyCommands($commands);

	if (!file_exists($websitePath . '/version.txt')) {
		echo 'Error: Unable to extract website files.' . "\n";
		exit;
	}

	$keys = array();
	$letters = 'abcdefghijklmnopqrstuvwxyz';
	$numbers = '0123456789012345678901234567890123456789';

	foreach (range(1, 3) as $key) {
		$keys[] = $letters[rand(0, 25)] . substr(str_shuffle(str_repeat($letters . $numbers, 2)), 0, rand(17, 34));
	}

	file_put_contents($websitePath . '/keys.php', "<?php \$keys = array('salt' => '" . $keys[0] . "', 'start' => '" . $keys[1] . "', 'stop' => '" . $keys[2] . "'); ?>");
	$crontabFile = '/etc/crontab';
	$crontabCommands = array(
		'# [Start]',
		'* * * * * root sudo ' . $binaryFiles['php'] . ' ' . $websitePath . '/shell.php main processPublicRequestLimitations',
		'@reboot root sudo ' . $binaryFiles['crontab'] . ' ' . $crontabFile,
		'# [Stop]'
	);

	if (
		!file_exists($crontabFile) ||
		($crontabFileContents = file_get_contents($crontabFile)) === false
	) {
		echo 'Error: Unable to retrieve crontab contents at ' . $crontabFile . '.' . "\n";
		exit;
	}

	$crontabFileContents = explode("\n", $crontabFileContents);

	while (($startCrontabFileContents = array_search('# [Start]', $crontabFileContents)) !== false) {
		if (
			($stopCrontabFileContents = array_search('# [Stop]', $crontabFileContents)) !== false &&
			$stopCrontabFileContents > $startCrontabFileContents
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
		!empty($sshPorts) &&
		is_array($sshPorts)
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
	shell_exec('sudo rm /tmp/firewall /tmp/website.php');
	exit;
?>
