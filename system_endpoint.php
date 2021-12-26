<?php
	function _createUniqueId() {
		$uniqueId = random_bytes(17);
		$uniqueId = bin2hex($uniqueId);
		$uniqueId = uniqid() . $uniqueId;
		return $uniqueId;
	}

	function _output($response) {
		if (empty($response['authenticated_status']) === true) {
			// todo: log invalid action for DDoS protection
		}

		echo json_encode($response);
		exit;
	}

	$response = array(
		'authenticated_status' => '0',
		'data' => array(),
		'message' => 'Invalid endpoint request, please try again.',
		'valid_status' => '0'
	);

	if (empty($_POST) === false) {
		$parameters = json_decode($_POST, true);

		if (empty($parameters) === true) {
			_output($response);
		}

		require_once('/var/www/ghostcompute/system_databases.php');

		if (
			(ctype_alnum(str_replace('_', '', $parameters['action'])) === false) ||
			(file_exists('/var/www/ghostcompute/system_action_' . $parameters['action'] . '.php') === false)
		) {
			$response['message'] = 'Invalid endpoint request action, please try again.';
			_output($response);
		}

		if (
			(empty($parameters['node_authentication_token']) === true) &&
			(empty($parameters['system_user_authentication_token']) === true)
		) {
			$response['message'] = 'Endpoint request must have either a node or system user authentication token, please try again.';
			_output($response);
		}

		if (
			(empty($parameters['node_authentication_token']) === true) ||
			(ctype_alnum($parameters['node_authentication_token']) === false)
		) {
			$response['message'] = 'Invalid endpoint request node authentication token, please try again.';
			_output($response);
		}

		if (
			(empty($parameters['system_user_authentication_token']) === true) ||
			(ctype_alnum($parameters['system_user_authentication_token']) === false)
		) {
			$response['message'] = 'Invalid endpoint request system user authentication token, please try again.';
			_output($response);
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
				'in' => $parameters['databases']['nodes'],
				'where' => array(
					'authentication_token' => $parameters['node_authentication_token']
				)
			), $response);
			$node = current($node);

			if (empty($node) === true) {
				$response['message'] = 'Invalid endpoint node authentication token, please try again.';
				_output($response);
			}

			$parameters['node'] = $node;

			if (in_array(strval($parameters['action']), array(
				'add_node_process_node_user_request_logs'
			)) === false) {
				$response['message'] = 'Invalid endpoint node authentication token scope, please try again.';
				_output($response);
			}
		} else {
			$systemUserAuthenticationToken = _list(array(
				'data' => array(
					'id',
					'system_user_id'
				),
				'in' => $parameters['databases']['system_user_authentication_tokens'],
				'where' => array(
					'string' => $parameters['system_user_authentication_token']
				)
			), $response);
			$systemUserAuthenticationToken = current($systemUserAuthenticationToken);

			if (empty($systemUserAuthenticationToken) === true) {
				$response['message'] = 'Invalid endpoint system user authentication token, please try again.';
				_output($response);
			}

			$parameters['system_user_id'] = $systemUserAuthenticationToken['system_user_id'];
			$systemUserAuthenticationTokenScopeCount = _count(array(
				'in' => $parameters['databases']['system_user_authentication_token_scopes'],
				'where' => array(
					'system_action' => $parameters['action'],
					'system_user_authentication_token_id' => $systemUserAuthenticationToken['id']
				)
			), $response);

			if (($systemUserAuthenticationTokenScopeCount <= 0) === true) {
				$response['message'] = 'Invalid endpoint system user authentication token scope, please try again.';
				_output($response);
			}

			require_once('/var/www/ghostcompute/system_action_validate_ip_address_version_number.php');
			$parameters['source'] = array(
				'ip_address' => $_SERVER['REMOTE_ADDR'],
				'ip_address_version_number' => '4'
			);

			if (is_int(strpos($parameters['source']['ip_address'], ':')) === true) {
				$parameters['source']['ip_address_version_number'] = '6';
			}

			$parameters['source']['ip_address'] = _validateIpAddressVersionNumber($parameters['source']['ip_address'], $parameters['source']['ip_address_version_number']);

			if ($parameters['source']['ip_address'] === false) {
				$response['message'] = 'Invalid source IP address, please try again.';
				_output($response);
			}

			$systemUserAuthenticationTokenSourceCountParameters = array(
				'in' => $parameters['databases']['system_user_authentication_token_sources'],
				'where' => array(
					'system_user_authentication_token_id' => $systemUserAuthenticationToken['id']
				)
			);
			$systemUserAuthenticationTokenSourceCount = _count($systemUserAuthenticationTokenSourceCountParameters, $response);

			if (($systemUserAuthenticationTokenSourceCount > 0) === true) {
				$systemUserAuthenticationTokenSourceCountParameters['where'] += array(
					'ip_address_range_start <=' => $parameters['source']['ip_address'],
					'ip_address_range_stop >=' => $parameters['source']['ip_address'],
					'ip_address_range_version_number' => $parameters['source']['ip_address_version_number']
				);
				$systemUserAuthenticationTokenSourceCount = _count($systemUserAuthenticationTokenSourceCountParameters, $response);

				if (($systemUserAuthenticationTokenSourceCount <= 0) === true) {
					$response['message'] = 'Invalid endpoint system user authentication token source IP address ' . $sourceIpAddress . ', please try again.';
					_output($response);
				}
			}
		}

		$response['authenticated_status'] = '1';
		require_once('/var/www/ghostcompute/system_action_' . $parameters['action'] . '.php');
	}

	_output($response);
?>
