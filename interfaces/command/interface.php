<?php
	require_once(__DIR__ . '/../../system.php');
	$output = 'Error processing command, please try again.';

	if (
		(empty($system) === false) &&
		(empty($_SERVER['argv'][1]) === false)
	) {
		$extend = ($_SERVER['argv'][1] !== 'system');
		require_once($system->settings['base_path'] . '/methods/' . $_SERVER['argv'][1] . '.php');
		$methodObjectNameParts = explode('_', $_SERVER['argv'][1]);
		$methodObjectName = implode('', array_map('ucfirst', $methodObjectNameParts));
		$methodObjectName = lcfirst($methodObjectName) . 'Methods';
		$methodObject = new $methodObjectName();

		if (empty($_SERVER['argv'][2]) === false) {
			$methodName = ucwords($_SERVER['argv'][2]);

			if (method_exists($methodObject, $methodName) === true) {
				$methodResponse = $methodObject->$methodName();

				if (empty($methodResponse['message']) === false) {
					$output = $methodResponse['message'];
				}
			}
		}
	}

	echo $output;
	exit;
?>
