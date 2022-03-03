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
			$response['message'] = 'Node processes must have an ID, please try again.';
			return $response;
		}

		$nodeProcessesIds = $parameters['where']['id'];
		$nodeProcessesIdsPartsIndex = 0;
		$nodeProcessesIdsParts = array();

		if (is_array($nodeProcessesIds) === false) {
			$nodeProcessesIds = array(
				$nodeProcessesIds
			);
		}

		foreach ($nodeProcessesIds as $nodeProcessesId) {
			if (empty($nodeProcessesIdsParts[$nodeProcessesIdsPartsIndex][10]) === false) {
				$nodeProcessesIdsPartsIndex++;
			}

			$nodeProcessesIdsParts[$nodeProcessesIdsPartsIndex][] = $nodeProcessesId;
		}

		foreach ($nodeProcessesIdsParts as $nodeProcessesIdsPart) {
			_delete(array(
				'in' => $parameters['systemDatabases']['nodeProcesses'],
				'where' => array(
					'id' => $nodeProcessesIdsPart
				)
			), $response);
		}

		$response['message'] = 'Node processes deleted successfully.';
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
