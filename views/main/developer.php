<?php
	$styleSheets = array(
		'/assets/css/default.css'
	);
	require_once($configuration->settings['base_path'] . '/models/main.php');
	require_once($configuration->settings['base_path'] . '/assets/php/header.php');
?>
<main process="developer">
	<section class="section">
		<div class="container">
			<h1>Developer</h1>
			<div class="content-container">
				<p>Automate SOCKS proxy control panel functions with this built-in developer API.</p>
				<p>Requests are authenticated using the <a class="no-margin" href="/servers?#password">account</a> IP whitelist.</p>
				<p>POST data is sent as a JSON-encoded string in a key named <strong>json</strong>.</p>
				<p>Here's an example request using <strong>wget</strong>.</p>
				<pre>sudo wget -O data.json --post-data "json={\"action\":\"activate\",\"where\":{\"id\":1}}" <?php echo $configuration->settings['base_domain']; ?>/endpoint/servers</pre>
				<h2>Activate Server</h2>
				<pre>
<span class="request-heading">POST <?php echo $configuration->settings['base_domain']; ?>/endpoint/servers</span>{
	action: <span class="value">"activate"</span>,
	camelCaseResponseKeys: <span class="value">true</span>, <span class="comment">Set to true for response key formatting as "camelCaseFormat", false for "snake_case_format"</span>
	where: {
		id: <span class="value">1</span> <span class="comment">Server ID to activate.</span>
	}
}
<span class="response-heading">Response</span>{
	data: {f
		deploymentCommand: <span class="value">"DEPLOYMENT_COMMAND"</span>, <span class="comment">Command to execute on the server to deploy connected processes.</span>
		server: {
			id: <span class="value">1</span>,
			ip: <span class="value">10.10.10.10</span>, <span class="comment">Main server IP address.</span>
			ipCount: <span class="value">1000</span>, <span class="comment">Total count of node IP addresses added to server.</span>
			statusActivated: <span class="value">true</span>
		}
	},
	message: {
		status: <span class="value">"success"</span>,
		text: <span class="value">"Server activated successfully."</span>
	}
}</pre>
				<h2>Add Server</h2>
				<pre>
<span class="request-heading">POST <?php echo $configuration->settings['base_domain']; ?>/endpoint/servers</span>{
	action: <span class="value">"add"</span>,
	camelCaseResponseKeys: <span class="value">true</span>, <span class="comment">Set to true for response key formatting as "camelCaseFormat", false for "snake_case_format"</span>
	data: {
		ip: <span class="value">10.10.10.10</span> <span class="comment">Main server IP address.</span>
	}
}
<span class="response-heading">Response</span>{
	message: {
		status: <span class="value">"success"</span>,
		text: <span class="value">"Server added successfully."</span>
	}
}</pre>
				<h2>Add Server Nameserver Process</h2>
				<pre>
<span class="request-heading">POST <?php echo $configuration->settings['base_domain']; ?>/endpoint/server-nameserver-processes</span>{
	action: <span class="value">"add"</span>,
	camelCaseResponseKeys: <span class="value">true</span>, <span class="comment">Set to true for response key formatting as "camelCaseFormat", false for "snake_case_format"</span>
	data: {
		createInternalProcess: <span class="value">true</span>, <span class="comment">Set to true to use server node IP as nameserver source IP. Set to false if listening IP is a public nameserver.</span>
		externalSourceIp: <span class="value">"10.10.10.10"</span>, <span class="comment">Nameserver source IP that external destinations will see from proxy requests.</span>
		listeningIp: <span class="value">"127.0.0.1"</span>, <span class="comment">Nameserver listening IP that internal proxy processes connect to. Can be either a local IP or a public nameserver IP.</span>
		serverId: <span class="value">1</span> <span class="comment">Server ID to add nameserver process to.</span>
	}
}
<span class="response-heading">Response</span>{
	message: {
		status: <span class="value">"success"</span>,
		text: <span class="value">"Server nameserver process added successfully."</span>
	}
}</pre>
				<h2>Add Server Node</h2>
				<pre>
