<?php
	$extend = true;
	require_once($system->settings['base_path'] . '/methods/system.php');

	class UserMethods extends SystemMethods {

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

		public function remove($parameters) {
			$response = array();
			// ..
			return $response;
		}

	}

	if (empty($system->parameters) === false) {
		$userMethods = new UserMethods();
		$data = $userMethods->route($system->parameters);
	}
?>
