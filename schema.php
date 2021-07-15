<?php
	// refactoring is always worth it
	// todo: parse - from empty usernames in logs
	// todo: allow nameserver process editing (source ip, port, listening ip, etc)
	// todo: make sure externally-hosted public dns (with only a listening IP) still works
	// todo: delete background action processing code and limit selected items to 10000
		// retain item indexes for front-end selection speed
		// users who want to update more than 10000 items at once can use the API (a company using 10000 ips would probably already have a developer implement the API)
	// todo: add deploy_ prefix to all deployment files in /assets/php/
	// todo: refactor all php functions to avoid ! shortcode, instead use === boolean
	// todo: deploy custom nameservers (non-caching) in website.php instead of relying on each cloud hosts default nameservers
	// todo: make sure tinyint boolean default values are saved as boolean type in database.php
	// todo: add search function to /servers/id page
	// todo: forward internal server node ips to external ip if the internal ip is private and isn't on the primary interface
	// todo: consistently add status_ prefix to all boolean columns that represent status (processing, limiting, removed, etc)
	// todo: change all url_request_log names to request_log since URLs will be an optional field when DNS + reverse proxies are included
	$schema = array(
		/*'proxies' => array(
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
		),*/
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
			'target_ip' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(30)'
			),
			'target_url' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(1000)'
			),
			'username' => array(
				'default' => "'-'",
				'type' => 'VARCHAR(15)'
			)
		),
		'servers' => array(
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'type' => 'DATETIME'
			),
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
			'modified' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'type' => 'DATETIME'
			),
			'removed' => array(
				'default' => 0,
				'type' => 'TINYINT(1)'
			),
			'server_node_count' => array(
				'default' => 1,
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
		'server_nameserver_processes' => array(
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'type' => 'DATETIME'
			),
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
				'type' => 'DATETIME'
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
		'server_nodes' => array(
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
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
				'type' => 'DATETIME'
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
			'status_active' => array(
				'default' => 0,
				'null' => true,
				'type' => 'TINYINT(1)'
			),
			'status_processing' => array(
				'default' => 0,
				'null' => true,
				'type' => 'TINYINT(1)'
			),
			'type' => array(
				'default' => "'proxy'",
				'null' => true,
				'type' => 'CHAR(10)'
			)
		),
		'server_node_users' => array(
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
			'user_id' => array(
				'default' => null,
				'null' => true,
				'type' => 'BIGINT(11)'
			)
		),
		'server_proxy_processes' => array(
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
		'tokens' => array(
			'created' => array(
				'default' => 'CURRENT_TIMESTAMP',
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
				'primary_key' => true,
				'type' => 'BIGINT(11)'
			),
			'modified' => array(
				'default' => 'CURRENT_TIMESTAMP',
				'type' => 'DATETIME'
			),
			'string' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(255)'
			)
		),
		'unauthorized_request_logs' => array( // todo: add unauthorized request logging / limiting to server_node_users to prevent proxy username:password brute forcing
			'client_ip' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(30)'
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
			'unauthorized_request_count' => array(
				'default' => 0,
				'type' => 'TINYINT(1)'
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
			'status_allowing_requests' => array( // todo: use in combination with rate limiting specific URLs / IPs to only allow or block access to specific URLs / IPs
				'default' => 0,
				'type' => 'TINYINT(1)'
			),
			'status_blocking_requests' => array(
				'default' => 0,
				'type' => 'TINYINT(1)'
			),
			'status_logging_requests' => array(
				'default' => 0,
				'null' => true,
				'type' => 'TINYINT(1)'
			)
		)
	);
?>
