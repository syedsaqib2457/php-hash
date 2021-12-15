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

	if (file_exists('/usr/local/ghostcompute/node_data.json') === false) {
		$response['message'] = 'Node must be redeployed because node data file is missing, please try again.';
		_output($response);
	}

	$nodeData = json_decode(file_get_contents('/usr/local/ghostcompute/node_data.json'), true);

	if ($nodeData === false) {
		$response['message'] = 'Error listing node data, please try again.';
		_output($response);
	}

	if (
		(empty($nodeData['authentication_token']) === true) ||
		(is_string($nodeData['authentication_token']) === false) ||
		(empty($nodeData['system_endpoint_destination_address']) === true) ||
		(is_string($nodeData['system_endpoint_destination_address']) === false) ||
		(isset($nodeData['system_version']) === false) ||
		(is_numeric($nodeData['system_version']) === false)
	) {
		$response['message'] = 'Node must be redeployed because node data is invalid, please try again.';
		_output($response);
	}

	$parameters = array(
		'action' => $_SERVER['argv'][1],
		'node_authentication_token' => $nodeData['authentication_token'],
		'system_endpoint_destination_address' => $nodeData['system_endpoint_destination_address'],
		'system_version' => $nodeData['system_version']
	);

	if (in_array(strval($parameters['action']), array(
		'process_node_processes',
		'process_node_resource_usage_logs',
		'process_node_user_blockchain_mining',
		'process_node_user_request_logs'
	)) === true) {
		$systemSettingsFile = '/tmp/' . $parameters['action'] . '_system_settings.json';
		shell_exec('sudo wget -O ' . $systemSettingsFile . ' --no-dns-cache --post-data "json={\"action\":\"list_system_settings\",\"node_authentication_token\":\"' . $parameters['node_authentication_token'] . '\"}" --retry-connrefused --timeout=10 --tries=2 ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php');

		if (file_exists($systemSettingsFile) === false) {
			$response['message'] = 'Error listing system settings, please try again.';
			_output($response);
		}

		$systemSettings = json_decode(file_get_contents($systemSettingsFile), true);

		if ($systemSettings === false) {
			$response['message'] = 'Error listing system settings, please try again.';
			_output($response);
		}

		if (($parameters['system_version'] < $systemSettings['version']) === true) {
			$systemFiles = json_decode($systemSettings['files'], true);

			foreach ($systemFiles as $systemFile) {
				// todo: kill existing $systemFile process
				// todo: update system file
			}
		}

		// todo: update system_endpoint_destination_address if changed
	}

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
