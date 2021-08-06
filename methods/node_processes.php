<?php
	$extend = true;
	require_once($system->settings['base_path'] . '/methods/system.php');

	class NodeProcessMethods extends SystemMethods {

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding node process, please try again.',
				'status_valid' => (
					(empty($parameters['data']['type']) === false) &&
					(in_array($parameters['data']['type'], array(
						'http_proxy',
						'nameserver',
						'socks_proxy'
					)) === true)
				)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process type, please try again.';
				return $response;
			}

			$response['status_valid'] = (
				(empty($parameters['data']['transport_protocol']) === false) &&
				(in_array($parameters['data']['transport_protocol'], array(
					'tcp',
					'udp'
				)) === true)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process transport protocol, please try again.';
				return $response;
			}

			if (empty($parameters['data']['node_id']) === false) {
				$nodeParameters = array(
					'fields' => array(
						'id',
						'node_id',
						'type'
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

			if ($node['type'] === 'nameserver') {
				unset($parameters['data']['application_protocol']);
			}

			if (empty($parameters['data']['application_protocol']) === false) {
				$response['status_valid'] = (in_array($parameters['data']['application_protocol'], array(
					'http',
					'socks'
				)) === true);

				if ($parameters['data']['application_protocol'] === 'http') {
					$parameters['data']['transport_protocol'] = 'tcp';
				}
			}

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process application protocol, please try again.';
				return $response;
			}

			if (empty($parameters['data']['port_id']) === false) {
				$nodeProcessPortId = $this->_validatePort($parameters['data']['port_id']);
				$response['status_valid'] = (is_int($nodeProcessPortId) === true);
			}

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process port ID, please try again.';
				return $response;
			}

			$nodeProcessIpVersions = array(
				4,
				6
			);

			foreach ($nodeProcessIpVersions as $nodeProcessIpVersion) {
				$nodeProcessIpKey = 'external_ip_version_' . $nodeProcessIpVersion;

				if (empty($parameters['data'][$nodeProcessIpKey]) === false) {
					$nodeProcessIp = $parameters['data'][$nodeProcessIpKey];
					$response['status_valid'] = ($this->_detectIpType($nodeProcessIp, $nodeProcessIpVersion) === 'public');

					if ($response['status_valid'] === false) {
						$response['message'] = 'Node process external IP version ' . $nodeProcessIpVersion . ' must be public, please try again.';
						return $response;
					}

					$response['status_valid'] = ($nodeProcessIpVersion === key($this->_sanitizeIps(array($nodeProcessIp))));

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid node process external IP version ' . $nodeProcessIpVersion . ', please try again.';
						return $response;
					}
				}
			}

			$conflictingNodeProcessCount = $this->count(array(
				'in' => 'node_processes',
				'where' => array(
					'node_id' => $nodeProcessNodeId,
					'port_id' => $nodeProcessPortId
				)
			));
			$response['status_valid'] = (is_int($conflictingNodeProcessCount) === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($conflictingNodeProcessCount === 0);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Node process already in use, please try again.';
				return $response;
			}

			$nodeProcessDataSaved = $this->save(array(
				'data' => array_intersect_key($parameters['data'], array(
					'application_protocol' => true,
					'external_ip_version_4' => true,
					'external_ip_version_6' => true,
					'node_id' => true,
					'port_id' => true,
					'transport_protocol' => true,
					'type' => true
				)),
				'to' => 'nodes'
			));
			$response['status_valid'] = ($nodeProcessDataSaved === true);

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
					(in_array($parameters['data']['type'], array(
						'http_proxy',
						'nameserver',
						'socks_proxy'
					)) === true)
				)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process type, please try again.';
				return $response;
			}

			$response['status_valid'] = (
				(empty($parameters['data']['transport_protocol']) === true) ||
				(in_array($parameters['data']['transport_protocol'], array(
					'tcp',
					'udp'
				)) === true)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process transport protocol, please try again.';
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

			if ($node['type'] === 'nameserver') {
				unset($parameters['data']['application_protocol']);
			}

			if (empty($parameters['data']['application_protocol']) === false) {
				$response['status_valid'] = (in_array($parameters['data']['application_protocol'], array(
					'http',
					'socks'
				)) === true);

				if ($parameters['data']['application_protocol'] === 'http') {
					$parameters['data']['transport_protocol'] = 'tcp';
				}

				if ($response['status_valid'] === false) {
					$response['message'] = 'Invalid node process application protocol, please try again.';
					return $response;
				}
			}

			if (empty($parameters['data']['port_id']) === false) {
				$nodeProcessPortId = $this->_validatePort($parameters['data']['port_id']);
				$response['status_valid'] = (is_int($nodeProcessPortId) === true);

				if ($response['status_valid'] === false) {
					$response['message'] = 'Invalid node process port ID, please try again.';
					return $response;
				}
			}

			$nodeProcessIpVersions = array(
				4,
				6
			);

			foreach ($nodeProcessIpVersions as $nodeProcessIpVersion) {
				$nodeProcessIpKey = 'external_ip_version_' . $nodeProcessIpVersion;

				if (empty($parameters['data'][$nodeProcessIpKey]) === false) {
					$nodeProcessIp = $parameters['data'][$nodeProcessIpKey];
					$response['status_valid'] = ($this->_detectIpType($nodeProcessIp, $nodeProcessIpVersion) === 'public');

					if ($response['status_valid'] === false) {
						$response['message'] = 'Node process external IP version ' . $nodeProcessIpVersion . ' must be public, please try again.';
						return $response;
					}

					$response['status_valid'] = ($nodeProcessIpVersion === key($this->_sanitizeIps(array($nodeProcessIp))));

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid node process external IP version ' . $nodeProcessIpVersion . ', please try again.';
						return $response;
					}
				}
			}

			if (empty($nodeProcessPortId) === false)
				$conflictingNodeProcessCount = $this->count(array(
					'in' => 'node_processes',
					'where' => array(
						'node_id' => $nodeProcessNodeId,
						'port_id' => $nodeProcessPortId
					)
				));
				$response['status_valid'] = (is_int($conflictingNodeProcessCount) === true);

				if ($response['status_valid'] === false) {
					return $response;
				}

				$response['status_valid'] = ($conflictingNodeProcessCount === 0);

				if ($response['status_valid'] === false) {
					$response['message'] = 'Node process already in use, please try again.';
					return $response;
				}
			}

			$nodeProcessDataUpdated = $this->update(array(
				'data' => array_intersect_key($parameters['data'], array(
					'application_protocol' => true,
					'external_ip_version_4' => true,
					'external_ip_version_6' => true,
					'ip' => true,
					'node_id' => true,
					'port_id' => true,
					'transport_protocol' => true,
					'type' => true
				)),
				'in' => 'node_processs',
				'where' => array(
					'id' => $nodeProcessId
				)
			));
			$response['status_valid'] = ($nodeProcessDataUpdated === true);

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

			$nodeProcessDataDeleted = $this->delete(array(
				'from' => 'node_processes',
				'where' => array(
					'id' => $nodeProcessIds
				)
			));
			$response['status_valid'] = ($nodeProcessDataDeleted === true);

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
