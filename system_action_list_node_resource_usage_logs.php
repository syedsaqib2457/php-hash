<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'node_resource_usage_logs'
	), $parameters['system_databases'], $response);
	$parameters['system_databases']['node_resource_usage_logs'] = $systemDatabasesConnections['node_resource_usage_logs'];

	function _listNodeResourceUsageLogs($parameters, $response) {
		
	}

	if (($parameters['action'] === 'list_node_resource_usage_logs') === true) {
		$response = _listNodeResourceUsageLogs($parameters, $response);
	}
?>
