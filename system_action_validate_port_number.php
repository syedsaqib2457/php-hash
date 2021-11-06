<?php
	if (empty($parameters) === false) {
		exit;
	}

	function _validatePortNumber($portNumber) {
		$response = false;	

		if (
			((trim($portNumber) === $portNumber) === true) &&
			(is_numeric($portNumber) === true) &&
			(
				(($portNumber > 0) === true) &&
				(($portNumber < 65536) === true)
			)
		) {
			$response = $portNumber;
		}

		return $response;
	}
?>
