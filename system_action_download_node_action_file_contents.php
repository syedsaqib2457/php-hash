<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _downloadNodeActionFileContents($parameters, $response) {
		if (
			(empty($parameters['where']['node_action']) === true) ||
			(file_exists('node_action_' . strval($parameters['where']['node_action']) . '.php') === false)
		) {
			$response['message'] = 'Error downloading node action file contents, please try again.';
			return $response;
		}

		header('Content-Type: text/plain');
		echo file_get_contents('/var/www/ghostcompute/node_action_' . strval($parameters['where']['node_action']) . '.php');
		exit;
	}

	if (($parameters['action'] === 'download_node_action_file_contents') === true) {
		$response = _downloadNodeActionFileContents($parameters, $response);
		_output($response);
	}
?>