<span class="request-heading">POST <?php echo $configuration->settings['base_domain']; ?>/endpoint/server-nodes</span>{
	action: <span class="value">"add"</span>,
	camelCaseResponseKeys: <span class="value">true</span>, <span class="comment">Set to true for response key formatting as "camelCaseFormat", false for "snake_case_format"</span>
	data: {
		externalIp: <span class="value">"10.10.10.100"</span>, <span class="comment">External source IP.</span>
		internalIp: <span class="value">false</span>, <span class="comment">Optional internal listening IP. External IP will be used if empty.</span>
		serverId: <span class="value">1</span> <span class="comment">Server ID to add server node to.</span>
	}
}
<span class="response-heading">Response</span>{
	message: {
		status: <span class="value">"success"</span>,
		text: <span class="value">"Server node added successfully."</span>
	}
}</pre>
				<h2>Add Server Proxy Process</h2>
				<pre>
<span class="request-heading">POST <?php echo $configuration->settings['base_domain']; ?>/endpoint/server-proxy-processes</span>{
	action: <span class="value">"add"</span>,
	camelCaseResponseKeys: <span class="value">true</span>, <span class="comment">Set to true for response key formatting as "camelCaseFormat", false for "snake_case_format"</span>
	data: {
		port: <span class="value">1080</span>, <span class="comment">Proxy port to open that will be used for balancing proxy requests.</span>
		serverId: <span class="value">1</span> <span class="comment">Server ID to add server node to.</span>
	}
}
<span class="response-heading">Response</span>{
	message: {
		status: <span class="value">"success"</span>,
		text: <span class="value">"Server proxy process added successfully."</span>
	}
}</pre>
				<h2>Authenticate Proxies</h2>
				<pre>
<span class="request-heading">POST <?php echo $configuration->settings['base_domain']; ?>/endpoint/proxies</span>{
	action: <span class="value">"authenticate"</span>,
	camelCaseResponseKeys: <span class="value">true</span>, <span class="comment">Set to true for response key formatting as "camelCaseFormat", false for "snake_case_format".</span>
	data: {
		generateUnique: <span class="value">true</span>, <span class="comment">Generate unique alphanumeric strings for each proxy.</span>
		ignoreEmpty: <span class="value">false</span>, <span class="comment">Existing proxy username and password values will not be overwritten by empty values if set to true.</span>
		password: <span class="value">"password"</span>, <span class="comment">Proxy username between 4 and 15 characters.</span>
		username: <span class="value">"username"</span>, <span class="comment">Proxy password between 4 and 15 characters.</span>
		whitelistedIps: [
			<span class="value">"127.0.0.1"</span>,
			<span class="value">"127.0.0.2"</span>
			<span class="comment">List of client IPs that are allowed to connect to proxies with no authentication.</span>
		]
	},
	where: {
		id: [
			<span class="value">1</span>,
			<span class="value">2</span>
			<span class="comment">List of proxy IDs to authenticate.</span>,
			<span class="comment">Maximum is 10000 proxies per API request.</span>
		]
	}
}
<span class="response-heading">Response</span>{
	message: {
		status: <span class="value">"success"</span>,
		text: <span class="value">"Proxies authenticated successfully."</span>
	}
}
</pre>
				<h2>Deactivate Server</h2>
				<pre>
<span class="request-heading">POST <?php echo $configuration->settings['base_domain']; ?>/endpoint/servers</span>{
	action: <span class="value">"deactivate"</span>,
	camelCaseResponseKeys: <span class="value">true</span>, <span class="comment">Set to true for response key formatting as "camelCaseFormat", false for "snake_case_format".</span>
	where: {
		id: <span class="value">1</span> <span class="comment">Server ID to deactivate.</span>
	}
}
<span class="response-heading">Response</span>{
	data: {
		server: {
			id: <span class="value">1</span>,
			ip: <span class="value">10.10.10.10</span>, <span class="comment">Main server IP address.</span>
			ipCount: <span class="value">1000</span>, <span class="comment">Total count of node IP addresses added to server.</span>
			statusActivated: <span class="value">false</span>
		}
	},
	message: {
		status: <span class="value">"success"</span>,
		text: <span class="value">"Server deactivated successfully."</span>
	}
}</pre>
<!-- todo: Add API method to Edit Proxy -->
				<h2>Edit Server Node</h2>
				<pre>
