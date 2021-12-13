<?php
	function _output($response) {
		echo json_encode($response);
		exit;
	}

	$response = array(
		'authenticated_status' => '1',
		'data' => array(),
		'message' => 'Invalid node endpoint request, please try again.',
		'valid_status' => '0'
	);

	if (empty($_SERVER['argv'][1]) === true) {
		_output($response);
	}

	$parameters = array(
		'action' => $_SERVER['argv'][1]
	);

	if (file_exists('/usr/local/ghostcompute/system_data.json') === false) {
		$response['message'] = 'Node must be redeployed because system data is missing, please try again.';
		_output($response);
	}

	$systemData = json_decode(file_get_contents('/usr/local/ghostcompute/system_data.json'), true);

	if ($systemData === false) {
		$response['message'] = 'Error listing system data, please try again.';
		_output($response);
	}

	if (
		(empty($systemData['endpoint_destination_address']) === true) ||
		(is_string($systemData['endpoint_destination_address']) === false) ||
		(isset($systemData['version']) === false) ||
		(is_numeric($systemData['version']) === false)
	) {
		$response['message'] = 'Node must be redeployed because system data is invalid, please try again.';
		_output($response);
	}

	$parameters += array(
		'system_endpoint_destination_address' => $systemData['endpoint_destination_address'],
		'system_version' => $systemData['version']
	);

	// todo: list system_version from system_endpoint_destination_address data and update files if new version is available

	if (
		(ctype_alnum(str_replace('_', '', $parameters['action'])) === false) ||
		(file_exists('/usr/local/ghostcompute/node_action_' . $parameters['action'] . '.php') === false)
	) {
		$response['message'] = 'Invalid node endpoint request action, please try again.';
		_output($response);
	}

	require_once('/usr/local/ghostcompute/node_action_' . $parameters['action'] . '.php');
	_output($response);
?>
