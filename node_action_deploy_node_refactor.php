<?php
	if (
		(empty($_SERVER['argv'][1]) === true) ||
		(empty($_SERVER['argv'][2]) === true)
	) {
		echo 'Error deploying node, please try again.' . "\n";
		exit;
	}

	function _executeCommands($commands) {
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
	_executeCommands($commands);
	$uniqueId = '_' . uniqid() . time();
	$binaries = array(
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
			'command' => $uniqueId,
			'name' => 'telinit',
			'output' => 'single',
			'package' => 'systemd'
		)
	);
	// todo
?>
