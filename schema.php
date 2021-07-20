<?php
	// refactoring is always worth it
	// todo: create optional automatic update scripts for versions above 19
	// todo: increase tcp-clients and allow public-facing dns over both tcp and udp (previously was private with udp only and tcp for health checks)
	// todo: combine server_proxy_processes and server_nameserver_processes into node_processes
	// todo: delete servers table, use status_main_ip for nodes table, additional node ips should have node_id with the main node, relational tables for server statistics should be node_usage, node_etc etc
	// todo: remove blackhat DNS source IP rotation on every request feature, only allow load balancing on local ips with one source IP
		// use a combination of random ports + range of listening ips [start-end] to allow a matrix of processes greater than 60000
		// handle local source ip conflicts automatically with connection.php process
	// todo: rename $defaultMessage to $defaultResponseMessageText
	// todo: set a combined maximum of 55000 proxy + nameserver processes (allow binding proxy ips to local ips instead of using ports for load balancing to exceed 55000 processes, try grouping with ipset and dnat random)
	// todo: add automated server deployment for each cloud service that allows it with an api key
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
	// todo: consistently add status_ prefix to all boolean columns that represent status (active, limited, etc)
	// todo: change all url_request_log names to request_log since URLs will be an optional field when DNS + reverse proxies are included

	$schema = array(
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
			'destination' => array(
				'default' => null,
				'null' => true,
				'type' => 'VARCHAR(255)'
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
			),
			'type' => array(
				'default' => null,
				'null' => true,
				'type' => 'CHAR(10)'
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
				'type' => 'BIGINT(11)'
			),
			'bytes_sent' => array(
				'type' => 'BIGINT(11)'
			),
			'code' => array(
				'default' => null,
				'null' => true,
				'type' => 'SMALLINT(3)'
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
			)
		)
	);
?>
