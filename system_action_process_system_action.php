<?php
	if (empty($_SERVER['argv'][1]) === true) {
		exit;
	}

	function _createUniqueId() {
		$uniqueId = random_bytes(17) . hrtime(true);
		$uniqueId = bin2hex($uniqueId);
		return $uniqueId;
	}

	function _output($response) {
		echo json_encode($response);
		exit;
	}

	$parameters = array(
		'process_id' => getmypid()
	);
	$response = array(
		'authenticated_status' => '1',
		'data' => array(),
		'message' => 'Error processing system action, please try again.',
		'valid_status' => '0'
	);

	if (file_exists('/var/www/nodecompute/system_action_' . $_SERVER['argv'][1] . '.php') === false) {
		$response['message'] = 'Invalid system action, please try again.';
	}

	_output($response);
?>
