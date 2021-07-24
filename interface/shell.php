<?php
	require_once(__DIR__ . '/configuration.php');
	$output = 'Error processing shell method, please check the parameters and try again.';

	if (
		!empty($configuration) &&
		!empty($_SERVER['argv'][1])
	) {
		$from = strtolower($_SERVER['argv'][1]);

		if ($from !== 'main') {
			$extend = true;
		}

		require_once($configuration->settings['base_path'] . '/models/' . $from . '.php');
		$shellObjectName = ucwords($from) . 'Model';
		$shellObject = new $shellObjectName();

		if (
			!empty($_SERVER['argv'][2]) &&
			method_exists($shellObject, $shellMethod = 'shell' . ucwords($_SERVER['argv'][2]))
		) {
			$response = $shellObject->$shellMethod();
			$output = 'Completed processing shell method.';

			if (!empty($response['message']['text'])) {
				$output = $response['message']['text'];
			}
		}
	}

	echo $output;
	exit;
?>
