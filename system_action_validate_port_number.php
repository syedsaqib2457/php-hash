<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _validatePortNumber($portNumber) {
		$response = false;	

		if (
			(($portNumber > 0) === true) &&
			(($portNumber < 65536) === true)
		) {
			$response = intval($portNumber);
		}

		return $response;
	}
?>
