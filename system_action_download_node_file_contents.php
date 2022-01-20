<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _downloadNodeFileContents($parameters, $response) {
		if (
			(empty($parameters['where']['node_file']) === true) ||
			(file_exists($parameters['where']['node_file']) === false)
		) {
			$response['message'] = 'Error downloading node file contents, please try again.';
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
