<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeResourceUsageLogs'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeResourceUsageLogs'] = $systemDatabasesConnections['nodeResourceUsageLogs'];

	function _listNodeResourceUsageLogs($parameters, $response) {
		
	}

	if (($parameters['action'] === 'list-node-resource-usage-logs') === true) {
		$response = _listNodeResourceUsageLogs($parameters, $response);
	}
?>
