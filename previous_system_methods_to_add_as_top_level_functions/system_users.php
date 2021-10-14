<?php
	$extend = true;
	require_once($system->settings['base_path'] . '/methods/system.php');

	class SystemUserMethods extends SystemMethods {

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding system user, please try again.',
				'status_valid' => false
			);
			// todo: user sign up process
			$response['message'] = 'System user added successfully.';
			return $response;
		}

		public function authenticate($parameters) {
			$response = array(
				'message' => 'Error authenticating system user, please try again.',
				'status_valid' => false
			);
			// todo: user log in process
			$response['message'] = 'System user authenticated successfully.';
		}

		public function edit($parameters) {
			$response = array(
				'message' => 'Error editing system user, please try again.',
				'status_valid' => false
			);
			// todo: system user edit process
			$response['message'] = 'System user edited successfully.';
			return $response;
		}

		public function list($parameters) {
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
		$systemUserMethods = new SystemUserMethods();
		$data = $systemUserMethods->route($system->parameters);
	}
?>
