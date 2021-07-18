<?php
	if (
		empty($_SERVER['argv'][1]) ||
		$_SERVER['argv'][1] === 'STATIC_IP_ADDRESS'
	) {
		echo 'Error: STATIC_IP_ADDRESS should be the static public IPv4 address of the server.' . "\n";
		exit;
	}

	require_once('/var/www/' . ($url = $_SERVER['argv'][1]) . '/configuration.php');
	$connection = mysqli_connect($configuration->settings['database']['hostname'], $configuration->settings['database']['username'], $configuration->settings['database']['password']);
	$queries = array();

	if (!$connection) {
		echo 'Error: ' . mysqli_connect_error() . '.';
		exit;
	}

	mysqli_query($connection, 'CREATE DATABASE IF NOT EXISTS `' . $configuration->settings['database']['name'] . '` CHARSET utf8');
	$database = mysqli_connect($configuration->settings['database']['hostname'], $configuration->settings['database']['username'], $configuration->settings['database']['password'], $configuration->settings['database']['name']);

	if (!$database) {
		echo 'Error: ' . mysqli_connect_error() . '.';
		exit;
	}

	foreach ($configuration->settings['database']['schema'] as $table => $columns) {
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

		if (!empty($columnKey)) {
			$queries[] = 'CREATE TABLE IF NOT EXISTS `' . $table . '` (`' . $columnKey . '` ' . $columns[$columnKey]['type'] . ' ' . (empty($columns[$columnKey]['null']) ? 'NOT ' : '') . 'NULL' . (isset($columns[$columnKey]['default']) ? ' DEFAULT ' . $columns[$columnKey]['default'] : '') . ');';

			foreach ($columns as $columnName => $columnStructure) {
				$queryActions = array(
					'add' => 'ADD `' . $columnName . '`',
					'change' => 'CHANGE `' . $columnName . '` `' . $columnName . '`'
				);
				$query = 'ALTER TABLE `' . $table . '` ' . $queryActions['change'] . ' ' . $columnStructure['type'] . ' ' . (empty($columnStructure['null']) ? 'NOT ' : '') . 'NULL' . (isset($columnStructure['default']) ? ' DEFAULT ' . $columnStructure['default'] : '');

				if (
					$columnName !== $columnKey &&
					mysqli_query($database, $query) === false
				) {
					$queries[] = str_replace($queryActions['change'], $queryActions['add'], $query);
				}

				if (!empty($columnStructure['primary_key'])) {
					$queries[$columnName . $configuration->keys['salt'] . $table] = 'ALTER TABLE `' . $table . '` ADD PRIMARY KEY(`' . $columnName . '`)';

					if (!empty($columnStructure['auto_increment'])) {
						$queries[] = $query . ' AUTO_INCREMENT';
					}
				}

				if (!empty($columnStructure['index'])) {
					$queries[$columnName . $configuration->keys['salt'] . $table] = 'ALTER TABLE `' . $table . '` ADD INDEX(`' . $columnName . '`)';
				}
			}
		}
	}

	foreach ($queries as $queryKey => $query) {
		if (
			(
				strpos($query, 'ADD INDEX') !== false ||
				strpos($query, 'ADD PRIMARY KEY') !== false
			) &&
			!is_numeric($queryKey) &&
			($queryKey = explode($configuration->keys['salt'], $queryKey)) &&
			!empty($queryKey[0]) &&
			!empty($queryKey[1]) &&
			mysqli_query($database, 'SHOW KEYS FROM `' . $queryKey[1] . '` WHERE Column_name=\'' . $queryKey[0] . '\'')->num_rows
		) {
			continue;
		}

		$queryResult = mysqli_query($database, $query);

		if (!$queryResult) {
			echo 'Error: Unable to run the following database query, please try restarting the install script. ' . $query;
			exit;
		}
	}

	if (mysqli_query($database, 'SELECT `id` FROM `users` LIMIT 1')->num_rows) {
		echo 'Website and database already installed.' . "\n";
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
		"'" . sha1($configuration->keys['salt'] . $configuration->keys['start'] . $configuration->keys['stop']) . "'"
	);
	$userDataFields = array(
		'created',
		'id',
		'modified',
		'password',
		'whitelisted_ips'
	);
	$userDataValues = array(
		"'" . date('Y-m-d H:i:s', time()) . "'",
		1,
		"'" . date('Y-m-d H:i:s', time()) . "'",
		"'" . ($password = 'x' . substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789012345678901234567890123456789'), 0, 14)) . "'",
		"''"
	);
	mysqli_query($database, 'INSERT IGNORE INTO `settings` (`' . implode('`, `', $settingDataFields) . '`) VALUES (' . implode(', ', $settingDataValues) . ')');
	mysqli_query($database, 'INSERT IGNORE INTO `users` (`' . implode('`, `', $userDataFields) . '`) VALUES (' . implode(', ', $userDataValues) . ')');

	if (
		!mysqli_query($database, 'SELECT `id` FROM `settings`')->num_rows ||
		!mysqli_query($database, 'SELECT `id` FROM `users`')->num_rows
	) {
		echo 'Unable to create user account. Please try restarting the install script.' . "\n";
		exit;
	}

	echo 'Website and database installed successfully.' . "\n";
	echo 'You can now log in at http://' . ($url = $_SERVER['argv'][1]) . '/servers?#login with this password:' . "\n";
	echo 'Password: ' . $password . "\n";
	shell_exec('sudo rm /tmp/database.php');
	exit;
?>
