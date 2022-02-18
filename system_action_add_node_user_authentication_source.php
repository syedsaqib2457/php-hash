<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_user_authentication_sources',
		'node_users'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_user_authentication_sources'] = $systemDatabasesConnections['node_user_authentication_sources'];
	$parameters['system_databases']['node_users'] = $systemDatabasesConnections['node_users'];

	function _addNodeUserAuthenticationSource($parameters, $response) {
		// todo
	}

	if (($parameters['action'] === 'add_node_user_authentication_source') === true) {
		$response = _addNodeUserAuthenticationSource($parameters, $response);
	}
?>
