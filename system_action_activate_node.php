<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'nodes'
	), $parameters['system_databases'], $response);

	function _activateNode($parameters, $response) {
		$nodeParameters = array(
			'data' => array(
				'activated_status',
				'authentication_token',
				'deployed_status'
			),
			'in' => $parameters['system_databases']['nodes']
		);

		if (empty($parameters['where']['authentication_token']) === false) {
			$nodeParameters['where']['authentication_token'] = $parameters['where']['authentication_token'];
		}

		if (empty($parameters['where']['id']) === false) {
			$nodeParameters['where']['id'] = $parameters['where']['id'];
		}

		if (empty($parameters['where']) === true) {
			$response['message'] = 'Node authentication token or ID is required, please try again.';
			return $response;
		}

		$node = _list($nodeParameters, $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node authentication token or ID, please try again.';
			return $response;
		}

		if (
			(empty($parameters['node_authentication_token']) === true) &&
			(($node['deployed_status'] === '0') === true)
		) {
			$systemEndpointDestinationAddress = _list(array(
				'data' => array(
					'value'
				),
				'where' => array(
					'name' => 'system_endpoint_destination_address'
				)
			), $response);
			$systemEndpointDestinationAddress = current(current($systemEndpointDestinationAddress));

			if (empty($systemEndpointDestinationAddress) === true) {
				$response['message'] = 'Error listing system endpoint destination address, please try again.';
				return $response;
			}

			// todo: use updated filename instead of proxy.php after node deploy refactor
			$response['data']['command'] = 'cd /tmp && rm -rf /etc/cloud/ /var/lib/cloud/ ; apt-get update ; DEBIAN_FRONTEND=noninteractive apt-get -y install sudo ; sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk \'{print $1}\') ; sudo $(whereis telinit | awk \'{print $2}\') u ; sudo rm -rf /etc/cloud/ /var/lib/cloud/ ; sudo dpkg --configure -a ; sudo apt-get update && sudo DEBIAN_FRONTEND=noninteractive apt-get -y php wget --fix-missing && sudo wget -O proxy.php --no-dns-cache --retry-connrefused --timeout=10 --tries=2 "' . $systemEndpointDestinationAddress . '/node_action_deploy_node.php?' . random_bytes(10) . '" && sudo php node_action_deploy_node.php ' . $parameters['node_authentication_token'] . ' ' . $systemEndpointDestinationAddress;
			$response['message'] = 'Node is ready for activation.';
			return $response;
		}

		if (($node['activated_status'] === '1') === true) {
			$response['message'] = 'Node is already activated, please try again.';
			return $response;
		}

		$nodeParameters['data'] = array(
			'activated_status' => '1'
		);
		_update($nodeParameters, $response);
		$response['message'] = 'Node activated successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'activate_node') === true) {
		$response = _activateNode($parameters, $response);
		_output($response);
	}
?>
