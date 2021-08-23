<?php
	class System {

		public function __construct() {

			require(__DIR__ . '/keys.php');

			$this->settings = array(
				'base_domain' => basename(dirname(__DIR__)),
				'base_path' => __DIR__,
				'database' => array(
					'hostname' => 'localhost',
					'name' => 'ghostcompute',
					'password' => 'password',
					'structure' => array(
						'node_ports' => array(
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
							'port_id' => array(
								'auto_increment' => true,
								'primary_key' => true,
								'type' => 'MEDIUMINT(3)'
							),
							'status_allowing' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
							),
							'status_denying' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
							),
							'status_processed' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
							),
							'status_removed' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
							),
							'type' => array(
								'default' => null,
								'null' => true,
								'type' => 'CHAR(10)'
							)
						),
						'node_processes' => array(
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
							'modified' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
							'node_id' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'port_id' => array(
								'default' => null,
								'null' => true,
								'type' => 'MEDIUMINT(3)'
							),
							'type' => array(
								'default' => null,
								'null' => true,
								'type' => 'CHAR(10)'
							)
						),
						'node_process_resource_usage_logs' => array(
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
							'cpu_percentage' => array(
								'default' => null,
								'null' => true,
								'type' => 'TINYINT(1)'
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
							'memory_percentage' => array(
								'default' => null,
								'null' => true,
								'type' => 'TINYINT(1)'
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
							'node_process_type' => array(
								'default' => null,
								'null' => true,
								'type' => 'CHAR(10)'
							),
							'requests' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							)
						),
						'node_process_user_resource_usage_logs' => array(
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
							'node_process_type' => array(
								'default' => null,
								'null' => true,
								'type' => 'CHAR(10)'
							),
							'request_limit' => array(
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
						'node_resource_usage_logs' => array(
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
							'cpu_capacity_cores' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'cpu_capacity_megahertz' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'cpu_percentage' => array(
								'default' => null,
								'null' => true,
								'type' => 'TINYINT(1)'
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
							'memory_capacity_megabytes' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'memory_percentage' => array(
								'default' => null,
								'null' => true,
								'type' => 'TINYINT(1)'
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
							'requests' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'storage_capacity_megabytes' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'storage_percentage' => array(
								'default' => null,
								'null' => true,
								'type' => 'TINYINT(1)'
							)
						),
						'node_user_request_destination_logs' => array(
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
							'request_destination_id' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'requests' => array(
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
						'node_user_resource_usage_logs' => array(
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
							'requests' => array(
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
							'status_removed' => array(
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
							'memory_capacity_megabytes' => array(
								'default' => null,
								'null' => true,
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
							'status_active' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
							),
							'status_deployed' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
							),
							'status_processed' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
							),
							'storage_capacity_megabytes' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							)
						),
						'ports' => array(
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
							)
						),
						'request_destinations' => array(
							'created' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
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
							'modified' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							)
						),
						'request_limit_rules' => array(
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
							'request_interval_minutes' => array(
								'default' => 1,
								'type' => 'SMALLINT(3)'
							),
							'request_limit' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'request_limit_interval_minutes' => array(
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
							'created' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
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
							'modified' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
							'node_user_id' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'status_processed' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
							),
							'status_processing' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
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
							),
							'type' => array(
								'default' => null,
								'null' => true,
								'type' => 'CHAR(10)'
							)
						),
						'resource_usage_logs' => array(
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
							'cpu_capacity_cores' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'cpu_capacity_megahertz' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'cpu_percentage' => array(
								'default' => null,
								'null' => true,
								'type' => 'TINYINT(1)'
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
							'memory_capacity_megabytes' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'memory_percentage' => array(
								'default' => null,
								'null' => true,
								'type' => 'TINYINT(1)'
							),
							'modified' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
							'storage_capacity_megabytes' => array(
								'default' => null,
								'null' => true,
								'type' => 'BIGINT(11)'
							),
							'storage_percentage' => array(
								'default' => null,
								'null' => true,
								'type' => 'TINYINT(1)'
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
							'status_processed' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
							),
							'status_removed' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
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
							'limit_until' => array(
								'default' => null,
								'null' => true,
								'type' => 'DATETIME'
							),
							'modified' => array(
								'default' => 'CURRENT_TIMESTAMP',
								'type' => 'DATETIME'
							),
							'request_limit_rule_id' => array(
								'default' => null,
								'null' => true,
								'type' => 'TINYINT(1)'
							),
							'status_limit_exceeded_destination_only' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
							),
							'status_processed' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
							),
							'status_removed' => array(
								'default' => 0,
								'null' => true,
								'type' => 'TINYINT(1)'
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
							'status_requiring_strict_authentication' => array(
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
				'private_network' => array(
					'ip_blocks' => array(
						4 => array(
							'0.0.0.0/8',
							'10.0.0.0/8',
							'100.64.0.0/10',
							'127.0.0.0/8',
							'172.16.0.0/12',
							'192.0.0.0/24',
							'192.0.2.0/24',
							'192.88.99.0/24',
							'192.168.0.0/16',
							'198.18.0.0/15',
							'198.51.100.0/24',
							'203.0.113.0/24',
							'224.0.0.0/4',
							'240.0.0.0/4',
							'255.255.255.255/32'
						),
						6 => array(
							'::/0',
							'::/128',
							'::1/128',
							'::ffff:0:0/96',
							'::ffff:0:0:0/96',
							'64:ff9b::/96',
							'100::/64',
							'2001:0000:/32',
							'2001:20::/28',
							'2001:db8::/32',
							'2002::/16',
							'fc00::/7',
							'fe80::/10',
							'ff00::/8'
						)
					),
					'ip_ranges' => array(
						4 => array(
							0 => 16777215,
							167772160 => 184549375,
							1681915904 => 1686110207,
							2130706432 => 2147483647,
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
					'reserved_internal_ip' => array(
						4 => '10.10.10.10',
						6 => 'fc10.1010.1010.1010.1010.1010.1010.1010'
					)
				),
				'version' => 1
			);

		}

	}

	$system = new System();
?>
