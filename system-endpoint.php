<?php
	function _generateUniqueId() {
		$uniqueIdPart = (microtime(true) * 10000);
		$uniqueId = sprintf('%016s', $uniqueIdPart);
		$uniqueIdPart = mt_rand(0, 99999999999999);
		return $uniqueId . sprintf('%014s', $uniqueIdPart);
	}

	function _output($parameters, $response) {
		if (($response['authenticatedStatus'] === '0') === true) {
			require_once('system-action-process-system-unauthenticated-request.php');
		}

		$systemRequestLogsData = array(
			// 'bytesReceived',
			// 'bytesSent',
			'id' => _generateUniqueId(),
			'responseAuthenticatedStatus' => $response['authenticatedStatus'],
			'responseMessage' => $response['message'],
			'responseValidatedStatus' => $response['validatedStatus'],
			'sourceIpAddress' => $_SERVER['REMOTE_ADDR']
		);

		if (empty($parameters['action']) === false) {
			$systemRequestLogsData['systemAction'] = $parameters['action'];
			$systemRequestLogsData['value'] = $parameters;
			unset($systemRequestLogsData['value']['systemDatabases']);
			$systemRequestLogsData['value'] = json_encode($systemRequestLogsData['value']);
		}

		if (empty($parameters['nodeAuthenticationToken']) === false) {
			$systemDatabasesConnections = _connect(array(
				'nodes'
			), $parameters['systemDatabases'], $response);
			$parameters['systemDatabases']['nodes'] = $systemDatabasesConnections['nodes'];
			$systemRequestLogsNode = _list(array(
				'data' => array(
					'id'
				),
				'in' => $parameters['systemDatabases']['nodes'],
				'where' => array(
					'authenticationToken' => $parameters['nodeAuthenticationToken'],
					'nodeId' => ''
				)
			), $response);
			$systemRequestLogsNode = current($systemRequestLogsNode);

			if (empty($systemRequestLogsNode) === false) {
				$systemRequestLogsData['nodeId'] = $systemRequestLogsNode['id'];
			}
		}

		if (empty($parameters['systemUserAuthenticationToken']) === false) {
			$systemRequestLogsData['systemUserAuthenticationTokenId'] = $parameters['systemUserAuthenticationTokenId'];
			$systemRequestLogsData['systemUserId'] = $parameters['systemUserId'];
		}

		if (empty($response['data']) === false) {
			$systemRequestLogsData['responseData'] = json_encode($response['data']);
		}

		_save(array(
			'data' => $systemRequestLogsData,
			'in' => $parameters['systemDatabases']['systemRequestLogs']
		), $response);
		echo json_encode($response);
		exit;
	}

	if (empty($_SERVER['REMOTE_ADDR']) === false) {
		$unauthenticatedSourceIpAddress = $_SERVER['REMOTE_ADDR'];

		if ((strpos($unauthenticatedSourceIpAddress, ':') === false) === false) {
			$unauthenticatedSourceIpAddress = str_replace(':', '_', $unauthenticatedSourceIpAddress);
		} else {
			$unauthenticatedSourceIpAddress = str_replace('.', '', $unauthenticatedSourceIpAddress);
		}

		$unauthenticatedSourceIpAddressIndex = 0;
		$unauthenticatedSourceIpAddressLogPath = '';

		while (isset($unauthenticatedSourceIpAddress[$unauthenticatedSourceIpAddressIndex]) === true) {
			$unauthenticatedSourceIpAddressLogPath .= $unauthenticatedSourceIpAddress[$unauthenticatedSourceIpAddressIndex] . '/';
			$unauthenticatedSourceIpAddressIndex++;
		}
		
		if (is_dir('/tmp/firewall-security-api/unauthenticated-source-ip-addresses-logs/denied/' . $unauthenticatedSourceIpAddressLogPath) === true) {
			echo 'Requests from source IP address ' . $_SERVER['REMOTE_ADDR'] . ' are temporarily blocked from too many unauthenticated requests, please try again.';
			exit;
		}
	}

	$parameters = array();

	if (empty($_POST['json']) === false) {
		$parametersData = json_decode($_POST['json'], true);

		if (empty($parametersData) === false) {
			$parameters = $parametersData;
		}
	}

	$response = array(
		'authenticatedStatus' => '0',
		'data' => array(),
		'message' => 'Invalid parameters, please try again.',
		'validatedStatus' => '0'
	);
	require_once('system-databases.php');
	$systemSettingsData = file_get_contents('system-settings-data.json');
	$systemSettingsData = json_decode($systemSettingsData, true);

	if ($systemSettingsData === false) {
		$response['message'] = 'Error listing system settings data, please try again.';
		_output($parameters, $response);
	}

	foreach ($systemSettingsData as $systemSettingsDataKey => $systemSettingsDataValue) {
		$parameters['system' . ucwords($systemSettingsDataKey)] = $systemSettingsDataValue;
	}

	if (empty($_POST['json']) === false) {
		if (empty($parametersData) === true) {
			_output($parameters, $response);
		}

		if (empty($parameters['action']) === true) {
			$response['message'] = 'Request must have an action, please try again.';
			_output($parameters, $response);
		}

		if ((strpos($parameters['action'], '/') === false) === false) {
			$response['message'] = 'Invalid system action, please try again.';
			_output($parameters, $response);
		}

		$actionIndex = 0;
		$parameters['systemActionFile'] = '';

		while (isset($parameters['action'][$actionIndex]) === true) {
			if (ctype_upper($parameters['action'][$actionIndex]) === true) {
				$parameters['systemActionFile'] .= '-' . strtolower($parameters['action'][$actionIndex]);
			} else {
				$parameters['systemActionFile'] .= $parameters['action'][$actionIndex];
			}

			$actionIndex++;
		}

		$parameters['systemActionFile'] = '/var/www/firewall-security-api/' . $parameters['systemEndpointDestinationSubdirectory'] . '/system-action-' . $parameters['systemActionFile'] . '.php';

		if (file_exists($parameters['systemActionFile']) === false) {
			$response['message'] = 'Error listing system action file, please try again.';
			_output($parameters, $response);
		}

		if (
			(
				(empty($parameters['nodeAuthenticationToken']) === true) &&
				(empty($parameters['systemUserAuthenticationToken']) === true)
			) ||
			(
				(empty($parameters['nodeAuthenticationToken']) === false) &&
				(empty($parameters['systemUserAuthenticationToken']) === false)
			)
		) {
			$response['message'] = 'Request must have either a node authentication token or a system user authentication token, please try again.';
			_output($parameters, $response);
		}

		if (empty($parameters['nodeAuthenticationToken']) === false) {
			$systemDatabasesConnections = _connect(array(
				'nodes'
			), $parameters['systemDatabases'], $response);
			$parameters['systemDatabases']['nodes'] = $systemDatabasesConnections['nodes'];
			$node = _list(array(
				'data' => array(
					'externalIpAddressVersion4',
					'externalIpAddressVersion6',
					'id',
					'internalIpAddressVersion4',
					'internalIpAddressVersion6',
					'nodeId'
				),
				'in' => $parameters['systemDatabases']['nodes'],
				'where' => array(
					'authenticationToken' => $parameters['nodeAuthenticationToken'],
					'nodeId' => ''
				)
			), $response);
			$node = current($node);

			if (empty($node) === true) {
				$response['message'] = 'Invalid node authentication token, please try again.';
				_output($parameters, $response);
			}

			$parameters['node'] = $node;
			unset($parameters['systemUserAuthenticationToken']);
		} else {
			$systemUserAuthenticationToken = _list(array(
				'data' => array(
					'id',
					'systemUserId'
				),
				'in' => $parameters['systemDatabases']['systemUserAuthenticationTokens'],
				'where' => array(
					'value' => $parameters['systemUserAuthenticationToken']
				)
			), $response);
			$systemUserAuthenticationToken = current($systemUserAuthenticationToken);

			if (empty($systemUserAuthenticationToken) === true) {
				$response['message'] = 'Invalid system user authentication token, please try again.';
				_output($parameters, $response);
			}

			$parameters['systemUserAuthenticationTokenId'] = $systemUserAuthenticationToken['id'];
			$parameters['systemUserId'] = $systemUserAuthenticationToken['systemUserId'];
			$systemUserAuthenticationTokenScopeCount = _count(array(
				'in' => $parameters['systemDatabases']['systemUserAuthenticationTokenScopes'],
				'where' => array(
					'systemAction' => $parameters['action'],
					'systemUserAuthenticationTokenId' => $systemUserAuthenticationToken['id']
				)
			), $response);

			if (($systemUserAuthenticationTokenScopeCount === 1) === false) {
				$response['message'] = 'Invalid system user authentication token scope, please try again.';
				_output($parameters, $response);
			}

			require_once('/var/www/firewall-security-api/' . $parameters['systemEndpointDestinationSubdirectory'] . '/system-action-validate-ip-address-version-number.php');
			$parameters['source'] = array(
				'ipAddress' => $_SERVER['REMOTE_ADDR'],
				'ipAddressVersionNumber' => '4'
			);

			if ((strpos($parameters['source']['ipAddress'], ':') === false) === false) {
				$parameters['source']['ipAddressVersionNumber'] = '6';
			}

			$parameters['source']['ipAddress'] = _validateIpAddressVersionNumber($parameters['source']['ipAddress'], $parameters['source']['ipAddressVersionNumber']);

			if ($parameters['source']['ipAddress'] === false) {
				$response['message'] = 'Invalid source IP address, please try again.';
				_output($parameters, $response);
			}

			$systemUserAuthenticationTokenSourceCountParameters = array(
				'in' => $parameters['systemDatabases']['systemUserAuthenticationTokenSources'],
				'where' => array(
					'systemUserAuthenticationTokenId' => $systemUserAuthenticationToken['id']
				)
			);
			$systemUserAuthenticationTokenSourceCount = _count($systemUserAuthenticationTokenSourceCountParameters, $response);

			if (($systemUserAuthenticationTokenSourceCount === 0) === false) {
				$systemUserAuthenticationTokenSourceCountParameters['where']['ipAddressRangeStart lessThanOrEqualTo'] = $parameters['source']['ipAddress'];
				$systemUserAuthenticationTokenSourceCountParameters['where']['ipAddressRangeStop greaterThanOrEqualTo'] = $parameters['source']['ipAddress'];
				$systemUserAuthenticationTokenSourceCountParameters['where']['ipAddressRangeVersionNumber'] = $parameters['source']['ipAddressVersionNumber'];
				$systemUserAuthenticationTokenSourceCount = _count($systemUserAuthenticationTokenSourceCountParameters, $response);

				if (($systemUserAuthenticationTokenSourceCount === 0) === true) {
					$response['message'] = 'Invalid system user authentication token source IP address ' . $sourceIpAddress . ', please try again.';
					_output($parameters, $response);
				}
			}

			unset($parameters['nodeAuthenticationToken']);
		}

		$response['authenticatedStatus'] = '1';
		require_once($parameters['systemActionFile']);

		if (function_exists('_' . $parameters['action']) === true) {
			$response = call_user_func('_' . $parameters['action'], $parameters, $response);
		}
	}

	_output($parameters, $response);
?>
