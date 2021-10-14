<?php
	shell_exec('sudo apt-get update');
	shell_exec('sudo /usr/bin/systemctl stop mysql');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y purge mysql-server mysql-client mysql-common mysql-server-core-* mysql-client-core-*');
	shell_exec('sudo rm -rf /etc/mysql /var/lib/mysql /var/log/mysql');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y autoremove');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y autoclean');
	shell_exec('cd /tmp && sudo wget -O mysql_apt_config.deb ' . ($wgetParameters = '--no-dns-cache --retry-connrefused --timeout=60 --tries=2') . ' https://dev.mysql.com/get/mysql-apt-config_0.8.13-1_all.deb');

	if (file_exists('/tmp/mysql_apt_config.deb') === false) {
		echo 'Error downloading MySQL source file, please try again.' . "\n";
		exit;
	}

	shell_exec('cd /tmp && sudo DEBIAN_FRONTEND=noninteractive dpkg -i mysql_apt_config.deb');
	shell_exec('sudo add-apt-repository -y universe');
	shell_exec('sudo apt-get update');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install libmecab2');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get --fix-broken -y install mysql-common mysql-client mysql-community-server-core mysql-community-client mysql-community-client-core mysql-community-server mysql-community-client-plugins mysql-server');

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
	shell_exec('sudo /usr/sbin/service mysql restart');
	shell_exec('sudo mysql -u root -p"password" -e "DELETE FROM mysql.user WHERE User=\'\'; DELETE FROM mysql.user WHERE User=\'root\' AND Host NOT IN (\'localhost\', \'127.0.0.1\', \'::1\');"');
	shell_exec('sudo mysql -u root -p"password" -e "DROP USER \'root\'@\'localhost\'; CREATE USER \'root\'@\'localhost\' IDENTIFIED BY \'password\'; GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\' WITH GRANT OPTION; FLUSH PRIVILEGES;"');
	shell_exec('sudo /usr/sbin/service mysql restart');
	shell_exec('sudo apt-get update');
	// todo: add apache config for /var/www/ghostcompute + add files from git clone
	require_once('/var/www/ghostcompute/settings.php');
	$databaseConnection = mysqli_connect('localhost', 'root', 'password');

	if ($databaseConnection === false) {
		echo 'Error: ' . mysqli_connect_error() . '.';
		exit;
	}

	mysqli_query($databaseConnection, 'CREATE DATABASE IF NOT EXISTS `ghostcompute` CHARSET utf8');
	$databaseConnection = mysqli_connect('localhost', 'root', 'password', 'ghostcompute');

	if ($connection === false) {
		echo 'Error: ' . mysqli_connect_error() . '.';
		exit;
	}

	$databaseCommands = array();

	foreach ($settings['databases'] as $databaseTableName => $databaseTable) {
		$databaseCommands[] = 'CREATE TABLE IF NOT EXISTS `' . $databaseTableName . '` (`created_date` DATETIME NULL DEFAULT CURRENT_TIMESTAMP);';
		unset($databaseTable['structure']['created_date']);

		foreach ($databaseTable['structure'] as $databaseColumnName => $databaseColumn) {
			$databaseColumnDefault = '';
			$databaseNull = 'NULL';

			if (isset($databaseColumn['default']) === true) {
				$databaseColumnDefault = ' DEFAULT ' . $databaseColumn['default'];
			}

			if ($databaseColumnName === 'id') {
				$databaseNull = 'NOT ' . $databaseNull;
			}

			$databaseCommandActions = array(
				'add' => 'ADD `' . $databaseColumnName . '`',
				'change' => 'CHANGE `' . $databaseColumnName . '` `' . $databaseColumnName . '`'
			);
			$databaseCommand = 'ALTER TABLE `' . $databaseTableName . '` ' . $databaseCommandActions['change'] . ' ' . $databaseColumn['type'] . ' ' . $databaseNull . $databaseColumnDefault;

			if (mysqli_query($connection, $databaseCommand) === false) {
				$databaseCommands[] = str_replace($databaseCommandActions['change'], $databaseCommandActions['add'], $databaseCommand);
			}

			if ($databaseColumnName === 'id') {
				$databaseCommands[$databaseColumnName . '__' . $databaseTableName] = 'ALTER TABLE `' . $databaseTableName . '` ADD PRIMARY KEY(`' . $databaseColumnName . '`)';

				if ($databaseColumn['type'] === 'BIGINT(11)') {
					$databaseCommands[] = $databaseCommand . ' AUTO_INCREMENT';
				}
			}

			if (empty($databaseColumn['index']) === false) {
				$databaseCommands[$databaseColumnName . '__' . $databaseTableName] = 'ALTER TABLE `' . $databaseTableName . '` ADD INDEX(`' . $databaseColumnName . '`)';
			}
		}
	}

	foreach ($databaseCommands as $databaseCommandKey => $databaseCommand) {
		if (
			(is_numeric($databaseCommandKey) === false) &&
			(
				(strpos($databaseCommand, 'ADD INDEX') !== false) ||
				(strpos($databaseCommand, 'ADD PRIMARY KEY') !== false)
			)
		) {
			$databaseCommandKey = explode('__', $databaseCommandKey);

			if (empty(mysqli_query($databaseConnection, 'SHOW KEYS FROM `' . $databaseCommandKey[1] . '` WHERE Column_name=\'' . $databaseCommandKey[0] . '\'')->num_rows) === false) {
				continue;
			}
		}

		$databaseCommandResult = mysqli_query($databaseConnection, $databaseCommand);

		if ($databaseCommandResult === false) {
			echo $databaseCommand . "\n";
			echo 'Error executing database command, please try again.';
			exit;
		}
	}

	/*
	todo: add default user
	$databaseData = array(
		'table_name' => array(
			array(
				'column_key1' => 'column_value1',
				'column_key2' => 'column_value2'
			)
		)
	);
	foreach ($databaseData as $databaseTableName => $databaseRows) {
		foreach ($databaseRows as $databaseRow) {
			mysqli_query($databaseConnection, 'INSERT IGNORE INTO `' . $databaseTableName . '` (`' . implode('`, `', array_keys($databaseRow)) . '`) VALUES (' . implode(', ', array_values($databaseRow)) . ')');
		}
	}
	*/
	echo 'GhostCompute system installed successfully.' . "\n";
	exit;
?>
