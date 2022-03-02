<?php
	function _createUniqueId() {
		$uniqueId = hrtime(true);
		$uniqueId = substr($uniqueId, -10);
		$uniqueId = sprintf('%010s', $uniqueId);
		$uniqueId .= (microtime(true) * 10000) . mt_rand(100000, 999999);
		return $uniqueId;
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

		$killProcessCommands[] = 'sudo ' . $binaryFiles['kill'] . ' -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk \'{print $1}\')';
		$killProcessCommands[] = 'sudo ' . $binaryFiles['telinit'] . ' u';
		$killProcessCommands = implode("\n", $killProcessCommands);

		if (file_put_contents('/var/www/firewall-security-api/system-action-deploy-system-commands.sh', $killProcessCommands) === false) {
			echo 'Error adding kill process ID commands, please try again.' . "\n";
			exit;
		}

		shell_exec('sudo chmod +x /var/www/firewall-security-api/system-action-deploy-system-commands.sh');
		shell_exec('cd /var/www/firewall-security-api/ && sudo ./system-action-deploy-system-commands.sh');
		return;
	}

	$ipAddressVersionNumbers = array(
		'32' => '4',
		'128' => '6'
	);

	if (empty($_SERVER['argv'][1]) === true) {
		echo 'Invalid URL parameter, please try again.' . "\n";
		exit;
	}

	if (empty($_SERVER['argv'][2]) === true) {
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

		$imageName = ucwords($imageDetails['id']) . ' ' . $imageDetails['version_id'];

		if (empty($packageSources[$imageDetails['id']][$imageDetails['version_id']]) === true) {
			echo 'Error installing on unsupported ' . $imageName . ' image, please try again.' . "\n";
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

		if (is_dir('/var/www/firewall-security-api/') === true) {
			shell_exec('sudo rm -rf /var/www/firewall-security-api/');
		}

		mkdir('/var/www/firewall-security-api/');
		chmod('/var/www/firewall-security-api/', 0755);
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install procps');
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install systemd');
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install sysvinit-core sysvinit-utils');
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install upstart*');
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
				'package' => 'systemd'
			)
		);
		$binaryFiles = array();

		foreach ($binaries as $binary) {
			$binaryFileListCommands = array(
				'#!/bin/bash',
				'whereis ' . $binary['name'] . ' | awk \'{ for (i=2; i<=NF; i++) print $i }\' | while read -r binaryFile; do echo $((sudo $binaryFile "' . $binary['command'] . '") 2>&1) | grep -c "' . $binary['output'] . '" && echo $binaryFile && break; done | tail -1'
			);
			$binaryFileListCommands = implode("\n", $binaryFileListCommands);

			if (file_exists('/var/www/firewall-security-api/system-action-deploy-system-binary-file-list-commands.sh') === true) {
				unlink('/var/www/firewall-security-api/system-action-deploy-system-binary-file-list-commands.sh');
			}

			file_put_contents('/var/www/firewall-security-api/system-action-deploy-system-binary-file-list-commands.sh', $binaryFileListCommands);
			chmod('/var/www/firewall-security-api/system-action-deploy-system-binary-file-list-commands.sh', 0755);
			exec('cd /var/www/firewall-security-api/ && sudo ./system-action-deploy-system-binary-file-list-commands.sh', $binaryFile);
			$binaryFile = current($binaryFile);

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
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install apache2 bind9 bind9utils coreutils cron curl git iptables libapache2-mod-fcgid net-tools php-curl php-fpm php-mysqli syslinux systemd util-linux');
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install gnupg');
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install procps');
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install sysvinit-core sysvinit-utils');
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install upstart*');
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y purge conntrack');
		$binaries = array(
			array(
				'command' => $uniqueId,
				'name' => 'a2dismod',
				'output' => 'Module ' . $uniqueId,
				'package' => 'apache2'
			),
			array(
				'command' => $uniqueId,
				'name' => 'a2enconf',
				'output' => 'Conf ' . $uniqueId,
				'package' => 'apache2'
			),
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
				'name' => 'ip6tables-restore',
				'output' => 'tables-restore ',
				'package' => 'iptables'
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
			),
			array(
				'command' => '-' . $uniqueId,
				'name' => 'tar',
				'output' => 'invalid option',
				'package' => 'tar'
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

		foreach ($binaries as $binary) {
			$binaryFileListCommands = array(
				'#!/bin/bash',
				'whereis ' . $binary['name'] . ' | awk \'{ for (i=2; i<=NF; i++) print $i }\' | while read -r binaryFile; do echo $((sudo $binaryFile "' . $binary['command'] . '") 2>&1) | grep -c "' . $binary['output'] . '" && echo $binaryFile && break; done | tail -1'
			);
			$binaryFileListCommands = implode("\n", $binaryFileListCommands);

			if (file_exists('/var/www/firewall-security-api/system-action-deploy-system-binary-file-list-commands.sh') === true) {
				unlink('/var/www/firewall-security-api/system-action-deploy-system-binary-file-list-commands.sh');
			}

			file_put_contents('/var/www/firewall-security-api/system-action-deploy-system-binary-file-list-commands.sh', $binaryFileListCommands);
			chmod('/var/www/firewall-security-api/system-action-deploy-system-binary-file-list-commands.sh', 0755);
			exec('cd /var/www/firewall-security-api/ && sudo ./system-action-deploy-system-binary-file-list-commands.sh', $binaryFile);
			$binaryFile = current($binaryFile);

			if (empty($binaryFile) === true) {
				shell_exec('sudo apt-get update');
				shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install ' . $binary['package']);
				echo 'Error listing ' . $binary['name'] . ' binary file from the ' . $binary['package'] . ' package, please try again.' . "\n";
				exit;
			}

			$binaryFiles[$binary['name']] = $binaryFile;
		}

		if (file_exists('/var/www/firewall-security-api/system-action-deploy-system-binary-file-list-commands.sh') === true) {
			unlink('/var/www/firewall-security-api/system-action-deploy-system-binary-file-list-commands.sh');
		}

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
		file_put_contents('/etc/php/' . $phpVersion . '/fpm/php.ini', $phpSettings);
		shell_exec('sudo ' . $binaryFiles['service'] . ' php' . $phpVersion . '-fpm stop');
		shell_exec('sudo ' . $binaryFiles['service'] . ' php' . $phpVersion . '-fpm start');
		$kernelSettings = array(
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
			'net.ipv6.bindv6only = 0',
			'net.ipv6.conf.all.accept_redirects = 0',
			'net.ipv6.conf.all.accept_source_route = 0',
			'net.ipv6.conf.all.disable_ipv6 = 0',
			'net.ipv6.conf.all.forwarding = 0',
			'net.ipv6.ip6frag_high_thresh = 64000000',
			'net.ipv6.ip6frag_low_thresh = 32000000',
			'net.ipv6.neigh.default.gc_interval = 50',
			'net.ipv6.neigh.default.gc_stale_time = 10',
			'net.ipv6.neigh.default.gc_thresh1 = 32',
			'net.ipv6.neigh.default.gc_thresh2 = 1024',
			'net.ipv6.neigh.default.gc_thresh3 = 2048',
			'net.ipv6.route.gc_timeout = 2',
			'vm.dirty_background_ratio = 10',
			'vm.dirty_expire_centisecs = 10',
			'vm.dirty_ratio = 10',
			'vm.dirty_writeback_centisecs = 100',
			'vm.max_map_count = 1000000',
			'vm.mmap_min_addr = 4096',
			'vm.overcommit_memory = 0',
			'vm.swappiness = 0'
		);
		$kernelSettings = implode("\n", $kernelSettings);
		file_put_contents('/etc/sysctl.conf', $kernelSettings);
		shell_exec('sudo ' . $binaryFiles['sysctl'] . ' -p');
		exec('getconf PAGE_SIZE 2>&1', $kernelPageSize);
		$kernelPageSize = current($kernelPageSize);
		exec('free -b | grep "Mem:" | grep -v free | awk \'{print $2}\'', $memoryCapacityBytes);
		$memoryCapacityBytes = current($memoryCapacityBytes);
		$memoryCapacityPages = ceil($memoryCapacityBytes / $kernelPageSize);
		$defaultSocketBufferMemoryBytes = ceil($memoryCapacityBytes * 0.00034);
		$kernelSettings = array(
			'kernel.shmall="' . floor($memoryCapacityBytes / $kernelPageSize) . '"',
			'kernel.shmmax="' . $memoryCapacityBytes . '"',
			'net.core.optmem_max="' . ceil($memoryCapacityBytes * 0.02) . '"',
			'net.core.rmem_default="' . $defaultSocketBufferMemoryBytes . '"',
			'net.core.rmem_max="' . ($defaultSocketBufferMemoryBytes * 2) . '"',
			'net.core.wmem_default="' . $defaultSocketBufferMemoryBytes . '"',
			'net.core.wmem_max="' . ($defaultSocketBufferMemoryBytes * 2) . '"'
		);

		foreach ($ipAddressVersionNumbers as $ipAddressVersionNumber) {
			$kernelSettings[] = 'net.ipv' . $ipAddressVersionNumber . '.tcp_mem="' . $memoryCapacityPages . ' ' . $memoryCapacityPages . ' ' . $memoryCapacityPages . '"';
			$kernelSettings[] = 'net.ipv' . $ipAddressVersionNumber . '.tcp_rmem="1 ' . $defaultSocketBufferMemoryBytes . ' ' . ($defaultSocketBufferMemoryBytes * 2) . '"';
			$kernelSettings[] = 'net.ipv' . $ipAddressVersionNumber . '.tcp_wmem="1 ' . $defaultSocketBufferMemoryBytes . ' ' . ($defaultSocketBufferMemoryBytes * 2) . '"';
			$kernelSettings[] = 'net.ipv' . $ipAddressVersionNumber . '.udp_mem="' . $memoryCapacityPages . ' ' . $memoryCapacityPages . ' ' . $memoryCapacityPages . '"';
		}

		foreach ($kernelSettings as $kernelSetting) {
			shell_exec('sudo ' . $binaryFiles['sysctl'] . ' -w ' . $kernelSetting);
		}

		shell_exec('sudo /usr/bin/systemctl stop mysql');
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y purge mysql-server mysql-client mysql-common mysql-server-core-* mysql-client-core-*');
		shell_exec('sudo rm -rf /etc/mysql/ /var/lib/mysql/ /var/log/mysql/');
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y autoremove');
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y autoclean');

		if (file_exists('/var/www/firewall-security-api/mysql.deb') === true) {
			unlink('/var/www/firewall-security-api/mysql.deb');
		}

		shell_exec('cd /var/www/firewall-security-api/ && sudo ' . $binaryFiles['wget'] . ' -O mysql.deb --connect-timeout=5 --dns-timeout=5 --no-dns-cache --read-timeout=60 --tries=1 https://dev.mysql.com/get/mysql-apt-config_0.8.20-1_all.deb');

		if (file_exists('/var/www/firewall-security-api/mysql.deb') === false) {
			echo 'Error downloading MySQL, please try again.' . "\n";
			exit;
		}

		shell_exec('cd /var/www/firewall-security-api/ && sudo DEBIAN_FRONTEND=noninteractive dpkg -i mysql.deb');

		if (file_exists('/var/www/firewall-security-api/mysql.deb') === true) {
			unlink('/var/www/firewall-security-api/mysql.deb');
		}

		shell_exec('sudo add-apt-repository -y universe');
		shell_exec('sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 467B942D3A79BD29');
		shell_exec('sudo apt-get update');
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install libmecab2 lsb-release');
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get --fix-broken -y install mysql-common mysql-client mysql-community-server-core mysql-community-client mysql-community-client-core mysql-community-server mysql-community-client-plugins mysql-server');

		if (file_exists('/etc/mysql/mysql.conf.d/mysqld.cnf') === false) {
			echo 'Error installing MySQL, please try again.' . "\n";
			exit;
		}

		$mysqlSettings = array(
			'[mysqld]',
			'bind_address = 127.0.0.1',
			'datadir = /var/lib/mysql/',
			'default_authentication_plugin = mysql_native_password',
			'host_cache_size = 0',
			'innodb_read_io_threads = 64',
			'innodb_write_io_threads = 64',
			'log_error = /var/log/mysql/error.log',
			'long_query_time = 100',
			'max_allowed_packet = 1000000000',
			'max_connections = 100000',
			'open_files_limit = 1000000',
			'port = 3306',
			'pid_file = /var/run/mysqld/mysqld.pid',
			'socket = /var/run/mysqld/mysqld.sock'
		);
		$mysqlSettings = implode("\n", $mysqlSettings);
		file_put_contents('/etc/mysql/mysql.conf.d/mysqld.cnf', $mysqlSettings);
		shell_exec('sudo ' . $binaryFiles['service'] . ' mysql restart');
		shell_exec('sudo mysql -u root -p"password" -e "DELETE FROM mysql.user WHERE User=\'\'; DELETE FROM mysql.user WHERE User=\'root\' AND Host NOT IN (\'localhost\', \'127.0.0.1\', \'::1\');"');
		shell_exec('sudo mysql -u root -p"password" -e "DROP USER \'root\'@\'localhost\'; CREATE USER \'root\'@\'localhost\' IDENTIFIED BY \'password\'; GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\' WITH GRANT OPTION; FLUSH PRIVILEGES;"');
		shell_exec('sudo ' . $binaryFiles['service'] . ' mysql restart');
		shell_exec('sudo apt-get update');
		shell_exec('sudo ' . $binaryFiles['systemctl'] . ' start apache2');
		$apacheSettings = array(
			'<VirtualHost *:80>',
			'ServerAlias ' . $_SERVER['argv'][1],
			'ServerName ' . $_SERVER['argv'][1],
			'DocumentRoot /var/www/firewall-security-api/',
			'<Directory /var/www/firewall-security-api/>',
			'Allow from all',
			'Options FollowSymLinks',
			'AllowOverride All',
			'</Directory>',
			'</VirtualHost>'
		);
		$apacheSettings = implode("\n", $apacheSettings);
		file_put_contents('/etc/apache2/sites-available/' . $_SERVER['argv'][1] . '.conf', $apacheSettings);
		shell_exec('cd /etc/apache2/sites-available && sudo ' . $binaryFiles['a2ensite'] . ' ' . $_SERVER['argv'][1]);
		$apacheSettings = array(
			'<IfModule mpm_event_module>',
			'AsyncRequestWorkerFactor 10000',
			'MaxConnectionsPerChild 100000',
			'MaxMemFree 0',
			'MaxRequestWorkers 1000',
			'MaxSpareThreads 1000',
			'MinSpareThreads 10',
			'ServerLimit 100',
			'StartServers 1',
			'ThreadLimit 1000',
			'ThreadsPerChild 1000',
			'</IfModule>'
		);
		$apacheSettings = implode("\n", $apacheSettings);
		file_put_contents('/etc/apache2/mods-available/mpm_event.conf', $apacheSettings);
		shell_exec('sudo ' . $binaryFiles['systemctl'] . ' stop apache2');
		shell_exec('cd /etc/apache2/mods-available && sudo ' . $binaryFiles['a2dismod'] . ' php*');
		shell_exec('cd /etc/apache2/mods-available && sudo ' . $binaryFiles['a2dismod'] . ' mpm_prefork');
		shell_exec('cd /etc/apache2/mods-available && sudo ' . $binaryFiles['a2dismod'] . ' mpm_worker');
		shell_exec('cd /etc/apache2/mods-available && sudo ' . $binaryFiles['a2enmod'] . ' rewrite.load');
		shell_exec('cd /etc/apache2/mods-available && sudo ' . $binaryFiles['a2enmod'] . ' mpm_event');
		shell_exec('cd /etc/apache2/conf-available && sudo ' . $binaryFiles['a2enconf'] . ' php*');
		shell_exec('cd /etc/apache2/mods-available && sudo ' . $binaryFiles['a2enmod'] . ' proxy');
		shell_exec('cd /etc/apache2/mods-available && sudo ' . $binaryFiles['a2enmod'] . ' proxy_fcgi');
		shell_exec('sudo ' . $binaryFiles['systemctl'] . ' start apache2');
		shell_exec('sudo ' . $binaryFiles['apachectl'] . ' graceful');
		shell_exec('sudo rm -rf /var/www/firewall-security-api/');
		// todo: download from most-recent release after v1
		shell_exec('cd /var/www/ && sudo ' . $binaryFiles['wget'] . ' --connect-timeout=5 --dns-timeout=5 --no-dns-cache --read-timeout=60 --tries=1 https://github.com/twexxor/firewall-security-api/archive/refs/heads/main.tar.gz');
		shell_exec('cd /var/www/ && sudo ' . $binaryFiles['tar'] . ' -xvzf main.tar.gz && sudo rm main.tar.gz');
		shell_exec('cd /var/www/ && sudo mv firewall-security-api-main firewall-security-api');

		if (file_exists('/var/www/firewall-security-api/readme.md') === false) {
			echo 'Error downloading system files, please try again.' . "\n";
			exit;
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
		$crontabCommandIndex = array_search('# firewall-security-api-system-processes', $crontabCommands);

		while (is_int($crontabCommandIndex) === true) {
			unset($crontabCommands[$crontabCommandIndex]);
			$crontabCommandIndex++;

			if (strpos($crontabCommands[$crontabCommandIndex], ' firewall-security-api-system-processes') === false) {
				$crontabCommandIndex = false;
			}
		}

		$crontabCommands[] = '# firewall-security-api-system-processes';
		$crontabCommands[] = '@reboot root sudo ' . $binaryFiles['crontab'] . ' /etc/crontab firewall-security-api-system-processes';
		// $crontabCommands[] = '* * * * * root sudo ' . $binaryFiles['php'] . ' /var/www/firewall-security-api/system-action-process-system-action.php process-node-request-logs firewall-security-api-system-processes';
		$crontabCommands[] = '';
		$crontabCommands = implode("\n", $crontabCommands);
		file_put_contents('/etc/crontab', $crontabCommands);
		shell_exec('sudo ' . $binaryFiles['crontab'] . ' /etc/crontab');
		$sshPortNumbers = array();

		if (file_exists('/etc/ssh/sshd_config') === true) {
			exec('grep "Port " /etc/ssh/sshd_config | grep -v "#" | awk \'{print $2}\' 2>&1', $sshPortNumbers);

			foreach ($sshPortNumbers as $sshPortNumberKey => $sshPortNumber) {
				if (
					((strlen($sshPortNumber) > 5) === true) ||
					(is_numeric($sshPortNumber) === false)
				) {
					unset($sshPortNumbers[$sshPortNumberKey]);
				}
			}
		}

		$firewallBinaryFiles = array(
			'4' => $binaryFiles['iptables-restore'],
			'6' => $binaryFiles['ip6tables-restore']
		);
		$nodeReservedInternalSources = array(
			'4' => array(
				array(
					'ipAddress' => '0.0.0.0',
					'ipAddressBlockLength' => '8'
				),
				array(
					'ipAddress' => '10.0.0.0',
					'ipAddressBlockLength' => '8'
				),
				array(
					'ipAddress' => '100.64.0.0',
					'ipAddressBlockLength' => '10'
				),
				array(
					'ipAddress' => '127.0.0.0',
					'ipAddressBlockLength' => '8'
				),
				array(
					'ipAddress' => '169.254.0.0',
					'ipAddressBlockLength' => '16'
				),
				array(
					'ipAddress' => '172.16.0.0',
					'ipAddressBlockLength' => '12'
				),
				array(
					'ipAddress' => '192.0.0.0',
					'ipAddressBlockLength' => '24'
				),
				array(
					'ipAddress' => '192.0.2.0',
					'ipAddressBlockLength' => '24'
				),
				array(
					'ipAddress' => '192.88.99.0',
					'ipAddressBlockLength' => '24'
				),
				array(
					'ipAddress' => '192.168.0.0',
					'ipAddressBlockLength' => '16'
				),
				array(
					'ipAddress' => '198.18.0.0',
					'ipAddressBlockLength' => '15'
				),
				array(
					'ipAddress' => '198.51.100.0',
					'ipAddressBlockLength' => '24'
				),
				array(
					'ipAddress' => '203.0.113.0',
					'ipAddressBlockLength' => '24'
				),
				array(
					'ipAddress' => '224.0.0.0',
					'ipAddressBlockLength' => '4'
				),
				array(
					'ipAddress' => '233.252.0.0',
					'ipAddressBlockLength' => '24'
				),
				array(
					'ipAddress' => '240.0.0.0',
					'ipAddressBlockLength' => '4'
				),
				array(
					'ipAddress' => '255.255.255.255',
					'ipAddressBlockLength' => '32'
				)
			),
			'6' => array(
				array(
					'ipAddress' => '0000:0000:0000:0000:0000:0000:0000:0000',
					'ipAddressBlockLength' => '128'
				),
				array(
					'ipAddress' => '0000:0000:0000:0000:0000:0000:0000:0001',
					'ipAddressBlockLength' => '128'
				),
				array(
					'ipAddress' => '0000:0000:0000:0000:0000:ffff:0000:0000',
					'ipAddressBlockLength' => '96'
				),
				array(
					'ipAddress' => '0000:0000:0000:0000:ffff:0000:0000:0000',
					'ipAddressBlockLength' => '96'
				),
				array(
					'ipAddress' => '0064:ff9b:0000:0000:0000:0000:0000:0000',
					'ipAddressBlockLength' => '96'
				),
				array(
					'ipAddress' => '0064:ff9b:0001:0000:0000:0000:0000:0000',
					'ipAddressBlockLength' => '48'
				),
				array(
					'ipAddress' => '0100:0000:0000:0000:0000:0000:0000:0000',
					'ipAddressBlockLength' => '64'
				),
				array(
					'ipAddress' => '2001:0000:0000:0000:0000:0000:0000:0000',
					'ipAddressBlockLength' => '32'
				),
				array(
					'ipAddress' => '2001:0020:0000:0000:0000:0000:0000:0000',
					'ipAddressBlockLength' => '28'
				),
				array(
					'ipAddress' => '2001:0db8:0000:0000:0000:0000:0000:0000',
					'ipAddressBlockLength' => '32'
				),
				array(
					'ipAddress' => '2002:0000:0000:0000:0000:0000:0000:0000',
					'ipAddressBlockLength' => '16'
				),
				array(
					'ipAddress' => 'fc00:0000:0000:0000:0000:0000:0000:0000',
					'ipAddressBlockLength' => '7'
				),
				array(
					'ipAddress' => 'fe80:0000:0000:0000:0000:0000:0000:0000',
					'ipAddressBlockLength' => '10'
				),
				array(
					'ipAddress' => 'ff00:0000:0000:0000:0000:0000:0000:0000',
					'ipAddressBlockLength' => '8'
				)
			)
		);
		$gcloudBinaryFileListCommands = array(
			'#!/bin/bash',
			'whereis gcloud | awk \'{ for (i=2; i<=NF; i++) print $i }\' | while read -r gcloudBinaryFile; do echo $((sudo $gcloudBinaryFile "-_-") 2>&1) | grep -c "unrecognized" && echo $gcloudBinaryFile && break; done | tail -1'
		);
		$gcloudBinaryFileListCommands = implode("\n", $gcloudBinaryFileListCommands);
		file_put_contents('/var/www/firewall-security-api/system-action-deploy-system-gcloud-binary-file-list-commands.sh', $gcloudBinaryFileListCommands);
		chmod('/var/www/firewall-security-api/system-action-deploy-system-gcloud-binary-file-list-commands.sh', 0755);
		exec('cd /var/www/firewall-security-api/ && sudo ./system-action-deploy-system-gcloud-binary-file-list-commands.sh', $gcloudBinaryFile);
		$gcloudBinaryFile = current($gcloudBinaryFile);

		if (empty($gcloudBinaryFile) === false) {
			unset($nodeReservedInternalSources['4'][4]);
			unset($nodeReservedInternalSources['6'][12]);
		}

		foreach ($ipAddressVersionNumbers as $ipAddressVersionNetworkMask => $ipAddressVersionNumber) {
			$firewallRules = array(
				'*filter',
				':INPUT ACCEPT [0:0]',
				':FORWARD ACCEPT [0:0]',
				':OUTPUT ACCEPT [0:0]',
				'-A INPUT -p icmp -m hashlimit --hashlimit-above 2/second --hashlimit-burst 2 --hashlimit-htable-gcinterval 100000 --hashlimit-htable-expire 10000 --hashlimit-mode srcip --hashlimit-name icmp --hashlimit-srcmask ' . $ipAddressVersionNetworkMask . ' -j DROP'
			);

			if (
				(empty($sshPortNumbers) === false) &&
				(is_array($sshPortNumbers) === true)
			) {
				foreach ($sshPortNumbers as $sshPortNumber) {
					$firewallRules[] = '-A INPUT -p tcp --dport ' . $sshPortNumber . ' -m hashlimit --hashlimit-above 10/minute --hashlimit-burst 10 --hashlimit-htable-gcinterval 600000 --hashlimit-htable-expire 60000 --hashlimit-mode srcip --hashlimit-name ssh --hashlimit-srcmask ' . $ipAddressVersionNetworkMask . ' -j DROP';
				}
			}

			$firewallRules[] = 'COMMIT';
			$firewallRules[] = '*raw';
			$firewallRules[] = ':PREROUTING ACCEPT [0:0]';
			$firewallRules[] = ':OUTPUT ACCEPT [0:0]';

			foreach ($nodeReservedInternalSources[$ipAddressVersionNumber] as $nodeReservedInternalSource) {
				$firewallRules[] = '-A PREROUTING ! -i lo -s ' . $nodeReservedInternalSource['ipAddress'] . '/' . $nodeReservedInternalSource['ipAddressBlockLength'] . ' -j DROP';
			}

			$firewallRules[] = 'COMMIT';

			if (file_exists('/var/www/firewall-security-api/firewall-ip-address-version-' . $ipAddressVersionNumber . '.dat') === true) {
				unlink('/var/www/firewall-security-api/firewall-ip-address-version-' . $ipAddressVersionNumber . '.dat');
			}

			touch('/var/www/firewall-security-api/firewall-ip-address-version-' . $ipAddressVersionNumber . '.dat');
			$firewallRulesParts = array();
			$firewallRulesPartsKey = 0;

			foreach ($firewallRules as $firewallRulesKey => $firewallRule) {
				if ((($firewallRulesKey % 1000) === 0) === true) {
					$firewallRulesPartsKey++;
					$firewallRulesParts[$firewallRulesPartsKey] = '';
				}

				$firewallRulesParts[$firewallRulesPartsKey] .= $firewallRule . "\n";
			}

			foreach ($firewallRulesParts as $firewallRulesPart) {
				shell_exec('sudo echo "' . $firewallRulesPart . '" >> /var/www/firewall-security-api/firewall-ip-aaddress-version-' . $ipAddressVersionNumber . '.dat');
			}

			shell_exec('sudo ' . $firewallBinaryFiles[$ipAddressVersionNumber] . ' < /var/www/firewall-security-api/firewall-ip-address-version-' . $ipAddressVersionNumber . '.dat');

			if (file_exists('/var/www/firewall-security-api/firewall-ip-address-version-' . $ipAddressVersionNumber . '.dat') === true) {
				unlink('/var/www/firewall-security-api/firewall-ip-address-version-' . $ipAddressVersionNumber . '.dat');
			}

			sleep(1);
		}
	} else {
		$systemDatabaseConnection = mysqli_connect('localhost', 'root', 'password');

		if ($systemDatabaseConnection === false) {
			echo 'Error connecting to system database, please try again.' . "\n";
			exit;
		}

		if (mysqli_query($systemDatabaseConnection, 'CREATE DATABASE IF NOT EXISTS `firewallSecurityApi` CHARSET UTF8') === false) {
			echo 'Error creating system database, please try again.' . "\n";
			exit;
		}

		$systemDatabaseConnection = mysqli_connect('localhost', 'root', 'password', 'firewallSecurityApi');

		if ($systemDatabaseConnection === false) {
			echo 'Error connecting to system database, please try again.' . "\n";
			exit;
		}

		$systemDatabases = array(
			'nodeProcesses' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeId' => 'VARCHAR(30)',
				'nodeNodeId' => 'VARCHAR(30)',
				'portNumber' => 'VARCHAR(5)',
				'type' => 'VARCHAR(14)'
			),
			'nodeProcessForwardingDestinations' => array(
				'addressVersion4' => 'VARCHAR(15)',
				'addressVersion4NodeId' => 'VARCHAR(30)',
				'addressVersion6' => 'VARCHAR(39)',
				'addressVersion6NodeId' => 'VARCHAR(30)',
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeId' => 'VARCHAR(30)',
				'nodeNodeId' => 'VARCHAR(30)',
				'nodeProcessType' => 'VARCHAR(14)',
				'portNumberVersion4' => 'VARCHAR(5)',
				'portNumberVersion6' => 'VARCHAR(5)'
			),
			'nodeProcessNodeUserAuthenticationCredentials' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeId' => 'VARCHAR(30)',
				'nodeNodeId' => 'VARCHAR(30)',
				'nodeProcessType' => 'VARCHAR(14)',
				'nodeUserAuthenticationCredentialId' => 'VARCHAR(30)',
				'nodeUserAuthenticationCredentialPassword' => 'VARCHAR(900)',
				'nodeUserAuthenticationCredentialUsername' => 'VARCHAR(900)',
				'nodeUserId' => 'VARCHAR(30)'
			),
			'nodeProcessNodeUserAuthenticationSources' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeId' => 'VARCHAR(30)',
				'nodeNodeId' => 'VARCHAR(30)',
				'nodeProcessType' => 'VARCHAR(14)',
				'nodeUserAuthenticationSourceId' => 'VARCHAR(30)',
				'nodeUserAuthenticationSourceIpAddress' => 'VARCHAR(39)',
				'nodeUserAuthenticationSourceIpAddressBlockLength' => 'VARCHAR(3)',
				'nodeUserAuthenticationSourceIpAddressVersionNumber' => 'VARCHAR(1)',
				'nodeUserId' => 'VARCHAR(30)'
			),
			'nodeProcessNodeUserNodeRequestDestinationLogs' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeId' => 'VARCHAR(30)',
				'nodeNodeId' => 'VARCHAR(30)',
				'nodeProcessType' => 'VARCHAR(14)',
				'nodeRequestDestinationId' => 'VARCHAR(30)',
				'nodeUserId' => 'VARCHAR(30)',
				'requestCount' => 'VARCHAR(30)'
			),
			'nodeProcessNodeUserNodeRequestDestinations' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeId' => 'VARCHAR(30)',
				'nodeNodeId' => 'VARCHAR(30)',
				'nodeProcessType' => 'VARCHAR(14)',
				'nodeRequestDestinationAddress' => 'VARCHAR(900)',
				'nodeRequestDestinationId' => 'VARCHAR(30)',
				'nodeUserId' => 'VARCHAR(30)'
			),
			'nodeProcessNodeUserNodeRequestLimitRules' => array(
				'activatedStatus' => 'VARCHAR(1)',
				'createdTimestamp' => 'VARCHAR(10)',
				'expiredTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeId' => 'VARCHAR(30)',
				'nodeNodeId' => 'VARCHAR(30)',
				'nodeProcessType' => 'VARCHAR(14)',
				'nodeRequestDestinationId' => 'VARCHAR(30)',
				'nodeRequestLimitRuleId' => 'VARCHAR(30)',
				'nodeUserId' => 'VARCHAR(30)'
			),
			'nodeProcessNodeUserRequestLogs' => array(
				'bytesReceived' => 'VARCHAR(30)',
				'bytesSent' => 'VARCHAR(30)',
				'createdTimestamp' => 'VARCHAR(10)',
				'destinationHostnameAddress' => 'VARCHAR(900)',
				'destinationIpAddress' => 'VARCHAR(39)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeId' => 'VARCHAR(30)',
				'nodeNodeId' => 'VARCHAR(30)',
				'nodeProcessType' => 'VARCHAR(14)',
				'nodeRequestDestinationId' => 'VARCHAR(30)',
				'nodeUserId' => 'VARCHAR(30)',
				'processedStatus' => 'VARCHAR(1)',
				'processingProcessId' => 'VARCHAR(5)',
				'responseCode' => 'VARCHAR(30)',
				'sourceIpAddress' => 'VARCHAR(39)'
			),
			'nodeProcessNodeUserResourceUsageLogs' => array(
				'bytesReceived' => 'VARCHAR(30)',
				'bytesSent' => 'VARCHAR(30)',
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeId' => 'VARCHAR(30)',
				'nodeNodeId' => 'VARCHAR(30)',
				'nodeProcessType' => 'VARCHAR(14)',
				'nodeUserId' => 'VARCHAR(30)',
				'requestCount' => 'VARCHAR(15)'
			),
			'nodeProcessNodeUsers' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeId' => 'VARCHAR(30)',
				'nodeNodeId' => 'VARCHAR(30)',
				'nodeProcessType' => 'VARCHAR(14)',
				'nodeUserAuthenticationStrictOnlyAllowedStatus' => 'VARCHAR(1)',
				'nodeUserId' => 'VARCHAR(30)',
				'nodeUserNodeRequestDestinationsOnlyAllowedStatus' => 'VARCHAR(1)',
				'nodeUserNodeRequestLogsAllowedStatus' => 'VARCHAR(1)'
			),
			'nodeProcessRecursiveDnsDestinations' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'destinationIpAddressVersion4' => 'VARCHAR(15)',
				'destinationIpAddressVersion4NodeId' => 'VARCHAR(30)',
				'destinationIpAddressVersion6' => 'VARCHAR(39)',
				'destinationIpAddressVersion6NodeId' => 'VARCHAR(30)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeId' => 'VARCHAR(30)',
				'nodeNodeId' => 'VARCHAR(30)',
				'nodeProcessType' => 'VARCHAR(14)',
				'portNumberVersion4' => 'VARCHAR(5)',
				'portNumberVersion6' => 'VARCHAR(5)',
				'sourceIpAddressVersion4' => 'VARCHAR(15)',
				'sourceIpAddressVersion6' => 'VARCHAR(39)'
			),
			'nodeProcessResourceUsageLogs' => array(
				'bytesReceived' => 'VARCHAR(30)',
				'bytesSent' => 'VARCHAR(30)',
				'cpuPercentage' => 'VARCHAR(3)',
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'memoryPercentage' => 'VARCHAR(3)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeId' => 'VARCHAR(30)',
				'nodeProcessType' => 'VARCHAR(14)',
				'requestCount' => 'VARCHAR(30)'
			),
			'nodeRequestDestinations' => array(
				'address' => 'VARCHAR(900)',
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)'
			),
			'nodeRequestLimitRules' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'intervalMinutes' => 'VARCHAR(10)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'requestCount' => 'VARCHAR(30)',
				'requestCountIntervalMinutes' => 'VARCHAR(10)',
			),
			'nodeReservedInternalDestinations' => array(
				'addedStatus' => 'VARCHAR(1)',
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'ipAddress' => 'VARCHAR(39)',
				'ipAddressVersionNumber' => 'VARCHAR(1)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeId' => 'VARCHAR(30)',
				'nodeNodeId' => 'VARCHAR(30)',
				'processedStatus' => 'VARCHAR(1)'
			),
			'nodeReservedInternalSources' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'ipAddress' => 'VARCHAR(39)',
				'ipAddressBlockLength' => 'VARCHAR(3)',
				'ipAddressVersionNumber' => 'VARCHAR(1)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeId' => 'VARCHAR(30)'
			),
			'nodeResourceUsageLogs' => array(
				'bytesReceived' => 'VARCHAR(30)',
				'bytesSent' => 'VARCHAR(30)',
				'cpuCapacityMegahertz' => 'VARCHAR(30)',
				'cpuCoreCount' => 'VARCHAR(30)',
				'cpuPercentage' => 'VARCHAR(3)',
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'memoryCapacityMegabytes' => 'VARCHAR(30)',
				'memoryPercentage' => 'VARCHAR(3)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeId' => 'VARCHAR(30)',
				'requestCount' => 'VARCHAR(30)',
				'storageCapacityMegabytes' => 'VARCHAR(30)',
				'storagePercentage' => 'VARCHAR(3)'
			),
			'nodes' => array(
				'activatedStatus' => 'VARCHAR(1)',
				'authenticationToken' => 'VARCHAR(30)',
				'cpuCapacityMegahertz' => 'VARCHAR(30)',
				'cpuCoreCount' => 'VARCHAR(30)',
				'createdTimestamp' => 'VARCHAR(10)',
				'deployedStatus' => 'VARCHAR(1)',
				'externalIpAddressVersion4' => 'VARCHAR(15)',
				'externalIpAddressVersion4Type' => 'VARCHAR(23)',
				'externalIpAddressVersion6' => 'VARCHAR(39)',
				'externalIpAddressVersion6Type' => 'VARCHAR(23)',
				'id' => 'VARCHAR(30)',
				'internalIpAddressVersion4' => 'VARCHAR(15)',
				'internalIpAddressVersion4Type' => 'VARCHAR(23)',
				'internalIpAddressVersion6' => 'VARCHAR(39)',
				'internalIpAddressVersion6Type' => 'VARCHAR(23)',
				'memoryCapacityMegabytes' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeId' => 'VARCHAR(30)',
				'processedStatus' => 'VARCHAR(1)',
				'processingProgressCheckpoint' => 'VARCHAR(35)',
				'processingProgressOverrideStatus' => 'VARCHAR(1)',
				'processingProgressPercentage' => 'VARCHAR(3)',
				'processingStatus' => 'VARCHAR(1)',
				'storageCapacityMegabytes' => 'VARCHAR(30)'
			),
			'nodeUserAuthenticationCredentials' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeUserId' => 'VARCHAR(30)',
				'password' => 'VARCHAR(900)',
				'username' => 'VARCHAR(900)'
			),
			'nodeUserAuthenticationSources' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'ipAddress' => 'VARCHAR(39)',
				'ipAddressBlockLength' => 'VARCHAR(3)',
				'ipAddressVersionNumber' => 'VARCHAR(1)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeUserId' => 'VARCHAR(30)'
			),
			'nodeUserNodeRequestDestinations' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeRequestDestinationAddress' => 'VARCHAR(39)',
				'nodeRequestDestinationId' => 'VARCHAR(30)',
				'nodeUserId' => 'VARCHAR(30)'
			),
			'nodeUserNodeRequestLimitRules' => array(
				'activatedStatus' => 'VARCHAR(1)',
				'createdTimestamp' => 'VARCHAR(10)',
				'expiredTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeRequestDestinationId' => 'VARCHAR(30)',
				'nodeRequestLimitRuleId' => 'VARCHAR(30)',
				'nodeUserId' => 'VARCHAR(30)'
			),
			'nodeUsers' => array(
				'authenticationStrictOnlyAllowedStatus' => 'VARCHAR(1)',
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeRequestDestinationsOnlyAllowedStatus' => 'VARCHAR(1)',
				'nodeRequestLogsAllowedStatus' => 'VARCHAR(1)',
				'tag' => 'VARCHAR(900)'
			),
			'systemDatabaseColumns' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'key' => 'VARCHAR(900)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'systemDatabaseId' => 'VARCHAR(30)'
			),
			'systemDatabases' => array(
				'authenticationCredentialAddress' => 'VARCHAR(30)',
				'authenticationCredentialPassword' => 'VARCHAR(30)',
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'tableKey' => 'VARCHAR(900)',
				'tag' => 'VARCHAR(900)'
			),
			'systemRequestLogs' => array(
				'bytesReceived' => 'VARCHAR(30)',
				'bytesSent' => 'VARCHAR(30)',
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'nodeId' => 'VARCHAR(30)',
				'responseAuthenticatedStatus' => 'VARCHAR(1)',
				'responseData' => 'VARCHAR(900)',
				'responseMessage' => 'VARCHAR(100)',
				'responseValidatedStatus' => 'VARCHAR(1)',
				'sourceIpAddress' => 'VARCHAR(39)',
				'systemAction' => 'VARCHAR(100)',
				'systemUserAuthenticationTokenId' => 'VARCHAR(30)',
				'systemUserId' => 'VARCHAR(30)',
				'value' => 'VARCHAR(900)'
			),
			'systemResourceUsageLogs' => array(
				'bytesReceived' => 'VARCHAR(30)',
				'bytesSent' => 'VARCHAR(30)',
				'cpuCapacityMegahertz' => 'VARCHAR(30)',
				'cpuCoreCount' => 'VARCHAR(30)',
				'cpuPercentage' => 'VARCHAR(3)',
				'createdTimestamp' => 'VARCHAR(10)',
				'destinationIpAddress' => 'VARCHAR(39)',
				'id' => 'VARCHAR(30)',
				'memoryCapacityMegabytes' => 'VARCHAR(30)',
				'memoryPercentage' => 'VARCHAR(3)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'storageCapacityMegabytes' => 'VARCHAR(30)',
				'storagePercentage' => 'VARCHAR(3)'
			),
			'systemSettings' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'key' => 'VARCHAR(900)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'value' => 'VARCHAR(900)'
			),
			'systemUserAuthenticationTokens' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'systemUserId' => 'VARCHAR(30)',
				'value' => 'VARCHAR(30)'
			),
			'systemUserAuthenticationTokenScopes' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'systemAction' => 'VARCHAR(900)',
				'systemUserAuthenticationTokenId' => 'VARCHAR(30)',
				'systemUserId' => 'VARCHAR(30)'
			),
			'systemUserAuthenticationTokenSources' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'ipAddressRangeStart' => 'VARCHAR(39)',
				'ipAddressRangeStop' => 'VARCHAR(39)',
				'ipAddressRangeVersionNumber' => 'VARCHAR(1)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'systemUserAuthenticationTokenId' => 'VARCHAR(30)',
				'systemUserId' => 'VARCHAR(30)'
			),
			'systemUsers' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'systemUserId' => 'VARCHAR(30)'
			),
			'systemUserSystemUsers' => array(
				'createdTimestamp' => 'VARCHAR(10)',
				'id' => 'VARCHAR(30)',
				'modifiedTimestamp' => 'VARCHAR(10)',
				'systemUserId' => 'VARCHAR(30)',
				'systemUserSystemUserId' => 'VARCHAR(30)'
			)
		);
		$systemDatabaseCommands = array();

		foreach ($systemDatabases as $systemDatabaseTableKey => $systemDatabaseColumnKeys) {
			$systemDatabaseCommands[] = 'CREATE TABLE IF NOT EXISTS `' . $systemDatabaseTableKey . '` (`createdTimestamp` VARCHAR(10) DEFAULT "");';

			foreach ($systemDatabaseColumnKeys as $systemDatabaseColumnKey => $systemDatabaseColumnType) {
				if (($systemDatabaseColumnKey === 'createdTimestamp') === true) {
					continue;
				}

				$systemDatabaseCommandActions = array(
					'add' => 'ADD `' . $systemDatabaseColumnKey . '`',
					'change' => 'CHANGE `' . $systemDatabaseColumnKey . '` `' . $systemDatabaseColumnKey . '`'
				);
				$systemDatabaseCommand = 'ALTER TABLE `' . $systemDatabaseTableKey . '` ' . $systemDatabaseCommandActions['change'] . ' ' . $systemDatabaseColumnType . ' DEFAULT ""';

				if (mysqli_query($systemDatabaseConnection, $systemDatabaseCommand) === false) {
					$systemDatabaseCommands[] = str_replace($systemDatabaseCommandActions['change'], $systemDatabaseCommandActions['add'], $systemDatabaseCommand);
				}

				if (($systemDatabaseColumnKey === 'id') === true) {
					$systemDatabaseCommands[$systemDatabaseColumnKey . '__' . $systemDatabaseTableKey] = 'ALTER TABLE `' . $systemDatabaseTableKey . '` ADD PRIMARY KEY (`' . $systemDatabaseColumnKey . '`)';
				}
			}
		}

		foreach ($systemDatabaseCommands as $systemDatabaseCommandKey => $systemDatabaseCommand) {
			if (
				(is_numeric($systemDatabaseCommandKey) === false) &&
				((strpos($systemDatabaseCommand, 'ADD PRIMARY KEY') === false) === false)
			) {
				$systemDatabaseCommandKey = explode('__', $systemDatabaseCommandKey);
				$systemDatabaseKeys = mysqli_query($systemDatabaseConnection, 'SHOW KEYS FROM `' . $systemDatabaseCommandKey[1] . '` WHERE Column_name=\'' . $systemDatabaseCommandKey[0] . '\'')->num_rows;

				if (empty($systemDatabaseKeys) === false) {
					continue;
				}
			}

			mysqli_query($systemDatabaseConnection, $systemDatabaseCommand);
		}

		foreach ($systemDatabases as $systemDatabaseTableKey => $systemDatabaseColumnKeys) {
			$systemDatabaseCommandResponse = mysqli_query($systemDatabaseConnection, 'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = \'' . $systemDatabaseTableKey . '\'');

			if (empty($systemDatabaseCommandResponse->num_rows) === true) {
				echo 'Error executing system database commands, please try again.' . "\n";
				exit;
			}

			foreach ($systemDatabaseCommandResponse as $systemDatabaseCommandResponse) {
				$systemDatabaseCommandResponse = current($systemDatabaseCommandResponse);
				$systemDatabaseCommandResponse = intval($systemDatabaseCommandResponse);

				if ((count($systemDatabaseColumnKeys) === $systemDatabaseCommandResponse) === false) {
					echo 'Error counting system database columns in system database ' . $systemDatabaseTableKey . ', please try again.' . "\n";
					exit;
				}
			}
                }

		$timestamp = time();
		$systemUserAuthenticationToken = _createUniqueId();
		$systemUserAuthenticationTokenId = _createUniqueId();
		$systemUserId = _createUniqueId();
		$systemDatabaseData = array(
			'systemUserAuthenticationTokens' => array(
				array(
					'createdTimestamp' => $timestamp,
					'id' => $systemUserAuthenticationTokenId,
					'modifiedTimestamp' => $timestamp,
					'systemUserId' => $systemUserId,
					'value' => $systemUserAuthenticationToken
				)
			),
			'systemUsers' => array(
				array(
					'createdTimestamp' => $timestamp,
					'id' => $systemUserId,
					'modifiedTimestamp' => $timestamp,
					'systemUserId' => $systemUserId
				)
			),
			'systemUserSystemUsers' => array(
				array(
					'createdTimestamp' => $timestamp,
					'id' => _createUniqueId(),
					'modifiedTimestamp' => $timestamp,
					'systemUserId' => $systemUserId,
					'systemUserSystemUserId' => $systemUserId
				)
			)
		);
		require_once('/var/www/firewall-security-api/system-action-validate-ip-address-version-number.php');
		$systemSettingsData = array(
			'versionNumber' => '1'
		);

		foreach ($ipAddressVersionNumbers as $ipAddressVersionNumber) {
			$systemSettingsData['endpointDestinationIpAddress'] = _validateIpAddressVersionNumber($_SERVER['argv'][1], $ipAddressVersionNumber);

			if (($systemSettingsData['endpointDestinationIpAddress'] === false) === false) {
				$systemSettingsData['endpointDestinationIpAddressVersionNumber'] = $ipAddressVersionNumber;
				break;
			}
		}

		if ($systemSettingsData['endpointDestinationIpAddress'] === false) {
			echo 'Invalid system endpoint destination IP address, please try again.' . "\n";
			exit;
		}

		require_once('/var/www/firewall-security-api/system-action-validate-ip-address-type.php');
		$systemSettingsData['endpointDestinationIpAddressType'] = _validateIpAddressType($systemSettingsData['endpointDestinationIpAddress'], $systemSettingsData['endpointDestinationIpAddressVersionNumber']);

		foreach ($systemSettingsData as $systemSettingsDataKey => $systemSettingsDataValue) {
			$systemDatabaseData['systemSettings'][] = array(
				'createdTimestamp' => $timestamp,
				'id' => _createUniqueId(),
				'modifiedTimestamp' => $timestamp,
				'key' => $systemSettingsDataKey,
				'value' => $systemSettingsDataValue
			);
		}

		$systemSettingsData = json_encode($systemSettingsData);
		file_put_contents('/var/www/firewall-security-api/system-settings-data.json', $systemSettingsData);

		foreach ($systemDatabases as $systemDatabaseTableKey => $systemDatabaseColumnKeys) {
			$systemDatabaseId = _createUniqueId();
			$systemDatabaseData['systemDatabases'][] = array(
				'authenticationCredentialAddress' => 'localhost',
				'authenticationCredentialPassword' => 'password',
				'createdTimestamp' => $timestamp,
				'id' => $systemDatabaseId,
				'modifiedTimestamp' => $timestamp,
				'tableKey' => $systemDatabaseTableKey
			);

			foreach ($systemDatabaseColumnKeys as $systemDatabaseColumnKey => $systemDatabaseColumnType) {
				$systemDatabaseData['systemDatabaseColumns'][] = array(
					'createdTimestamp' => $timestamp,
					'id' => _createUniqueId(),
					'key' => $systemDatabaseColumnKey,
					'modifiedTimestamp' => $timestamp,
					'systemDatabaseId' => $systemDatabaseId
				);
			}
		}

		$systemFiles = scandir('/var/www/firewall-security-api/');

		foreach ($systemFiles as $systemFile) {
			if ((substr($systemFile, 0, 13) === 'system-action') === true) {
				$systemAction = '';
				$systemActionFile = substr($systemFile, 14);
				$systemActionFile = substr($systemActionFile, 0, -4);
				$systemActionFileIndex = 0;

				while (isset($systemActionFile[$systemActionFileIndex]) === true) {
					if ((strpos($systemActionFile[$systemActionFileIndex], '-') === false) === false) {
						$systemActionFileIndex++;
						$systemActionFile[$systemActionFileIndex] = strtoupper($systemActionFile[$systemActionFileIndex]);
					}

					$systemAction .= $systemActionFile[$systemActionFileIndex];
					$systemActionFileIndex++;
				}

				$systemDatabaseData['systemUserAuthenticationTokenScopes'][] = array(
					'createdTimestamp' => $timestamp,
					'id' => _createUniqueId(),
					'modifiedTimestamp' => $timestamp,
					'systemAction' => $systemAction,
					'systemUserAuthenticationTokenId' => $systemUserAuthenticationTokenId,
					'systemUserId' => $systemUserId
				);
			}
		}

		foreach ($systemDatabaseData as $systemDatabaseTableKey => $systemDatabaseRows) {
			foreach ($systemDatabaseRows as $systemDatabaseRow) {
				$systemDatabaseRowColumnKeys = array_keys($systemDatabaseRow);
				$systemDatabaseRowColumnKeys = implode('`, `', $systemDatabaseRowColumnKeys);
				$systemDatabaseRowColumnValues = array_values($systemDatabaseRow);
				$systemDatabaseRowColumnValues = implode('\', \'', $systemDatabaseRowColumnValues);

				if (mysqli_query($systemDatabaseConnection, 'INSERT IGNORE INTO `' . $systemDatabaseTableKey . '` (`' . $systemDatabaseRowColumnKeys . '`) VALUES (\'' . $systemDatabaseRowColumnValues . '\')') === false) {
					echo 'Error adding system database data, please try again.' . "\n";
					exit;
				}
			}
		}

		echo "\n" . 'System user authentication token is ' . $systemUserAuthenticationToken . "\n";
		echo 'System deployed successfully.' . "\n";
		exit;
	}
?>
