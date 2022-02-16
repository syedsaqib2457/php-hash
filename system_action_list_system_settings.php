<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'system_user_system_users'
	), $parameters['system_databases'], $response);

	function _listSystemSettings($parameters, $response) {
		if (empty($parameters['system_user_authentication_token']) === true) {
			return $response;
		}

		if (empty($parameters['pagination']['results_page_number']) === true) {
			$parameters['pagination']['results_page_number'] = 1;
		}

		if (empty($parameters['pagination']['results_per_page_count']) === true) {
			$parameters['pagination']['results_per_page_count'] = 100;
		}

		$parameters['pagination']['results_total_count'] = _count(array(
			'in' => $parameters['system_databases']['system_settings'],
			'where' => $parameters['where']
		), $response);
		$systemSettings = _list(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['system_settings'],
			'limit' => $parameters['pagination']['results_per_page_count'],
			'offset' => (($parameters['pagination']['results_page_number'] - 1) * $parameters['pagination']['results_per_page_count']),
			'sort' => $parameters['sort'],
			'where' => $parameters['where']
		), $response);
		$response['data'] = $systemSettings;
		$response['message'] = 'System settings listed successfully.';
		$response['pagination'] = $parameters['pagination'];
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'list_system_settings') === true) {
		$response = _listSystemSettings($parameters, $response);
	}
?>
