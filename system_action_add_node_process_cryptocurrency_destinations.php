<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_process_cryptocurrency_destinations'
	), $parameters['system_databases'], $response);
	require_once('/var/www/nodecompute/system_action_validate_ip_address_version_number.php');

	function _addNodeProcessCryptocurrencyDestination($parameters, $response) {
		if (empty($parameters['data']['ip_address']) === true) {
			$response['message'] = 'Node process cryptocurrency destination must have an IP address, please try again.';
			return $response;
		}

		$ipAddressVersionNumbers = array(
			'4',
			'6'
		);

		foreach ($ipAddressVersionNumbers as $ipAddressVersionNumber) {
			$ipAddress = _validateIpAddressVersionNumber($ipAddress, $ipAddressVersionNumber);

			if (is_string($ipAddress) === true) {
				$parameters['data']['ip_address'] = $ipAddress;
				break;
			}
		}

		if ($ipAddress === false) {
			$response['message'] = 'Invalid node process cryptocurrency destination, please try again.';
			return $response;
		}

		$existingNodeProcessCryptocurrencyDestinationCount = _count(array(
			'in' => $parameters['system_databases']['node_process_cryptocurrency_destinations'],
			'where' => array(
				'ip_address' => $parameters['data']['ip_address']
			)
		), $response);

		if (($existingNodeProcessCryptocurrencyDestinationCount > 0) === true) {
			$response['message'] = 'Node process cryptocurrency destination already exists with the same IP address ' . $parameters['data']['ip_address'] . ', please try again.';
			return $response;
		}

		// todo: validate duplicate node ip addresses + overwrite duplicate node reserved internal IP addresses
		$parameters['data']['id'] = _createUniqueId();
		_save(array(
			'data' => $parameters['data'],
			'in' => $parameters['system_databases']['node_process_cryptocurrency_destinations']
		), $response);
		$nodeProcessCryptocurrencyDestination = _list(array(
			'in' => $parameters['system_databases']['node_process_cryptocurrency_destinations'],
			'where' => array(
				'id' => $parameters['data']['id']
			)
		), $response);
		$nodeProcessCryptocurrencyDestination = current($nodeProcessCryptocurrencyDestination);
		$response['data'] = $nodeProcessCryptocurrencyDestination;
		$response['message'] = 'Node process cryptocurrency destination added successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'node_process_cryptocurrency_destinations') === true) {
		$response = _addNodeProcessCryptocurrencyDestination($parameters, $response);
	}
?>
