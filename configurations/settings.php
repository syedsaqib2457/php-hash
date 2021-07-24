<?php
	class Configuration {

		public function __construct() {

			require(__DIR__ . '/database.php');
			require(__DIR__ . '/keys.php');

			$this->keys = array(
				'start' => $keys['salt'] . $keys['start'] . $keys['salt'],
				'stop' => $keys['salt'] . $keys['stop'] . $keys['salt']
			);

			$this->publicPermissions = array(
				'system' => array(
					'login'
				)
			);

			$this->privateIpRangeIntegers = array(
				0 => 16777215,
				167772160 => 184549375,
				1681915904 => 1686110207,
				2130706432 => 2147483647,
				2851995648 => 2852061183,
				2886729728 => 2887778303,
				3221225472 => 3221225727,
				3221225984 => 3221226239,
				3227017984 => 3227018239,
				3232235520 => 3232301055,
				3323068416 => 3323199487,
				3325256704 => 3325256959,
				3405803776 => 3405804031,
				3758096384 => 4026531839,
				4026531840 => 4294967294,
				4294967295 => 4294967295
			);

			$this->settings = array(
				'base_domain' => basename(__DIR__),
				'base_path' => __DIR__,
				'database' => $database,
				'version' => (integer) file_get_contents(__DIR__ . '/version.txt')
			);

			if (empty($_SERVER['REMOTE_ADDR']) === false) {
				$this->settings['source_ip'] = $_SERVER['REMOTE_ADDR'];
			}

		}

		public function redirect($redirect, $responseCode = 301) {
			header('Location: ' . $redirect, true, $responseCode);
			exit;
		}

	}

	$configuration = new Configuration();
?>
