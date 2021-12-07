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
