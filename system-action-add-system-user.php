<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'systemUsers',
		'systemUserSystemUsers'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['systemUsers'] = $systemDatabasesConnections['systemUsers'];
	$parameters['systemDatabases']['systemUserSystemUsers'] = $systemDatabasesConnections['systemUserSystemUsers'];

	function _addSystemUser($parameters, $response) {
		$systemUserData = array(
			'id' => _createUniqueId(),
			'systemUserId' => $parameters['systemUserId']
		);
		$systemUserSystemUsersData = array(
			array(
				'id' => _createUniqueId(),
				'systemUserId' => $parameters['data']['id'],
				'systemUserSystemUserId' => $parameters['systemUserId']
			)
		);
		$systemUserSystemUsersDataProcessed = false;

		while ($systemUserSystemUsersDataProcessed === false) {
			$systemUser = _list(array(
				'data' => array(
					'id',
					'systemUserId'
				),
				'in' => $parameters['systemDatabases']['systemUsers'],
				'where' => array(
					'id' => $parameters['systemUserId']
				)
			), $response);
			$systemUser = current($systemUser);
			$parameters['systemUserId'] = $systemUser['systemUserId'];
			$systemUserSystemUsersData[] = array(
				'id' => _createUniqueId(),
				'systemUserId' => $parameters['data']['id'],
				'systemUserSystemUserId' => $systemUser['systemUserId']
			);

			if (($systemUser['id'] === $systemUser['systemUserId']) === true) {
				$systemUserSystemUsersDataProcessed = true;
			}
		}

		_save(array(
			'data' => $systemUserSystemUsersData,
			'in' => $parameters['systemDatabases']['systemUserSystemUsers']
		), $response);
		_save(array(
			'data' => $systemUserData,
			'in' => $parameters['systemDatabases']['systemUsers']
		), $response);
		$systemUser = _list(array(
			'in' => $parameters['systemDatabases']['systemUsers'],
			'where' => array(
				'id' => $systemUserData['id']
			)
		), $response);
		$systemUser = current($systemUser);
		$response['data'] = $systemUser;
		$response['message'] = 'System user added successfully.';
		$response['validStatus'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'addSystemUser') === true) {
		$response = _addSystemUser($parameters, $response);
	}
?>
