<?php
	if (empty($_SERVER['argv'][1]) === true) {
		exit;
	}

	function _createUniqueId() {
		$uniqueId = hrtime(true);
		$uniqueId = substr($uniqueId, 6, 10) . (microtime(true) * 10000);
		$uniqueIdCharacters = 'abcdefghijklmnopqrstuvwxyz0123456789';

		while (isset($uniqueId[29]) === false) {
			$uniqueId .= $uniqueIdCharacters[mt_rand(0, 35)];
		}

		return $uniqueId;
	}

	function _output($parameters, $response) {
		// todo: log internal system requests without system user ID
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

	if (file_exists('/var/www/cloud_node_automation_api/system_action_' . $_SERVER['argv'][1] . '.php') === false) {
		$response['message'] = 'Invalid system action, please try again.';
	}

	_output($parameters, $response);
?>