<span class="request-heading">POST <?php echo $configuration->settings['base_domain']; ?>/endpoint/server-nodes</span>{
	action: <span class="value">"edit"</span>,
	camelCaseResponseKeys: <span class="value">true</span>, <span class="comment">Set to true for response key formatting as "camelCaseFormat", false for "snake_case_format".</span>
	data: {
		externalIp: <span class="value">"10.10.10.100"</span>, <span class="comment">External source IP.</span>
		internalIp: <span class="value">false</span> <span class="comment">Optional internal listening IP. External IP will be used if empty.</span>
	},
	where: {
		id: <span class="value">1</span> <span class="comment">Server node ID to edit.</span>
	}
}
<span class="response-heading">Response</span>{
	message: {
		status: <span class="value">"success"</span>,
		text: <span class="value">"Server node edited successfully."</span>
	}
}</pre>
				<h2>Edit Server Proxy Process</h2>
				<pre>
<span class="request-heading">POST <?php echo $configuration->settings['base_domain']; ?>/endpoint/server-proxy-processes</span>{
	action: <span class="value">"edit"</span>,
	camelCaseResponseKeys: <span class="value">true</span>, <span class="comment">Set to true for response key formatting as "camelCaseFormat", false for "snake_case_format".</span>
	data: {
		port: <span class="value">1080</span> <span class="comment">Proxy port to open that will be used for balancing proxy requests.</span>
	},
	where: {
		id: <span class="value">1</span> <span class="comment">Server node ID to edit.</span>
	}
}
<span class="response-heading">Response</span>{
	message: {
		status: <span class="value">"success"</span>,
		text: <span class="value">"Server proxy process edited successfully."</span>
	}
}</pre>
				<h2>Fetch Proxies</h2>
				<pre>
<span class="request-heading">POST <?php echo $configuration->settings['base_domain']; ?>/endpoint/proxies</span>{
	action: <span class="value">"fetch"</span>,
	camelCaseResponseKeys: <span class="value">true</span>, <span class="comment">Set to true for response key formatting as "camelCaseFormat", false for "snake_case_format".</span>
	fields: [
		<span class="value">"created"</span>, <span class="comment">Created date in datetime format.</span>
		<span class="value">"external_ip"</span>, <span class="comment">IP that is used for connecting to the proxy externally.</span>
		<span class="value">"external_ip_version"</span>, <span class="comment">Either 4 or 6.</span>
		<span class="value">"id"</span>,
		<span class="value">"internal_ip"</span>, <span class="comment">IP that is used as a local IP on the server.</span>
		<span class="value">"internal_ip_version"</span>, <span class="comment">Either 4 or 6.</span>
		<span class="value">"modified"</span>, <span class="comment">The last modified date in datetime format.</span>
		<span class="value">"password"</span>, <span class="comment">Proxy password for SOCKS authentication.</span>
		<span class="value">"server_id"</span>, <span class="comment">Server ID that the proxy belongs to.</span>
		<span class="value">"server_node_id"</span>, <span class="comment">Server node ID that the proxy belongs to.</span>
		<span class="value">"status"</span>, <span class="comment">Either active or inactive.</span>
		<span class="value">"username"</span>, <span class="comment">Proxy username for SOCKS authentication.</span>
		<span class="value">"whitelisted_ips"</span> <span class="comment">List of client IPs and subnets that are allowed to connect to proxies with no authentication.</span>
	],
	resultsPage: <span class="value">1</span>,
	resultsPerPage: <span class="value">10000</span>, <span class="comment">Maximum is 10000 results per page with pagination using the resultsPage value.</span>
	sort: {
		field: <span class="value">"created"</span>, <span class="comment">Field to sort by.</span>
		order: <span class="value">"DESC"</span> <span class="comment">Either DESC for descending order or ASC for ascending order.</span>
	},
	where: {
		status: <span class="value">"active"</span>
	}
}
<span class="response-heading">Response</span>{
	data: [
		{
			created: <span class="value">"2021-01-01 00:00:00"</span>,
			externalIp: <span class="value">"10.20.30.40"</span>,
			externalIpVersion: <span class="value">4</span>,
			id: <span class="value">1</span>,
			internalIp: <span class="value">"192.168.0.1"</span>,
			internalIpVersion: <span class="value">4</span>,
			modified: <span class="value">"2021-01-01 00:00:00"</span>,
			password: <span class="value">"password"</span>,
			serverId: <span class="value">1</span>,
			serverNodeId: <span class="value">1</span>,
			status: <span class="value">"active"</span>,
			username: <span class="value">"username"</span>,
			whitelistedIps: <span class="value">""</span>
		}
	],
	message: {
		status: <span class="value">"success"</span>,
		text: <span class="value">"Proxies fetched successfully."</span>
	}
}
</pre>
				<h2>Fetch Servers</h2>
				<pre>
