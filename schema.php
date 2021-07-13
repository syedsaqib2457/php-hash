<?php
	// todo: add search function to /servers/id page
	// todo: forward internal server node ips to external ip if the internal ip is private and isn't on the primary interface
	// todo: consistently add status_ prefix to all boolean columns that represent status (processing, limiting, etc)
	// todo: change all url_request_log names to request_log since URLs will be an optional field when DNS + reverse proxies are included
	$schema = array(
		'actions' => array(
			'chunks' => array(
				'default' => null,
				'null' => true,
				'type' => 'INT(5)'
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
				'primary_key' => true,
				'type' => 'BIGINT(11)'
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
				'type' => 'INT(3)'
			)
		),
		'proxies' => array(
			'block_all_urls' => array(
				'default' => 0,
				'type' => 'TINYINT(1)'
			),
			'enable_url_request_logs' => array(
				'default' => 0,
				'null' => true,
				'type' => 'TINYINT(1)'
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
			'only_allow_urls' => array(
				'default' => 0,
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
		'proxy_urls' => array(
			'id' => array(
				'auto_increment' => true,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'removed' => array(
				'default' => 0,
				'type' => 'TINYINT(1)'
			),
			'url' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(1000)'
			)
		),
		'proxy_url_request_limitation_proxies' => array(
			'id' => array(
				'auto_increment' => true,
				'primary_key' => true,
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
			'id' => array(
				'auto_increment' => true,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'limited' => array(
				'default' => 0,
				'null' => true,
				'type' => 'TINYINT(1)'
                        ),
			'previous_limitation_date' => array(
				'default' => null,
				'null' => true,
				'type' => 'DATETIME'
			),
			'proxy_url_block_interval_type' => array(
				'default' => "'minute'",
				'type' => 'VARCHAR(255)'
			),
			'proxy_url_block_interval_value' => array(
				'default' => 1,
				'type' => 'SMALLINT(3)'
			),
			'proxy_url_request_interval_type' => array(
				'default' => "'minute'",
				'type' => 'VARCHAR(255)'
			),
			'proxy_url_request_interval_value' => array(
				'default' => 1,
				'type' => 'SMALLINT(3)'
			),
			'proxy_url_request_number' => array(
				'default' => 1,
				'type' => 'BIGINT(11)'
			),
			'removed' => array(
				'default' => 0,
				'type' => 'TINYINT(1)'
			)
		),
		'proxy_url_request_logs' => array(
			'bytes_received' => array(
				'type' => 'BIGINT(11)'
			),
			'bytes_sent' => array(
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
			'id' => array(
				'auto_increment' => true,
				'primary_key' => true,
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
			'username' => array(
				'default' => "'-'",
				'type' => 'VARCHAR(15)'
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
		'public_request_limitations' => array(
			'client_ip' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(30)'
			),
			'id' => array(
				'auto_increment' => true,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'request_attempts' => array(
				'default' => null,
				'null' => true,
				'type' => 'TINYINT(1)'
			)
		),
		'servers' => array(
			'id' => array(
				'auto_increment' => true,
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
			'removed' => array(
				'default' => 0,
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
			'id' => array(
				'auto_increment' => true,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'listening_ip' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(100)'
			),
			'removed' => array(
				'default' => 0,
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
				'type' => 'BIGINT(11)'
			)
		),
		'server_nameserver_processes' => array(
			'external_source_ip' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(100)'
			),
			'id' => array(
				'auto_increment' => true,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'local' => array(
				'default' => 1,
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
			'removed' => array(
				'default' => 0,
				'type' => 'TINYINT(1)'
			),
			'server_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			)
		),
		'server_nodes' => array(
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
			'processing' => array(
				'default' => 0,
				'type' => 'TINYINT(1)'
			),
			'removed' => array(
				'default' => 0,
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
			'id' => array(
				'auto_increment' => true,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'port' => array(
				'default' => null,
				'null' => true,
				'type' => 'INT(5)'
			),
			'removed' => array(
				'default' => 0,
				'type' => 'TINYINT(1)'
			),
			'server_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			)
		),
		'settings' => array(
			'id' => array(
				'primary_key' => true,
				'type' => 'VARCHAR(255)'
			),
			'value' => array(
				'type' => 'VARCHAR(255)'
			)
		),
		'tokens' => array(
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
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'string' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(255)'
			)
		),
		'users' => array(
			'id' => array(
				'auto_increment' => true,
				'primary_key' => true,
				'type' => 'BIGINT(11)'
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
