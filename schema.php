<?php
	$schema = array(
		'actions' => array(
			'chunks' => array(
				'default' => null,
				'null' => true,
				'type' => 'INT(5)'
			),
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'encoded_items_processed' => array(
				'default' => null,
				'null' => true,
				'type' => 'TEXT'
			),
			'encoded_items_to_process' => array(
				'default' => null,
				'null' => true,
				'type' => 'TEXT'
			),
			'encoded_parameters' => array(
				'default' => null,
				'null' => true,
				'type' => 'TEXT'
			),
			'id' => array(
				'auto_increment' => true,
				'null' => false,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'modified' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'processed' => array(
				'default' => 0,
				'null' => true,
				'type' => 'TINYINT(1)'
			),
			'processing' => array(
				'default' => 0,
				'null' => true,
				'type' => 'TINYINT(1)'
			),
			'progress' => array(
				'default' => 0,
				'null' => false,
				'type' => 'INT(3)'
			)
		),
		'proxies' => array(
			'block_all_urls' => array(
				'default' => 0,
				'null' => false,
				'type' => 'TINYINT(1)'
			),
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'external_ip' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(30)'
			),
			'external_ip_version' => array(
				'default' => 4,
				'null' => true,
				'type' => 'TINYINT(1)'
			),
			'id' => array(
				'auto_increment' => true,
				'null' => false,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'internal_ip' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(30)'
			),
			'internal_ip_version' => array(
				'default' => 4,
				'null' => true,
				'type' => 'TINYINT(1)'
			),
			'modified' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'index' => true,
				'null' => false,
				'type' => 'DATETIME'
			),
			'only_allow_urls' => array(
				'default' => 0,
				'null' => false,
				'type' => 'TINYINT(1)'
			),
			'password' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(40)'
			),
			'server_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			),
			'server_node_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			),
			'status' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(10)'
			),
			'username' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(40)'
			),
			'whitelisted_ips' => array(
				'default' => null,
				'null' => true,
				'type' => 'TEXT'
			)
		),
		'proxy_authentications' => array(
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'id' => array(
				'auto_increment' => true,
				'null' => false,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'modified' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'proxy_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			),
			'server_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			),
			'username' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(40)'
			),
			'whitelisted_ips' => array(
				'default' => null,
				'null' => true,
				'type' => 'TEXT'
			)
		),
		'proxy_urls' => array(
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'id' => array(
				'auto_increment' => true,
				'null' => false,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'modified' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'removed' => array(
				'default' => 0,
				'null' => false,
				'type' => 'TINYINT(1)'
			),
			'url' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(1000)'
			)
		),
		'proxy_url_request_limitation_proxies' => array(
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'id' => array(
				'auto_increment' => true,
				'null' => false,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'modified' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'proxy_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			),
			'proxy_url_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			),
			'proxy_url_request_limitation_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			),
			'server_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			)
		),
		'proxy_url_request_limitations' => array(
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'id' => array(
				'auto_increment' => true,
				'null' => false,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'modified' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'previous_limitation_date' => array(
				'default' => null,
				'null' => true,
				'type' => 'DATETIME'
			),
			'proxy_url_block_interval_type' => array(
				'default' => "'minute'",
				'null' => false,
				'type' => 'VARCHAR(255)'
			),
			'proxy_url_block_interval_value' => array(
				'default' => 1,
				'null' => false,
				'type' => 'SMALLINT(3)'
			),
			'proxy_url_request_interval_type' => array(
				'default' => "'minute'",
				'null' => false,
				'type' => 'VARCHAR(255)'
			),
			'proxy_url_request_interval_value' => array(
				'default' => 1,
				'null' => false,
				'type' => 'SMALLINT(3)'
			),
			'proxy_url_request_number' => array(
				'default' => 1,
				'null' => false,
				'type' => 'BIGINT(11)'
			),
			'removed' => array(
				'default' => 0,
				'null' => false,
				'type' => 'TINYINT(1)'
			),
			'status_limited' => array(
				'default' => 0,
				'null' => true,
				'type' => 'TINYINT(1)'
			)
		),
		'proxy_url_request_logs' => array(
			'bytes_received' => array(
				'null' => false,
				'type' => 'BIGINT(11)'
			),
			'bytes_sent' => array(
				'null' => false,
				'type' => 'BIGINT(11)'
			),
			'client_ip' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(30)'
			),
			'code' => array(
				'default' => null,
				'null' => true,
				'type' => 'SMALLINT(3)'
			),
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'id' => array(
				'auto_increment' => true,
				'null' => false,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'modified' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'proxy_authentication_id' => array(
                                'default' => null,
                                'null' => true,
                                'type' => 'BIGINT(11)'
                        ),
			'proxy_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			),
			'proxy_url_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			),
			'server_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			),
			'target_ip' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(30)'
			),
			'target_url' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(1000)'
			)
		),
		'servers' => array(
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'id' => array(
				'auto_increment' => true,
				'null' => false,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'ip' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(30)'
			),
			'ip_count' => array(
				'default' => 1,
				'null' => true,
				'type' => 'BIGINT(11)'
			),
			'modified' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'removed' => array(
				'default' => 0,
				'null' => false,
				'type' => 'TINYINT(1)'
			),
			'status_activated' => array(
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
		'server_nameserver_listening_ips' => array(
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'id' => array(
				'auto_increment' => true,
				'null' => false,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'listening_ip' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(100)'
			),
			'modified' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'removed' => array(
				'default' => 0,
				'null' => false,
				'type' => 'TINYINT(1)'
			),
			'server_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			),
			'server_nameserver_process_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			),
			'source_ip_count' => array(
				'default' => 1,
				'null' => false,
				'type' => 'BIGINT(11)'
			)
		),
		'server_nameserver_processes' => array(
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'external_source_ip' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(100)'
			),
			'id' => array(
				'auto_increment' => true,
				'null' => false,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'local' => array(
				'default' => 1,
				'null' => false,
				'type' => 'TINYINT(1)'
			),
			'internal_source_ip' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(100)'
			),
			'listening_ip' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(100)'
			),
			'modified' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'removed' => array(
				'default' => 0,
				'null' => false,
				'type' => 'TINYINT(1)'
			),
			'server_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			)
		),
		'server_nodes' => array(
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'external_ip' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(30)'
			),
			'external_ip_version' => array(
				'default' => 4,
				'null' => true,
				'type' => 'TINYINT(1)'
			),
			'id' => array(
				'auto_increment' => true,
				'null' => false,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'internal_ip' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(30)'
			),
			'internal_ip_version' => array(
				'default' => 4,
				'null' => true,
				'type' => 'TINYINT(1)'
			),
			'modified' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'processing' => array(
				'default' => 0,
				'null' => false,
				'type' => 'TINYINT(1)'
			),
			'removed' => array(
				'default' => 0,
				'null' => false,
				'type' => 'TINYINT(1)'
			),
			'server_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			),
			'status' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(10)'
			)
		),
		'server_proxy_processes' => array(
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'id' => array(
				'auto_increment' => true,
				'null' => false,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'modified' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'port' => array(
				'default' => null,
				'null' => true,
				'type' => 'INT(5)'
			),
			'removed' => array(
				'default' => 0,
				'null' => false,
				'type' => 'TINYINT(1)'
			),
			'server_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			)
		),
		'settings' => array(
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'id' => array(
				'null' => false,
				'primary_key' => true,
				'type' => 'VARCHAR(255)'
			),
			'modified' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'value' => array(
				'null' => false,
				'type' => 'VARCHAR(255)'
			)
		),
		'tokens' => array(
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'encoded_parameters' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(10000)'
			),
			'expiration' => array(
				'default' => null,
				'null' => true,
				'type' => 'DATETIME'
			),
			'foreign_key' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(255)'
			),
			'foreign_value' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(255)'
			),
			'id' => array(
				'auto_increment' => true,
				'null' => false,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'modified' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'string' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(255)'
			)
		),
		'users' => array(
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'id' => array(
				'auto_increment' => true,
				'null' => false,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'modified' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'null' => false,
				'type' => 'DATETIME'
			),
			'password' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(255)'
			),
			'whitelisted_ips' => array(
				'default' => null,
				'null' => true,
				'type' => 'TEXT'
			)
		)
	);
?>