<span class="request-heading">POST <?php echo $configuration->settings['base_domain']; ?>/endpoint/servers</span>{
	action: <span class="value">"fetch"</span>,
	camelCaseResponseKeys: <span class="value">true</span>, <span class="comment">Set to true for response key formatting as "camelCaseFormat", false for "snake_case_format".</span>
	fields: [
		<span class="value">"created"</span>, <span class="comment">Created date in datetime format.</span>
		<span class="value">"id"</span>,
		<span class="value">"ip"</span>, <span class="comment">Main server IP address.</span>
		<span class="value">"ip_count"</span>, <span class="comment">Total count of node IP addresses added to server.</span>
		<span class="value">"modified"</span>, <span class="comment">The last modified date in datetime format.</span>
		<span class="value">"status_activated"</span>,
		<span class="value">"status_deployed"</span>
	],
	resultsPage: <span class="value">1</span>,
	resultsPerPage: <span class="value">10000</span>, <span class="comment">Maximum is 10000 results per page with pagination using the resultsPage value.</span>
	sort: {
		field: <span class="value">"created"</span>, <span class="comment">Field to sort by.</span>
		order: <span class="value">"DESC"</span> <span class="comment">Either DESC for descending order or ASC for ascending order</span>
	},
	where: {
		id: <span class="value">1</span>
	}
}
<span class="response-heading">Response</span>{
	data: [
		{
			created: <span class="value">"2021-01-01 00:00:00"</span>,
			id: <span class="value">1</span>,
			ip: <span class="value">"10.10.10.10"</span>,
			ipCount: <span class="value">1000</span>,
			modified: <span class="value">"2021-01-01 00:00:00"</span>,
			statusActivated: <span class="value">false</span>,
			statusDeployed: <span class="value">false</span>
		}
	],
	message: {
		status: <span class="value">"success"</span>,
		text: <span class="value">"Servers fetched successfully."</span>
	}
}
</pre>
				<h2>Fetch Server Nameserver Processes</h2>
				<pre>
<span class="request-heading">POST <?php echo $configuration->settings['base_domain']; ?>/endpoint/server-nameserver-processes</span>{
	action: <span class="value">"fetch"</span>,
	camelCaseResponseKeys: <span class="value">true</span>, <span class="comment">Set to true for response key formatting as "camelCaseFormat", false for "snake_case_format".</span>
	fields: [
		<span class="value">"created"</span>, <span class="comment">Created date in datetime format.</span>
		<span class="value">"external_source_ip"</span>, <span class="comment">Nameserver source IP that external destinations will see from proxy requests.</span>
		<span class="value">"id"</span>,
		<span class="value">"internal_source_ip"</span>, <span class="comment">Nameserver listening IP that internal proxy processes use.</span>
		<span class="value">"listening_ip"</span>, <span class="comment">Nameserver listening IP that internal proxy processes connect to. Can be either a local IP or a public nameserver IP.</span>
		<span class="value">"local"</span>, <span class="comment">Set to true if nameserver process runs locally on server, false if public DNS.</span>
		<span class="value">"modified"</span>, <span class="comment">The last modified date in datetime format.</span>
		<span class="value">"server_id"</span> <span class="comment">Server ID that the proxy belongs to.</span>
	],
	resultsPage: <span class="value">1</span>,
	resultsPerPage: <span class="value">10000</span>, <span class="comment">Maximum is 10000 results per page with pagination using the resultsPage value.</span>
	sort: {
		field: <span class="value">"created"</span>, <span class="comment">Field to sort by.</span>
		order: <span class="value">"DESC"</span> <span class="comment">Either DESC for descending order or ASC for ascending order.</span>
	},
	where: {
		serverId: <span class="value">1</span>
	}
}
<span class="response-heading">Response</span>{
	data: [
		{
			created: <span class="value">"2021-01-01 00:00:00"</span>,
			externalSourceIp: <span class="value">"10.10.10.100"</span>,
			id: <span class="value">1</span>,
			local: <span class="value">true</span>
			internalSourceIp: <span class="value">false</span>,
			listeningIp: <span class="value">"127.0.0.1"</span>,
			modified: <span class="value">"2021-01-01 00:00:00"</span>,
			serverId: <span class="value">1</span>
		}
	],
	message: {
		status: <span class="value">"success"</span>,
		text: <span class="value">"Server nameserver processes fetched successfully."</span>
	}
}
</pre>
				<h2>Fetch Server Nodes</h2>
				<pre>
