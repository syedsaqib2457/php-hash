<?php
	$response = array(
		'message' => 'Invalid endpoint request, please try again.',
		'status_valid' => false
	);

	if (empty($_POST['json']) === false) {
		$parameters = json_decode($_POST['json'], true);

		if (empty($parameters) === true) {
			echo json_encode($response);
			exit;
		}

		require_once('/var/www/ghostcompute/system/settings.php');
		require_once('/var/www/ghostcompute/system/database.php');

		if (
			(ctype_alnum(str_replace('_', '', $parameters['function'])) === false) ||
			(file_exists('/var/www/ghostcompute/system/' . $parameters['function'] . '.php') === false)
		) {
			$response['message'] = 'Invalid endpoint request method, please try again.';
			echo json_encode($response);
			exit;
		}

		require_once('/var/www/ghostcompute/system/' . $parameters['function'] . '.php');
	}

	echo json_encode($response);
	exit;
?>
