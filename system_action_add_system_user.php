<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'system_user_system_users',
		'system_users'
	), $parameters['system_databases'], $response);

	function _addSystemUser($parameters, $response) {
		$systemUserData = array(
			'id' => _createUniqueId(),
			'system_user_id' => $parameters['system_user_id']
		);
		$systemUserSystemUserData = array(
			array(
				'id' => _createUniqueId(),
				'system_user_id' => $parameters['data']['id'],
				'system_user_system_user_id' => $parameters['system_user_id']
			)
		);
		$systemUserSystemUserDataProcessed = false;

		while ($systemUserSystemUserDataProcessed === false) {
			$systemUser = _list(array(
				'data' => array(
					'id',
					'system_user_id'
				),
				'in' => $parameters['system_databases']['system_users'],
				'where' => array(
					'id' => $parameters['system_user_id']
				)
			), $response);
			$systemUser = current($systemUser);

			if (empty($systemUser['system_user_id']) === false) {
				$parameters['system_user_id'] = $systemUser['system_user_id'];
				$systemUserSystemUserData[] = array(
					'id' => _createUniqueId(),
					'system_user_id' => $parameters['data']['id'],
					'system_user_system_user_id' => $systemUser['system_user_id']
				);
				continue;
			}

			$systemUserSystemUserDataProcessed = true;
		}

		_save(array(
			'data' => $systemUserSystemUserData,
			'in' => $parameters['system_databases']['system_user_system_users']
		), $response);
		_save(array(
			'data' => $systemUserData,
			'in' => $parameters['system_databases']['system_users']
		), $response);
		$systemUser = _list(array(
			'in' => $parameters['system_databases']['system_users'],
			'where' => array(
				'id' => $systemUserData['id']
			)
		), $response);
		$systemUser = current($systemUser);
		$response['data'] = $systemUser;
		$response['message'] = 'System user added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'add_system_user') === true) {
		$response = _addSystemUser($parameters, $response);
	}
?>
