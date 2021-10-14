<?php
	// todo: change add_system_user functionality to add sub-users with their own API keys + disable API functionality on main system user

	if (empty($parameters) === true) {
		exit;
	}

	$databases = _connect(array(
		'system_users' => $settings['databases']['system_users']
	));
	$response['message'] = 'Error adding system user, please try again.';

	if ($databases === false) {
		$response['message'] = 'Error connecting to database, please try again.';
		echo json_encode($response);
		exit;
	}

	require_once('/var/www/ghostcompute/system/validate_email_address_format.php');
	$parameters['where']['email_address'] = _validateEmailAddressFormat(base64_decode($parameters['where']['email_address']));

	if ($parameters['where']['email_address'] === false) {
		$response['message'] = 'Invalid system user email address, please try again.';
		echo json_encode($response);
		exit;
	}

	$existingSystemUserCount = _count(array(
		'in' => $databases['system_users'],
		'where' => array(
			'email_address' => $parameters['where']['email_address']
		)
	));

	if ($existingSystemUserCount === false) {
		echo json_encode($response);
		exit;
	}

	if ($existingSystemUserCount !== 0) {
		$response['message'] = 'System user email address <strong>' . $parameters['where']['email_address'] . '</strong> already in use, please try again.';
		echo json_encode($response);
		exit;
	}

	if (empty($parameters['where']['authentication_password'][100]) === false) {
		$response['message'] = 'System user authentication password must be 100 characters or less, please try again.';
		echo json_encode($response);
		exit;
	}

	$parameters['where']['authentication_password'] = password_hash(base64_decode($parameters['where']['authentication_password']), PASSWORD_BCRYPT);

	if (empty($parameters['where']['authentication_password']) === true) {
		echo json_encode($response);
		exit;
	}

	$systemUserData = array(
		'authentication_password' => $parameters['where']['authentication_password'],
		// 'authentication_username' => $parameters['where']['authentication_username'],
		'credit_count' => 0,
		'email_address' => $parameters['where']['email_address'],
		'type' => 'individual'
	);
	$systemUserDataSaved = _save(array(
		'data' => $systemUserData,
		'to' => $databases['system_users']
	));

	if ($systemUserDataSaved === false) {
		echo json_encode($response);
		exit;
	}

	$systemUser = _fetch(array(
		'from' => $databases['system_users'],
		'where' => $systemUserData
	));

	if (empty($systemUser) === true) {
		_delete(array(
			'from' => $databases['system_users'],
			'where' => $systemUserData
		));
		echo json_encode($response);
		exit;
	}

	// todo: create browser session authentication token with custom "name" parameter in session_start() to prevent cookie brute force stealing (https://www.php.net/manual/en/session.configuration.php)
	// todo: fetch browser session token for signing in
	$response['message'] = 'System user added successfully.';
?>
