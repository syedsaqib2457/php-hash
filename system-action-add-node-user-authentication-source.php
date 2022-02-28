<?php
	if (empty($parameters) === true) {
		exit;
	}

	$systemDatabasesConnections = _connect(array(
		'nodeProcessNodeUserAuthenticationSources',
		'nodeProcessNodeUsers',
		'nodeUserAuthenticationSources',
		'nodeUsers'
	), $parameters['systemDatabases'], $response);
	$parameters['systemDatabases']['nodeProcessNodeUserAuthenticationSources'] = $systemDatabasesConnections['nodeProcessNodeUserAuthenticationSources'];
	$parameters['systemDatabases']['nodeProcessNodeUsers'] = $systemDatabasesConnections['nodeProcessNodeUsers'];
	$parameters['systemDatabases']['nodeUserAuthenticationSources'] = $systemDatabasesConnections['nodeUserAuthenticationSources'];
	$parameters['systemDatabases']['nodeUsers'] = $systemDatabasesConnections['nodeUsers'];

	function _addNodeUserAuthenticationSource($parameters, $response) {
		if (empty($parameters['data']['nodeUserId']) === true) {
			$response['message'] = 'Node user authentication source must have a node user ID, please try again.';
			return $response;
		}

		$nodeUser = _list(array(
			'data' => array(
				'id'
			),
			'in' => $parameters['systemDatabases']['nodeUsers'],
			'where' => array(
				'id' => $parameters['data']['nodeUserId']
			)
		), $response);
		$nodeUser = current($nodeUser);

		if (empty($nodeUser) === true) {
			$response['message'] = 'Error listing node user authentication source node user, please try again.';
			return $response;
		}

		$parameters['data']['ipAddressBlockLength'] = '32';
		$parameters['data']['ipAddressVersionNumber'] = '4';

		if ((strpos($parameters['data']['ipAddress'], ':') === false) === false) {
			$parameters['data']['ipAddressBlockLength'] = '128';
			$parameters['data']['ipAddressVersionNumber'] = '6';
		}

		if ((strpos($parameters['data']['ipAddress'], '/') === false) === false) {
			$parameters['data']['ipAddress'] = explode('/', $parameters['data']['ipAddress']);
			$parameters['data']['ipAddressBlockLength'] = next($parameters['data']['ipAddress']);
			$parameters['data']['ipAddress'] = prev($parameters['data']['ipAddress']);
		}

		$parameters['data']['ipAddress'] = _validateIpAddressVersionNumber($parameters['data']['ipAddress'], $parameters['data']['ipAddressVersionNumber']);

		if ($parameters['data']['ipAddress'] === false) {
			$response['message'] = 'Invalid node user authentication source IP address, please try again.';
			return $response;
		}

		if (
			(
				(($parameters['data']['ipAddressVersionNumber'] === '4') === true) &&
				(
					(($parameters['data']['ipAddressBlockLength'] > '32') === true) ||
					(($parameters['data']['ipAddressBlockLength'] < '8') === true)
				)
			) ||
			(
				(($parameters['data']['ipAddressVersionNumber'] === '6') === true) &&
				(
					(($parameters['data']['ipAddressBlockLength'] > '128') === true) ||
					(($parameters['data']['ipAddressBlockLength'] < '48') === true)
				)
			)
		) {
			$response['message'] = 'Invalid node user authentication source IP address block length ' . $parameters['data']['ip_address_block_length'] . ', please try again.';
			return $response;
		}

		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['systemDatabases']['nodeUserAuthenticationSources']
		), $response);
		$nodeUserAuthenticationSource = _list(array(
			'in' => $parameters['systemDatabases']['nodeUserAuthenticationSources'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeUserAuthenticationSource = current($nodeUserAuthenticationSource);
		$nodeProcessNodeUsers = _list(array(
			'data' => array(
				'nodeId',
				'nodeNodeId',
				'nodeProcessType',
				'nodeUserId'
			),
			'in' => $parameters['systemDatabases']['nodeProcessNodeUsers'],
			'where' => array(
				'nodeUserId' => $parameters['data']['nodeUserId']
			)
		), $response);

		if (empty($nodeProcessNodeUsers) === false) {
			$nodeProcessNodeUserNodeAuthenticationSources = array();

			foreach ($nodeProcessNodeUsers as $nodeProcessNodeUser) {
				$nodeProcessNodeUser['createdTimestamp'] = $nodeUserAuthenticationSource['createdTimestamp'];
				$nodeProcessNodeUser['modifiedTimestamp'] = $nodeUserAuthenticationSource['modifiedTimestamp'];
				$nodeProcessNodeUser['nodeUserAuthenticationSourceId'] = $nodeUserAuthenticationSource['id'];
				$nodeProcessNodeUser['nodeUserAuthenticationSourceIpAddress'] = $nodeUserAuthenticationSource['ipAddress'];
				$nodeProcessNodeUser['nodeUserAuthenticationSourceIpAddressBlockLength'] = $nodeUserAuthenticationSource['ipAddressBlockLength'];
				$nodeProcessNodeUser['nodeUserAuthenticationSourceIpAddressVersionNumber'] = $nodeUserAuthenticationSource['ipAddressVersionNumber'];
				$nodeProcessNodeUserNodeAuthenticationSources[] = $nodeProcessNodeUser;
			}

			_save(array(
				'data' => $nodeProcessNodeUserNodeAuthenticationSources,
				'in' => $parameters['systemDatabases']['nodeProcessNodeUserNodeAuthenticationSources']
			));
		}

		$response['data'] = $nodeUserAuthenticationSource;
		$response['message'] = 'Node user authentication source added successfully.';
		$response['validatedStatus'] = '1';
		return $response;
	}
?>
