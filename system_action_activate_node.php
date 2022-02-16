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
				'deployed_status',
				'node_authentication_token'
			),
			'in' => $parameters['system_databases']['nodes']
		);

		if (empty($parameters['node_authentication_token']) === false) {
			$nodeParameters['where']['node_authentication_token'] = $parameters['node_authentication_token'];
		}

		if (empty($parameters['where']['id']) === false) {
			$nodeParameters['where']['id'] = $parameters['where']['id'];
		}

		if (empty($parameters['where']['node_authentication_token']) === true) {
			$response['message'] = 'Node must have an authentication token ID, please try again.';
			return $response;
		}

		$node = _list($nodeParameters, $response);
		$node = current($node);

		if (empty($node) === true) {
			$response['message'] = 'Invalid node, please try again.';
			return $response;
		}

		if (
			(($node['deployed_status'] === '0') === true) &&
			(empty($parameters['node_authentication_token']) === true)
		) {
			$systemEndpointDestinationIpAddress = _list(array(
				'data' => array(
					'value'
				),
				'in' => $parameters['system_databases']['system_settings'],
				'where' => array(
					'name' => 'system_endpoint_destination_ip_address'
				)
			), $response);
			$systemEndpointDestinationIpAddress = current($systemEndpointDestinationIpAddress);
			$systemEndpointDestinationIpAddress = current($systemEndpointDestinationIpAddress);

			if (empty($systemEndpointDestinationIpAddress) === true) {
				$response['message'] = 'Error listing system endpoint destination address, please try again.';
				return $response;
			}

			$response['data']['command'] = 'cd /tmp && rm -rf /etc/cloud/ /var/lib/cloud/ ; apt-get update ; DEBIAN_FRONTEND=noninteractive apt-get -y install sudo ; sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk \'{print $1}\') ; sudo $(whereis telinit | awk \'{print $2}\') u ; sudo rm -rf /etc/cloud/ /var/lib/cloud/ ; sudo dpkg --configure -a ; sudo apt-get update && sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php7.3 wget --fix-missing && sudo wget -O node_action_deploy_node.php --no-dns-cache --retry-connrefused --timeout=10 --tries=2 "' . $systemEndpointDestinationIpAddress . '/node_action_deploy_node.php?$RANDOM" && sudo php node_action_deploy_node.php ' . $parameters['node_authentication_token'] . ' ' . $systemEndpointDestinationIpAddress;
			$response['message'] = 'Node is ready for activation and deployment.';
			$response['valid_status'] = '1';
			return $response;
		}

		if (($node['activated_status'] === '1') === true) {
			$response['message'] = 'Node is already activated, please try again.';
			return $response;
		}

		$nodeParameters['data'] = array(
			'activated_status' => '1'
		);
		_edit($nodeParameters, $response);
		$response['message'] = 'Node activated successfully.';
		$response['valid_status'] = '1';
		return $response;
	}

	if (($parameters['action'] === 'activate_node') === true) {
		$response = _activateNode($parameters, $response);
	}
?>
