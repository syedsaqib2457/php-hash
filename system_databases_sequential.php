<?php
	// this file will replace system_databases.php when all sequential database functions are working without MySQL

	if (empty($parameters) === true) {
		exit;
	}

	function _count($parameters, $response) {
		// todo
	}

	function _delete($parameters, $response) {
		// todo
	}

	function _edit($parameters, $response) {
		// todo
	}

	function _list($parameters, $response) {
		// todo
	}

	function _parseSystemDatabaseCommandWhereConditions($whereConditions, $whereConditionConjunction = 'AND') {
		// todo
	}

	function _save($parameters, $response) {
		// todo
	}

	touch('/usr/local/nodecompute/system_database/process_ids/' . $parameters['process_id']);

	if (file_exists('/usr/local/nodecompute/system_database/process_ids/' . $parameters['process_id']) === false) {
		$response['message'] = 'Error processing system database, please try agian.';
		return $response;
	}

	while (true) {
		exec('cd /usr/local/nodecompute/system_database/process_id/ && ls --ignore="." --ignore=".."', $systemDatabaseCurrentProcessId);
		$systemDatabaseCurrentProcessId = current($systemDatabaseCurrentProcessId);
		exec('cd /usr/local/nodecompute/system_database/process_ids/ && ls -f --ignore="." --ignore=".." | head -1', $systemDatabaseNextProcessId);
		$systemDatabaseNextProcessId = current($systemDatabaseNextProcessId);

		if (
			(
				(empty($systemDatabaseCurrentProcessId) === true) ||
				(
					(is_dir('/proc/' . $systemDatabaseCurrentProcessId) === false) &&
					(unlink('/usr/local/nodecompute/system_database/process_id/' . $systemDatabaseCurrentProcessId) === true)
				)
			) &&
			(($systemDatabaseNextProcessId === $parameters['process_id']) === true)
		) {
			// todo
		}

		usleep(500);
	}
?>
