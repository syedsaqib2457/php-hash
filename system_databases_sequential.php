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
		// todo: add rollback + crash recovery with redundancy of original data (next process in queue applies redundancy data if it exists before proceeding with its own database requests)

		if (empty($parameters['data']) === false) {
			$systemDatabaseDataIndex = key($parameters['data']);

			if (is_numeric($systemDatabaseDataIndex) === false) {
				$parameters['data'] = array(
					$parameters['data']
				);
			}

			$systemDatabaseDataKeyDataIndexes = array();
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

						if (
							(empty($systemDatabaseDataKeyFileDetails) === true) &&
							(touch('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/0') === false)
						) {
							$response['message'] = 'Error saving system database data, please try again.';
							return $response;
						}

						if (empty($systemDatabaseDataKeyFileDetails[0]) === true) {
							$systemDatabaseDataKeyDataIndexes[$systemDatabaseDataKey] = 0;
							$systemDatabaseDataKeyFileDetails = array(
								0,
								0
							);
						}

						if (($systemDatabaseDataKeyFileDetails[0] > 10000000) === true) {
							$systemDatabaseDataKeyFileDetails[1]++;

							if (touch('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/' . $systemDatabaseDataKeyFileDetails[1]) === false) {
								$response['message'] = 'Error saving system database data, please try again.';
								return $response;
							}
						}

						$systemDatabaseDataKeyDataParts[$systemDatabaseDataKey] = '';
					}

					if (isset($systemDatabaseDataKeyDataIndexes[$systemDatabaseDataKey]) === false) {
						$systemDatabaseDataKeyFileData = file_get_contents('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/' . $systemDatabaseDataKeyFileDetails[1]);

						if (
							(empty($systemDatabaseDataKeyFileData) === true) &&
							(file_exists('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/' . ($systemDatabaseDataKeyFileDetails[1] - 1)) === true)
						) {
							$systemDatabaseDataKeyFileData = file_get_contents('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/' . ($systemDatabaseDataKeyFileDetails[1] - 1));

							if ($systemDatabaseDataKeyFileData === false) {
								$response['message'] = 'Error saving system database data, please try again.';
								return $response;
							}
						}

						$systemDatabaseDataKeyDataIndexes[$systemDatabaseDataKey] = '';
						$systemDatabaseDataKeyDataIndexPosition = (strripos($systemDatabaseDataKeyFileData, '-_-') + 3);

						while (isset($systemDatabaseDataKeyFileData[$systemDatabaseDataKeyDataIndexPosition + 3]) === true) {
							$systemDatabaseDataKeyDataIndexes[$systemDatabaseDataKey] .= $systemDatabaseDataKeyFileData[$systemDatabaseDataKeyDataIndexPosition];
							$systemDatabaseDataKeyDataIndexPosition++;
						}
					}

					$systemDatabaseDataKeyDataParts[$systemDatabaseDataKey] .= strlen($systemDatabaseDataValue) . '_-_' . $systemDatabaseDataValue . '-_-' . $systemDatabaseDataKeyDataIndexes[$systemDatabaseDataKey] . '_-_';
					$systemDatabaseDataKeyDataIndexes[$systemDatabaseDataKey]++;

					if ((($systemDatabaseDataKeyDataIndexes[$systemDatabaseDataKey] % 100) === 0) === true) {
						if (file_put_contents('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/' . $systemDatabaseDataKeyFileDetails[1], $systemDatabaseDataKeyDataParts[$systemDatabaseDataKey], FILE_APPEND) === false) {
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
					$response['message'] = 'Error saving system database data, please try again.';
					return $response;
				}
			}

			$systemDatabaseData = $parameters['data'];
			$systemDatabaseDataIndexes = array();

			foreach ($systemDatabaseDataKeyFiles['id'] as $systemDatabaseDataKeyFile) {
				$systemDatabaseDataKeyFileData = file_get_contents('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/' . $systemDatabaseDataKeyFile);

				foreach ($systemDatabaseData as $systemDatabaseDataKey => $systemDatabaseDataValue) {
					$systemDatabaseDataValuePosition = strpos($systemDatabaseDataKeyFileData, '_-_' . $systemDatabaseDataValue['id'] . '-_-');

					if (($systemDatabaseDataValuePosition === false) === false) {
						$systemDatabaseDataIndexes[$systemDatabaseDataKey] = '';
						$systemDatabaseDataValuePosition += 72;

						while (is_numeric($systemDatabaseDataKeyFileData[$systemDatabaseDataValuePosition]) === true) {
							$systemDatabaseDataIndexes[$systemDatabaseDataKey] .= $systemDatabaseDataKeyFileData[$systemDatabaseDataValuePosition];
							$systemDatabaseDataValuePosition++;
						}

						unset($systemDatabaseData[$systemDatabaseDataKey]);
					}
				}
			}

			$systemDatabaseDataIndex = 0;
			$systemDatabaseDataKeyDataParts = array();

			if (empty($systemDatabaseData) === false) {
				foreach ($systemDatabaseData as $systemDatabaseDataKey => $systemDatabaseDataValue) {
					$systemDatabaseDataValue['id'] = _createUniqueId();
					unset($parameters['data'][$systemDatabaseDataKey]);

					foreach ($systemDatabaseDataValue as $systemDatabaseDataKey => $systemDatabaseDataValue) {
						if (empty($systemDatabaseDataKeyDataParts[$systemDatabaseDataKey) === true) {
							$systemDatabaseDataKeyFileDetails = false;
							exec('cd /usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/ ls -f --ignore="." --ignore=".." --size | tail -1 | awk \'{print $1"\n"$2}\'', $systemDatabaseDataKeyFileDetails);

							if (
								(empty($systemDatabaseDataKeyFileDetails) === true) &&
								(touch('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/0') === false)
							) {
								$response['message'] = 'Error saving system database data, please try again.';
								return $response;
							}

							if (empty($systemDatabaseDataKeyFileDetails[0]) === true) {
								$systemDatabaseDataKeyDataIndexes[$systemDatabaseDataKey] = 0;
								$systemDatabaseDataKeyFileDetails = array(
									0,
									0
								);
							}

							if (($systemDatabaseDataKeyFileDetails[0] > 10000000) === true) {
								$systemDatabaseDataKeyFileDetails[1]++;

								if (touch('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/' . $systemDatabaseDataKeyFileDetails[1]) === false) {
									$response['message'] = 'Error saving system database data, please try again.';
									return $response;
								}
							}

							$systemDatabaseDataKeyDataParts[$systemDatabaseDataKey] = '';
						}

						if (isset($systemDatabaseDataKeyDataIndexes[$systemDatabaseDataKey]) === false) {
							$systemDatabaseDataKeyFileData = file_get_contents('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/' . $systemDatabaseDataKeyFileDetails[1]);

							if (
								(empty($systemDatabaseDataKeyFileData) === true) &&
								(file_exists('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/' . ($systemDatabaseDataKeyFileDetails[1] - 1)) === true)
							) {
								$systemDatabaseDataKeyFileData = file_get_contents('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/' . ($systemDatabaseDataKeyFileDetails[1] - 1));

								if ($systemDatabaseDataKeyFileData === false) {
									$response['message'] = 'Error saving system database data, please try again.';
									return $response;
								}
							}

							$systemDatabaseDataKeyDataIndexes[$systemDatabaseDataKey] = '';
							$systemDatabaseDataKeyDataIndexPosition = (strripos($systemDatabaseDataKeyFileData, '-_-') + 3);

							while (isset($systemDatabaseDataKeyFileData[$systemDatabaseDataKeyDataIndexPosition + 3]) === true) {
								$systemDatabaseDataKeyDataIndexes[$systemDatabaseDataKey] .= $systemDatabaseDataKeyFileData[$systemDatabaseDataKeyDataIndexPosition];
								$systemDatabaseDataKeyDataIndexPosition++;
							}
						}

						$systemDatabaseDataKeyDataParts[$systemDatabaseDataKey] .= strlen($systemDatabaseDataValue) . '_-_' . $systemDatabaseDataValue . '-_-' . $systemDatabaseDataKeyDataIndexes[$systemDatabaseDataKey] . '_-_';
						$systemDatabaseDataKeyDataIndexes[$systemDatabaseDataKey]++;

						if ((($systemDatabaseDataKeyDataIndexes[$systemDatabaseDataKey] % 100) === 0) === true) {
							if (file_put_contents('/usr/local/nodecompute/system_database/data/' . $parameters['in'] . '/' . $systemDatabaseDataKey . '/' . $systemDatabaseDataKeyFileDetails[1], $systemDatabaseDataKeyDataParts[$systemDatabaseDataKey], FILE_APPEND) === false) {
								$response['message'] = 'Error saving system database data, please try again.';
								return $response;
							}

							unset($systemDatabaseDataKeyDataParts[$systemDatabaseDataKey]);
						}
					}
				}
			}

			if (empty($parameters['data']) === false) {
				foreach ($parameters['data'] as $systemDatabaseDataKey => $systemDatabaseDataValue) {
					foreach ($systemDatabaseDataValue as $systemDatabaseDataKey => $systemDatabaseDataValue) {
						// todo: updating records in $parameters['data'] with valid $systemDatabaseDataIndexes
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
