<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'system_user_authentication_tokens',
		'system_user_system_users'
	), $parameters['system_databases'], $response);

	function _listSystemUserAuthenticationTokens($parameters, $response) {
		if (empty($parameters['system_user_authentication_token']) === true) {
			return $response;
		}

		if (empty($parameters['pagination']['results_page_number']) === true) {
			$parameters['pagination']['results_page_number'] = 1;
		}

		if (empty($parameters['pagination']['results_per_page_count']) === true) {
			$parameters['pagination']['results_per_page_count'] = 100;
		}

		if (empty($parameters['where']['system_user_id']) === true) {
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
				$parameters['where']['system_user_id'][] = $systemUserSystemUser['system_user_id'];
			}
		} else {
			if (is_array($parameters['where']['system_user_id']) === false) {
				$parameters['where']['system_user_id'][] = $parameters['where']['system_user_id'];
			}

			foreach ($parameters['where']['system_user_id'] as $systemUserId) {
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
			'in' => $parameters['system_databases']['system_user_authentication_tokens'],
			'where' => $parameters['where']
		), $response);
		$systemUserAuthenticationTokens = _list(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['system_user_authentication_tokens'],
			'limit' => $parameters['pagination']['results_per_page_count'],
			'offset' => (($parameters['pagination']['results_page_number'] - 1) * $parameters['pagination']['results_per_page_count']),
			'sort' => $parameters['sort'],
			'where' => $parameters['where']
		), $response);
		$response['data'] = $systemUserAuthenticationTokens;
		$response['message'] = 'System user authentication tokens listed successfully.';
		$response['pagination'] = $parameters['pagination'];
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list_system_user_authentication_tokens') === true) {
		$response = _listSystemUserAuthenticationTokens($parameters, $response);
	}
?>
