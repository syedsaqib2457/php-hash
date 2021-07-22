<?php
	$extend = true;
	require_once($configuration->settings['base_path'] . '/models/main.php');

	class NodeProcessesModel extends MainModel {

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding node process, please try again.',
				'status_valid' => (
					(empty($parameters['data']['transport_protocol']) === false) &&
					in_array($parameters['data']['transport_protocol'], array(
						'tcp',
						'udp'
					))
				)
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
				$response['status_valid'] = in_array($parameters['data']['application_protocol'], array(
					'http',
					'socks'
				));

				if ($parameters['data']['application_protocol'] === 'http') {
					$parameters['data']['transport_protocol'] = 'tcp';
				}
			}

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process application protocol, please try again.';
				return $response;
			}

			if (empty($parameters['data']['port']) === false) {
				$nodeProcessPort = $this->_validatePort($parameters['data']['port']);
				$response['status_valid'] = (is_int($nodeProcessPort) === true);
			}

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process port, please try again.';
				return $response;
			}

			$nodeProcessExternalIps = $nodeProcessIps = $nodeProcessIpVersions = array();
			$nodeProcessIpTypes = array(
				'external' => 'public',
				'internal' => 'private'
			);
			$nodeProcessIpVersions = array(
				'4',
				'6'
			);

			foreach ($nodeProcessIpTypes as $nodeProcessIpInterface => $nodeProcessIpType) {
				foreach ($nodeProcessIpVersions as $nodeProcessIpVersion) {
					$nodeProcessIpKey = $nodeProcessIpInterface . '_ip_version_' . $nodeProcessIpVersion;

					if (empty($parameters['data'][$nodeIpKey]) === false) {
						$nodeProcessIp = $nodeProcessIps[$nodeProcessIpKey] = $nodeProcessIpVersions[$nodeProcessIpVersion][] = $parameters['data'][$nodeProcessIpKey];

						if (empty($node['external_ip_version_' . $nodeProcessIpVersion]) === true) {
							$response = array(
								'message' => 'Node must have an external IP version ' . $nodeProcessIpVersion . ' before adding a node process ' . $nodeProcessIpInterface . ' IP version ' . $nodeProcessIpVersion . ', please try again.',
								'status_valid' => false
							);
							return $response;
						}

						if ($nodeProcessIpInterface === 'external') {
							$nodeProcessExternalIps[$nodeProcessIpKey] = $nodeProcessIp;
						}

						if ($nodeProcessIpType !== $this->_fetchIpType($nodeProcessIp, $nodeProcessIpVersion)) {
							$response = array(
								'message' => 'Node process ' . $nodeProcessIpInterface . ' IPs must be ' . $nodeProcessIpType . ', please try again.',
								'status_valid' => false
							);
							return $response;
						}

						if ($nodeProcessIpVersion !== key($this->_sanitizeIps(array($nodeProcessIp)))) {
							$response = array(
								'message' => 'Invalid node process ' . $nodeProcessIpInterface . ' IP version ' . $nodeProcessIpVersion . ', please try again.',
								'status_valid' => false
							);
							return $response;
						}
					}
				}
			}

			if (empty($nodeIps) === false) {
				$conflictingNodeIpCountParameters = array(
					'in' => 'nodes',
					'where' => array(
						'OR' => array(
							array(
								'id' => $nodeProcessNodeId,
								'OR' => $nodeProcessIps
							),
							array(
								'node_id' => $nodeProcessNodeId,
								'OR' => $nodeProcessIps
							)
						)
					)
				));
				$conflictingNodeProcessIpCountParameters = array(
					'in' => 'node_processes',
					'where' => array(
						'node_id' => $nodeProcessNodeId,
						'OR' => $nodeProcessIps
					)
				);

				if (empty($nodeProcessExternalIps) === false) {
					$conflictingNodeIpCountParameters['where']['OR'][] = array(
						'OR' => $nodeProcessExternalIps
					);
					$conflictingNodeProcessIpCountParameters['where']['OR'] = array(
						$conflictingNodeProcessIpCountParameters['where'],
						array(
							'OR' => $nodeProcessExternalIps
						)
					);
				}

				$conflictingNodeIpCount = $this->count($conflictingNodeIpCountParameters);
				$conflictingNodeProcessIpCount = $this->count($conflictingNodeProcessIpCountParameters);
				$response['status_valid'] = (
					is_int($conflictingNodeIpCount) === true &&
					is_int($conflictingNodeProcessIpCount) === true
				);

				if ($response['status_valid'] === false) {
					return $response;
				}

				$response['status_valid'] = (
					$conflictingNodeIpCount === 0 &&
					$conflictingNodeProcessIpCount === 0
				);

				if ($response['status_valid'] === false) {
					$response['message'] = 'Node process IP already in use, please try again.';
					return $response;
				}
			}

			$conflictingNodeProcessPortCount = $this->count(array(
				'in' => 'node_processes',
				'where' => array(
					'node_id' => $nodeProcessNodeId,
					'port' => $nodeProcessPort
				)
			));
			$response['status_valid'] = (is_int($conflictingNodeProcessPortCount) === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($conflictingNodeProcessPortCount === 0);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Node process port already in use, please try again.';
				return $response;
			}

			$nodeProcessData = array(
				array_intersect_key($parameters['data'], array(
					'application_protocol' => true,
					'external_ip_version_4' => true,
					'external_ip_version_6' => true,
					'internal_ip_version_4' => true,
					'internal_ip_version_6' => true,
					'node_id' => true,
					'port' => true,
					'transport_protocol' => true
				))
			);
			$nodeProcessDataSaved = $this->save(array(
				'data' => $nodeProcessData,
				'to' => 'nodes'
			));
			$response['status_valid'] = ($nodeProcessDataSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response = array(
				'message' => 'Node process added successfully.',
				'status_valid' => true
			);
			return $response;
		}

		public function edit($parameters) {
			$response = array(
				'message' => 'Error editing node process, please try again.',
				'status_valid' => (
					empty($parameters['data']['type']) === true ||
					in_array($parameters['data']['type'], array(
						'nameserver',
						'proxy'
					))
				)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node process type, please try again.';
				return $response;
			}

			$response['status_valid'] = (
				empty($parameters['data']['transport_protocol']) === true ||
				in_array($parameters['data']['transport_protocol'], array(
					'tcp',
					'udp'
				))
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
				$response['status_valid'] = in_array($parameters['data']['application_protocol'], array(
					'http',
					'socks'
				));

				if ($parameters['data']['application_protocol'] === 'http') {
					$parameters['data']['transport_protocol'] = 'tcp';
				}

				if ($response['status_valid'] === false) {
					$response['message'] = 'Invalid node process application protocol, please try again.';
					return $response;
				}
			}

			if (empty($parameters['data']['port']) === false) {
				$nodeProcessPort = $this->_validatePort($parameters['data']['port']);
				$response['status_valid'] = (is_int($nodeProcessPort) === true);

				if ($response['status_valid'] === false) {
					$response['message'] = 'Invalid node process port, please try again.';
					return $response;
				}
			}

			$nodeProcessExternalIps = $nodeProcessIps = $nodeProcessIpVersions = array();
			$nodeProcessIpTypes = array(
				'external' => 'public',
				'internal' => 'private'
			);
			$nodeProcessIpVersions = array(
				'4',
				'6'
			);

			foreach ($nodeProcessIpTypes as $nodeProcessIpInterface => $nodeProcessIpType) {
				foreach ($nodeProcessIpVersions as $nodeProcessIpVersion) {
					$nodeProcessIpKey = $nodeProcessIpInterface . '_ip_version_' . $nodeProcessIpVersion;

					if (empty($parameters['data'][$nodeIpKey]) === false) {
						$nodeProcessIp = $nodeProcessIps[$nodeProcessIpKey] = $nodeProcessIpVersions[$nodeProcessIpVersion][] = $parameters['data'][$nodeProcessIpKey];

						if (empty($node['external_ip_version_' . $nodeProcessIpVersion]) === true) {
							$response = array(
								'message' => 'Node must have an external IP version ' . $nodeProcessIpVersion . ' before adding a node process ' . $nodeProcessIpInterface . ' IP version ' . $nodeProcessIpVersion . ', please try again.',
								'status_valid' => false
							);
							return $response;
						}

						if ($nodeProcessIpInterface === 'external') {
							$nodeProcessExternalIps[$nodeProcessIpKey] = $nodeProcessIp;
						}

						if ($nodeProcessIpType !== $this->_fetchIpType($nodeProcessIp, $nodeProcessIpVersion)) {
							$response = array(
								'message' => 'Node process ' . $nodeProcessIpInterface . ' IPs must be ' . $nodeProcessIpType . ', please try again.',
								'status_valid' => false
							);
							return $response;
						}

						if ($nodeProcessIpVersion !== key($this->_sanitizeIps(array($nodeProcessIp)))) {
							$response = array(
								'message' => 'Invalid node process ' . $nodeProcessIpInterface . ' IP version ' . $nodeProcessIpVersion . ', please try again.',
								'status_valid' => false
							);
							return $response;
						}
					}
				}
			}

			if (empty($nodeIps) === false) {
				$conflictingNodeIpCountParameters = array(
					'in' => 'nodes',
					'where' => array(
						'OR' => array(
							array(
								'id' => $nodeProcessNodeId,
								'OR' => $nodeProcessIps
							),
							array(
								'node_id' => $nodeProcessNodeId,
								'OR' => $nodeProcessIps
							)
						)
					)
				));
				$conflictingNodeProcessIpCountParameters = array(
					'in' => 'node_processes',
					'where' => array(
						'id !=' => $nodeProcessId,
						'OR' => array(
							array(
								'node_id' => $nodeProcessNodeId,
								'OR' => $nodeProcessIps
							)
						)
					)
				);

				if (empty($nodeProcessExternalIps) === false) {
					$conflictingNodeIpCountParameters['where']['OR'][] = $conflictingNodeProcessIpCountParameters['where']['OR'][] = array(
						'OR' => $nodeProcessExternalIps
					);
				}

				$conflictingNodeIpCount = $this->count($conflictingNodeIpCountParameters);
				$conflictingNodeProcessIpCount = $this->count($conflictingNodeProcessIpCountParameters);
				$response['status_valid'] = (
					is_int($conflictingNodeIpCount) === true &&
					is_int($conflictingNodeProcessIpCount) === true
				);

				if ($response['status_valid'] === false) {
					return $response;
				}

				$response['status_valid'] = (
					$conflictingNodeIpCount === 0 &&
					$conflictingNodeProcessIpCount === 0
				);

				if ($response['status_valid'] === false) {
					$response['message'] = 'Node process IP already in use, please try again.';
					return $response;
				}
			}

			if (empty($nodeProcessPort) === false)
				$conflictingNodeProcessPortCount = $this->count(array(
					'in' => 'node_processes',
					'where' => array(
						'node_id' => $nodeProcessNodeId,
						'port' => $nodeProcessPort
					)
				));
				$response['status_valid'] = (is_int($conflictingNodeProcessPortCount) === true);

				if ($response['status_valid'] === false) {
					return $response;
				}

				$response['status_valid'] = ($conflictingNodeProcessPortCount === 0);

				if ($response['status_valid'] === false) {
					$response['message'] = 'Node process port already in use, please try again.';
					return $response;
				}
			}

			$nodeProcessData = array_intersect_key($parameters['data'], array(
				'application_protocol' => true,
				'external_ip_version_4' => true,
				'external_ip_version_6' => true,
				'internal_ip_version_4' => true,
				'internal_ip_version_6' => true,
				'ip' => true,
				'node_id' => true,
				'port' => true,
				'transport_protocol' => true
			));
			$nodeProcessDataUpdated = $this->update(array(
				'data' => $nodeProcessData,
				'in' => 'node_processs',
				'where' => array(
					'id' => $nodeProcessId
				)
			));
			$response['status_valid'] = ($nodeProcessDataUpdated === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response = array(
				'message' => 'Node process edited successfully.',
				'status_valid' => true
			);
			return $response;
		}

		public function remove($parameters) {
			$response = array(
				'message' => 'Error removing node processes, please try again.',
				'status_valid' => (empty($parameters['items']['node_processes']['id']) === false)
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$nodeProcessIds = $parameters['items']['node_processes']['id'];
			$nodeProcessCount = $this->count(array(
				'in' => 'nodes',
				'where' => array(
					'id' => $nodeProcessIds
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

			$response = array(
				'message' => 'Node processes removed successfully.',
				'status_valid' => true
			);
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

	if (!empty($configuration->parameters)) {
		$nodeProcessesModel = new NodeProcessesModel();
		$data = $nodeProcessesModel->route($configuration->parameters);
	}
?>
