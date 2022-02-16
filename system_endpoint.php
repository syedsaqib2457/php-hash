<?php
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
		$systemRequestLogsData = array(
			// 'bytes_received',
			// 'bytes_sent',
			'id' => _createUniqueId(),
			'response_authenticated_status' => $response['authenticated_status'],
			'response_message' => $response['message'],
			'response_valid_status' => $response['valid_status'],
			'source_ip_address' => $_SERVER['REMOTE_ADDR']
		);

		if (empty($parameters['action']) === false) {
			$systemRequestLogsData['system_action'] = $parameters['action'];
			$systemRequestLogsData['value'] = $parameters;
			unset($systemRequestLogsData['value']['system_databases']);
			$systemRequestLogsData['value'] = json_encode($systemRequestLogsData['value']);
		}

		if (empty($parameters['node_authentication_token']) === false) {
			$parameters['system_databases'] += _connect(array(
				'nodes'
			), $parameters['system_databases'], $response);
			$systemRequestLogsNode = _list(array(
				'data' => array(
					'id'
				),
				'in' => $parameters['system_databases']['nodes'],
				'where' => array(
					'authentication_token' => $parameters['node_authentication_token'],
					'node_id' => null
				)
			), $response);
			$systemRequestLogsNode = current($systemRequestLogsNode);

			if (empty($systemRequestLogsNode) === false) {
				$systemRequestLogsData['node_id'] = $systemRequestLogsNode['id'];
			}
		}

		if (empty($parameters['system_user_authentication_token']) === false) {
			$systemRequestLogsData['system_user_authentication_token_id'] = $parameters['system_user_authentication_token_id'];
			$systemRequestLogsData['system_user_id'] = $parameters['system_user_id'];
		}

		if (empty($response['data']) === false) {
			$systemRequestLogsData['response_data'] = json_encode($response['data']);
		}

		_save(array(
			'data' => $systemRequestLogsData,
			'in' => $parameters['system_databases']['system_request_logs']
		), $response);
		echo json_encode($response);
		exit;
	}

	$response = array(
		'authenticated_status' => '0',
		'data' => array(),
		'message' => 'Invalid system endpoint parameters, please try again.',
		'valid_status' => '0'
	);
	require_once('/var/www/nodecompute/system_databases.php');
	$systemSettingsData = file_get_contents('/var/www/nodecompute/system_settings_data.json');
	$systemSettingsData = json_decode($systemSettingsData, true);

	if ($systemSettingsData === false) {
		$response['message'] = 'Error listing system settings data, please try again.';
		_output($parameters, $response);
	}

	foreach ($systemSettingsData as $systemSettingsDataKey => $systemSettingsDataValue) {
		$parameters[$systemSettingsDataKey] = $systemSettingsDataValue;
	}

	if (empty($_POST['json']) === false) {
		$parametersData = json_decode($_POST['json'], true);

		if (empty($parametersData) === true) {
			_output($parameters, $response);
		}

		$parameters = $parametersData;

		if (empty($parameters['action']) === true) {
			$response['message'] = 'System endpoint must have an action, please try again.';
			_output($parameters, $response);
		}

		$systemAction = str_replace('_', '', $parameters['action']);

		if (
			(ctype_alnum($systemAction) === false) ||
			(file_exists('/var/www/nodecompute/system_action_' . $parameters['action'] . '.php') === false)
		) {
			$response['message'] = 'Invalid system endpoint action, please try again.';
			_output($parameters, $response);
		}

		if (
			(
				(empty($parameters['node_authentication_token']) === true) &&
				(empty($parameters['system_user_authentication_token']) === true)
			) ||
			(
				(empty($parameters['node_authentication_token']) === false) &&
				(empty($parameters['system_user_authentication_token']) === false)
			)
		) {
			$response['message'] = 'System endpoint must have either a node authentication token or a system user authentication token, please try again.';
			_output($parameters, $response);
		}

		if (
			(empty($parameters['node_authentication_token']) === false) &&
			(ctype_alnum($parameters['node_authentication_token']) === false)
		) {
			$response['message'] = 'Invalid system endpoint node authentication token , please try again.';
			_output($parameters, $response);
		}

		if (
			(empty($parameters['system_user_authentication_token']) === false) &&
			(ctype_alnum($parameters['system_user_authentication_token']) === false)
		) {
			$response['message'] = 'Invalid system endpoint system user authentication token, please try again.';
			_output($parameters, $response);
		}

		if (empty($parameters['node_authentication_token']) === false) {
			$node = _list(array(
				'data' => array(
					'external_ip_address_version_4',
					'external_ip_address_version_6',
					'id',
					'internal_ip_address_version_4',
					'internal_ip_address_version_6',
					'node_id'
				),
				'in' => $parameters['system_databases']['nodes'],
				'where' => array(
					'authentication_token' => $parameters['node_authentication_token'],
					'node_id' => null
				)
			), $response);
			$node = current($node);

			if (empty($node) === true) {
				$response['message'] = 'Invalid system endpoint node authentication token, please try again.';
				_output($parameters, $response);
			}

			$parameters['node'] = $node;
			unset($parameters['system_user_authentication_token']);
		} else {
			$systemUserAuthenticationToken = _list(array(
				'data' => array(
					'id',
					'system_user_id'
				),
				'in' => $parameters['system_databases']['system_user_authentication_tokens'],
				'where' => array(
					'value' => $parameters['system_user_authentication_token']
				)
			), $response);
			$systemUserAuthenticationToken = current($systemUserAuthenticationToken);

			if (empty($systemUserAuthenticationToken) === true) {
				$response['message'] = 'Invalid system endpoint system user authentication token, please try again.';
				_output($parameters, $response);
			}

			$parameters['system_user_authentication_token_id'] = $systemUserAuthenticationToken['id'];
			$parameters['system_user_id'] = $systemUserAuthenticationToken['system_user_id'];
			$systemUserAuthenticationTokenScopeCount = _count(array(
				'in' => $parameters['system_databases']['system_user_authentication_token_scopes'],
				'where' => array(
					'system_action' => $parameters['action'],
					'system_user_authentication_token_id' => $systemUserAuthenticationToken['id']
				)
			), $response);

			if (($systemUserAuthenticationTokenScopeCount < 1) === true) {
				$response['message'] = 'Invalid system endpoint request system user authentication token scope, please try again.';
				_output($parameters, $response);
			}

			require_once('/var/www/nodecompute/system_action_validate_ip_address_version_number.php');
			$parameters['source'] = array(
				'ip_address' => $_SERVER['REMOTE_ADDR'],
				'ip_address_version_number' => '4'
			);

			if ((strpos($parameters['source']['ip_address'], ':') === false) === false) {
				$parameters['source']['ip_address_version_number'] = '6';
			}

			$parameters['source']['ip_address'] = _validateIpAddressVersionNumber($parameters['source']['ip_address'], $parameters['source']['ip_address_version_number']);

			if ($parameters['source']['ip_address'] === false) {
				$response['message'] = 'Invalid system endpoint source IP address, please try again.';
				_output($parameters, $response);
			}

			$systemUserAuthenticationTokenSourceCountParameters = array(
				'in' => $parameters['system_databases']['system_user_authentication_token_sources'],
				'where' => array(
					'system_user_authentication_token_id' => $systemUserAuthenticationToken['id']
				)
			);
			$systemUserAuthenticationTokenSourceCount = _count($systemUserAuthenticationTokenSourceCountParameters, $response);

			if (($systemUserAuthenticationTokenSourceCount > 0) === true) {
				$systemUserAuthenticationTokenSourceCountParameters['where']['ip_address_range_start <='] = $parameters['source']['ip_address'];
				$systemUserAuthenticationTokenSourceCountParameters['where']['ip_address_range_stop >='] = $parameters['source']['ip_address'];
				$systemUserAuthenticationTokenSourceCountParameters['where']['ip_address_range_version_number'] = $parameters['source']['ip_address_version_number'];
				$systemUserAuthenticationTokenSourceCount = _count($systemUserAuthenticationTokenSourceCountParameters, $response);

				if (($systemUserAuthenticationTokenSourceCount === 0) === true) {
					$response['message'] = 'Invalid system endpoint system user authentication token source IP address ' . $sourceIpAddress . ', please try again.';
					_output($parameters, $response);
				}
			}

			unset($parameters['node_authentication_token']);
		}

		$response['authenticated_status'] = '1';
		require_once('/var/www/nodecompute/system_action_' . $parameters['action'] . '.php');
	}

	_output($parameters, $response);
?>
