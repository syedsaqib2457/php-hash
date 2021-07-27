<?php
	class System {

		public function __construct() {

			require(__DIR__ . '/keys.php');

			$this->settings = array(
				'base_domain' => basename(dirname(__DIR__)),
				'base_path' => __DIR__,
				'database' => array(
					'hostname' => 'localhost',
					'name' => 'overlord',
					'password' => 'password',
					'structure' => array(
						'node_processes' => array(
							'application_protocol' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(4)'
							),
							'created' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
							'external_ip_version_4' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(15)'
							),
							'external_ip_version_6' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(39)'
							),
							'id' => array(
								'auto_increment' => true,
								'primary_key' => true,
								'type' => 'BIGINT(11)'
							),
							'internal_ip_version_4' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(15)'
							),
							'internal_ip_version_6' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(39)'
							),
							'modified' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
							'node_id' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'port' => array(
								'default' => null,
								'null' => true,
								'type' => 'INT(5)'
							),
							'transport_protocol' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(3)'
							),
							'type' => array(
								'default' => null,
								'null' => true,
								'type' => 'CHAR(10)'
							)
						),
						'node_users' => array(
							'created' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
							'id' => array(
								'auto_increment' => true,
								'primary_key' => true,
								'type' => 'BIGINT(11)'
							),
							'modified' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
							'node_id' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'status_processed' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
							),
							'type' => array(
								'default' => null,
								'null' => true,
								'type' => 'CHAR(10)'
							),
							'user_id' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							)
						),
						'nodes' => array(
							'created' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
							'destination_address_version_4' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(1000)'
							),
							'destination_address_version_6' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(1000)'
							),
							'destination_port_version_4' => array(
								'default' => null,
								'null' => true,
								'type' => 'INT(5)'
							),
							'destination_port_version_6' => array(
								'default' => null,
								'null' => true,
								'type' => 'INT(5)'
							),
							'external_ip_version_4' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(15)'
							),
							'external_ip_version_6' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(39)'
							),
							'id' => array(
								'auto_increment' => true,
								'primary_key' => true,
								'type' => 'BIGINT(11)'
							),
							'internal_ip_version_4' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(15)'
							),
							'internal_ip_version_6' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(39)'
							),
							'modified' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
							'node_id' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'status_active' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
							),
							'status_deployed' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
							)
						),
						'request_destinations' => array(
							'destination' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(1000)'
							),
							'id' => array(
								'auto_increment' => true,
								'primary_key' => true,
								'type' => 'BIGINT(11)'
							),
							'type' => array(
								'default' => null,
								'null' => true,
								'type' => 'CHAR(6)'
							)
						),
						'request_limit_rules' => array(
							'id' => array(
								'auto_increment' => true,
								'primary_key' => true,
								'type' => 'BIGINT(11)'
							),
							'request_interval_type' => array(
								'default' => "'minute'",
								'type' => 'VARCHAR(255)'
							),
							'request_interval_value' => array(
								'default' => 1,
								'type' => 'SMALLINT(3)'
							),
							'request_limit_interval_type' => array(
								'default' => "'minute'",
								'type' => 'VARCHAR(255)'
							),
							'request_limit_interval_value' => array(
								'default' => 1,
								'type' => 'SMALLINT(3)'
							)
						),
						'request_logs' => array(
							'bytes_received' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'bytes_sent' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'destination_id' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'destination_ip' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(30)'
							),
							'destination_url' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(1000)'
							),
							'id' => array(
								'auto_increment' => true,
								'primary_key' => true,
								'type' => 'BIGINT(11)'
							),
							'node_user_id' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'response_code' => array(
								'default' => null,
								'null' => true,
								'type' => 'SMALLINT(3)'
							),
							'source_ip' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(30)'
							),
							'username' => array(
								'default' => "'-'",
								'type' => 'VARCHAR(15)'
							)
						),
						'settings' => array(
							'created' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
							'id' => array(
								'primary_key' => true,
								'type' => 'VARCHAR(255)'
							),
							'modified' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
							'value' => array(
								'type' => 'VARCHAR(255)'
							)
						),
						'user_request_destinations' => array(
							'created' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
							'id' => array(
								'auto_increment' => true,
								'primary_key' => true,
								'type' => 'BIGINT(11)'
							),
							'modified' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
							'request_destination_id' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'user_id' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							)
						),
						'user_request_limit_rules' => array(
							'created' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
							'id' => array(
								'auto_increment' => true,
								'primary_key' => true,
								'type' => 'BIGINT(11)'
							),
							'modified' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
							'status_request_limit_exceeded' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
							),
							'user_request_limit_rule_id' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'user_id' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							)
						),
						'users' => array(
							'authentication_password' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(255)'
							),
							'authentication_expires' => array(
								'default' => null,
								'null' => true,
								'type' => 'DATETIME'
							),
							'authentication_username' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(255)'
							),
							'authentication_whitelist' => array(
								'default' => null,
								'null' => true,
								'type' => 'TEXT'
							),
							'created' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
							'id' => array(
								'auto_increment' => true,
								'primary_key' => true,
								'type' => 'BIGINT(11)'
							),
							'modified' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
							'status_allowing_request_destinations_only' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
							),
							'status_allowing_request_logs' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
							),
							'tag' => array(
								'default' => null,
								'null' => true,
								'type' => 'VARCHAR(100)'
							)
						)
					),
					'username' => 'root'
				),
				'keys' => $keys,
				'private_ip_ranges' => array(
					4 => array(
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
					),
					6 => array(
						'0000:0000:0000:0000:0000:0000:0000:0000',
						'0000:0000:0000:0000:0000:0000:0000:0001',
						'0000:0000:0000:0000:0000:ffff:y',
						'0000:0000:0000:0000:ffff:0000:y',
						'0064:ff9b:0000:0000:0000:0000:y',
						'0100:0000:0000:0000:x:x:x:x',
						'fe80:0000:0000:0000:x:x:x:x',
						'2001:0000:x:x:x:x:x:x',
						'2001:0db8:x:x:x:x:x:x',
						'2001:002x:x:x:x:x:x:x',
						'2002:x:x:x:x:x:x:x',
						'fcx:x:x:x:x:x:x:x',
						'fdx:x:x:x:x:x:x:x',
						'ffx:x:x:x:x:x:x:x'
					)
				),
				'version' => 19
			);

		}

	}

	$system = new System();
?>
