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

	if (($parameters['action'] === 'listNodeResourceUsageLogs') === true) {
		$response = _listNodeResourceUsageLogs($parameters, $response);
	}
?>
