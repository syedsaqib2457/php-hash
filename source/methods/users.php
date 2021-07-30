<?php
	$extend = true;
	require_once($system->settings['base_path'] . '/methods/system.php');

	class UserMethods extends SystemMethods {

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding user, please try again.',
				'status_valid' => (
					(
						(empty($parameters['data']['authentication_password']) === false) ||
						(empty($parameters['data']['authentication_username']) === false)
					) &&
					(
						(empty($parameters['data']['authentication_password']) === true) ||
						(empty($parameters['data']['authentication_username']) === true)
					)
				)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Authentication username and password must be either set or empty, please try again.';
				return $response;
			}

			if (empty($parameters['data']['authentication_password']) === true) {
				$parameters['data']['authentication_password'] = $parameters['data']['authentication_username'] = null;
			}

			$response['status_valid'] = (
				(empty($parameters['data']['authentication_username']) === true) ||
				(
					(strlen($parameters['data']['authentication_password']) > 1) &&
					(strlen($parameters['data']['authentication_password']) < 34) &&
					(strlen($parameters['data']['authentication_username']) > 1) &&
					(strlen($parameters['data']['authentication_username']) < 34)
				)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Authentication username and password must be between 1 and 34 characters, please try again.';
				return $response;
			}

			if (isset($parameters['data']['authentication_interval_minutes']) === true) {
				$parameters['data']['authentication_interval_minutes'] = intval($parameters['data']['authentication_interval_minutes']);

				if ($parameters['data']['authentication_interval_minutes'] !== false) {
					$response['status_valid'] = (is_int($parameters['data']['authentication_interval_minutes']) === true);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid temporary authentication interval, please try again.';
						return $response;
					}

					$parameters['data']['authentication_interval_minutes'] = date('Y-m-d H:i:s', strtotime('+' . $parameters['data']['authentication_interval_minutes'] . ' minutes'))
				}
			}

			if (empty($parameters['data']['authentication_whitelist']) === false) {
				$authenticationWhitelist = array();
				$authenticationWhitelistSourceVersions = $this->_sanitizeIps($parameters['data']['authentication_whitelist'], true);

				if (!empty($authenticationWhitelistSourceVersions)) {
					foreach ($authenticationWhitelistSourceVersions as $authenticationWhitelistSources) {
						$authenticationWhitelist += $authenticationWhitelistSources;
					}
				}

				$parameters['data']['authentication_whitelist'] = implode("\n", $authenticationWhitelist);
			}

			if (isset($parameters['data']['status_allowing_request_logs']) === true) {
				$parameters['data']['status_allowing_request_logs'] = boolval($parameters['data']['status_allowing_request_logs']);
			}

			if (empty($parameters['data']['tag']) === false) {
				$response['status_valid'] = (strval($parameters['data']['tag']) <= 100);

				if ($response['status_valid'] === false) {
					$response['message'] = 'User tag must be 100 characters or less, please try again.';
					return $response;
				}
			}

			$userParameters = array(
				'fields' => array(
					'id'
				),
				'from' => 'users',
				'where' => array_intersect_key($parameters['data'], array(
					'authentication_password',
					'authentication_username',
					'authentication_whitelist',
					'tag'
				))
			));
			$conflictingUser = $this->fetch($userParameters);
			$response['status_valid'] = ($conflictingUser !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (empty($conflictingUser) === true);

			if ($response['status_valid'] === false) {
				$response['message'] = 'User already exists, please try again.';
				return $response;
			}

			// ..

			$userDataSaved = $this->save(array(
				'data' => array(
					array_intersect_key($parameters['data'], array(
						'authentication_interval_minutes',
						'authentication_password',
						'authentication_username',
						'authentication_whitelist',
						'status_allowing_request_destinations_only',
						'status_allowing_request_logs',
						'tag'
					))
				),
				'to' => 'users'
			));
			$response['status_valid'] = ($userDataSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$user = $this->fetch($userParameters);
			$response['status_valid'] = (
				($user !== false) &&
				(empty($user) === false)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$userId = $user['id'];
			// ..
			$response = array(
				'message' => 'User added successfully.',
				'status_valid' => true
			);
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