<span class="request-heading">POST <?php echo $configuration->settings['base_domain']; ?>/endpoint/server-nodes</span>{
	action: <span class="value">"fetch"</span>,
	camelCaseResponseKeys: <span class="value">true</span>, <span class="comment">Set to true for response key formatting as "camelCaseFormat", false for "snake_case_format".</span>
	fields: [
		<span class="value">"created"</span>, <span class="comment">Created date in datetime format.</span>
		<span class="value">"external_ip"</span>, <span class="comment">External source IP.</span>
		<span class="value">"external_ip_version"</span>, <span class="comment">Either 4 or 6.</span>
		<span class="value">"id"</span>,
		<span class="value">"internal_ip"</span>, <span class="comment">Optional internal listening IP. External IP will be used if empty.</span>
		<span class="value">"internal_ip_version"</span>, <span class="comment">Either 4 or 6.</span>
		<span class="value">"modified"</span>, <span class="comment">The last modified date in datetime format.</span>
		<span class="value">"server_id"</span>, <span class="comment">Server ID that the server node belongs to.</span>
		<span class="value">"status"</span> <span class="comment">Either active or inactive.</span>
	],
	resultsPage: <span class="value">1</span>,
	resultsPerPage: <span class="value">10000</span>, <span class="comment">Maximum is 10000 results per page with pagination using the resultsPage value.</span>
	sort: {
		field: <span class="value">"created"</span>, <span class="comment">Field to sort by.</span>
		order: <span class="value">"DESC"</span> <span class="comment">Either DESC for descending order or ASC for ascending order.</span>
	},
	where: {
		serverId: <span class="value">1</span>
	}
}
<span class="response-heading">Response</span>{
	data: [
		{
			created: <span class="value">"2021-01-01 00:00:00"</span>,
			externalIp: <span class="value">"10.10.10.100"</span>,
			externalIpVersion: <span class="value">4</span>,
			id: <span class="value">2</span>,
			internalIp: <span class="value">false</span>,
			internalIpVersion: <span class="value">4</span>,
			modified: <span class="value">"2021-01-01 00:00:00"</span>,
			serverId: <span class="value">1</span>,
			status: <span class="value">"active"</span>
		}
	],
	message: {
		status: <span class="value">"success"</span>,
		text: <span class="value">"Server nameserver processes fetched successfully."</span>
	}
}
</pre>
				<h2>Fetch Server Proxy Processes</h2>
				<pre>
