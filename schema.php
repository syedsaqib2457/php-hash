<?php
	// refactoring is always worth it
	// todo: increase tcp-clients and allow public-facing dns over both tcp and udp (previously was private with udp only and tcp for health checks)
	// todo: combine server_proxy_processes and server_nameserver_processes into node_processes
	// todo: delete servers table, use status_main_ip for nodes table, additional node ips should have node_id with the main node, relational tables for server statistics should be node_usage, node_etc etc
	// todo: remove blackhat DNS source IP rotation on every request feature, only allow load balancing on local ips with one source IP
		// use a combination of random ports + range of listening ips [start-end] to allow a matrix of processes greater than 60000
		// handle local source ip conflicts automatically with connection.php process
	// todo: rename $defaultMessage to $defaultResponseMessageText
	// todo: set a combined maximum of 55000 proxy + nameserver processes (allow binding proxy ips to local ips instead of using ports for load balancing to exceed 55000 processes, try grouping with ipset and dnat random)
	// todo: add automated server deployment for each cloud service that allows it with an api key
	// todo: create optional automatic update scripts as gists for version 18 to 19, 19 to 20, etc so complete reinstall isn't required
	// todo: add each of these as GitHub issues
	// todo: test the maximum amount of iptables statistic nth mode before performance degradation
	// todo: test iptables statistic with --random flag when proxy process count is above X count for automatic process scaling
	// todo: test iptables with ipset instead of repeating rules with chunks of dports
	// todo: allow node_external_ip to be private ip for proxy processes on a public server (automatically deletes internal_ip value if set to different public/private ip)
	// todo: only allow local nameserver processes with public external_source_ip and allow public listening ips with whitelist (nameserver processes with the same public listening ip but different external source ips will still load balance with different source ips)
	// todo: avoid variable assignments in conditional unless that variable is used in the same conditional
	// todo: add "Enable IPv6" checkbox option in Account section, disable for control panel by default
	// todo: auto-detect if server is ipv4 or ipv6 only for deployment command URLs + add main_ipv4 and main_ipv6 instead of base_domain in configuration.php settings
	// todo: delete ip_version fields, create ipv4 and ipv6 versions of both internal_ip and external_ip fields
	// todo: add primary ipv4 and ipv6 fields to servers instead of ip field
	// todo: show live ipv4_count and ipv6 count instead of using a static ip_count field for servers
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
		),*/
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
			'protocol' => array(
				'default' => null,
				'null' => true,
				'type' => 'SMALLINT(3)'
			),
			'removed' => array(
				'default' => 0,
				'type' => 'TINYINT(1)'
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
			'removed' => array(
				'default' => 0,
				'type' => 'TINYINT(1)'
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
			'target' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(1000)'
			),
			'type' => array(
				'default' => null,
				'null' => true,
				'type' => 'CHAR(10)'
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
