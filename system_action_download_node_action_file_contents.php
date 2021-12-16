<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _downloadNodeActionFileContents($parameters, $response) {
		// todo
	}

	if (($parameters['action'] === 'download_node_action_file_contents') === true) {
		$response = _downloadNodeActionFileContents($parameters, $response);
		_output($response);
	}
?>
