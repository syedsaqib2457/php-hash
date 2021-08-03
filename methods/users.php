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

			if (empty($parameters['data']['authentication_whitelist']) === false) {
				$authenticationWhitelist = array();
				$authenticationWhitelistSourceVersions = $this->_sanitizeIps($parameters['data']['authentication_whitelist'], true);

				if (empty($authenticationWhitelistSourceVersions) === false) {
					foreach ($authenticationWhitelistSourceVersions as $authenticationWhitelistSources) {
						$authenticationWhitelist += $authenticationWhitelistSources;
					}
				}

				$parameters['data']['authentication_whitelist'] = implode("\n", $authenticationWhitelist);
			}

			if (isset($parameters['data']['status_allowing_request_logs']) === true) {
				$parameters['data']['status_allowing_request_logs'] = boolval($parameters['data']['status_allowing_request_logs']);
			}

			if (isset($parameters['data']['status_requiring_strict_authentication']) === true) {
				$parameters['data']['status_requiring_strict_authentication'] = boolval($parameters['data']['status_requiring_strict_authentication']);
			}

			if (empty($parameters['data']['tag']) === false) {
				$response['status_valid'] = (strval($parameters['data']['tag']) <= 100);

				if ($response['status_valid'] === false) {
					$response['message'] = 'User tag must be 100 characters or less, please try again.';
					return $response;
				}
			}

			$conflictingUserCount = $this->count(array(
				'in' => 'users',
				'where' => array(
					'authentication_username' => $parameters['data']['authentication_username']
				)
			));
			$response['status_valid'] = (is_int($conflictingUserCount) === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($conflictingUserCount === 0);

			if ($response['status_valid'] === false) {
				$response['message'] = 'User already exists, please try again.';
				return $response;
			}

			$userDataSaved = $this->save(array(
				'data' => array(
					array_intersect_key($parameters['data'], array(
						'authentication_password' => true,
						'authentication_username' => true,
						'authentication_whitelist' => true,
						'status_allowing_request_destinations_only' => true,
						'status_allowing_request_logs' => true,
						'status_requiring_strict_authentication' => true,
						'tag' => true
					))
				),
				'to' => 'users'
			));
			$response['status_valid'] = ($userDataSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['message'] = 'User added successfully.';
			return $response;
		}

		public function edit($parameters) {
			$response = array(
				'message' => 'Error editing user, please try again.',
				'status_valid' => (empty($parameters['data']['id']) === false)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$user = $this->fetch(array(
				'fields' => array(
					'id'
				),
				'from' => 'users',
				'where' => array(
					'id' => ($userId = $parameters['data']['id'])
				)
			));
			$response['status_valid'] = ($user !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (empty($user) === false);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid user ID, please try again.';
				return $response;
			}

			$response['status_valid'] = (
				(
					(empty($parameters['data']['authentication_password']) === false) ||
					(empty($parameters['data']['authentication_username']) === false)
				) &&
				(
					(empty($parameters['data']['authentication_password']) === true) ||
					(empty($parameters['data']['authentication_username']) === true)
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

			if (empty($parameters['data']['authentication_whitelist']) === false) {
				$authenticationWhitelist = array();
				$authenticationWhitelistSourceVersions = $this->_sanitizeIps($parameters['data']['authentication_whitelist'], true);

				if (empty($authenticationWhitelistSourceVersions) === false) {
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

			$conflictingUserCount = $this->count(array(
				'in' => 'users',
				'where' => array_intersect_key($parameters['data'], array(
					'authentication_password' => true,
					'authentication_username' => true,
					'authentication_whitelist' => true,
					'tag' => true
				))
			));
			$response['status_valid'] = (is_int($conflictingUserCount) === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($conflictingUserCount === 0);

			if ($response['status_valid'] === false) {
				$response['message'] = 'User already exists, please try again.';
				return $response;
			}

			$userDataUpdated = $this->update(array(
				'data' => array(
					array_intersect_key($parameters['data'], array(
						'authentication_password' => true,
						'authentication_username' => true,
						'authentication_whitelist' => true,
						'status_allowing_request_destinations_only' => true,
						'status_allowing_request_logs' => true,
						'tag' => true
					))
				),
				'in' => 'users',
				'where' => array(
					'id' => $userId
				)
			));
			$userRequestDestinationDataDeleted = $this->delete(array(
				'in' => 'user_request_destinations',
				'where' => array(
					'status_removed' => true,
					'user_id' => $userId
				)
			));
			$userRequestDestinationDataUpdated = $this->update(array(
				'data' => array(
					'status_processed' => true
				),
				'in' => 'user_request_destinations',
				'where' => array(
					'status_processed' => false,
					'status_removed' => false,
					'user_id' => $userId
				)
			));
			$userRequestLimitRuleDataDeleted = $this->delete(array(
				'in' => 'user_request_limit_rules',
				'where' => array(
					'status_removed' => true,
					'user_id' => $userId
				)
			));
			$userRequestLimitRuleDataUpdated = $this->update(array(
				'data' => array(
					'status_processed' => true
				),
				'in' => 'user_request_limit_rules',
				'where' => array(
					'status_removed' => false,
					'user_id' => $userId
				)
			));
			$response['status_valid'] = (
				($userRequestDestinationDataDeleted === true) &&
				($userRequestDestinationDataUpdated === true) &&
				($userRequestLimitRuleDataDeleted === true) &&
				($userRequestLimitRuleDataUpdated === true)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['message'] = 'User edited successfully.';
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
		$userMethods = new UserMethods();
		$data = $userMethods->route($system->parameters);
	}
?>
