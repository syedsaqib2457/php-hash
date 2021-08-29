<?php
	$extend = true;
	require_once($system->settings['base_path'] . '/methods/system.php');

	class NodeProcessMethods extends SystemMethods {

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding node process, please try again.',
				'status_valid' => (
					(empty($parameters['data']['type']) === false) &&
					(in_array($parameters['data']['type'], array_keys($this->settings['node_process_type_default_port_numbers'])) === true)
				)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process type, please try again.';
				return $response;
			}

			if (empty($parameters['data']['node_id']) === false) {
				$nodeParameters = array(
					'fields' => array(
						'id',
						'node_id'
					),
					'from' => 'nodes',
					'where' => array(
						'id' => $parameters['data']['node_id']
					)
				);
				$node = $this->fetch($nodeParameters);
				$response['status_valid'] = ($node !== false);

				if ($response['status_valid'] === true) {
					$response['status_valid'] = (empty($node) === false);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid node ID, please try again.';
					}
				}
			}

			if ($response['status_valid'] === false) {
				return $response;
			}

			if (empty($node['node_id']) === false) {
				$nodeParameters['where']['id'] = $node['node_id'];
				$node = $this->fetch($nodeParameters);
				$response['status_valid'] = ($node !== false);

				if ($response['status_valid'] === true) {
					$response['status_valid'] = (empty($node) === false);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid node ID, please try again.';
					}
				}
			}

			$nodeProcessNodeId = $parameters['data']['node_id'] = $node['id'];

			if ($response['status_valid'] === false) {
				return $response;
			}

			if (empty($parameters['data']['port_id']) === false) {
				$nodeProcessPortId = $this->_validatePortNumber($parameters['data']['port_id']);
				$response['status_valid'] = (is_int($nodeProcessPortId) === true);
			}

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process port ID, please try again.';
				return $response;
			}

			$existingNodeProcessCount = $this->count(array(
				'in' => 'node_processes',
				'where' => array(
					'node_id' => $nodeProcessNodeId,
					'port_number' => $nodeProcessPortId
				)
			));
			$response['status_valid'] = (is_int($existingNodeProcessCount) === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($existingNodeProcessCount === 0);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Node process already in use, please try again.';
				return $response;
			}

			// todo: save matching node_process_port_numbers record
			$nodeProcessesSaved = $this->save(array(
				'data' => array_intersect_key($parameters['data'], array(
					'node_id' => true,
					'port_number' => true,
					'type' => true
				)),
				'to' => 'node_processes'
			));
			$response['status_valid'] = ($nodeProcessesSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['message'] = 'Node process added successfully.';
			return $response;
		}

		public function edit($parameters) {
			$response = array(
				'message' => 'Error editing node process, please try again.',
				'status_valid' => (
					(empty($parameters['data']['type']) === true) ||
					(in_array($parameters['data']['type'], array_keys($this->settings['node_process_type_default_port_numbers'])) === true)
				)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process type, please try again.';
				return $response;
			}

			if (empty($parameters['data']['id']) === false) {
				$nodeProcess = $this->fetch(array(
					'fields' => array(
						'id',
						'node_id'
					),
					'from' => 'node_processes',
					'where' => array(
						'id' => ($nodeProcessId = $parameters['data']['id'])
					)
				));
				$response['status_valid'] = ($nodeProcess !== false);
			}

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (empty($nodeProcess) === false);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process ID, please try again.';
				return $response;
			}

			$nodeParameters = array(
				'fields' => array(
					'id',
					'node_id'
				),
				'from' => 'nodes',
				'where' => array(
					'id' => $nodeProcess['node_id']
				)
			);
			$node = $this->fetch($nodeParameters);
			$response['status_valid'] = ($node !== false);

			if ($response['status_valid'] === true) {
				$response['status_valid'] = (empty($node) === false);

				if ($response['status_valid'] === false) {
					$response['message'] = 'Invalid node ID, please try again.';
				}
			}

			if ($response['status_valid'] === false) {
				return $response;
			}

			if (empty($node['node_id']) === false) {
				$nodeParameters['where']['id'] = $node['node_id'];
				$node = $this->fetch($nodeParameters);
				$response['status_valid'] = ($node !== false);

				if ($response['status_valid'] === true) {
					$response['status_valid'] = (empty($node) === false);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid node ID, please try again.';
					}
				}
			}

			$nodeProcessNodeId = $node['id'];

			if ($response['status_valid'] === false) {
				return $response;
			}

			if (empty($parameters['data']['port_number']) === false) {
				$nodeProcessPortNumber = $this->_validatePortNumber($parameters['data']['port_number']);
				$response['status_valid'] = (is_int($nodeProcessPortNumber) === true);

				if ($response['status_valid'] === false) {
					$response['message'] = 'Invalid node process port number, please try again.';
					return $response;
				}
			}

			if (empty($nodeProcessPortNumber) === false) {
				$existingNodeProcessCount = $this->count(array(
					'in' => 'node_processes',
					'where' => array(
						'node_id' => $nodeProcessNodeId,
						'port_number' => $nodeProcessPortNumber
					)
				));
				$response['status_valid'] = (is_int($existingNodeProcessCount) === true);

				if ($response['status_valid'] === false) {
					return $response;
				}

				$response['status_valid'] = ($existingNodeProcessCount === 0);

				if ($response['status_valid'] === false) {
					$response['message'] = 'Node process already in use, please try again.';
					return $response;
				}
			}

			// todo: update matching node_process_port_numbers record
			$nodeProcessesUpdated = $this->update(array(
				'data' => array_intersect_key($parameters['data'], array(
					'id' => true,
					'node_id' => true,
					'port_number' => true,
					'type' => true
				)),
				'in' => 'node_processs',
				'where' => array(
					'id' => $nodeProcessId
				)
			));
			$response['status_valid'] = ($nodeProcessesUpdated === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['message'] = 'Node process edited successfully.';
			return $response;
		}

		public function remove($parameters) {
			$response = array(
				'message' => 'Error removing node processes, please try again.',
				'status_valid' => (empty($parameters['where']['id']) === false)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$nodeProcessCount = $this->count(array(
				'in' => 'nodes',
				'where' => array(
					'id' => ($nodeProcessIds = $parameters['where']['id'])
				)
			));
			$response['status_valid'] = (is_int($nodeProcessCount) === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($nodeProcessCount === count($nodeProcessIds));

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process IDs, please try again.';
				return $response;
			}

			// todo: delete matching node_process_port_numbers record
			$nodeProcessesDeleted = $this->delete(array(
				'from' => 'node_processes',
				'where' => array(
					'id' => $nodeProcessIds
				)
			));
			$response['status_valid'] = ($nodeProcessesDeleted === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['message'] = 'Node processes removed successfully.';
			return $response;
		}

		public function view($parameters = array()) {
			$response = array(
				'message' => 'Error viewing node process, please try again.',
				'status_valid' => false
			);

			if (empty($parameters['where']['id']) === false) {
				// ..
			}

			return $response;
		}

	}

	if (empty($system->parameters) === false) {
		$nodeProcessMethods = new NodeProcessMethods();
		$data = $nodeProcessMethods->route($system->parameters);
	}
?>
