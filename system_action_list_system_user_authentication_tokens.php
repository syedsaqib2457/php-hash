<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'system_user_authentication_tokens',
		'system_users'
	), $parameters['system_databases'], $response);

	function _listSystemUserAuthenticationTokens($parameters, $response) {
		if (empty($parameters['system_user_authentication_token']) === true) {
			return $response
		}

		if (empty($parameters['pagination']['results_page_number']) === true) {
			$parameters['pagination']['results_page_number'] = 1;
		}

		if (empty($parameters['pagination']['results_per_page_count']) === true) {
			$parameters['pagination']['results_per_page_count'] = 100;
		}

		// todo

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
