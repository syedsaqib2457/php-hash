<?php
	$response = array(
		'message' => 'Invalid endpoint request, please try again.',
		'status_valid' => false
	);

	if (empty($_POST) === false) {
		$parameters = json_decode($_POST, true);

		if (empty($parameters) === true) {
			echo json_encode($response);
			exit;
		}

		require_once('/var/www/ghostcompute/system_settings.php');
		require_once('/var/www/ghostcompute/system_database.php');

		if (
			(ctype_alnum(str_replace('_', '', $parameters['action'])) === false) ||
			(file_exists('/var/www/ghostcompute/system_action_' . $parameters['action'] . '.php') === false)
		) {
			$response['message'] = 'Invalid endpoint request action, please try again.';
			// todo: log invalid action for DDoS protection
			echo json_encode($response);
			exit;
		}

		if (
			(empty($parameters['authentication_token']) === true) ||
			(ctype_alnum($parameters['authentication_token']) === false)
		) {
			$response['message'] = 'Invalid endpoint system user authentication token, please try again.';
			// todo: log invalid action for DDoS protection
			echo json_encode($response);
			exit;
		}

		$response['message'] = 'Error processing request to ' . str_replace('_', ' ', $parameters['action']) . ', please try again.';
		$systemUserAuthenticationToken = _fetch(array(
			'from' => $parameters['databases']['system_user_authentication_token'],
			'where' => array(
				'string' => $parameters['authentication_token']
			)
		));

		if ($systemUserAuthenticationToken === false) {
			echo json_encode($response);
			exit;
		}

		// todo: authorize system user authentication token scope before processing function
		require_once('/var/www/ghostcompute/system_action_' . $parameters['action'] . '.php');
	}

	echo json_encode($response);
	exit;
?>
