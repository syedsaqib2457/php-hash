<?php
	// this file will replace system_databases.php when all sequential database functions are working without MySQL
	// todo: create database directories + initial data in system_action_deploy_system.php

	if (
		(empty($parameters) === true) &&
		(empty($_SERVER['argv'][1]) === true)
	) {
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
		if (empty($parameters['data']) === false) {
			$systemDatabaseRowIndex = key($parameters['data']);

			if (is_numeric($systemDatabaseRowIndex) === false) {
				$parameters['data'] = array(
					$parameters['data']
				);
			}

			foreach ($parameters['data'] as $systemDatabaseRecords) {
				foreach ($systemDatabaseRecords as $systemDatabaseRecordKey => $systemDatabaseRecordValue) {
					// todo: file_append records with no ID, update records with ID
				}
			}
		}

		return true;
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
				(is_dir('/proc/' . $systemDatabaseCurrentProcessId) === false)
			) &&
			(($systemDatabaseNextProcessId === $parameters['process_id']) === true)
		) {
			if (file_exists('/usr/local/nodecompute/system_database/process_id/' . $systemDatabaseCurrentProcessId) === true) {
				unlink('/usr/local/nodecompute/system_database/process_id/' . $systemDatabaseCurrentProcessId);
				// todo: re-index modified records from previous failed process
			}

			touch('/usr/local/nodecompute/system_database/process_id/' . $parameters['process_id']);
			break;
		}

		usleep(500);
	}
?>
