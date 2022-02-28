<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _downloadNodeFile($parameters, $response) {
		if (
			(empty($parameters['nodeAuthenticationToken']) === true) ||
			(empty($parameters['where']['nodeFile']) === true)
		) {
			return $response;
		}

		if (
			(($parameters['where']['nodeFile'] === 'node-action-process-node-process-node-user-request-logs.php') === false) &&
			(($parameters['where']['nodeFile'] === 'node-action-process-node-process_resource-usage-logs.php') === false) &&
			(($parameters['where']['nodeFile'] === 'node-action-process-node-processes.php') === false) &&
			(($parameters['where']['nodeFile'] === 'node-action-process-node-resource-usage-logs.php') === false) &&
			(($parameters['where']['nodeFile'] === 'node-action-process-recursive-dns-destination') === false) &&
			(($parameters['where']['nodeFile'] === 'node-endpoint.php') === false)
		) {
			$response['message'] = 'Invalid node file ' . $parameters['where']['nodeFile'] . ', please try again.';
			return $response;
		}

		if (file_exists($parameters['where']['nodeFile']) === false) {
			$response['message'] = 'Error downloading node file ' . $parameters['where']['nodeFile'] . ', please try again.';
			return $response;
		}

		header('Content-Type: text/plain');
		echo file_get_contents('/var/www/firewall-security-api/' . $parameters['where']['nodeFile']);
		exit;
	}

	if (($parameters['action'] === 'downloadNodeFileContents') === true) {
		$response = _downloadNodeFileContents($parameters, $response);
	}
?>
