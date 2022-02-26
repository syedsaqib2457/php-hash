<?php
	if (empty($_SERVER['argv'][1]) === true) {
		exit;
	}

	function _createUniqueId() {
		$uniqueId = hrtime(true);
		$uniqueId = substr($uniqueId, -10);
		$uniqueId = sprintf('%010s', $uniqueId);
		$uniqueId .= (microtime(true) * 10000) . mt_rand(100000, 999999);
		return $uniqueId;
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
		'validStatus' => '0'
	);

	if (file_exists('/var/www/firewall-security-api/system-action-' . $_SERVER['argv'][1] . '.php') === false) {
		$response['message'] = 'Invalid system action, please try again.';
	}

	_output($parameters, $response);
?>
