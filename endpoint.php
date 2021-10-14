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
			(ctype_alnum(str_replace('_', '', $parameters['function'])) === false) ||
			(file_exists('/var/www/ghostcompute/function_' . $parameters['function'] . '.php') === false)
		) {
			$response['message'] = 'Invalid endpoint request method, please try again.';
			// todo: log invalid action for DDoS protection
			echo json_encode($response);
			exit;
		}

		todo: authorize system user token and system user function scope before processing function
		require_once('/var/www/ghostcompute/function_' . $parameters['function'] . '.php');
	}

	echo json_encode($response);
	exit;
?>
