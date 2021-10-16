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

		// todo: authorize system user token and system user function scope before processing function
		require_once('/var/www/ghostcompute/system_action_' . $parameters['action'] . '.php');
	}

	echo json_encode($response);
	exit;
?>
