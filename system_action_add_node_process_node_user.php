<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_process_node_users',
		'node_users'
	), $parameters['system_databases'], $response);

	$parameters['system_databases']['node_process_node_users'] = $systemDatabasesConnections['node_process_node_users'];
	$parameters['system_databases']['node_users'] = $systemDatabasesConnections['node_users'];

	function _addNodeProcessNodeUser($parameters, $response) {
		
	}

	if (($parameters['action'] === 'add_node_process_node_user') === true) {
		$response = _addNodeProcessNodeUser($parameters, $response);
	}
?>
