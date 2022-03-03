<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeProcesses'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeProcesses'] = $systemDatabasesConnections['nodeProcesses'];

	function _deleteNodeProcesses($parameters, $response) {
		if (empty($parameters['where']['id']) === true) {
			$response['message'] = 'Node processes must have IDs, please try again.';
			return $response;
		}

		_delete(array(
			'in' => $parameters['systemDatabases']['nodeProcesses'],
			'where' => array(
				'id' => $parameters['where']['id']
			)
		), $response);
		$response['message'] = 'Node processes deleted successfully.';
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
