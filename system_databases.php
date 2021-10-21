<?php
	if (empty($parameters) === true) {
		exit;
	}

	$databases = array(
		'node_process_blockchain_mining_resource_usage_rules' => array(),
		'node_process_forwarding_destinations' => array(
			'authentication' => array(
				array(
					'hostname' => 'localhost',
					'password' => 'password'
				)
			),
			'structure' => array(
				'columns' => array(
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
				),
				'table' => 'node_process_forwarding_destinations'
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
				'columns' => array(
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
				),
				'name' => 'node_process_node_user_request_destination_logs'
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
				'columns' => array(
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
				),
				'table' => 'node_process_node_user_request_logs'
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
				'columns' => array(
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
				),
				'table' => 'node_process_node_user_resource_usage_logs'
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
				'columns' => array(
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
					)
				),
				'table' => 'node_process_node_users'
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
				'columns' => array(
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
				),
				'table' => 'node_process_recursive_dns_destinations'
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
				'columns' => array(
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
				),
				'table' => 'node_process_resource_usage_logs'
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
				'columns' => array(
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
					)
				),
				'table' => 'node_processes'
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
				'columns' => array(
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
				),
				'table' => 'node_request_destinations'
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
				'columns' => array(
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
				),
				'table' => 'node_request_limit_rules'
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
				'columns' => array(
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
						'type' => 'BIGINT(11)'
					),
					'node_node_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_node_external_ip_address_type' => array(
						'default' => null,
						'type' => 'VARCHAR(7)'
					),
					'status_added' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					),
					'status_processed' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					)
				),
				'table' => 'node_reserved_internal_destinations'
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
				'columns' => array(
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
				),
				'table' => 'node_resource_usage_logs'
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
				'columns' => array(
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
					)
				),
				'table' => 'node_user_node_request_destinations'
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
				'columns' => array(
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
					)
				),
				'table' => 'node_user_node_request_limit_rules'
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
				'columns' => array(
					'authentication_password' => array(
						'default' => null,
						'type' => 'VARCHAR(255)'
					),
					'authentication_username' => array(
						'default' => null,
						'type' => 'VARCHAR(255)'
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
				),
				'table' => 'node_users'
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
				'columns' => array(
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
						'type' => 'VARCHAR(8)'
					),
					'external_ip_address_version_4_usage' => array(
						'default' => null,
						'type' => 'VARCHAR(25)'
					),
					'external_ip_address_version_6' => array(
						'default' => null,
						'type' => 'VARCHAR(45)'
					),
					'external_ip_address_version_6_type' => array(
						'default' => null,
						'type' => 'VARCHAR(8)'
					),
					'external_ip_address_version_6_usage' => array(
						'default' => null,
						'type' => 'VARCHAR(25)'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'internal_ip_address_version_4' => array(
						'default' => null,
						'type' => 'VARCHAR(15)'
					),
					'internal_ip_address_version_4_usage' => array(
						'default' => null,
						'type' => 'VARCHAR(25)'
					),
					'internal_ip_address_version_6' => array(
						'default' => null,
						'type' => 'VARCHAR(45)'
					),
					'internal_ip_address_version_6_usage' => array(
						'default' => null,
						'type' => 'VARCHAR(25)'
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
				),
				'table' => 'nodes'
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
				'columns' => array(
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
				),
				'table' => 'system_resource_usage_logs'
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
				'columns' => array(
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
					'system_action' => array(
						'default' => null,
						'type' => 'VARCHAR(100)'
					),
					'system_user_authentication_token_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'system_user_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					)
				),
				'table' => 'system_user_authentication_token_scopes'
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
				'columns' => array(
					'address_range_start' => array(
						'default' => null,
						'type' => 'VARCHAR(45)'
					),
					'address_range_stop' => array(
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
				),
				'table' => 'system_user_authentication_token_sources'
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
				'columns' => array(
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
				),
				'table' => 'system_user_authentication_tokens'
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
				'columns' => array(
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
					'system_action' => array(
						'default' => null,
						'type' => 'VARCHAR(100)'
					),
					'system_user_authentication_token_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'system_user_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					)
				),
				'table' => 'system_user_request_logs'
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
				'columns' => array(
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
				),
				'table' => 'system_users'
			)
		)
	);

	function _connect($databases, $existingDatabases, $response) {
		foreach ($databases as $database) {
			if (
				(empty($existingDatabases) === false) &&
				(empty($existingDatabases[$database['structure']['table']]) === false)
			) {
				continue;
			}

			$response['_connect'][$database['structure']['table']] = $database['structure'];

			foreach ($database['authentication'] as $databaseAuthenticationIndex => $databaseAuthentication) {
				$response['_connect'][$database['structure']['table']]['connections'][$databaseAuthenticationIndex] = mysqli_connect($databaseAuthentication['hostname'], 'root', $databaseAuthentication['password'], 'ghostcompute');

				if ($response['_connect'][$database['structure']['table']]['connections'][$databaseAuthenticationIndex] === false) {
					$response['message'] = 'Error connecting to ' . $database['structure']['table'] . ' database, please try again.';
					unset($response['_connect']);
					_output($response);
				}
			}
		}

		$response = $response['_connect'];
		return $response;
	}

	function _count($parameters, $response) {
		$response['_count'] = 0;
		$command = 'SELECT COUNT(id) FROM ' . $parameters['in']['table'];

		if (empty($parameters['where']) === false) {
			$command .= ' WHERE ' . implode(' AND ', _parseCommandWhereConditions($parameters['where']));
		}

		foreach ($parameters['in']['connections'] as $connection) {
			$commandResponse = mysqli_query($connection, $command);

			if ($commandResponse === false) {
				$response['message'] = 'Error counting data in ' . $parameters['in']['table'] . ' database, please try again.';
				unset($response['_count']);
				_output($response);
			}

			$commandResponse = mysqli_fetch_assoc($commandResponse);

			if (is_int($commandResponse['COUNT(id)']) === false) {
				$response['message'] = 'Error counting data in ' . $parameters['in']['table'] . ' database, please try again.';
				unset($response['_count']);
				_output($response);
			}

			$response['_count'] += $commandResponse['COUNT(id)'];
		}

		$response = $response['_count'];
		return $response;
	}

	function _delete($parameters, $response) {
		$command = 'DELETE FROM ' . $parameters['in']['table'];

		if (empty($parameters['where']) === false) {
			$command .= ' WHERE ' . implode(' AND ', _parseCommandWhereConditions($parameters['where']));
		}

		foreach ($parameters['in']['connections'] as $connection) {
			$commandResponse = mysqli_query($connection, $command);

			if ($commandResponse === false) {
				$response['message'] = 'Error deleting data in ' . $parameters['in']['table'] . ' database, please try again.';
				_output($response);
			}
		}

		$response = true;
		return $response;
	}

	function _list($parameters, $response) {
		$response['_list'] = array();
		$command = 'SELECT * FROM ' . $parameters['in']['table'];

		if (empty($parameters['where']) === false) {
			$command .= ' WHERE ' . implode(' AND ', _parseCommandWhereConditions($parameters['where']));
		}

		if (empty($parameters['sort']) === false) {
			$command .= ' ORDER BY ';

			if ($parameters['sort'] === 'random') {
				$command .= 'RAND()';
			} elseif (
				(empty($parameters['sort']['key']) === false) &&
				($sortKey = $parameters['sort']['key'])
			) {
				if (empty($parameters['sort']['order']) === true) {
					$parameters['sort']['order'] = 'DESC';
				}

				$command .= $sortKey . ' ' . $parameters['sort']['order'] . ', id DESC';
			}
		}

		if (empty($parameters['limit']) === false) {
			$command .= ' LIMIT ' . $parameters['limit'];
		}

		if (empty($parameters['offset']) === false) {
			$command .= ' OFFSET ' . $parameters['offset'];
		}

		foreach ($parameters['in']['connections'] as $connectionIndex => $connection) {
			$commandResponse = mysqli_query($connection, $command);

			if ($commandResponse === false) {
				$response['message'] = 'Error listing data in ' . $parameters['in']['table'] . ' database, please try again.';
				unset($response['_list']);
				_output($response);
			}

			$response['_list'][$connectionIndex] = mysqli_fetch_assoc($commandResponse);

			if ($response[$connectionIndex] === false) {
				$response['message'] = 'Error listing data in ' . $parameters['in']['table'] . ' database, please try again.';
				unset($response['_list']);
				_output($response);
			}
		}

		if (isset($response['_list'][1]) === false) {
			$response['_list'] = current($response['_list']);
		}

		$response = $response['_list'];
		return $response;
	}

	function _parseCommandWhereConditions($parameters, $conjunction = 'AND') {
		foreach ($parameters['where'] as $whereConditionKey => $whereConditionValue) {
			if ($whereConditionKey === 'OR') {
				$conjunction = $whereConditionKey;
			}

			if (
				(is_array($whereConditionValue) === true) &&
				(count($whereConditionValue) !== count($whereConditionValue, COUNT_RECURSIVE))
			) {
				$recursiveParameters = array(
					'where' => $whereConditionValue
				);
				$parameters['where'][$whereConditionKey] = '(' . implode(') ' . $conjunction . ' (', _parseCommandWhereConditions($recursiveParameters, $conjunction)) . ')';
			} else {
				if (
					(($conjunction === $whereConditionKey) === false) &&
					(isset($parameters['in']['settings']['structure'][substr($whereConditionKey, 0, strpos($whereConditionKey, ' '))]) === false)
				) {
					unset($parameters['where'][$whereConditionKey]);
					continue;
				}

				if (is_array($whereConditionValue) === false) {
					$whereConditionValue = array(
						$whereConditionValue
					);
				}

				$whereConditionValueConditions = array();

				foreach ($whereConditionValue as $whereConditionValueKey => $whereConditionValueValue) {
					if (is_bool($whereConditionValueValue) === true) {
						$whereConditionValueValue = intval($whereConditionValueValue);
					}

					if (is_null($whereConditionValueValue) === true) {
						$whereConditionValueValue = 'IS NULL';
					}

					$whereConditionValue[$whereConditionValueKey] = $whereConditionValueValue;

					if ($conjunction === $whereConditionKey) {
						$whereConditionValueValueCondition = 'IN';

						if (is_int(strpos($whereConditionValueKey, ' !=')) === true) {
							$whereConditionValueKey = substr($whereConditionValueKey, 0, strpos($whereConditionValueKey, ' '));
							$whereConditionValueValueCondition = 'NOT ' . $whereConditionValueValueCondition;
						}

						$whereConditionValueConditions[] = $whereConditionValueKey . ' ' . $whereConditionValueValueCondition . " ('" . str_replace("'", "\'", $whereConditionValueValue) . "')";
					}
				}

				if (empty($whereConditionValueConditions) === true) {
					if (
						(strpos($whereConditionKey, ' >') !== false) ||
						(strpos($whereConditionKey, ' <') !== false)
					) {
						$whereConditionValueConditions[] = $whereConditionKey . ' ' . str_replace("'", "\'", current($whereConditionValue));
					} else {
						$whereConditionValueCondition = 'IN';
						$whereConditionValueKey = $whereConditionKey;

						if (is_int(strpos($whereConditionValueKey, ' !=')) === true) {
							$whereConditionValueKey = substr($whereConditionValueKey, 0, strpos($whereConditionValueKey, ' '));
							$whereConditionValueCondition = 'NOT ' . $whereConditionValueCondition;
						}

						$whereConditionValueConditions[] = $whereConditionValueKey . ' ' . $whereConditionValueCondition . " ('" . implode("','", str_replace("'", "\'", $whereConditionValue)) . "')";
					}
				}

				$parameters['where'][$whereConditionKey] = '(' . implode(' ' . $conjunction . ' ', $whereConditionValueConditions) . ')';
			}
		}

		$response = $parameters['where'];
		return $response;
	}

	function _save($parameters, $response) {
		if (empty($parameters['data']) === false) {
			if (is_numeric(key($parameters['data'])) === false) {
				$parameters['data'] = array(
					$parameters['data']
				);
			}

			$connectionIndex = 0;

			foreach ($parameters['data'] as $data) {
				$dataInsertValues = $dataKeys = $dataUpdateValues = '';
				$timestamp = date('Y-m-d H:i:s', time());

				foreach ($data as $dataKey => $dataValue) {
					$dataInsertValue = str_replace("'", "\'", str_replace('\\', '\\\\', $dataValue));
					$dataInsertValues .= "','" . $dataInsertValue;
					$dataKeys .= ',' . $dataKey;
					$dataUpdateValues .= "," . $dataKey . "='" . $dataInsertValue . "'";
				}

				if (empty($data['modified_date']) === true) {
					$dataInsertValues .= "','" . $timestamp;
					$dataKeys .= ',modified_date';
					$dataUpdateValues .= ",modified_date='" . $timestamp . "'";
				}

				if (empty($data['id']) === true) {
					$dataInsertValues .= "','" . $timestamp;
					$dataKeys .= ',created_date';
					$dataUpdateValues = '';
				} else {
					$dataUpdateValues = ' ON DUPLICATE KEY UPDATE ' . substr($dataUpdateValues, 1);
				}

				$commandResponse = mysqli_query($parameters['in']['connections'][$connectionIndex], 'INSERT INTO ' . $parameters['in']['table'] . '(' . substr($dataKeys, 1) . ") VALUES (" . substr($dataInsertValues, 2) . "')" . $dataUpdateValues);

				if ($commandResponse === false) {
					$response['message'] = 'Error saving data in ' . $parameters['in']['table'] . ' database, please try again.';
					_output($response);
				}

				if (empty($parameters['in']['connections'][1]) === false) {
					$connectionIndex++;

					if (empty($parameters['in']['connections'][$connectionIndex]) === true) {
						$connectionIndex = 0;
					}
				}
			}
		}

		$response = true;
		return $response;
	}

	function _update($parameters, $response) {
		if (empty($parameters['data']) === false) {
			$command = 'UPDATE ' . $parameters['in']['table'] . ' SET ';

			foreach ($parameters['data'] as $updateValueKey => $updateValue) {
				$command .= $updateValueKey . "='" . str_replace("'", "\'", $updateValue) . "',";
			}

			$command = rtrim($command, ',') . ' WHERE ' . implode(' AND ', _parseCommandWhereConditions($parameters['where']));

			foreach ($parameters['in']['connections'] as $connection) {
				$commandResponse = mysqli_query($connection, $command);

				if ($commandResponse === false) {
					$response['message'] = 'Error updating data in ' . $parameters['in']['table'] . ' database, please try again.';
					_output($response);
				}
			}
		}

		$response = true;
		return $response;
	}

	$parameters['databases'] = _connect(array(
		$databases['system_user_authentication_token_scopes'],
		$databases['system_user_authentication_token_sources'],
		$databases['system_user_authentication_tokens'],
		$databases['system_users']
	), false, $response);
?>
