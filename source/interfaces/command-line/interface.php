<?php
	require_once(__DIR__ . '/../../system.php');
	$output = 'Error processing command, please try again.';

	if (
		(empty($system) === false) &&
		(empty($_SERVER['argv'][1]) === false)
	) {
		$extend = ($_SERVER['argv'][1] !== 'system');
		require_once($system->settings['base_path'] . '/methods/' . $_SERVER['argv'][1] . '.php');
		$methodObjectName = ucwords($_SERVER['argv'][1]) . 'Methods';
		$methodObject = new $methodObjectName();

		if (empty($_SERVER['argv'][2]) === false) {
			$methodName = ucwords($_SERVER['argv'][2];

			if (method_exists($methodObject, $methodName) === true) {
				$output = 'Command processed successfully.';
				$methodResponse = $methodObject->$methodName();

				if (empty($response['message']) === false) {
					$output = $response['message'];
				}
			}
		}
	}

	echo $output;
	exit;
?>
