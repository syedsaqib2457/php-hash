<?php
	if (empty($_SERVER['argv'][1]) === true) {
		exit;
	}

	function _generateUniqueId() {
		$uniqueIdPart = (microtime(true) * 10000);
		$uniqueId = sprintf('%016s', $uniqueIdPart);
		$uniqueIdPart = mt_rand(0, 99999999999999);
		return $uniqueId . sprintf('%014s', $uniqueIdPart);
	}

	function _output($parameters, $response) {
		// todo: log internal system requests without system user ID
		echo json_encode($response);
		exit;
	}

	$parameters = array(
		'processId' => getmypid()
	);
	$response = array(
		'authenticatedStatus' => '1',
		'data' => array(),
		'message' => 'Error processing system action, please try again.',
		'validatedStatus' => '0'
	);
	// todo

	if (file_exists('/var/www/firewall-security-api/system-action-' . $_SERVER['argv'][1] . '.php') === false) {
		$response['message'] = 'Invalid system action, please try again.';
	}

	_output($parameters, $response);
?>