<span class="request-heading">POST <?php echo $configuration->settings['base_domain']; ?>/endpoint/server-proxy-processes</span>{
	action: <span class="value">"fetch"</span>,
	camelCaseResponseKeys: <span class="value">true</span>, <span class="comment">Set to true for response key formatting as "camelCaseFormat", false for "snake_case_format".</span>
	fields: [
		<span class="value">"created"</span>, <span class="comment">Created date in datetime format.</span>
		<span class="value">"id"</span>,
		<span class="value">"modified"</span>, <span class="comment">The last modified date in datetime format.</span>
		<span class="value">"port"</span>, <span class="comment">Open proxy port for SOCKS requests.</span>
		<span class="value">"server_id"</span> <span class="comment">Server ID that the server proxy process belongs to.</span>
	],
	resultsPage: <span class="value">1</span>,
	resultsPerPage: <span class="value">10000</span>, <span class="comment">Maximum is 10000 results per page with pagination using the resultsPage value.</span>
	sort: {
		field: <span class="value">"created"</span>, <span class="comment">Field to sort by.</span>
		order: <span class="value">"DESC"</span> <span class="comment">Either DESC for descending order or ASC for ascending order.</span>
	},
	where: {
		serverId: <span class="value">1</span>
	}
}
<span class="response-heading">Response</span>{
	data: [
		{
			created: <span class="value">"2021-01-01 00:00:00"</span>,
			id: <span class="value">1</span>,
			modified: <span class="value">"2021-01-01 00:00:00"</span>,
			port: <span class="value">1080</span>,
			serverId: <span class="value">1</span>
		}
	],
	message: {
		status: <span class="value">"success"</span>,
		text: <span class="value">"Server proxy processes fetched successfully."</span>
	}
}
</pre>
				<h2>Remove Servers</h2>
				<pre>
<span class="request-heading">POST <?php echo $configuration->settings['base_domain']; ?>/endpoint/servers</span>{
	action: <span class="value">"remove"</span>,
	camelCaseResponseKeys: <span class="value">true</span>, <span class="comment">Set to true for response key formatting as "camelCaseFormat", false for "snake_case_format".</span>
	where: {
		id: [
			<span class="value">1</span>,
			<span class="value">2</span>
			<span class="comment">Maximum is 10000 servers per API request.</span>
		]
	}
}
<span class="response-heading">Response</span>{
	message: {
		status: <span class="value">"success"</span>,
		text: <span class="value">"Server nameserver processes removed successfully."</span>
	}
}
</pre>
				<h2>Remove Server Nameserver Processes</h2>
				<pre>
<span class="request-heading">POST <?php echo $configuration->settings['base_domain']; ?>/endpoint/server-nameserver-processes</span>{
	action: <span class="value">"remove"</span>,
	camelCaseResponseKeys: <span class="value">true</span>, <span class="comment">Set to true for response key formatting as "camelCaseFormat", false for "snake_case_format".</span>
	where: {
		id: [
			<span class="value">1</span>,
			<span class="value">2</span>
			<span class="comment">Maximum is 10000 server nameserver processes per API request.</span>
		]
	}
}
<span class="response-heading">Response</span>{
	message: {
		status: <span class="value">"success"</span>,
		text: <span class="value">"Server nameserver processes removed successfully."</span>
	}
}
</pre>
				<h2>Remove Server Nodes</h2>
				<pre>
<span class="request-heading">POST <?php echo $configuration->settings['base_domain']; ?>/endpoint/server-nodes</span>{
	action: <span class="value">"remove"</span>,
	camelCaseResponseKeys: <span class="value">true</span>, <span class="comment">Set to true for response key formatting as "camelCaseFormat", false for "snake_case_format".</span>
	where: {
		id: [
			<span class="value">1</span>,
			<span class="value">2</span>
			<span class="comment">Maximum is 10000 server nodes per API request.</span>
		]
	}
}
<span class="response-heading">Response</span>{
	message: {
		status: <span class="value">"success"</span>,
		text: <span class="value">"Server nodes removed successfully."</span>
	}
}
</pre>
				<h2>Remove Server Proxy Processes</h2>
				<pre>
<span class="request-heading">POST <?php echo $configuration->settings['base_domain']; ?>/endpoint/server-proxy-processes</span>{
	action: <span class="value">"remove"</span>,
	camelCaseResponseKeys: <span class="value">true</span>, <span class="comment">Set to true for response key formatting as "camelCaseFormat", false for "snake_case_format".</span>
	where: {
		id: [
			<span class="value">1</span>,
			<span class="value">2</span>
			<span class="comment">Maximum is 10000 server proxy processes per API request.</span>
		]
	}
}
<span class="response-heading">Response</span>{
	message: {
		status: <span class="value">"success"</span>,
		text: <span class="value">"Server proxy processes removed successfully."</span>
	}
}</pre>
			</div>
		</div>
	</section>
</main>
<?php
	$scripts = array(
		'/assets/js/default.js',
		'/assets/js/main.js'
	);
	require_once($configuration->settings['base_path'] . '/assets/php/footer.php');
?>
