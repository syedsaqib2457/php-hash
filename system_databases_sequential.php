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
			$systemDatabaseDataIndex = key($parameters['data']);

			if (is_numeric($systemDatabaseDataIndex) === false) {
				$parameters['data'] = array(
					$parameters['data']
				);
			}

			$systemDatabaseDataKeyDataParts = array();

			foreach ($parameters['data'] as $systemDatabaseDataKey => $systemDatabaseDataValue) {
				if (empty($systemDatabaseDataValue['id']) === false) {
					continue;
				}

				$systemDatabaseDataValue['id'] = _createUniqueId();
				unset($parameters['data'][$systemDatabaseDataKey]);

				foreach ($systemDatabaseDataValue as $systemDatabaseDataKey => $systemDatabaseDataValue) {
					if (empty($systemDatabaseDataKeyDataParts[$systemDatabaseDataKey) === true) {
						$systemDatabaseDataKeyFileDetails = false;
						exec('cd /usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/ ls -f --ignore="." --ignore=".." --size | tail -1 | awk \'{print $1"\n"$2}\'', $systemDatabaseDataKeyFileDetails);

						if (empty($systemDatabaseDataKeyFileDetails) === true) {
							if (touch('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/0') === false) {
								// todo: re-index modified records from previous failed process
								$response['message'] = 'Error saving system database data, please try again.';
								return $response;
							}

							$systemDatabaseDataKeyFileDetails = array(
								0,
								0
							);
						}

						if (($systemDatabaseDataKeyFileDetails[0] > 10000000) === true) {
							$systemDatabaseDataKeyFileDetails[1]++;

							if (touch('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/' . $systemDatabaseDataKeyFileDetails[1]) === false) {
								// todo: re-index modified records from previous failed process
								$response['message'] = 'Error saving system database data, please try again.';
								return $response;
							}
						}

						$systemDatabaseDataKeyFileData = file_get_contents('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/' . $systemDatabaseDataKeyFileDetails[1]);

						if (
							(empty($systemDatabaseDataKeyFileData) === true) &&
							(file_exists('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/' . ($systemDatabaseDataKeyFileDetails[1] - 1)) === true)
						) {
							$systemDatabaseDataKeyFileData = file_get_contents('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/' . ($systemDatabaseDataKeyFileDetails[1] - 1));

							if ($systemDatabaseDataKeyFileData === false) {
								// todo: re-index modified records from previous failed process
								$response['message'] = 'Error saving system database data, please try again.';
								return $response;
							}
						}

						$systemDatabaseDataKeyDataIndex = '';
						$systemDatabaseDataKeyDataIndexPosition = (strripos($systemDatabaseDataKeyFileData, '-_-') + 3);

						while (isset($systemDatabaseDataKeyDataParts[$systemDatabaseDataKeyDataIndexPosition + 3]) === true) {
							$systemDatabaseDataKeyDataIndex .= $data[$systemDatabaseDataKeyDataIndexPosition];
							$systemDatabaseDataKeyDataIndexPosition++;
						}

						$systemDatabaseDataKeyDataParts[$systemDatabaseDataKey] = '';
					}

					$systemDatabaseDataKeyDataParts[$systemDatabaseDataKey] .= strlen($systemDatabaseDataValue) . '_-_' . $systemDatabaseDataValue . '-_-' . $systemDatabaseDataKeyDataIndex . '_-_';
					$systemDatabaseDataKeyDataIndex++;

					if ((($systemDatabaseDataKeyDataIndex % 100) === 0) === true) {
						if (file_put_contents('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/' . $systemDatabaseDataKeyFileDetails[1], $systemDatabaseDataKeyDataParts[$systemDatabaseDataKey], FILE_APPEND) === false) {
							// todo: re-index modified records from previous failed process
							$response['message'] = 'Error saving system database data, please try again.';
							return $response;
						}

						unset($systemDatabaseDataKeyDataParts[$systemDatabaseDataKey]);
					}
				}
			}

			if (empty($systemDatabaseDataKeyDataParts) === false) {
				foreach ($systemDatabaseDataKeyDataParts as $systemDatabaseDataKeyDataPartsKey => $systemDatabaseDataKeyDataPartsValue) {
					if (file_put_contents('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/' . $systemDatabaseDataKeyFileDetails[1], $systemDatabaseDataKeyDataParts[$systemDatabaseDataKeyDataPartsKey], FILE_APPEND) === false) {
						// todo: re-index modified records from previous failed process
						$response['message'] = 'Error saving system database data, please try again.';
						return $response;
					}
				}
			}

			$systemDatabaseDataKeyFiles = array();
			$systemDatabaseDataKeys = scandir('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/');

			foreach ($systemDatabaseDataKeys as $systemDatabaseDataKey) {
				$systemDatabaseDataKeyFiles[$systemDatabaseDataKey] = scandir('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/');

				if ($systemDatabaseDataKeyFiles[$systemDatabaseDataKey] === false) {
					// todo: re-index modified records from previous failed process
					$response['message'] = 'Error saving system database data, please try again.';
					return $response;
				}
			}

			foreach ($parameters['data'] as $systemDatabaseDataKey => $systemDatabaseDataValue) {
				// todo: find numeric index of ID for fast parsing of each key value pair in $systemDatabaseDataValue

				foreach ($systemDatabaseDataKeyFiles['id'] as $systemDatabaseDataKeyFile) {
					$systemDatabaseDataKeyFileData = file_get_contents('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/' . $systemDatabaseDataKeyFile);
					$systemDatabaseDataValuePosition = strpos($systemDatabaseDataKeyFileData, '_-_' . $systemDatabaseDataValue['id'] . '-_-');

					if (($systemDatabaseDataValuePosition === false) === false) {
						// todo
					}
				}
			}
		}

		return true;
	}

	touch('/usr/local/nodecompute/system_database/process_ids/' . $parameters['process_id']);

	if (file_exists('/usr/local/nodecompute/system_database/process_ids/' . $parameters['process_id']) === false) {
		$response['message'] = 'Error processing system database, please try again.';
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
