<?php
	if (
		(empty($_SERVER['argv'][1]) === true) ||
		(empty($_SERVER['argv'][2]) === true)
	) {
		echo 'Error deploying node, please try again.' . "\n";
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
		chmod($commandsFile, 0755);
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
	applyCommands($commands);
	$binaries = array(
		array(
			'command' => ($uniqueId = '_' . uniqid() . time()),
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
			'command' => $uniqueId,
			'name' => 'telinit',
			'output' => 'single',
			'package' => 'systemd'
		)
	);
	$binaryFiles = array();

	foreach ($binaries as $binary) {
		$binaryFiles[$binary['name']] = fetchBinaryFile($binary);
	}

	$commands = array(
		'sudo ' . $binaryFiles['sysctl'] . ' -w vm.overcommit_memory=0',
		'sudo wget -O ' . ($nodeActivateResponseFile = '/tmp/node_activate_response.json') . ' ' . ($wgetParameters = '--no-dns-cache --retry-connrefused --timeout=60 --tries=2') . ' --post-data "json={\"action\":\"activate\",\"where\":{\"id\":\"' . ($id = $_SERVER['argv'][1]) . '\"}}" ' . ($url = $_SERVER['argv'][2]) . '/endpoint/nodes'
	);
	applyCommands($commands);

	if (file_exists($nodeActivateResponseFile) === false) {
		echo 'Error activating node, please try again.' . "\n";
		exit;
	}

	$nodeActivateResponse = json_decode(file_get_contents($nodeActivateResponseFile), true);
	unlink($nodeActivateResponseFile);

	if (empty($nodeActivateResponse['message']) === true) {
		echo 'Error activating node, please try again.' . "\n";
		exit;
	}

	echo $nodeActivateResponse['message'] . "\n";

	if ($nodeActivateResponse['status_valid'] === false) {
		exit;
	}

	shell_exec('sudo wget -O ' . ($nodeProcessResponseFile = '/tmp/node_process_response.json') . ' ' . $wgetParameters . ' --post-data "json={\"action\":\"process\",\"where\":{\"id\":\"' . $id . '\"}}" ' . $url . '/endpoint/nodes');

	if (file_exists($nodeProcessResponseFile) === false) {
		echo 'Error processing node, please try again.' . "\n";
		exit;
	}

	$nodeProcessResponse = json_decode(file_get_contents($nodeProcessResponseFile), true);
	unlink($nodeProcessResponseFile);

	if (empty($nodeProcessResponse['message']) === true) {
		echo 'Error processing node, please try again.' . "\n";
		exit;
	}

	echo $nodeProcessResponse['message'] . "\n";

	if ($nodeProcessResponse['status_valid'] === false) {
		exit;
	}

	function killProcessIds($processIds, $telinitBinaryFile) {
		$commands = array(
			'#!/bin/bash'
		);
		$processIdParts = array_chunk($processIds, 10);

		foreach ($processIdParts as $processIds) {
			$commands[] = 'sudo kill -9 ' . implode(' ', $processIds);
		}

		$commands = array_merge($commands, array(
			'sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk \'{print $1}\')',
			'sudo ' . $telinitBinaryFile . ' u'
		));
		$commandsFile = '/tmp/commands.sh';

		if (file_exists($commandsFile)) {
			unlink($commandsFile);
		}

		file_put_contents($commandsFile, implode("\n", $commands));
		shell_exec('sudo chmod +x ' . $commandsFile);
		shell_exec('cd /tmp/ && sudo ./' . basename($commandsFile));
		unlink($commandsFile);
		return;
	}

	exec('fuser -v /var/cache/debconf/config.dat', $lockedProcessIds);
	killProcessIds($lockedProcessIds, $binaryFiles['telinit']);

	$commands = array(
		'sudo DEBIAN_FRONTEND=noninteractive apt-get -y install apache2 apache2-utils bind9 bind9utils build-essential cron curl dnsutils net-tools php-curl syslinux systemd util-linux',
		'sudo /etc/init.d/apache2 stop',
	);
	applyCommands($commands);
	$rootPath = '/usr/local/ghostcompute/';

	if (is_dir($rootPath) === true) {
		rmdir($rootPath);
	}

	mkdir($rootPath);
	chmod($rootPath, 0755);
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
			'command' => '-v',
			'name' => 'php',
			'output' => 'PHP ',
			'package' => 'php'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'netstat',
			'output' => 'invalid option',
			'package' => 'net-tools'
		),
		array(
			'command' => '-' . $uniqueId,
			'name' => 'systemctl',
			'output' => 'invalid option',
			'package' => 'systemd'
		)
	);

	foreach ($binaries as $binary) {
		$binaryFiles[$binary['name']] = fetchBinaryFile($binary);
	}

	exec('sudo ' . $binaryFiles['netstat'] . ' -i | grep -v face | awk \'NR==1{print $1}\' 2>&1', $interfaceName);
	$interfaceName = current($interfaceName);

	if (empty($interfaceName) === true) {
		echo 'Error detecting network interface, please try again.' . "\n";
		exit;
	}

	$commands = array();
	// ..

	$nameserverListeningIps = array_keys($serverResponse['data']['nameserver_process_external_ips']);
	$serverNodes = $serverResponse['data']['nodes'];
	$interfaceIps = array_unique(array_merge($nameserverListeningIps, $serverNodes));

	foreach ($interfaceIps as $interfaceIp) {
		$commands[] = 'sudo ' . $binaryFiles['ifconfig'] . ' ' . $interfaceName . ':' . ip2long($interfaceIp) . ' ' . $interfaceIp . ' netmask 255.255.255.0';
	}

	applyCommands($commands);
	$interfacesFile = $rootPath . 'interfaces.php';
	$interfacesFileContents = '<?php shell_exec(\'' . implode('\'); shell_exec(\'', $commands) . '\'); ?>';
	file_put_contents($interfacesFile, $interfacesFileContents);
	$nameserverIps = array();

	if (!empty($serverResponse['data']['nameserver_process_external_ips'])) {
		exec('pgrep named', $nameserverProcessIds);
		killProcessIds($nameserverProcessIds, $binaryFiles['telinit']);
		shell_exec('sudo mkdir -m 0775 /var/run/named');
		$nameserverServiceName = is_dir('/etc/default/bind9') ? 'bind9' : 'named';

		foreach ($serverResponse['data']['nameserver_process_external_ips'] as $nameserverListeningIp => $nameserverSourceIps) {
			foreach (array_values($nameserverSourceIps) as $nameserverSourceIp) {
				$nameserverProcessName = ip2long($nameserverSourceIp) . '_' . ip2long($nameserverListeningIp);
				$commands = array(
					'sudo ' . $binaryFiles['service'] . ' ' . $nameserverServiceName . ' stop',
					'cd /usr/sbin && sudo ln /usr/sbin/named named_' . $nameserverProcessName . ' && sudo cp /lib/systemd/system/' . $nameserverServiceName . '.service /lib/systemd/system/' . $nameserverServiceName . '_' . $nameserverProcessName . '.service',
					'sudo cp /etc/default/' . $nameserverServiceName . ' /etc/default/' . $nameserverServiceName . '_' . $nameserverProcessName,
					'sudo cp -r /etc/bind/ /etc/bind_' . $nameserverProcessName,
					'sudo mkdir -m 0775 /var/cache/bind_' . $nameserverProcessName
				);
				applyCommands($commands);
				$namedConfigurationContents = array(
					'include "/etc/bind_' . $nameserverProcessName . '/named.conf.options";',
					'include "/etc/bind_' . $nameserverProcessName . '/named.conf.local";',
					'include "/etc/bind_' . $nameserverProcessName . '/named.conf.default-zones";'
				);
				$namedConfigurationOptionContents = array(
					'acl internal {',
					'0.0.0.0/8;',
					'10.0.0.0/8;',
					'100.64.0.0/10;',
					'127.0.0.0/8;',
					'172.16.0.0/12;',
					'192.0.0.0/24;',
					'192.0.2.0/24;',
					'192.88.99.0/24;',
					'192.168.0.0/16;',
					'198.18.0.0/15;',
					'198.51.100.0/24;',
					'203.0.113.0/24;',
					'224.0.0.0/4;',
					'240.0.0.0/4;',
					'255.255.255.255/32;',
					'};',
					'options {',
					'allow-query {',
					'internal;',
					'};',
					'allow-recursion {',
					'internal;',
					'};',
					'auth-nxdomain yes;',
					'cleaning-interval 10;',
					'directory "/var/cache/bind_' . $nameserverProcessName . '";',
					'dnssec-enable yes;',
					'dnssec-must-be-secure mydomain.local no;',
					'dnssec-validation yes;',
					'empty-zones-enable no;',
					'filter-aaaa-on-v4 yes;',
					'lame-ttl 0;',
					'listen-on {',
					$nameserverListeningIp . '; ' . $nameserverSourceIp . ';',
					'};',
					'max-cache-ttl 1;',
					'max-ncache-ttl 1;',
					'max-zone-ttl 1;',
					'pid-file "/var/run/named/named_' . $nameserverProcessName . '.pid";',
					'query-source address ' . $nameserverSourceIp . ';',
					'resolver-query-timeout 10;',
					'tcp-clients 1000;',
					'};'
				);
				$systemdServiceContents = array(
					'[Unit]',
					'After=network.target',
					'[Service]',
					'ExecStart=/usr/sbin/named_' . $nameserverProcessName . ' -f ' . ($configurationFile = '-c /etc/bind_' . $nameserverProcessName . '/named.conf') . ' -4 -S 40000 -u root',
					'User=root',
					'[Install]',
					'WantedBy=multi-user.target'
				);
				file_put_contents('/etc/bind_' . $nameserverProcessName . '/named.conf', implode("\n", $namedConfigurationContents));
				file_put_contents('/etc/bind_' . $nameserverProcessName . '/named.conf.options', implode("\n", $namedConfigurationOptionContents));
				file_put_contents('/lib/systemd/system/' . $nameserverServiceName . '_' . $nameserverProcessName . '.service', implode("\n", $systemdServiceContents));
				$commands = array(
					'sudo ' . $binaryFiles['systemctl'] . ' daemon-reload',
					'sudo ' . $binaryFiles['service'] . ' ' . $nameserverServiceName . '_' . $nameserverProcessName . ' start',
					'sleep 10'
				);
				applyCommands($commands);
			}

			$nameserverIps[$nameserverListeningIp] = $nameserverListeningIp;
		}

		$commands = array(
			'sudo rm /etc/resolv.conf && sudo touch /etc/nameservers.conf',
			'sudo ln -s /etc/nameservers.conf /etc/resolv.conf'
		);
		applyCommands($commands);
		file_put_contents('/etc/nameservers.conf', 'nameserver ' . key($nameserverIps));
		echo 'Nameserver processes created successfully.' . "\n";
	}

	$commands = array(
		'sudo rm -rf /usr/src/3proxy/ && sudo mkdir -p /usr/src/3proxy/',
		'cd /usr/src/3proxy/ && sudo wget -O 3proxy.tar.gz ' . $wgetParameters . ' https://github.com/3proxy/3proxy/archive/refs/tags/0.9.3.tar.gz',
		'cd /usr/src/3proxy/ && sudo tar -xvzf 3proxy.tar.gz',
		'cd /usr/src/3proxy/*/ && sudo make -f Makefile.Linux && sudo make -f Makefile.Linux install',
		'sudo mkdir -p /var/log/3proxy'
	);
	applyCommands($commands);
	$proxyAuthentication = $proxyConnect = $proxyIps = array();
	$proxyConfiguration = array(
		'maxconn 20000',
		'nobandlimin',
		'nobandlimout',
		'nserver ' . key($serverResponse['data']['nameserver_process_external_ips']),
		'process_id' => false,
		'stacksize 0',
		'flush',
		'allow * * * * HTTP',
		'nolog',
		'allow * * * * HTTPS',
		'nolog'
	);

	if (empty($serverResponse['data']['proxies'])) {
		$proxyConnect[] = 'deny *';
		$proxyConnect[] = 'flush';
	}

	foreach ($serverResponse['data']['proxies'] as $proxy) {
		$proxyIps[$proxy['id']] = !empty($proxy['internal_ip']) ? $proxy['internal_ip'] : $proxy['external_ip'];

		if (
			!empty($proxy['username']) &&
			empty($proxyAuthentication[$proxy['username']])
		) {
			$proxyAuthentication[$proxy['username']] = 'users ' . $proxy['username'] . ':CL:' . $proxy['password'];
		}

		$proxyConnect[] = 'auth' . (!empty($proxy['whitelist_ips']) ? ' iponly ' : ' ') . 'strong';

		if (!empty($proxy['whitelist_ips'])) {
			$proxyConnect[] = 'allow * ' . implode(',', $proxy['whitelist_ips']) . ' *';
		}

		if (!empty($proxy['username'])) {
			$proxyConnect[] = 'allow ' . $proxy['username'] . ' *';
		}

		$proxyConnect[$proxyIps[$proxy['id']]] = false;
		$proxyConnect[] = 'deny *';
		$proxyConnect[] = 'flush';
	}

	$proxyConfiguration = array_merge($proxyConfiguration, $proxyAuthentication, $proxyConnect, array(
		'deny *'
	));

	foreach ($serverResponse['data']['proxy_process_ports'] as $proxyProcessPort) {
		$proxyProcessConfiguration = $proxyConfiguration;
		$proxyProcessConfiguration['process_id'] = 'pid /var/run/3proxy/' . ($proxyProcessName = 'socks_' . $proxyProcessPort) . '.pid';
		$proxyProcessConfigurationPath = '/etc/3proxy/' . $proxyProcessName . '.cfg';

		foreach ($proxyIps as $proxyIp) {
			$proxyProcessConfiguration[$proxyIp] = 'socks -a -e' . $proxyIp . ' -i' . $proxyIp . ' -n -p' . $proxyProcessPort . ' -46';
		}

		$commands = array(
			'cd /bin && sudo ln /bin/3proxy ' . $proxyProcessName,
			'sudo cp /etc/systemd/system/3proxy.service /etc/systemd/system/' . $proxyProcessName . '.service'
		);
		applyCommands($commands);
		$systemdServiceContents = array(
			'[Unit]',
			'After=network.target',
			'[Service]',
			'ExecStart=/bin/' . $proxyProcessName . ' ' . $proxyProcessConfigurationPath,
			'User=root',
			'[Install]',
			'WantedBy=multi-user.target'
		);
		file_put_contents('/etc/systemd/system/' . $proxyProcessName . '.service', implode("\n", $systemdServiceContents));
		file_put_contents($proxyProcessConfigurationPath, implode("\n", $proxyProcessConfiguration));
		$commands = array(
			'sudo chmod +x ' . $proxyProcessConfigurationPath,
			'sudo chown root:root ' . $proxyProcessConfigurationPath,
			'sudo chmod 0755 ' . $proxyProcessConfigurationPath
		);
		applyCommands($commands);
	}

	shell_exec('sudo ' . $binaryFiles['systemctl'] . ' daemon-reload');

	if (!file_exists('/bin/3proxy')) {
		echo 'Error: 3proxy binary file is missing at /bin/3proxy after compiling, please run the install script again.' . "\n";
		exit;
	}

	$connectScriptFile = '/ghostcompute/connect.php';
	$connectionScriptFile = '/ghostcompute/connection.php';
	$commands = array(
		'sudo wget -O ' . escapeshellarg($connectionScriptFile) . ' ' . $wgetParameters . ' "' . ($connectionScriptUrl = $url . '/assets/php/connection.php?' . time()) . '"'
	);
	applyCommands($commands);

	if (!file_exists($connectionScriptFile)) {
		echo 'Error: Unable to fetch connection script at ' . $connectionScriptUrl . '.' . "\n";
		exit;
	}

	$crontabFile = '/etc/crontab';
	$crontabCommands = array(
		'# [Start]',
		'* * * * * root sudo ' . $binaryFiles['php'] . ' ' . $connectScriptFile,
		'@reboot root sudo ' . $binaryFiles['php'] . ' ' . $interfacesFile,
		'@reboot root sudo ' . $binaryFiles['crontab'] . ' ' . $crontabFile,
		'# [Stop]'
	);

	if (
		!file_exists($crontabFile) ||
		($crontabFileContents = file_get_contents($crontabFile)) === false
	) {
		echo 'Error: Unable to fetch crontab contents at ' . $crontabFile . '.' . "\n";
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
	$versionFile = $rootPath . 'version.txt';
	$connectScriptFileContents = array(
		'<?php',
		'require_once(\'connection.php\');',
		'$parameters = array(',
		'\'id\' => \'' . $id . '\',',
		'\'url\' => \'' . $url . '\'',
		');',
		'$connection = new Connection($parameters);',
		'$processIds = $connection->fetchProcessIds(\'php\', \'connect.php\');',
		'if (',
		'!empty($processIds) &&',
		'count($processIds) > 1',
		') {',
		'exit;',
		'}',
		'$connection->fetchServerData();',
		'$currentVersion = (integer) $connection->decodedServerData[\'settings\'][\'version\'];',
		'$requireUpdate = false;',
		'$versionFile = $connection->rootPath . \'version.txt\';',
		'$version = (integer) (file_exists($versionFile) ? file_get_contents($versionFile) : 1);',
		'if ($currentVersion > $version) {',
		'$connectionScriptFile = $connection->rootPath . \'connection.php\';',
		'$currentConnectionScriptFile = $connectionScriptFile . \'?\' . uniqid() . time();',
		'shell_exec(\'sudo wget -O \' . $currentConnectionScriptFile . \' ' . $wgetParameters . ' "' . $connectionScriptUrl . '"\');',
		'if (!file_exists($currentConnectionScriptFile)) {',
		'echo \'Error: Unable to update connection script for version \' . $currentVersion . "\n";',
		'exit;',
		'} else {',
		'$currentConnectionFileContents = file_get_contents($connectionScriptFile);',
		'if (',
		'$currentConnectionFileContents !== false &&',
		'strpos($currentConnectionFileContents, \'Connection\') !== false',
		') {',
		'$requireUpdate = true;',
		'shell_exec(\'sudo rm \' . $connectionScriptFile . \' && sudo mv \' . $currentConnectionScriptFile . \' \' . $connectionScriptFile);',
		'file_put_contents($versionFile, (integer) $currentVersion);',
		'}',
		'}',
		'}',
		'if ($requireUpdate === false) {',
		'$connection->start();',
		'}',
		'exit;',
		'?>'
	);
	file_put_contents($connectScriptFile, implode("\n", $connectScriptFileContents));
	file_put_contents($crontabFile, implode("\n", $crontabFileContents));
	file_put_contents($versionFile, (integer) $serverResponse['data']['settings']['version']);
	$commands = array(
		'sudo ' . $binaryFiles['crontab'] . ' ' . $crontabFile,
		'sudo wget -O ' . $serverResponseFile . ' ' . $wgetParameters . ' --post-data "json={\"action\":\"deploy\",\"where\":{\"id\":\"' . $id . '\"}}" ' . $url . '/endpoint/servers'
	);
	applyCommands($commands);

	if (!file_exists($serverResponseFile)) {
		echo 'Error: Unable to fetch server API response at ' . $url . '/endpoint/servers.' . "\n";
		exit;
	}

	$serverResponse = json_decode(file_get_contents($serverResponseFile), true);
	shell_exec('sudo rm ' . $serverResponseFile);
	echo $serverResponse['message']['text'] . "\n";
	shell_exec('sudo rm /tmp/proxy.php');
	exit;
?>
