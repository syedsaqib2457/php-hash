<?php
	$extend = true;
	require_once($system->settings['base_path'] . '/methods/system.php');

	class RequestDestinationMethods extends SystemMethods {

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding request destination, please try again.',
				'status_valid' => (
					(empty($parameters['data']['destination']) === false) &&
					(is_string($parameters['data']['destination']) === true)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (
				($this->_validateHostname($parameters['data']['destination']) !== false) ||
				(empty($this->_sanitizeIps(array($parameters['data']['destination'])), true) === false)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid destination, please try again.';
				return $response;
			}

			$conflictingRequestDestinationCount = $this->count(array(
				'from' => 'request_destinations',
				'where' => array(
					'destination' => $parameters['data']['destination']
				)
			));
			$response['status_valid'] = ($conflictingRequestDestinationCount !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (empty($conflictingRequestDestinationCount) === true);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Request destination already exists, please try again.';
				return $response;
			}

			$requestDestinationDataSaved = $this->save(array(
				'data' => array(
					array(
						'destination' => $parameters['data']['destination']
					)
				),
				'to' => 'request_destinations'
			));
			$response['status_valid'] = ($requestDestinationDataSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['message'] = 'Destination added successfully.';
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
