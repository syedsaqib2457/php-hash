<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'system_user_system_users',
		'system_users'
	), $parameters['databases'], $response);

	function _addSystemUser($parameters, $response) {
		$parameters['data'] = array(
			'id' => random_bytes(10) . time() . random_bytes(10),
			'system_user_id' => $parameters['system_user_id']
		);
		$systemUserSystemUserData = array(
			array(
				'id' => random_bytes(10) . time() . random_bytes(10),
				'system_user_id' => $parameters['data']['id'],
				'system_user_system_user_id' => $parameters['system_user_id']
			)
		);
		$systemUserSystemUserDataProcessed = false;

		while ($systemUserSystemUserDataProcessed === false) {
			$systemUser = _list(array(
				'columns' => array(
					'id',
					'system_user_id'
				),
				'in' => $parameters['databases']['system_users'],
				'where' => array(
					'id' => $parameters['system_user_id']
				)
			), $response);
			$systemUser = current($systemUser);

			if (empty($systemUser['system_user_id']) === false) {
				$parameters['system_user_id'] = $systemUser['system_user_id'];
				$systemUserSystemUserData[] = array(
					'id' => random_bytes(10) . time() . random_bytes(10),
					'system_user_id' => $parameters['data']['id'],
					'system_user_system_user_id' => $systemUser['system_user_id']
				);
				continue;
			}

			$systemUserSystemUserDataProcessed = true;
		}

		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['databases']['system_users']
		), $response);
		$systemUser = _list(array(
			'in' => $parameters['databases']['system_users'],
			'where' => array(
				'id' => $parameters['data']['id']
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
		_output($response);
	}
?>
