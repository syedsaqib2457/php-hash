<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _downloadNodeActionFileContents($parameters, $response) {
		if (
			(empty($parameters['where']['node_action']) === true) ||
			(file_exists('node_action_' . strval($parameters['where']['node_action']) . '.php') === false)
		) {
			$response['message'] = 'Error listing node action, please try again.';
			return $response;
		}

		// todo
	}

	if (($parameters['action'] === 'download_node_action_file_contents') === true) {
		$response = _downloadNodeActionFileContents($parameters, $response);
		_output($response);
	}
?>
