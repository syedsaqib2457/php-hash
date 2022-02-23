<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _downloadNodeFile($parameters, $response) {
		if (
			(empty($parameters['node_authentication_token']) === true) ||
			(empty($parameters['where']['node_file']) === true)
		) {
			return $response;
		}

		if (in_array($parameters['where']['node_file'], array(
			'node_action_process_node_process_node_user_request_logs.php',
			'node_action_process_node_process_resource_usage_logs.php',
			'node_action_process_node_processes.php',
			'node_action_process_node_resource_usage_logs.php',
			'node_action_process_recursive_dns_destination',
			'node_endpoint.php'
		)) === false) {
			$response['message'] = 'Invalid node file ' . $parameters['where']['node_file'] . ', please try again.';
			return $response;
		}

		if (file_exists($parameters['where']['node_file']) === false) {
			$response['message'] = 'Error downloading node file ' . $parameters['where']['node_file'] . ', please try again.';
			return $response;
		}

		header('Content-Type: text/plain');
		echo file_get_contents('/var/www/ghostcompute/' . $parameters['where']['node_file']);
		exit;
	}

	if (($parameters['action'] === 'download_node_file_contents') === true) {
		$response = _downloadNodeFileContents($parameters, $response);
	}
?>
