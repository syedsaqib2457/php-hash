<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'system_user_system_users',
		'system_users'
	), $parameters['system_databases'], $response);

	function _listSystemUsers($parameters, $response) {
		if (empty($parameters['system_user_authentication_token']) === true) {
			return $response;
		}

		if (empty($parameters['pagination']['results_page_number']) === true) {
			$parameters['pagination']['results_page_number'] = 1;
		}

		if (empty($parameters['pagination']['results_per_page_count']) === true) {
			$parameters['pagination']['results_per_page_count'] = 100;
		}

		if (empty($parameters['where']['id']) === true) {
			$systemUserSystemUsers =  = _list(array(
				'data' => array(
					'system_user_id'
				),
				'in' => $parameters['system_databases']['system_user_system_users'],
				'where' => array(
					'either' => array(
						'system_user_id' => $parameters['system_user_id'],
						'system_user_system_user_id' => $parameters['system_user_id']
					)
				)
			), $response);

			foreach ($systemUserSystemUsers as $systemUserSystemUser) {
				$parameters['where']['id'][] = $systemUserSystemUser['system_user_id'];
			}
		} else {
			if (is_array($parameters['where']['id']) === false) {
				$parameters['where']['id'][] = $parameters['where']['id'];
			}

			foreach ($parameters['where']['id'] as $systemUserId) {
				$systemUserSystemUserCount = _count(array(
					'in' => $parameters['system_databases']['system_user_system_users'],
					'where' => array(
						'system_user_id' => $systemUserId,
						'system_user_system_user_id' => $parameters['system_user_id']
					)
				), $response);

				if (($systemUserSystemUserCount === 0) === true) {
					$response['message'] = 'Invalid permissions to list system user authentication token for system user ID ' . $systemUserId . ', please try again.';
					return $response;
				}
			}
		}

		$parameters['pagination']['results_total_count'] = _count(array(
			'in' => $parameters['system_databases']['system_users'],
			'where' => $parameters['where']
		), $response);
		$systemUsers = _list(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['system_users'],
			'limit' => $parameters['pagination']['results_per_page_count'],
			'offset' => (($parameters['pagination']['results_page_number'] - 1) * $parameters['pagination']['results_per_page_count']),
			'sort' => $parameters['sort'],
			'where' => $parameters['where']
		), $response);
		$response['data'] = $systemUsers;
		$response['message'] = 'System users listed successfully.';
		$response['pagination'] = $parameters['pagination'];
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list_system_users') === true) {
		$response = _listSystemUsers($parameters, $response);
	}
?>
