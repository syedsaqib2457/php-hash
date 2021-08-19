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
				)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (
				($this->_validateHostname($parameters['data']['destination']) !== false) ||
				(empty($this->_sanitizeIps(array($parameters['data']['destination']), true)) === false)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid destination, please try again.';
				return $response;
			}

			$conflictingRequestDestinationCount = $this->count(array(
				'in' => 'request_destinations',
				'where' => array(
					'destination' => $parameters['data']['destination']
				)
			));
			$response['status_valid'] = (is_int($conflictingRequestDestinationCount) === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($conflictingRequestDestinationCount === 0);

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
			$response = array(
				'message' => 'Error editing request destination, please try again.',
				'status_valid' => (empty($parameters['data']['id']) === false)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$requestDestination = $this->fetch(array(
				'fields' => array(
					'id'
				),
				'from' => 'request_destinations',
				'where' => array(
					'id' => ($requestDestinationId = $parameters['data']['id'])
				)
			));
			$response['status_valid'] = ($requestDestination !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (empty($requestDestination) === false);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid request destination ID, please try again.';
				return $response;
			}

			$response['status_valid'] = (
				(empty($parameters['data']['destination']) === false) &&
				(is_string($parameters['data']['destination']) === true)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (
				($this->_validateHostname($parameters['data']['destination']) !== false) ||
				(empty($this->_sanitizeIps(array($parameters['data']['destination']), true)) === false)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid destination, please try again.';
				return $response;
			}

			$conflictingRequestDestinationCount = $this->count(array(
				'in' => 'request_destinations',
				'where' => array(
					'destination' => $parameters['data']['destination']
				)
			));
			$response['status_valid'] = (is_int($conflictingRequestDestinationCount) === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($conflictingRequestDestinationCount === 0);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Request destination already exists, please try again.';
				return $response;
			}

			$requestDestinationDataUpdated = $this->update(array(
				'data' => array(
					'destination' => $parameters['data']['destination']
				),
				'in' => 'request_destinations',
				'where' => array(
					'id' => $requestDestinationId
				)
			));
			$response['status_valid'] = ($requestDestinationDataSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['message'] = 'Destination edited successfully.';
			return $response;
		}

	}

	if (empty($system->parameters) === false) {
		$requestDestinationMethods = new RequestDestinationMethods();
		$data = $requestDestinationMethods->route($system->parameters);
	}
?>
