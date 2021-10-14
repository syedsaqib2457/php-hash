<?php
	function _applyCommands($commands) {
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

	function _installDependencies() {
		// todo: add packages for website file installation
		$commands = array(
			'sudo apt-get update',
			'sudo /usr/bin/systemctl stop mysql',
			'sudo DEBIAN_FRONTEND=noninteractive apt-get -y purge mysql-server mysql-client mysql-common mysql-server-core-* mysql-client-core-*',
			'sudo rm -rf /etc/mysql /var/lib/mysql /var/log/mysql',
			'sudo DEBIAN_FRONTEND=noninteractive apt-get -y autoremove',
			'sudo DEBIAN_FRONTEND=noninteractive apt-get -y autoclean',
			'cd /tmp && sudo wget -O mysql_apt_config.deb ' . ($wgetParameters = '--no-dns-cache --retry-connrefused --timeout=60 --tries=2') . ' https://dev.mysql.com/get/mysql-apt-config_0.8.13-1_all.deb',
		);
		_applyCommands($commands);

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
		_applyCommands($commands);

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
			'sudo /usr/sbin/service mysql restart',
			'sudo mysql -u root -p"password" -e "DELETE FROM mysql.user WHERE User=\'\'; DELETE FROM mysql.user WHERE User=\'root\' AND Host NOT IN (\'localhost\', \'127.0.0.1\', \'::1\');"',
			'sudo mysql -u root -p"password" -e "DROP USER \'root\'@\'localhost\'; CREATE USER \'root\'@\'localhost\' IDENTIFIED BY \'password\'; GRANT ALL PRIVILEGES ON *.* TO \'root\'@\'localhost\' WITH GRANT OPTION; FLUSH PRIVILEGES;"',
			'sudo /usr/sbin/service mysql restart',
			'sudo apt-get update',
		);
		_applyCommands($commands);
		return;
	}

	function _installDatabase($parameters = true) {
		require_once('/var/www/ghostcompute/system/settings.php');
		$connection = mysqli_connect('localhost', 'root', 'password');

		if ($connection === false) {
			echo 'Error: ' . mysqli_connect_error() . '.';
			exit;
		}

		mysqli_query($connection, 'CREATE DATABASE IF NOT EXISTS `ghostcompute` CHARSET utf8');
		$connection = mysqli_connect('localhost', 'root', 'password', 'ghostcompute');

		if ($connection === false) {
			echo 'Error: ' . mysqli_connect_error() . '.';
			exit;
		}

		$commands = array();

		foreach ($settings['databases'] as $databaseTableName => $databaseTable) {
			$commands[] = 'CREATE TABLE IF NOT EXISTS `' . $databaseTableName . '` (`created_date` DATETIME NULL DEFAULT CURRENT_TIMESTAMP);';
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

				$commandActions = array(
					'add' => 'ADD `' . $databaseColumnName . '`',
					'change' => 'CHANGE `' . $databaseColumnName . '` `' . $databaseColumnName . '`'
				);
				$command = 'ALTER TABLE `' . $databaseTableName . '` ' . $commandActions['change'] . ' ' . $databaseColumn['type'] . ' ' . $databaseNull . $databaseColumnDefault;

				if (mysqli_query($connection, $command) === false) {
					$commands[] = str_replace($commandActions['change'], $commandActions['add'], $command);
				}

				if ($databaseColumnName === 'id') {
					$commands[$databaseColumnName . '__' . $databaseTableName] = 'ALTER TABLE `' . $databaseTableName . '` ADD PRIMARY KEY(`' . $databaseColumnName . '`)';

					if ($databaseColumn['type'] === 'BIGINT(11)') {
						$commands[] = $command . ' AUTO_INCREMENT';
					}
				}

				if (empty($databaseColumn['index']) === false) {
					$commands[$databaseColumnName . '__' . $databaseTableName] = 'ALTER TABLE `' . $databaseTableName . '` ADD INDEX(`' . $databaseColumnName . '`)';
				}
			}
		}

		foreach ($commands as $commandKey => $command) {
			if (
				(is_numeric($commandKey) === false) &&
				(
					(strpos($command, 'ADD INDEX') !== false) ||
					(strpos($command, 'ADD PRIMARY KEY') !== false)
				)
			) {
				$commandKey = explode('__', $commandKey);

				if (empty(mysqli_query($connection, 'SHOW KEYS FROM `' . $commandKey[1] . '` WHERE Column_name=\'' . $commandKey[0] . '\'')->num_rows) === false) {
					continue;
				}
			}

			$commandResult = mysqli_query($connection, $command);

			if ($commandResult === false) {
				echo $command . "\n";
				echo 'Error executing database command, please try again.';
				exit;
			}
		}

		/*
		todo: add default user
		$data = array(
			'table_name' => array(
				array(
					'column_key1' => 'column_value1',
					'column_key2' => 'column_value2'
				)
			)
		);
		foreach ($data as $tableName => $rows) {
			foreach ($rows as $row) {
				mysqli_query($connection, 'INSERT IGNORE INTO `' . $tableName . '` (`' . implode('`, `', array_keys($row)) . '`) VALUES (' . implode(', ', array_values($row)) . ')');
			}
		}
		*/

		return;
	}

	_installDatabase();
?>
