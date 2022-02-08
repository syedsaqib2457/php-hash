<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'system_user_authentication_tokens',
		'system_users'
	), $parameters['system_databases'], $response);

	function _listSystemUserAuthenticationTokens($parameters, $response) {
	}

	if (($parameters['action'] === 'list_system_user_authentication_tokens') === true) {
		$response = _listSystemUserAuthenticationTokens($parameters, $response);
	}
?>
