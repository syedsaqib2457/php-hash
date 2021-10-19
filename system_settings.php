<?php
	// todo: add authentication settings for multiple databases as database values

	if (empty($parameters) === true) {
		exit;
	}

	$settings = array(
		'databases' => array(
			'node_process_forwarding_destinations' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'address_version_4' => array(
						'default' => null,
						'type' => 'VARCHAR(1000)'
					),
					'address_version_6' => array(
						'default' => null,
						'type' => 'VARCHAR(1000)'
					),
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_process_type' => array(
						'default' => null,
						'type' => 'CHAR(10)'
					),
					'port_number_version_4' => array(
						'default' => null,
						'type' => 'INT(5)'
					),
					'port_number_version_6' => array(
						'default' => null,
						'type' => 'INT(5)'
					)
				)
			),
			'node_process_node_user_request_destination_logs' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_process_type' => array(
						'default' => null,
						'type' => 'CHAR(10)'
					),
					'node_request_destination_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_user_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'request_count' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					)
				)
			),
			'node_process_node_user_request_logs' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'bytes_received' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'bytes_sent' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'destination_ip_address' => array(
						'default' => null,
						'type' => 'VARCHAR(45)'
					),
					'destination_url' => array(
						'default' => null,
						'type' => 'VARCHAR(1000)'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_process_type' => array(
						'default' => null,
						'type' => 'CHAR(10)'
					),
					'node_request_destination_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_user_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'response_code' => array(
						'default' => null,
						'type' => 'SMALLINT(3)'
					),
					'source_ip_address' => array(
						'default' => null,
						'type' => 'VARCHAR(45)'
					),
					'status_processed' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					),
					'status_processing' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					)
				)
			),
			'node_process_node_user_resource_usage_logs' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'bytes_received' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'bytes_sent' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_process_type' => array(
						'default' => null,
						'type' => 'CHAR(10)'
					),
					'node_user_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'request_count' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					)
				)
			),
			'node_process_node_users' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_process_type' => array(
						'default' => null,
						'type' => 'CHAR(10)'
					),
					'node_user_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'status_removed' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					)
				)
			),
			'node_process_recursive_dns_destinations' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'listening_ip_address_version_4' => array(
						'default' => null,
						'type' => 'VARCHAR(15)'
					),
					'listening_ip_address_version_4_node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'listening_ip_address_version_6' => array(
						'default' => null,
						'type' => 'VARCHAR(45)'
					),
					'listening_ip_address_version_6_node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'listening_port_number_version_4' => array(
						'default' => null,
						'type' => 'INT(5)'
					),
					'listening_port_number_version_6' => array(
						'default' => null,
						'type' => 'INT(5)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_process_type' => array(
						'default' => null,
						'type' => 'CHAR(10)'
					),
					'source_ip_address_version_4' => array(
						'default' => null,
						'type' => 'VARCHAR(15)'
					),
					'source_ip_address_version_6' => array(
						'default' => null,
						'type' => 'VARCHAR(45)'
					)
				)
			),
			'node_process_resource_usage_logs' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'bytes_received' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'bytes_sent' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'cpu_percentage' => array(
						'default' => null,
						'type' => 'TINYINT(1)'
					),
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'memory_percentage' => array(
						'default' => null,
						'type' => 'TINYINT(1)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_process_type' => array(
						'default' => null,
						'type' => 'CHAR(10)'
					),
					'request_count' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					)
				)
			),
			'node_processes' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'port_number' => array(
						'default' => null,
						'type' => 'MEDIUMINT(3)'
					),
					'type' => array(
						'default' => null,
						'type' => 'CHAR(10)'
					),
					'status_removed' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					)
				)
			),
			'node_request_destinations' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'address' => array(
						'default' => null,
						'type' => 'VARCHAR(1000)'
					),
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					)
				)
			),
			'node_request_limit_rules' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'interval_minutes' => array(
						'default' => 1,
						'type' => 'SMALLINT(3)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'request_count' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'request_count_interval_minutes' => array(
						'default' => 1,
						'type' => 'SMALLINT(3)'
					)
				)
			),
			'node_reserved_internal_destinations' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'ip_address' => array(
						'default' => null,
						'null' => true,
						'type' => 'VARCHAR(45)'
					),
					'ip_address_version' => array(
						'default' => null,
						'type' => 'TINYINT(1)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'node_id' => array(
						'default' => null,
						'null' => true,
						'type' => 'BIGINT(11)'
					),
					'node_node_id' => array(
						'default' => null,
						'null' => true,
						'type' => 'BIGINT(11)'
					),
					'node_node_external_ip_address_type' => array(
						'default' => null,
						'null' => true,
						'type' => 'VARCHAR(7)'
					),
					'status_added' => array(
						'default' => 0,
						'null' => true,
						'type' => 'TINYINT(1)'
					)
				)
			),
			'node_resource_usage_logs' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'bytes_received' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'bytes_sent' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'cpu_capacity_cores' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'cpu_capacity_megahertz' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'cpu_percentage' => array(
						'default' => null,
						'type' => 'TINYINT(1)'
					),
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'memory_capacity_megabytes' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'memory_percentage' => array(
						'default' => null,
						'type' => 'TINYINT(1)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'request_count' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'storage_capacity_megabytes' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'storage_percentage' => array(
						'default' => null,
						'type' => 'TINYINT(1)'
					)
				)
			),
			'node_user_node_request_destinations' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'node_request_destination_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_user_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'status_removed' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					)
				)
			),
			'node_user_node_request_limit_rules' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'expiration_date' => array(
						'default' => null,
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'node_request_destination_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_request_limit_rule_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_user_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'status_removed' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					)
				)
			),
			'node_users' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'authentication_password' => array(
						'default' => null,
						'type' => 'VARCHAR(255)'
					),
					'authentication_username' => array(
						'default' => null,
						'type' => 'VARCHAR(255)'
					),
					'authentication_whitelist' => array(
						'default' => null,
						'type' => 'TEXT'
					),
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'status_allowing_request_destinations_only' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					),
					'status_allowing_request_logs' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					),
					'status_requiring_strict_authentication' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					),
					'tag' => array(
						'default' => null,
						'type' => 'VARCHAR(100)'
					)
				)
			),
			'nodes' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'authentication_token' => array(
						'default' => null,
						'type' => 'VARCHAR(100)'
					),
					'cpu_capacity_cores' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'cpu_capacity_megahertz' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'external_ip_address_version_4' => array(
						'default' => null,
						'type' => 'VARCHAR(15)'
					),
					'external_ip_address_version_4_type' => array(
						'default' => null,
						'type' => 'VARCHAR(7)'
					),
					'external_ip_address_version_6' => array(
						'default' => null,
						'type' => 'VARCHAR(45)'
					),
					'external_ip_address_version_6_type' => array(
						'default' => null,
						'type' => 'VARCHAR(7)'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'internal_ip_address_version_4' => array(
						'default' => null,
						'type' => 'VARCHAR(15)'
					),
					'internal_ip_address_version_6' => array(
						'default' => null,
						'type' => 'VARCHAR(45)'
					),
					'memory_capacity_megabytes' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'status_active' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					),
					'status_deployed' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					),
					'status_processed' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					),
					'storage_capacity_megabytes' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					)
				)
			),
			'system_resource_usage_logs' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'bytes_received' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'bytes_sent' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'cpu_capacity_cores' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'cpu_capacity_megahertz' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'cpu_percentage' => array(
						'default' => null,
						'type' => 'TINYINT(1)'
					),
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'destination_ip_address' => array(
						'default' => null,
						'type' => 'VARCHAR(45)'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'memory_capacity_megabytes' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'memory_percentage' => array(
						'default' => null,
						'type' => 'TINYINT(1)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'storage_capacity_megabytes' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'storage_percentage' => array(
						'default' => null,
						'type' => 'TINYINT(1)'
					)
				)
			),
			'system_user_authentication_token_scopes' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'action' => array(
						'default' => null,
						'type' => 'VARCHAR(100)'
					),
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'system_user_authentication_token_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'system_user_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					)
				)
			),
			'system_user_authentication_token_sources' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'address' => array(
						'default' => null,
						'type' => 'VARCHAR(45)'
					),
					'address_version' => array(
						'default' => null,
						'type' => 'TINYINT(1)'
					),
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'system_user_authentication_token_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'system_user_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					)
				)
			),
			'system_user_authentication_tokens' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'string' => array(
						'default' => null,
						'type' => 'VARCHAR(100)'
					),
					'system_user_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					)
				)
			),
			'system_user_request_logs' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'bytes_received' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'bytes_sent' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'response_code' => array(
						'default' => null,
						'type' => 'SMALLINT(3)'
					),
					'source_ip_address' => array(
						'default' => null,
						'type' => 'VARCHAR(45)'
					),
					'status_authorized' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					),
					'status_successful' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					),
					'system_function_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'system_user_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					)
				)
			),
			'system_users' => array(
				'authentication' => array(
					array(
						'hostname' => 'localhost',
						'password' => 'password'
					)
				),
				'structure' => array(
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'modified_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'system_user_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					)
				)
			)
		),
		'node_process_type_default_port_numbers' => array(
			'http_proxy' => 80,
			'recursive_dns' => 53,
			'socks_proxy' => 1080
		),
		'reserved_network' => array(
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
			)
		)
	);
?>
