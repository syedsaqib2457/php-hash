<?php
	// todo: add all database structures to system_databases during installation + make every column a string varchar for simplicity with dynamically-adjusting maximum length for memory optimization

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
					'created_timestamp' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'modified_timestamp' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
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
					'created_timestamp' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'modified_timestamp' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
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
					),
					'node_user_status_node_request_destinations_only_allowed' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					),
					'node_user_status_node_request_logs_allowed' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					),
					'node_user_status_strict_authentication_required' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
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
		'node_user_authentication_credentials' => array(
			'authentication' => array(
				array(
					'hostname' => 'localhost',
					'password' => 'password'
				)
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'modified_timestamp' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_user_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'password' => array(
						'default' => null,
						'type' => 'VARCHAR(255)'
					),
					'username' => array(
						'default' => null,
						'type' => 'VARCHAR(255)'
					)
				)
			),
			'table' => 'node_user_authentication_credentials'
		),
		'node_user_authentication_sources' => array(
			'authentication' => array(
				array(
					'hostname' => 'localhost',
					'password' => 'password'
				)
			),
			'structure' => array(
				'columns' => array(
					'created_timestamp' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'ip_address' => array(
						'default' => null,
						'type' => 'VARCHAR(45)'
					),
					'ip_address_block_length' => array(
						'default' => null,
						'type' => 'TINYINT(1)'
					),
					'ip_address_version' => array(
						'default' => null,
						'type' => 'TINYINT(1)'
					),
					'modified_timestamp' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'node_user_id' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					)
				),
				'table' => 'node_user_authentication_sources'
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
					'node_request_destination_address' => array(
						'default' => null,
						'type' => 'VARCHAR(1000)'
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
					'status_node_request_destinations_only_allowed' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					),
					'status_node_request_logs_allowed' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					),
					'status_strict_authentication_required' => array(
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
					'cpu_capacity_megahertz' => array(
						'default' => null,
						'type' => 'BIGINT(11)'
					),
					'cpu_core_count' => array(
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
						'type' => 'VARCHAR(25)'
					),
					'external_ip_address_version_6' => array(
						'default' => null,
						'type' => 'VARCHAR(45)'
					),
					'external_ip_address_version_6_type' => array(
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
					'internal_ip_address_version_4_type' => array(
						'default' => null,
						'type' => 'VARCHAR(25)'
					),
					'internal_ip_address_version_6' => array(
						'default' => null,
						'type' => 'VARCHAR(45)'
					),
					'internal_ip_address_version_6_type' => array(
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
					'processing_progress_checkpoint' => array(
						'default' => null,
						'type' => 'VARCHAR(100)'
					),
					'processing_progress_percentage' => array(
						'default' => 0,
						'type' => 'TINYINT(1)'
					),
					'status_activated' => array(
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
					'status_processing' => array(
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
					'created_date' => array(
						'default' => 'CURRENT_TIMESTAMP',
						'type' => 'DATETIME'
					),
					'id' => array(
						'primary_key' => true,
						'type' => 'BIGINT(11)'
					),
					'ip_address_range_start' => array(
						'default' => null,
						'type' => 'VARCHAR(45)'
					),
					'ip_address_range_stop' => array(
						'default' => null,
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
		$command = 'select count(id) from ' . $parameters['in']['table'];

		if (empty($parameters['where']) === false) {
			$command .= ' where ' . implode(' and ', _parseCommandWhereConditions($parameters['where']));
		}

		foreach ($parameters['in']['connections'] as $connection) {
			$commandResponse = mysqli_query($connection, $command);

			if ($commandResponse === false) {
				$response['message'] = 'Error counting data in ' . $parameters['in']['table'] . ' database, please try again.';
				unset($response['_count']);
				_output($response);
			}

			$commandResponse = mysqli_fetch_assoc($commandResponse);

			if ($commandResponse === false) {
				$response['message'] = 'Error counting data in ' . $parameters['in']['table'] . ' database, please try again.';
				unset($response['_count']);
				_output($response);
			}

			$commandResponse = current($commandResponse);

			if (is_int($commandResponse) === false) {
				$response['message'] = 'Error counting data in ' . $parameters['in']['table'] . ' database, please try again.';
				unset($response['_count']);
				_output($response);
			}

			$response['_count'] += $commandResponse;
		}

		$response = $response['_count'];
		return $response;
	}

	function _delete($parameters, $response) {
		$command = 'delete from ' . $parameters['in']['table'];

		if (empty($parameters['where']) === false) {
			$command .= ' where ' . implode(' and ', _parseCommandWhereConditions($parameters['where']));
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
		$databaseColumns = '*';

		if (empty($parameters['columns']) === false) {
			$databaseColumns = '';

			foreach ($parameters['columns'] as $databaseColumn) {
				$databaseColumns .= $databaseColumn . ',';
			}
		}

		$command = 'select ' . rtrim($databaseColumns, ',') . ' from ' . $parameters['in']['table'];

		if (empty($parameters['where']) === false) {
			$command .= ' where ' . implode(' and ', _parseCommandWhereConditions($parameters['where']));
		}

		if (empty($parameters['sort']) === false) {
			$command .= ' order by ';

			if ($parameters['sort'] === 'random') {
				$command .= 'rand()';
			} elseif (empty($parameters['sort']['key']) === false) {
				if (empty($parameters['sort']['order']) === true) {
					$parameters['sort']['order'] = 'desc';
				} else {
					$sortOrders = array(
						'ascending' => 'asc',
						'descending' => 'desc'
					);
					$parameters['sort']['order'] = $sortOrders[$parameters['sort']['order']];
				}

				$command .= $parameters['sort']['key'] . ' ' . $parameters['sort']['order'] . ', id DESC';
			}
		}

		if (empty($parameters['limit']) === false) {
			$command .= ' limit ' . $parameters['limit'];
		}

		if (empty($parameters['offset']) === false) {
			$command .= ' offset ' . $parameters['offset'];
		}

		foreach ($parameters['in']['connections'] as $connectionIndex => $connection) {
			$commandResponse = mysqli_query($connection, $command);

			if ($commandResponse === false) {
				$response['message'] = 'Error listing data in ' . $parameters['in']['table'] . ' database, please try again.';
				unset($response['_list']);
				_output($response);
			}

			$response['_list'][$connectionIndex] = mysqli_fetch_assoc($commandResponse);

			if ($response['_list'][$connectionIndex] === false) {
				$response['message'] = 'Error listing data in ' . $parameters['in']['table'] . ' database, please try again.';
				unset($response['_list']);
				_output($response);
			}
		}

		$response = $response['_list'];
		return $response;
	}

	function _parseCommandWhereConditions($parameters, $whereConditionConjunction = 'and') {
		foreach ($parameters['where'] as $whereConditionKey => $whereConditionValue) {
			if ($whereConditionKey === 'either') {
				$whereConditionConjunction = 'or';
			}

			if (
				(is_array($whereConditionValue) === true) &&
				((count($whereConditionValue) === count($whereConditionValue, true)) === false)
			) {
				$recursiveParameters = array(
					'where' => $whereConditionValue
				);
				$parameters['where'][$whereConditionKey] = '(' . implode(') ' . $conjunction . ' (', _parseCommandWhereConditions($recursiveParameters, $conjunction)) . ')';
			} else {
				if (
					(($whereConditionConjunction === 'either') === false) &&
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
						$whereConditionValueValue = 'is null';
					}

					$whereConditionValue[$whereConditionValueKey] = $whereConditionValueValue;

					if (($whereConditionKey === 'either') === true) {
						$whereConditionValueValueCondition = 'in';

						if (is_int(strpos($whereConditionValueKey, ' !=')) === true) {
							$whereConditionValueKey = substr($whereConditionValueKey, 0, strpos($whereConditionValueKey, ' '));
							$whereConditionValueValueCondition = 'not ' . $whereConditionValueValueCondition;
						}

						$whereConditionValueConditions[] = $whereConditionValueKey . ' ' . $whereConditionValueValueCondition . " ('" . str_replace("'", "\'", $whereConditionValueValue) . "')";
					}
				}

				if (empty($whereConditionValueConditions) === true) {
					if (
						(is_int(strpos($whereConditionKey, ' >')) === true) ||
						(is_int(strpos($whereConditionKey, ' <')) === true)
					) {
						$whereConditionValueConditions[] = $whereConditionKey . ' ' . str_replace("'", "\'", current($whereConditionValue));
					} else {
						$whereConditionValueCondition = 'in';
						$whereConditionValueKey = $whereConditionKey;

						if (is_int(strpos($whereConditionValueKey, ' !=')) === true) {
							$whereConditionValueKey = substr($whereConditionValueKey, 0, strpos($whereConditionValueKey, ' '));
							$whereConditionValueCondition = 'not ' . $whereConditionValueCondition;
						}

						$whereConditionValueConditions[] = $whereConditionValueKey . ' ' . $whereConditionValueCondition . " ('" . implode("','", str_replace("'", "\'", $whereConditionValue)) . "')";
					}
				}

				$parameters['where'][$whereConditionKey] = '(' . implode(' ' . $whereConditionConjunction . ' ', $whereConditionValueConditions) . ')';
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
			$timestamp = time();

			foreach ($parameters['data'] as $data) {
				$dataInsertValues = $dataKeys = $dataUpdateValues = '';

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
					$dataUpdateValues = ' on duplicate key update ' . substr($dataUpdateValues, 1);
				}

				$commandResponse = mysqli_query($parameters['in']['connections'][$connectionIndex], 'insert into ' . $parameters['in']['table'] . '(' . substr($dataKeys, 1) . ") values (" . substr($dataInsertValues, 2) . "')" . $dataUpdateValues);

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
			$command = 'update ' . $parameters['in']['table'] . ' set ';

			if (isset($parameters['data']['modified']) === false) {
				$parameters['data']['modified'] = time();
			}

			foreach ($parameters['data'] as $updateValueKey => $updateValue) {
				$command .= $updateValueKey . "='" . str_replace("'", "\'", $updateValue) . "',";
			}

			$command = rtrim($command, ',') . ' where ' . implode(' and ', _parseCommandWhereConditions($parameters['where']));

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
