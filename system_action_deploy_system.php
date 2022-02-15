<?php
	/*
		for debugging
		todo: condense into one line for easy deployment from readme instructions
		  systemEndpointDestinationAddress=127.0.0.1
 		  cd /tmp && rm -rf /etc/cloud/ /var/lib/cloud/ ; apt-get update ; DEBIAN_FRONTEND=noninteractive apt-get -y install sudo ; sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk '{print $1}') ; sudo $(whereis telinit | awk '{print $2}') u ; sudo rm -rf /etc/cloud/ /var/lib/cloud/ ; sudo dpkg --configure -a ; sudo apt-get update && sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php wget --fix-missing && sudo rm system_action_deploy_system.php ; sudo wget -O system_action_deploy_system.php --no-dns-cache --retry-connrefused --timeout=10 --tries=2 "https://raw.githubusercontent.com/nodecompute/nodecompute/main/system_action_deploy_system.php?$RANDOM" && sudo php system_action_deploy_system.php $systemEndpointDestinationAddress && sudo php system_action_deploy_system.php $systemEndpointDestinationAddress 1;
		  nodeExternalIpAddressVersion4=127.0.0.2
		  nodeInternalIpAddressVersion4=127.0.0.3
		  systemUserAuthenticationToken=1234
		  sudo wget -O /tmp/debug.php --no-dns-cache --post-data 'json={"action":"add_node","data":{"external_ip_address_version_4":"$nodeExternalIpAddressVersion4","internal_ip_address_version_4":"$nodeInternalIpAddressVersion4"},"system_user_authentication_token":"$systemUserAuthenticationToken"}' --retry-connrefused --timeout=10 --tries=2 $systemEndpointDestinationAddress/system_endpoint.php
		  sudo cat /tmp/debug.php for node ID
		  nodeId="1"
		  sudo wget -O /tmp/debug.php --no-dns-cache --post-data='{"action":"add_node_process","data":{"node_id":"$nodeId","port_number":"1080","type":"socks_proxy"},"system_user_authentication_token":"$systemUserAuthenticationToken"}' --retry-connrefused --timeout=10 --tries=2 $systemEndpointDestinationAddress/system_endpoint.php
		  sudo wget -O /tmp/debug.php --no-dns-cache --post-data='{"action":"add_node_process","data":{"node_id":"$nodeId","port_number":"1081","type":"socks_proxy"},"system_user_authentication_token":"$systemUserAuthenticationToken"}' --retry-connrefused --timeout=10 --tries=2 $systemEndpointDestinationAddress/system_endpoint.php
		  sudo wget -O /tmp/debug.php --no-dns-cache --post-data='{"action":"add_node_process","data":{"node_id":"$nodeId","port_number":"53","type":"recursive_dns"},"system_user_authentication_token":"$systemUserAuthenticationToken"}' --retry-connrefused --timeout=10 --tries=2 $systemEndpointDestinationAddress/system_endpoint.php
		  sudo wget -O /tmp/debug.php --no-dns-cache --post-data='{"action":"add_node_process","data":{"node_id":"$nodeId","port_number":"54","type":"recursive_dns"},"system_user_authentication_token":"$systemUserAuthenticationToken"}' --retry-connrefused --timeout=10 --tries=2 $systemEndpointDestinationAddress/system_endpoint.php
		  sudo wget -O /tmp/debug.php --no-dns-cache --post-data='{"action":"add_node_process_recursive_dns_destination","data":{"destination_ip_address_version_4":"$nodeExternalIpAddressVersion4","node_id":"$nodeId","port_number":"53","type":"socks_proxy"},"system_user_authentication_token":"$systemUserAuthenticationToken"}' --retry-connrefused --timeout=10 --tries=2 $systemEndpointDestinationAddress/system_endpoint.php
		  sudo wget -O /tmp/debug.php --no-dns-cache --post-data='{"action":"activate_node","data":{"id":"$nodeId","system_user_authentication_token":"$systemUserAuthenticationToken"}}' --retry-connrefused --timeout=10 --tries=2 $systemEndpointDestinationAddress/system_endpoint.php
	*/

	function _createUniqueId() {
		$uniqueId = hrtime(true);
		$uniqueId = substr($uniqueId, 6, 10) . (microtime(true) * 10000);
		$uniqueIdCharacters = 'abcdefghijklmnopqrstuvwxyz0123456789';

		while (isset($uniqueId[29]) === false) {
			$uniqueId .= $uniqueIdCharacters[mt_rand(0, 35)];
		}

		return $uniqueId;
	}

	if (empty($_SERVER['argv'][1]) === true) {
		echo 'Invalid URL parameter, please try again.' . "\n";
		exit;
	}

	if (empty($_SERVER['argv'][2]) === true) {
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

		$imageName = ucwords($imageDetails['id']) . ' ' . $imageDetails['version_id'];

		if (empty($packageSources[$imageDetails['id']][$imageDetails['version_id']]) === true) {
			echo 'Error installing on unsupported ' . $imageName . ' image, please try again.' . "\n";
			exit;
		}

		$packageSources = implode("\n", $packageSources[$imageDetails['id']][$imageDetails['version_id']]);

		if (file_put_contents('/etc/apt/sources.list', $packageSources) === false) {
			echo 'Error adding package sources, please try again.' . "\n";
			exit;
		}

		shell_exec('sudo kill -9 $(fuser -v /var/cache/debconf/config.dat)');
		shell_exec('sudo apt-get update');
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install apache2 bind9 bind9utils coreutils cron curl git iptables libapache2-mod-fcgid net-tools php-curl php-fpm php-mysqli procps syslinux systemd util-linux');
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install gnupg');
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y purge conntrack');
		shell_exec('sudo rm -rf /var/www/nodecompute/');
		mkdir('/var/www/nodecompute/');
		chmod('/var/www/nodecompute/', 0755);
		$uniqueId = '_' . uniqid();
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
				'command' => '-' . $uniqueId,
				'name' => 'git',
				'output' => 'usage: git ',
				'package' => 'git'
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
				'name' => 'timeout',
				'output' => 'invalid option',
				'package' => 'coreutils'
			)
		);
		$binaryFiles = array();

		foreach ($binaries as $binary) {
			$binaryFileListCommands = array(
				'#!/bin/bash',
				'whereis ' . $binary['name'] . ' | awk \'{ for (i=2; i<=NF; i++) print $i }\' | while read -r binaryFile; do echo $((sudo $binaryFile "' . $binary['command'] . '") 2>&1) | grep -c "' . $binary['output'] . '" && echo $binaryFile && break; done | tail -1'
			);
			$binaryFileListCommands = implode("\n", $binaryFileListCommands);

			if (file_put_contents('/var/www/nodecompute/system_action_deploy_system_binary_file_list_commands.sh', $binaryFileListCommands) === false) {
				echo 'Error adding binary file list commands, please try again.' . "\n";
				exit;
			}

			chmod('/var/www/nodecompute/system_action_deploy_system_binary_file_list_commands.sh', 0755);
			exec('cd /var/www/nodecompute/ && sudo ./system_action_deploy_system_binary_file_list_commands.sh', $binaryFile);
			$binaryFile = current($binaryFile);

			if (empty($binaryFile) === true) {
				shell_exec('sudo apt-get update');
				shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install ' . $binary['package']);
				echo 'Error listing ' . $binary['name'] . ' binary file from the ' . $binary['package'] . ' package, please try again.' . "\n";
				exit;
			}

			$binaryFiles[$binary['name']] = $binaryFile;
		}

		unlink('/var/www/nodecompute/system_action_deploy_system_binary_file_list_commands.sh');
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

		if (file_put_contents('/etc/sysctl.conf', $kernelSettings) === false) {
			echo 'Error adding kernel settings, please try again.' . "\n";
			exit;
		}

		shell_exec('sudo ' . $binaryFiles['sysctl'] . ' -p');
		exec('getconf PAGE_SIZE 2>&1', $kernelPageSize);
		$kernelPageSize = current($kernelPageSize);
		exec('free -b | grep "Mem:" | grep -v free | awk \'{print $2}\'', $memoryCapacityBytes);
		$memoryCapacityBytes = current($memoryCapacityBytes);
		$memoryCapacityPages = ceil($memoryCapacityBytes / $kernelPageSize);
		$defaultSocketBufferMemoryBytes = ceil($memoryCapacityBytes * 0.00034);
		$ipAddressVersionNumbers = array(
			'32' => '4',
			'128' => '6'
		);
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
		shell_exec('cd /var/www/nodecompute/ && sudo wget -O mysql_apt_config.deb --connect-timeout=5 --dns-timeout=5 --no-dns-cache --read-timeout=60 --tries=1 https://dev.mysql.com/get/mysql-apt-config_0.8.20-1_all.deb');

		if (file_exists('/var/www/nodecompute/mysql_apt_config.deb') === false) {
			echo 'Error downloading MySQL, please try again.' . "\n";
			exit;
		}

		shell_exec('cd /var/www/nodecompute/ && sudo DEBIAN_FRONTEND=noninteractive dpkg -i mysql_apt_config.deb');
		unlink('/var/www/nodecompute/mysql_apt_config.deb');
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

		if (file_put_contents('/etc/mysql/mysql.conf.d/mysqld.cnf', $mysqlSettings) === false) {
			echo 'Error adding MySQL settings, please try again.' . "\n";
			exit;
		}

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
			'DocumentRoot /var/www/nodecompute/',
			'<Directory /var/www/nodecompute/>',
			'Allow from all',
			'Options FollowSymLinks',
			'AllowOverride All',
			'</Directory>',
			'</VirtualHost>'
		);
		$apacheSettings = implode("\n", $apacheSettings);

		if (file_put_contents('/etc/apache2/sites-available/' . $_SERVER['argv'][1] . '.conf', $apacheSettings) === false) {
			echo 'Error adding Apache settings, please try again.' . "\n";
			exit;
		}

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

		if (file_put_contents('/etc/apache2/mods-available/mpm_event.conf', $apacheSettings) === false) {
			echo 'Error adding Apache settings, please try again.' . "\n";
			exit;
		}

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
		shell_exec('cd /var/www/nodecompute/ && sudo ' . $binaryFiles['git'] . ' clone https://github.com/twexxor/nodecompute .');

		if (file_exists('/var/www/nodecompute/readme.md') === false) {
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
		$crontabCommandIndex = array_search('# nodecompute_system_processes', $crontabCommands);

		if (is_int($crontabCommandIndex) === true) {
			while (is_int($crontabCommandIndex) === true) {
				unset($crontabCommands[$crontabCommandIndex]);
				$crontabCommandIndex++;

				if (strpos($crontabCommands[$crontabCommandIndex], ' nodecompute_system_processes') === false) {
					$crontabCommandIndex = false;
				}
			}
		}

		$crontabCommands += array(
			'# nodecompute_system_processes',
			'@reboot root sudo ' . $binaryFiles['crontab'] . ' /etc/crontab nodecompute_system_processes',
			// '* * * * * root sudo ' . $binaryFiles['php'] . ' /var/www/nodecompute/system_action_process_system_action.php process_node_request_logs nodecompute_system_processes',
			'*/10 * * * * root sudo ' . $binaryFiles['php'] . ' /var/www/nodecompute/system_action_process_node_process_cryptocurrency_blockchain_worker_settings.php nodecompute_system_processes'
		);
		$crontabCommands = implode("\n", $crontabCommands);

		if (file_put_contents('/etc/crontab', $crontabCommands) === false) {
			echo 'Error adding crontab commands, please try again.' . "\n";
			exit;
		}

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
					'ip_address' => '0.0.0.0',
					'ip_address_block_length' => '8'
				),
				array(
					'ip_address' => '10.0.0.0',
					'ip_address_block_length' => '8'
				),
				array(
					'ip_address' => '100.64.0.0',
					'ip_address_block_length' => '10'
				),
				array(
					'ip_address' => '127.0.0.0',
					'ip_address_block_length' => '8'
				),
				array(
					'ip_address' => '169.254.0.0',
					'ip_address_block_length' => '16'
				),
				array(
					'ip_address' => '172.16.0.0',
					'ip_address_block_length' => '12'
				),
				array(
					'ip_address' => '192.0.0.0',
					'ip_address_block_length' => '24'
				),
				array(
					'ip_address' => '192.0.2.0',
					'ip_address_block_length' => '24'
				),
				array(
					'ip_address' => '192.88.99.0',
					'ip_address_block_length' => '24'
				),
				array(
					'ip_address' => '192.168.0.0',
					'ip_address_block_length' => '16'
				),
				array(
					'ip_address' => '198.18.0.0',
					'ip_address_block_length' => '15'
				),
				array(
					'ip_address' => '198.51.100.0',
					'ip_address_block_length' => '24'
				),
				array(
					'ip_address' => '203.0.113.0',
					'ip_address_block_length' => '24'
				),
				array(
					'ip_address' => '224.0.0.0',
					'ip_address_block_length' => '4'
				),
				array(
					'ip_address' => '233.252.0.0',
					'ip_address_block_length' => '24'
				),
				array(
					'ip_address' => '240.0.0.0',
					'ip_address_block_length' => '4'
				),
				array(
					'ip_address' => '255.255.255.255',
					'ip_address_block_length' => '32'
				)
			),
			'6' => array(
				array(
					'ip_address' => '0000:0000:0000:0000:0000:0000:0000:0000',
					'ip_address_block_length' => '128'
				),
				array(
					'ip_address' => '0000:0000:0000:0000:0000:0000:0000:0001',
					'ip_address_block_length' => '128'
				),
				array(
					'ip_address' => '0000:0000:0000:0000:0000:ffff:0000:0000',
					'ip_address_block_length' => '96'
				),
				array(
					'ip_address' => '0000:0000:0000:0000:ffff:0000:0000:0000',
					'ip_address_block_length' => '96'
				),
				array(
					'ip_address' => '0064:ff9b:0000:0000:0000:0000:0000:0000',
					'ip_address_block_length' => '96'
				),
				array(
					'ip_address' => '0064:ff9b:0001:0000:0000:0000:0000:0000',
					'ip_address_block_length' => '48'
				),
				array(
					'ip_address' => '0100:0000:0000:0000:0000:0000:0000:0000',
					'ip_address_block_length' => '64'
				),
				array(
					'ip_address' => '2001:0000:0000:0000:0000:0000:0000:0000',
					'ip_address_block_length' => '32'
				),
				array(
					'ip_address' => '2001:0020:0000:0000:0000:0000:0000:0000',
					'ip_address_block_length' => '28'
				),
				array(
					'ip_address' => '2001:0db8:0000:0000:0000:0000:0000:0000',
					'ip_address_block_length' => '32'
				),
				array(
					'ip_address' => '2002:0000:0000:0000:0000:0000:0000:0000',
					'ip_address_block_length' => '16'
				),
				array(
					'ip_address' => 'fc00:0000:0000:0000:0000:0000:0000:0000',
					'ip_address_block_length' => '7'
				),
				array(
					'ip_address' => 'fe80:0000:0000:0000:0000:0000:0000:0000',
					'ip_address_block_length' => '10'
				),
				array(
					'ip_address' => 'ff00:0000:0000:0000:0000:0000:0000:0000',
					'ip_address_block_length' => '8'
				)
			)
		);
		$gcloudBinaryFileListCommands = array(
			'#!/bin/bash',
			'whereis gcloud | awk \'{ for (i=2; i<=NF; i++) print $i }\' | while read -r gcloudBinaryFile; do echo $((sudo $gcloudBinaryFile "-_-") 2>&1) | grep -c "unrecognized" && echo $gcloudBinaryFile && break; done | tail -1'
		);
		$gcloudBinaryFileListCommands = implode("\n", $gcloudBinaryFileListCommands);

		if (file_put_contents('/var/www/nodecompute/system_action_deploy_system_gcloud_binary_file_list_commands.sh', $gcloudBinaryFileListCommands) === false) {
			echo 'Error adding gcloud binary file list commands, please try again.' . "\n";
			exit;
		}

		chmod('/var/www/nodecompute/system_action_deploy_system_gcloud_binary_file_list_commands.sh', 0755);
		exec('cd /var/www/nodecompute/ && sudo ./system_action_deploy_system_gcloud_binary_file_list_commands.sh', $gcloudBinaryFile);
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
				$firewallRules[] = '-A PREROUTING ! -i lo -s ' . $nodeReservedInternalSource['ip_address'] . '/' . $nodeReservedInternalSource['ip_address_block_length'] . ' -j DROP';
			}

			$firewallRules[] = 'COMMIT';
			unlink('/var/www/nodecompute/firewall_ip_address_version_' . $ipAddressVersionNumber . '.txt');
			touch('/var/www/nodecompute/firewall_ip_address_version_' . $ipAddressVersionNumber . '.txt');
			$firewallRuleParts = array_chunk($firewallRules, 1000);

			foreach ($firewallRuleParts as $firewallRulePart) {
				$firewallRulePart = implode("\n", $firewallRulePart);
				shell_exec('sudo echo "' . $firewallRulePart . '" >> /var/www/nodecompute/firewall_ip_address_version_' . $ipAddressVersionNumber . '.txt');
			}

			shell_exec('sudo ' . $firewallBinaryFiles[$ipAddressVersionNumber] . ' < /var/www/nodecompute/firewall_ip_address_version_' . $ipAddressVersionNumber . '.txt');
			unlink('/var/www/nodecompute/firewall_ip_address_version_' . $ipAddressVersionNumber . '.txt');
			sleep(1);
		}
	} else {
		$systemDatabaseConnection = mysqli_connect('localhost', 'root', 'password');

		if ($systemDatabaseConnection === false) {
			echo 'Error connecting to system database, please try again.' . "\n";
			exit;
		}

		if (mysqli_query($systemDatabaseConnection, 'CREATE DATABASE IF NOT EXISTS `nodecompute` CHARSET UTF8') === false) {
			echo 'Error creating system database, please try again.' . "\n";
			exit;
		}

		$systemDatabaseConnection = mysqli_connect('localhost', 'root', 'password', 'nodecompute');

		if ($systemDatabaseConnection === false) {
			echo 'Error connecting to system database, please try again.' . "\n";
			exit;
		}

		$systemDatabases = array(
			'node_process_cryptocurrency_blockchain_block_processing_logs' => array(
				'block',
				'block_hash',
				'block_height',
				'block_reward_transaction',
				'created_timestamp',
				'id',
				'modified_timestamp',
				'node_id',
				'node_node_id',
				'node_process_type',
				'processed_status',
				'response'
			),
			'node_process_cryptocurrency_blockchain_socks_proxy_destinations' => array(
				'created_timestamp',
				'id',
				'ip_address',
				'ip_address_version_number',
				'modified_timestamp',
				'node_id',
				'node_node_id',
				'node_process_type',
				'port_number'
			),
			'node_process_cryptocurrency_blockchain_wallet_payment_requests' => array(
				// new addresses from the wallet are automatically created here for each payment request with a user-set time limit
				// partial payments are stored in node_process_cryptocurrency_wallet_transaction_logs
			),
			'node_process_cryptocurrency_blockchain_wallet_transaction_logs' => array(
				// logs all "sent" transactions + "received" transactions (in the case of multiple transactions received for 1 payment request) from wallets
			),
			'node_process_cryptocurrency_blockchain_wallets' => array(
				'created_timestamp',
				'id',
				'modified_timestamp',
				'node_id',
				'node_node_id',
				'node_process_type',
				'public_key',
				'public_key_hash',
				'public_key_script'
			),
			'node_process_cryptocurrency_blockchain_worker_block_headers' => array(
				'created_timestamp',
				'current_block_hash',
				'id',
				'modified_timestamp',
				'next_block_height',
				'next_block_maximum_timestamp',
				'next_block_merkle_root_hash',
				'next_block_minimum_timestamp',
				'next_block_reward_transaction',
				'next_block_target_hash',
				'next_block_target_hash_bits',
				'next_block_version',
				'node_id',
				'node_node_id',
				'node_process_type',
				'public_key_script'
			),
			'node_process_cryptocurrency_blockchain_worker_hash_speed_logs' => array(
				'created_timestamp',
				'estimated_per_second_count',
				'id',
				'modified_timestamp',
				'node_id',
				'node_node_id',
				'node_process_type'
			),
			'node_process_cryptocurrency_blockchain_worker_settings' => array(
				'count',
				'cpu_usage_maximum_percentage',
				'created_timestamp',
				'id',
				'memory_usage_maximum_percentage',
				'modified_timestamp',
				'node_id',
				'node_node_id',
				'node_process_type'
			),
			'node_process_cryptocurrency_blockchains' => array(
				'block_download_progress_percentage',
				'created_timestamp',
				'daily_sent_traffic_maximum_megabytes',
				'id',
				'modified_timestamp',
				'node_id',
				'node_node_id',
				'node_process_type',
				'simultaneous_received_connection_maximum_count',
				'simultaneous_sent_connection_maximum_count',
				'storage_usage_maximum_megabytes'
			),
			'node_process_forwarding_destinations' => array(
				'created_timestamp',
				'hostname_version_4',
				'hostname_version_6',
				'id',
				'modified_timestamp',
				'node_id',
				'node_node_id',
				'node_process_type',
				'port_number_version_4',
				'port_number_version_6'
			),
			'node_process_node_user_authentication_credentials' => array(
				'created_timestamp',
				'id',
				'modified_timestamp',
				'node_id',
				'node_node_id',
				'node_process_type',
				'node_user_authentication_credential_id',
				'node_user_authentication_credential_password',
				'node_user_authentication_credential_username',
				'node_user_id'
			),
			'node_process_node_user_authentication_sources' => array(
				'created_timestamp',
				'id',
				'modified_timestamp',
				'node_id',
				'node_node_id',
				'node_process_type',
				'node_user_authentication_source_ip_address',
				'node_user_authentication_source_ip_address_block_length',
				'node_user_authentication_source_ip_address_version_number',
				'node_user_id'
			),
			'node_process_node_user_request_destination_logs' => array(
				'created_timestamp',
				'id',
				'modified_timestamp',
				'node_id',
				'node_node_id',
				'node_process_type',
				'node_request_destination_id',
				'node_user_id',
				'request_count'
			),
			'node_process_node_user_node_request_destinations' => array(
				'created_timestamp',
				'id',
				'modified_timestamp',
				'node_id',
				'node_node_id',
				'node_process_type',
				'node_request_destination_hostname',
				'node_request_destination_id',
				'node_user_id'
			),
			'node_process_node_user_node_request_limit_rules' => array(
				'created_timestamp',
				'expired_timestamp',
				'id',
				'modified_timestamp',
				'node_id',
				'node_node_id',
				'node_process_type',
				'node_request_destination_id',
				'node_request_limit_rule_id',
				'node_user_id'
			),
			'node_process_node_user_request_logs' => array(
				'bytes_received',
				'bytes_sent',
				'created_timestamp',
				'destination_hostname',
				'destination_ip_address',
				'id',
				'modified_timestamp',
				'node_id',
				'node_node_id',
				'node_process_type',
				'node_request_destination_id',
				'node_user_id',
				'processed_status',
				'processing_process_id',
				'response_code',
				'source_ip_address'
			),
			'node_process_node_user_resource_usage_logs' => array(
				'bytes_received',
				'bytes_sent',
				'created_timestamp',
				'id',
				'modified_timestamp',
				'node_id',
				'node_node_id',
				'node_process_type',
				'node_user_id',
				'request_count'
			),
			'node_process_node_users' => array(
				'created_timestamp',
				'id',
				'modified_timestamp',
				'node_id',
				'node_node_id',
				'node_process_type',
				'node_user_authentication_strict_only_allowed_status',
				'node_user_id',
				'node_user_node_request_destinations_only_allowed_status',
				'node_user_node_request_logs_allowed_status'
			),
			'node_process_recursive_dns_destinations' => array(
				'created_timestamp',
				'destination_ip_address_version_4',
				'destination_ip_address_version_4_node_id',
				'destination_ip_address_version_6',
				'destination_ip_address_version_6_node_id',
				'id',
				'modified_timestamp',
				'node_id',
				'node_node_id',
				'node_process_type',
				'port_number_version_4',
				'port_number_version_6',
				'source_ip_address_version_4',
				'source_ip_address_version_6'
			),
			'node_process_resource_usage_logs' => array(
				'bytes_received',
				'bytes_sent',
				'cpu_percentage',
				'created_timestamp',
				'id',
				'memory_percentage',
				'modified_timestamp',
				'node_id',
				'node_process_type',
				'request_count'
			),
			'node_processes' => array(
				'created_timestamp',
				'id',
				'modified_timestamp',
				'node_id',
				'node_node_id',
				'port_number',
				'type'
			),
			'node_request_destinations' => array(
				'created_timestamp',
				'hostname',
				'id',
				'modified_timestamp'
			),
			'node_request_limit_rules' => array(
				'created_timestamp',
				'id',
				'interval_minutes',
				'modified_timestamp',
				'request_count',
				'request_count_interval_minutes'
			),
			'node_reserved_internal_destinations' => array(
				'added_status',
				'created_timestamp',
				'id',
				'ip_address',
				'ip_address_version_number',
				'modified_timestamp',
				'node_id',
				'node_node_id',
				'processed_status'
			),
			'node_reserved_internal_sources' => array(
				'created_timestamp',
				'id',
				'ip_address',
				'ip_address_block_length',
				'ip_address_version_number',
				'modified_timestamp',
				'node_id'
			),
			'node_resource_usage_logs' => array(
				'bytes_received',
				'bytes_sent',
				'cpu_capacity_megahertz',
				'cpu_core_count',
				'cpu_percentage',
				'created_timestamp',
				'id',
				'memory_capacity_megabytes',
				'memory_percentage',
				'modified_timestamp',
				'node_id',
				'request_count',
				'storage_capacity_megabytes',
				'storage_percentage'
			),
			'node_user_authentication_credentials' => array(
				'created_timestamp',
				'id',
				'modified_timestamp',
				'node_user_id',
				'password',
				'username'
			),
			'node_user_authentication_sources' => array(
				'created_timestamp',
				'id',
				'ip_address',
				'ip_address_block_length',
				'ip_address_version_number',
				'modified_timestamp',
				'node_user_id'
			),
			'node_user_node_request_destinations' => array(
				'created_timestamp',
				'id',
				'modified_timestamp',
				'node_request_destination_address',
				'node_request_destination_id',
				'node_user_id'
			),
			'node_user_node_request_limit_rules' => array(
				'created_timestamp',
				'expired_timestamp',
				'id',
				'modified_timestamp',
				'node_request_destination_id',
				'node_request_limit_rule_id',
				'node_user_id'
			),
			'node_users' => array(
				'authentication_strict_only_allowed_status',
				'created_timestamp',
				'id',
				'modified_timestamp',
				'node_request_destinations_only_allowed_status',
				'node_request_logs_allowed_status',
				'tag'
			),
			'nodes' => array(
				'activated_status',
				'authentication_token',
				'cpu_capacity_megahertz',
				'cpu_core_count',
				'created_timestamp',
				'deployed_status',
				'external_ip_address_version_4',
				'external_ip_address_version_4_type',
				'external_ip_address_version_6',
				'external_ip_address_version_6_type',
				'id',
				'internal_ip_address_version_4',
				'internal_ip_address_version_4_type',
				'internal_ip_address_version_6',
				'internal_ip_address_version_6_type',
				'memory_capacity_megabytes',
				'modified_timestamp',
				'node_id',
				'processed_status',
				'processing_progress_checkpoint',
				'processing_progress_override_status',
				'processing_progress_percentage',
				'processing_status',
				'storage_capacity_megabytes'
			),
			'system_database_columns' => array(
				'created_timestamp',
				'id',
				'modified_timestamp',
				'name',
				'system_database_id'
			),
			'system_databases' => array(
				'authentication_credential_hostname',
				'authentication_credential_password',
				'created_timestamp',
				'id',
				'modified_timestamp',
				'table_name',
				'tag'
			),
			'system_request_logs' => array(
				'bytes_received',
				'bytes_sent',
				'created_timestamp',
				'id',
				'modified_timestamp',
				'node_id',
				'response_authenticated_status',
				'response_data',
				'response_message',
				'response_valid_status',
				'source_ip_address',
				'system_action',
				'system_user_authentication_token_id',
				'system_user_id',
				'value'
			),
			'system_resource_usage_logs' => array(
				'bytes_received',
				'bytes_sent',
				'cpu_capacity_megahertz',
				'cpu_core_count',
				'cpu_percentage',
				'created_timestamp',
				'destination_ip_address',
				'id',
				'memory_capacity_megabytes',
				'memory_percentage',
				'modified_timestamp',
				'storage_capacity_megabytes',
				'storage_percentage'
			),
			'system_settings' => array(
				'created_timestamp',
				'id',
				'modified_timestamp',
				'name',
				'value'
			),
			'system_user_authentication_token_scopes' => array(
				'created_timestamp',
				'id',
				'modified_timestamp',
				'system_action',
				'system_user_authentication_token_id',
				'system_user_id'
			),
			'system_user_authentication_token_sources' => array(
				'created_timestamp',
				'id',
				'ip_address_range_start',
				'ip_address_range_stop',
				'ip_address_range_version_number',
				'modified_timestamp',
				'system_user_authentication_token_id',
				'system_user_id'
			),
			'system_user_authentication_tokens' => array(
				'created_timestamp',
				'id',
				'modified_timestamp',
				'system_user_id',
				'value'
			),
			'system_user_system_users' => array(
				'created_timestamp',
				'id',
				'modified_timestamp',
				'system_user_id',
				'system_user_system_user_id'
			),
			'system_users' => array(
				'created_timestamp',
				'id',
				'modified_timestamp',
				'system_user_id'
			)
		);
		$systemDatabaseCommands = array();

		foreach ($systemDatabases as $systemDatabaseTableName => $systemDatabaseColumnNames) {
			$systemDatabaseCommands[] = 'CREATE TABLE IF NOT EXISTS `' . $systemDatabaseTableName . '` (`created_timestamp` VARCHAR(10) NULL DEFAULT NULL);';

			foreach ($systemDatabaseColumnNames as $systemDatabaseColumnName) {
				if (($systemDatabaseColumnName === 'created_timestamp') === true) {
					continue;
				}

				$systemDatabaseColumnType = 'text';

				if (
					(($systemDatabaseColumnName === 'id') === true) ||
					((substr($systemDatabaseColumnName, -3) === '_id') === true)
				) {
					$systemDatabaseColumnType = 'VARCHAR(30)';
				}

				if ((substr($systemDatabaseColumnName, -11) === '_percentage') === true) {
					$systemDatabaseColumnType = 'VARCHAR(3)';
				}

				if ((substr($systemDatabaseColumnName, -7) === '_status') === true) {
					$systemDatabaseColumnType = 'VARCHAR(1)';
				}

				if ((substr($systemDatabaseColumnName, -10) === '_timestamp') === true) {
					$systemDatabaseColumnType = 'VARCHAR(10)';
				}

				$systemDatabaseCommandActions = array(
					'add' => 'ADD `' . $systemDatabaseColumnName . '`',
					'change' => 'CHANGE `' . $systemDatabaseColumnName . '` `' . $systemDatabaseColumnName . '`'
				);
				$systemDatabaseCommand = 'ALTER TABLE `' . $systemDatabaseTableName . '` ' . $systemDatabaseCommandActions['change'] . ' ' . $systemDatabaseColumnType . ' NULL DEFAULT NULL';

				if (mysqli_query($systemDatabaseConnection, $systemDatabaseCommand) === false) {
					$systemDatabaseCommands[] = str_replace($systemDatabaseCommandActions['change'], $systemDatabaseCommandActions['add'], $systemDatabaseCommand);
				}

				if (($systemDatabaseColumnName === 'id') === true) {
					$systemDatabaseCommands[$systemDatabaseColumnName . '__' . $systemDatabaseTableName] = 'ALTER TABLE `' . $systemDatabaseTableName . '` ADD PRIMARY KEY (`' . $systemDatabaseColumnName . '`)';
				}
			}
		}

		foreach ($systemDatabaseCommands as $systemDatabaseCommandKey => $systemDatabaseCommand) {
			if (
				(is_numeric($systemDatabaseCommandKey) === false) &&
				(is_int(strpos($systemDatabaseCommand, 'ADD PRIMARY KEY')) === true)
			) {
				$systemDatabaseCommandKey = explode('__', $systemDatabaseCommandKey);
				$systemDatabaseKeys = mysqli_query($systemDatabaseConnection, 'SHOW KEYS FROM `' . $systemDatabaseCommandKey[1] . '` WHERE Column_name=\'' . $systemDatabaseCommandKey[0] . '\'')->num_rows;

				if (empty($systemDatabaseKeys) === false) {
					continue;
				}
			}

			mysqli_query($systemDatabaseConnection, $systemDatabaseCommand);
		}

		foreach ($systemDatabases as $systemDatabaseTableName => $systemDatabaseColumnNames) {
			$systemDatabaseCommandResponse = mysqli_query($systemDatabaseConnection, 'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = \'' . $systemDatabaseTableName . '\'');

			if (empty($systemDatabaseCommandResponse->num_rows) === true) {
				echo 'Error executing system database commands, please try again.' . "\n";
				exit;
			}

			end($systemDatabaseColumnNames);
			$systemDatabaseColumnNameCount = (key($systemDatabaseColumnNames) + 1);

			foreach ($systemDatabaseCommandResponse as $systemDatabaseCommandResponse) {
				$systemDatabaseCommandResponse = current($systemDatabaseCommandResponse);
				$systemDatabaseCommandResponse = intval($systemDatabaseCommandResponse);

				if (($systemDatabaseColumnNameCount === $systemDatabaseCommandResponse) === false) {
					echo 'Error executing system database commands, please try again.' . "\n";
					exit;
				}
			}
                }

		require_once('/var/www/nodecompute/system_action_validate_ip_address_type.php');
		require_once('/var/www/nodecompute/system_action_validate_ip_address_version_number.php');

		foreach ($ipAddressVersionNumbers as $ipAddressVersionNumber) {
			$systemEndpointDestinationIpAddress = _validateIpAddressVersionNumber($_SERVER['argv'][1], $ipAddressVersionNumber);

			if (($systemEndpointDestinationIpAddress === false) === false) {
				break;
			}
		}

		if ($systemEndpointDestinationIpAddress === false) {
			echo 'Invalid system endpoint destination IP address, please try again.' . "\n";
			exit;
		}

		$timestamp = time();
		$systemEndpointDestinationIpAddressType = _validateIpAddressType($systemEndpointDestinationIpAddress, $ipAddressVersionNumber);
		$systemUserAuthenticationToken = _createUniqueId();
		$systemUserAuthenticationTokenId = _createUniqueId();
		$systemUserId = _createUniqueId();
		$systemDatabaseData = array(
			'system_settings' => array(
				array(
					'created_timestamp' => $timestamp,
					'id' => _createUniqueId(),
					'modified_timestamp' => $timestamp,
					'name' => 'endpoint_destination_ip_address',
					'value' => $systemEndpointDestinationIpAddress
				),
				array(
					'created_timestamp' => $timestamp,
					'id' => _createUniqueId(),
					'modified_timestamp' => $timestamp,
					'name' => 'endpoint_destination_ip_address_type',
					'value' => $systemEndpointDestinationIpAddressType
				),
				array(
					'created_timestamp' => $timestamp,
					'id' => _createUniqueId(),
					'modified_timestamp' => $timestamp,
					'name' => 'endpoint_destination_ip_address_version_number',
					'value' => $ipAddressVersionNumber
				),
				array(
					'created_timestamp' => $timestamp,
					'id' => _createUniqueId(),
					'modified_timestamp' => $timestamp,
					'name' => 'version_number',
					'value' => '1'
				)
			),
			'system_user_authentication_tokens' => array(
				array(
					'created_timestamp' => $timestamp,
					'id' => $systemUserAuthenticationTokenId,
					'modified_timestamp' => $timestamp,
					'system_user_id' => $systemUserId,
					'value' => $systemUserAuthenticationToken
				)
			),
			'system_user_system_users' => array(
				array(
					'created_timestamp' => $timestamp,
					'id' => _createUniqueId(),
					'modified_timestamp' => $timestamp,
					'system_user_id' => $systemUserId,
					'system_user_system_user_id' => $systemUserId
				)
			),
			'system_users' => array(
				array(
					'created_timestamp' => $timestamp,
					'id' => $systemUserId,
					'modified_timestamp' => $timestamp,
					'system_user_id' => $systemUserId
				)
			)
		);

		foreach ($systemDatabases as $systemDatabaseTableName => $systemDatabaseColumnNames) {
			$systemDatabaseId = _createUniqueId();
			$systemDatabaseData['system_databases'][] = array(
				'authentication_credential_hostname' => 'localhost',
				'authentication_credential_password' => 'password',
				'created_timestamp' => $timestamp,
				'id' => $systemDatabaseId,
				'modified_timestamp' => $timestamp,
				'table_name' => $systemDatabaseTableName
			);

			foreach ($systemDatabaseColumnNames as $systemDatabaseColumnName) {
				$systemDatabaseData['system_database_columns'][] = array(
					'created_timestamp' => $timestamp,
					'id' => _createUniqueId(),
					'modified_timestamp' => $timestamp,
					'name' => $systemDatabaseColumnName,
					'system_database_id' => $systemDatabaseId
				);
			}
		}

		$systemFiles = scandir('/var/www/nodecompute/');

		foreach ($systemFiles as $systemFile) {
			if ((substr($systemFile, 0, 13) === 'system_action') === true) {
				$systemAction = substr($systemFile, 14);
				$systemAction = substr($systemAction, 0, -4);
				$systemDatabaseData['system_user_authentication_token_scopes'][] = array(
					'created_timestamp' => $timestamp,
					'id' => _createUniqueId(),
					'modified_timestamp' => $timestamp,
					'system_action' => $systemAction,
					'system_user_authentication_token_id' => $systemUserAuthenticationTokenId,
					'system_user_id' => $systemUserId
				);
			}
		}

		foreach ($systemDatabaseData as $systemDatabaseTableName => $systemDatabaseRows) {
			foreach ($systemDatabaseRows as $systemDatabaseRow) {
				$systemDatabaseRowColumnNames = array_keys($systemDatabaseRow);
				$systemDatabaseRowColumnNames = implode('`, `', $systemDatabaseRowColumnNames);
				$systemDatabaseRowColumnValues = array_values($systemDatabaseRow);
				$systemDatabaseRowColumnValues = implode('\', \'', $systemDatabaseRowColumnValues);

				if (mysqli_query($systemDatabaseConnection, 'INSERT IGNORE INTO `' . $systemDatabaseTableName . '` (`' . $systemDatabaseRowColumnNames . '`) VALUES (\'' . $systemDatabaseRowColumnValues . '\')') === false) {
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
