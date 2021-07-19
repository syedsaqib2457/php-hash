<?php
	$extend = true;
	require_once($configuration->settings['base_path'] . '/models/main.php');

	class NodesModel extends MainModel {

		public function add($parameters) {
			$response = array(
				'message' => 'Error adding node, please try again.',
				'status_valid' => false
			);

			if (empty($parameters['data']['type']) === false) {
				$response['status_valid'] = in_array($parameters['data']['type'], array(
					'nameserver',
					'proxy'
				));
			}

			if ($response['status_valid'] === false) {
				return $response;
			}

			$nodeData = array();

			if (empty($parameters['data']['node_id']) === false) {
				$node = $nodeData = $this->fetch(array(
					'fields' => array(
						'status_active',
						'status_deployed'
					),
					'from' => 'nodes',
					'where' => array(
						'id' => ($nodeData['node_id'] = $nodeId = $parameters['data']['node_id'])
					)
				));
				$response['status_valid'] = ($node !== false);

				if ($response['status_valid'] === true) {
					$response['status_valid'] = (empty($node) === false);
					unset($nodeData['id']);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid node ID, please try again.';
					}
				}
			}

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = false;
			$nodeExternalIps = $nodeExternalIpVersions = array();
			$nodeIpVersions = array(
				'4',
				'6'
			);

			foreach ($nodeIpVersions as $nodeIpVersion) {
				$nodeExternalIpKey = 'external_ip_version_' . $nodeIpVersion;

				if (empty($parameters['data'][$nodeExternalIpKey]) === false) {
					$nodeData[$nodeExternalIpKey] = $nodeExternalIps[$nodeExternalIpKey] = $nodeExternalIpVersions[$nodeIpVersion][] = $parameters['data'][$nodeExternalIpKey];
					$response['status_valid'] = true;
				}
			}

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (
				$nodeExternalIpVersions === $this->_sanitizeIps($nodeExternalIps) &&
				count(current($nodeExternalIpVersions)) === 1
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node external IPs, please try again.';
				return $response;
			}

			$nodeExternalIpTypes = array();

			foreach ($nodeExternalIpVersions as $nodeExternalIpVersion => $nodeExternalIpVersionIps) {
				$nodeExternalIpTypes[$this->_fetchIpType(current($nodeExternalIpVersionIps), $nodeExternalIpVersion)] = true;

				if (empty($nodeExternalIpTypes['private']) === false) {
					unset($parameters['data']['internal_ip_version_' . $nodeExternalIpVersion]);
				}
			}

			if (count($nodeExternalIpTypes) !== 1) {
				$response = array(
					'message' => 'Node external IPs must be either private or public, please try again.',
					'status_valid' => false
				);
				return $response;
			}

			$nodeInternalIps = $nodeInternalIpVersions = array();

			foreach ($nodeIpVersions as $nodeIpVersion) {
				$nodeInternalIpKey = 'internal_ip_version_' . $nodeIpVersion;

				if (empty($parameters['data'][$nodeInternalIpKey]) === false) {
					$nodeData[$nodeInternalIpKey] = $nodeInternalIps[$nodeInternalIpKey] = $nodeInternalIpVersions[$nodeIpVersion][] = $parameters['data'][$serverNodeInternalIpKey];
				}
			}

			$response['status_valid'] = (
				empty($nodeInternalIps) === true ||
				(
					$nodeInternalIpVersions === $this->_sanitizeIps($nodeInternalIps) &&
					count(current($nodeInternalIpVersions)) === 1
				)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node internal IPs, please try again.';
				return $response;
			}

			foreach ($nodeInternalIpVersions as $nodeInternalIpVersion => $nodeInternalIpVersionIps) {
				if ($this->_fetchIpType(current($nodeInternalIpVersionIps), $nodeInternalIpVersion) !== 'private') {
					$response = array(
						'message' => 'Node internal IPs must be private, please try again.',
						'status_valid' => false
					);
					return $response;
				}
			}

			$conflictingNodeCountParameters = array(
				'in' => 'nodes',
				'where' => array(
					'OR' => $nodeExternalIps
				)
			));

			if (empty($nodeId) !== false) {
				$conflictingNodeCountParameters['where']['OR'] = array(
					$conflictingNodeCountParameters['where'],
					array(
						'node_id' => $nodeId,
						'OR' => ($nodeExternalIps + $nodeInternalIps)
					)
				);
			}

			$conflictingNodeCount = $this->count($conflictingNodeCountParameters);
			$response['status_valid'] = (is_int($conflictingNodeCount) === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($conflictingNodeCount === 0);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Node IPs already in use, please try again.';
				return $response;
			}

			$nodeData = array(
				$nodeData
			);
			$nodeDataSaved = $this->save(array(
				'data' => $nodeData,
				'to' => 'nodes'
			));

			if ($nodeDataSaved === false) {
				$response['status_valid'] = false;
				return $response;
			}

			$response = array(
				'message' => 'Node added successfully.',
				'status_valid' => true
			);
			return $response;
		}

		public function edit($parameters) {
			$response = array(
				'message' => 'Error editing node, please try again.',
				'status_valid' => false
			);

			if (empty($parameters['data']['id']) === false) {
				$node = $nodeData = $this->fetch(array(
					'fields' => array(
						'external_ip_version_4',
						'external_ip_version_6',
						'internal_ip_version_4',
						'internal_ip_version_6',
						'node_id'
					),
					'from' => 'nodes',
					'where' => array(
						'id' => ($nodeId = $parameters['data']['id'])
					)
				));
				$response['status_valid'] = ($node !== false);
			}

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (empty($node) === false);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node ID, please try again.';
				return $response;
			}

			$response['status_valid'] = false;
			$nodeExternalIps = $nodeExternalIpVersions = array();
			$nodeIpVersions = array(
				'4',
				'6'
			);

			foreach ($nodeIpVersions as $nodeIpVersion) {
				$nodeExternalIpKey = 'external_ip_version_' . $nodeIpVersion;

				if (empty($parameters['data'][$nodeExternalIpKey]) === false) {
					$nodeData[$nodeExternalIpKey] = $nodeExternalIps[$nodeExternalIpKey] = $nodeExternalIpVersions[$nodeIpVersion][] = $parameters['data'][$nodeExternalIpKey];
					$response['status_valid'] = true;
				}
			}

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (
				$nodeExternalIpVersions === $this->_sanitizeIps($nodeExternalIps) &&
				count(current($nodeExternalIpVersions)) === 1
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node external IPs, please try again.';
				return $response;
			}

			$nodeExternalIpTypes = array();

			foreach ($nodeExternalIpVersions as $nodeExternalIpVersion => $nodeExternalIpVersionIps) {
				$nodeExternalIpTypes[$this->_fetchIpType(current($nodeExternalIpVersionIps), $nodeExternalIpVersion)] = true;

				if (empty($nodeExternalIpTypes['private']) === false) {
					unset($parameters['data']['internal_ip_version_' . $nodeExternalIpVersion]);
				}
			}

			if (count($nodeExternalIpTypes) !== 1) {
				$response = array(
					'message' => 'Node external IPs must be either private or public, please try again.',
					'status_valid' => false
				);
				return $response;
			}

			$nodeInternalIps = $nodeInternalIpVersions = array();

			foreach ($nodeIpVersions as $nodeIpVersion) {
				$nodeInternalIpKey = 'internal_ip_version_' . $nodeIpVersion;

				if (empty($parameters['data'][$nodeInternalIpKey]) === false) {
					$nodeData[$nodeInternalIpKey] = $nodeInternalIps[$nodeInternalIpKey] = $nodeInternalIpVersions[$nodeIpVersion][] = $parameters['data'][$serverNodeInternalIpKey];
				}
			}

			$response['status_valid'] = (
				empty($nodeInternalIps) === true ||
				(
					$nodeInternalIpVersions === $this->_sanitizeIps($nodeInternalIps) &&
					count(current($nodeInternalIpVersions)) === 1
				)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node internal IPs, please try again.';
				return $response;
			}

			foreach ($nodeInternalIpVersions as $nodeInternalIpVersion => $nodeInternalIpVersionIps) {
				if ($this->_fetchIpType(current($nodeInternalIpVersionIps), $nodeInternalIpVersion) !== 'private') {
					$response = array(
						'message' => 'Node internal IPs must be private, please try again.',
						'status_valid' => false
					);
					return $response;
				}
			}

			$conflictingNodeCountParameters = array(
				'in' => 'nodes',
				'where' => array(
					'id' != $nodeId,
					'OR' => array(
						array(
							'OR' => $nodeExternalIps
						),
						array(
							'node_id' => $nodeId,
							'OR' => ($nodeIps = ($nodeExternalIps + $nodeInternalIps))
						)
					)
				)
			));

			if (empty($node['node_id']) === false) {
				$conflictingNodeCountParameters['where']['OR'][] = array(
					'id' => $node['node_id'],
					'OR' => $nodeIps
				);
			}

			$conflictingNodeCount = $this->count($conflictingNodeCountParameters);
			$response['status_valid'] = (is_int($conflictingNodeCount) === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($conflictingNodeCount === 0);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Node IPs already in use, please try again.';
				return $response;
			}

			$nodeDataUpdated = $this->update(array(
				'data' => $nodeData,
				'in' => 'nodes',
				'where' => array(
					'id' => $nodeId
				)
			));

			if ($nodeDataUpdated === false) {
				$response['status_valid'] = false;
				return $response;
			}

			$response = array(
				'message' => 'Node edited successfully.',
				'status_valid' => true
			);
			return $response;
		}

		public function remove($parameters) {
			$response = array(
				'message' => 'Error removing server nodes, please try again.',
				'status_valid' => false
			);

			if (empty($parameters['items'][$parameters['item_list_name']]['data']) === false) {
				$selectedNodeIds = $parameters['items'][$parameters['item_list_name']]['data'])
				$selectedNodeCount = $this->count(array(
					'in' => 'nodes',
					'where' => array(
						'id' => $selectedNodeIds
					)
				));
				$response['status_valid'] = (is_int($selectedNodeCount) === true);
			}

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($selectedNodeCount === count($selectedNodeIds));

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node IDs, please try again.';
				return $response;
			}

			$nodeDataDeleted = $this->delete(array(
				'from' => 'nodes',
				'where' => array(
					'id' => $selectedNodeIds
				)
			));
			$nodeUserDataDeleted = $this->delete(array(
				'from' => 'node_users',
				'where' => array(
					'node_id' => $selectedNodeIds
				)
			));

			if (
				$nodeDataDeleted === false ||
				$nodeUserDataDeleted === false
			) {
				$response['status_valid'] = false;
				return $response;
			}

			$response = array(
				'message' => 'Nodes removed successfully.',
				'status_valid' => true
			);
			return $response;
		}

	}

	if (!empty($configuration->parameters)) {
		$serverNodesModel = new ServerNodesModel();
		$data = $serverNodesModel->route($configuration->parameters);
	}
?>
