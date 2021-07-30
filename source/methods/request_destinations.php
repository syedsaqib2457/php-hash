<?php
	$extend = true;
	require_once($system->settings['base_path'] . '/methods/system.php');

	class RequestDestinationMethods extends SystemMethods {

		public function add($parameters) {
			$response = array();
			// ..
			return $response;
		}

		public function edit($parameters) {
			$response = array();
			// ..
			return $response;
		}

	}

	if (empty($system->parameters) === false) {
		$requestDestinationMethods = new RequestDestinationMethods();
		$data = $requestDestinationMethods->route($system->parameters);
	}
?>
