<?php
	$extend = true;
	require_once($system->settings['base_path'] . '/methods/system.php');

	class RequestLimitRuleMethods extends SystemMethods {

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding request limit rule, please try again.',
				'status_valid' => (
					(empty($parameters['data']['request_interval_minutes']) === false) &&
					(is_int($parameters['data']['request_interval_minutes']) === true)
				)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid request interval, please try again.';
				return $response;
			}

			$response['status_valid'] = (
				(empty($parameters['data']['request_limit']) === false) &&
				(is_int($parameters['data']['request_limit']) === true)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid request limit, please try again.';
				return $response;
			}

			$response['status_valid'] = (
				(empty($parameters['data']['request_limit_interval']) === false) &&
				(is_int($parameters['data']['request_limit_interval']) === true)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid request limit interval, please try again.';
				return $response;
			}

			$conflictingRequestLimitRuleCount = $this->count(array(
				'in' => 'request_limit_rules',
				'where' => array_intersect_key($parameters['data'], array(
					'request_interval_minutes' => true,
					'request_limit' => true,
					'request_limit_interval' => true
				))
			));
			$response['status_valid'] = (is_int($conflictingRequestLimitRuleCount) === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($conflictingRequestLimitRuleCount === 0);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Request limit rule already exists, please try again.';
				return $response;
			}

			$requestLimitRuleDataSaved = $this->save(array(
				'data' => array(
					array_intersect_key($parameters['data'], array(
						'request_interval_minutes' => true,
						'request_limit' => true,
						'request_limit_interval' => true
					))
				),
				'to' => 'request_limit_rules'
			));
			$response['status_valid'] = ($requestLimitRuleDataSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['message'] = 'Request limit rule added successfully.';
			return $response;
		}

		public function edit($parameters) {
			$response = array(
				'message' => 'Error editing request limit rule, please try again.',
				'status_valid' => (empty($parameters['data']['id']) === false)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$requestLimitRule = $this->fetch(array(
				'fields' => array(
					'id'
				),
				'from' => 'request_limit_rules',
				'where' => array(
					'id' => ($requestLimitRuleId = $parameters['data']['id'])
				)
			));
			$response['status_valid'] = ($requestLimitRule !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (empty($requestLimitRule) === false);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid request limit rule ID, please try again.';
				return $response;
			}

			$response['status_valid'] = (
				(empty($parameters['data']['request_interval_minutes']) === false) &&
				(is_int($parameters['data']['request_interval_minutes']) === true)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid request interval, please try again.';
				return $response;
			}

			$response['status_valid'] = (
				(empty($parameters['data']['request_limit']) === false) &&
				(is_int($parameters['data']['request_limit']) === true)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid request limit, please try again.';
				return $response;
			}

			$response['status_valid'] = (
				(empty($parameters['data']['request_limit_interval']) === false) &&
				(is_int($parameters['data']['request_limit_interval']) === true)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid request limit interval, please try again.';
				return $response;
			}

			$conflictingRequestLimitRuleCount = $this->count(array(
				'in' => 'request_limit_rules',
				'where' => array_intersect_key($parameters['data'], array(
					'request_interval_minutes' => true,
					'request_limit' => true,
					'request_limit_interval' => true
				))
			));
			$response['status_valid'] = (is_int($conflictingRequestLimitRuleCount) === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($conflictingRequestLimitRuleCount === 0);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Request limit rule already exists, please try again.';
				return $response;
			}

			$requestLimitRuleDataUpdated = $this->update(array(
				'data' => array_intersect_key($parameters['data'], array(
					'request_interval_minutes' => true,
					'request_limit' => true,
					'request_limit_interval' => true
				)),
				'in' => 'request_limit_rules',
				'where' => array(
					'id' => $requestLimitRuleId
				)
			));
			$response['status_valid'] = ($requestLimitRuleDataUpdated === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['message'] = 'Request limit rule edited successfully.';
			return $response;
		}

	}

	if (empty($system->parameters) === false) {
		$requestLimitRuleMethods = new RequestLimitRuleMethods();
		$data = $requestLimitRuleMethods->route($system->parameters);
	}
?>
