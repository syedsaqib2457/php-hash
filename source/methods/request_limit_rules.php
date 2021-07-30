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

			$requestLimitRuleDataSaved = $this->save(array(
				'data' => array(
					array_intersect_key($parameters['data'], array(
						'request_interval_minutes' => true,
						'request_limit' => true,
						'request_limit_interval' => true
					))
				),
				'to' => 'users'
			));
			$response['status_valid'] = ($requestLimitRuleDataSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response = array(
				'message' => 'Request limit rule added successfully.',
				'status_valid' => true
			);
			return $response;
		}

	}

	if (empty($system->parameters) === false) {
		$requestLimitRuleMethods = new RequestLimitRuleMethods();
		$data = $requestLimitRuleMethods->route($system->parameters);
	}
?>
