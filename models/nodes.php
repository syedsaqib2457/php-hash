<?php
	$extend = true;
	require_once($configuration->settings['base_path'] . '/models/main.php');

	class NodesModel extends MainModel {

		public function activate($parameters) {
			$response = array(
				'message' => 'Error activating node, please try again.',
				'status_valid' => false
			);
			// ..
			return $response;
		}

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
				$response['message'] = 'Invalid node type, please try again.';
				return $response;
			}

			if (empty($parameters['data']['node_id']) === false) {
				$node = $this->fetch(array(
					'fields' => array(
						'status_active',
						'status_deployed'
					),
					'from' => 'nodes',
					'where' => array(
						'id' => ($nodeId = $parameters['data']['node_id'])
					)
				));
				$response['status_valid'] = ($node !== false);

				if ($response['status_valid'] === true) {
					$response['status_valid'] = (empty($node) === false);
					$parameters['data'] = array_merge($parameters['data'], $node);

					if ($response['status_valid'] === false) {
						$response['message'] = 'Invalid node ID, please try again.';
					}
				}
			}

			if ($response['status_valid'] === false) {
				return $response;
			}

			$nodeExternalIps = $nodeExternalIpVersions = array();
			$nodeIpVersions = array(
				'4',
				'6'
			);

			foreach ($nodeIpVersions as $nodeIpVersion) {
				$nodeExternalIpKey = 'external_ip_version_' . $nodeIpVersion;

				if (empty($parameters['data'][$nodeExternalIpKey]) === false) {
					$nodeExternalIps[$nodeExternalIpKey] = $nodeExternalIpVersions[$nodeIpVersion][] = $parameters['data'][$nodeExternalIpKey];
				}
			}

			$response['status_valid'] = (empty($nodeExternalIps) === false);

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
					$nodeInternalIps[$nodeInternalIpKey] = $nodeInternalIpVersions[$nodeIpVersion][] = $parameters['data'][$serverNodeInternalIpKey];
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
			$conflictingNodeProcessCountParameters = array(
				'in' => 'node_processes',
				'where' => array(
					'OR' => $nodeExternalIps
				)
			);

			if (empty($nodeId) !== false) {
				$conflictingNodeCountParameters['where']['OR'] = $conflictingNodeProcessCountParameters['where']['OR'] = array(
					$conflictingNodeCountParameters['where'],
					array(
						'node_id' => $nodeId,
						'OR' => ($nodeExternalIps + $nodeInternalIps)
					)
				);
			}

			$conflictingNodeCount = $this->count($conflictingNodeCountParameters);
			$conflictingNodeProcessCount = $this->count($conflictingNodeProcessCountParameters);
			$response['status_valid'] = (
				is_int($conflictingNodeCount) === true &&
				is_int($conflictingNodeProcessCount) === true
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = (
				$conflictingNodeCount === 0 &&
				$conflictingNodeProcessCount === 0
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Node IPs already in use, please try again.';
				return $response;
			}

			$nodeData = array(
				array_intersect_key($parameters['data'], array(
					'external_ip_version_4' => true,
					'external_ip_version_6' => true,
					'internal_ip_version_4' => true,
					'internal_ip_version_6' => true,
					'node_id' => true,
					'status_active' => true,
					'status_deployed' => true,
					'type' => true
				))
			);
			$nodeDataSaved = $this->save(array(
				'data' => $nodeData,
				'to' => 'nodes'
			));
			$response['status_valid'] = ($nodeDataSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response = array(
				'message' => 'Node added successfully.',
				'status_valid' => true
			);
			return $response;
		}

		// todo: delete proxies.php

		public function authenticate($parameters) {
			$response = array(
				'message' => 'Error authenticating nodes, please try again.',
				'status_valid' => false
			);

			if (empty($parameters['ids']['nodes']) === true) {
				$response['message'] = 'Invalid node IDs, please try again.';
				return $response;
			}

			$response['status_valid'] = (
				(
					empty($parameters['data']['authentication_password']) === false ||
					empty($parameters['data']['authentication_username']) === false
				) &&
				(
					empty($parameters['data']['authentication_password']) === true ||
					empty($parameters['data']['authentication_username']) === true
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
				empty($parameters['data']['authentication_username']) === true ||
				(
					strlen($parameters['data']['authentication_username']) > 10 &&
					strlen($parameters['data']['authentication_username']) < 20 &&
					strlen($parameters['data']['authentication_password']) > 10 &&
					strlen($parameters['data']['authentication_password']) < 20
				)
			);

			if ($response['status_valid'] === false) {
				$response['message'] = 'Authentication username and password must be between 10 and 20 characters, please try again.';
				return $response;
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

			$userParameters = array(
				'fields' => array(
					'id'
				),
				'from' => 'users',
				'where' => array_intersect_key($parameters['data'], array(
					'authentication_password',
					'authentication_username',
					'authentication_whitelist'
				))
			));
			$user = $this->fetch($userParameters);
			$response['status_valid'] = ($user !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			if (empty($userId) === false) {
				$parameters['data']['id'] = $userId = $user['id'];
			}

			$userDataSaved = $this->save(array(
				'data' => array(
					array_intersect_key($parameters['data'], array(
						'authentication_password',
						'authentication_username',
						'authentication_whitelist',
						'id'
					))
				),
				'to' => 'users'
			));
			$response['status_valid'] = ($userDataSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			if (empty($userId) === true) {
				$user = $this->fetch($userParameters);
				$response['status_valid'] = ($user !== false);

				if ($response['status_valid'] === false) {
					return $response;
				}

				$userId = $user['id'];
			}

			$nodeUsers = $this->fetch(array(
				'fields' => array(
					'id',
					'node_id',
					'user_id'
				),
				'from' => 'node_users',
				'where' => array(
					'node_id' => ($nodeIds = $parameters['ids']['nodes']),
					'user_id' => $userId
				)
			));
			$response['status_valid'] = ($nodeUsers !== false);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$nodeUserData = array();

			foreach ($nodeUsers as $nodeUser) {
				$nodeUserData[$nodeUser['node_id']] = $nodeUser;
			}

			$nodeIds = array_diff($nodeIds, array_keys($nodeUserData));

			foreach ($nodeIds as $nodeId) {
				$nodeUserData[] = array(
					'node_id' => $nodeId,
					'user_id' => $user['id']
				);
			}

			$nodeUserDataSaved = $this->save(array(
				'data' => $nodeUserData,
				'to' => 'node_users'
			));
			$response['status_valid'] = ($nodeUserDataSaved === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response = array(
				'message' => 'Nodes authenticated successfully.',
				'status_valid' => true
			);
			return $response;
		}

		public function deploy($parameters) {
			$response = array(
				'message' => 'Error deploying node, please try again.',
				'status_valid' => false
			);
			// ..
			return $response;
		}

		public function download($parameters) {
			$response = array(
				'message' => 'Error downloading nodes, please try again.',
				'status_valid' => false
			);
			// ..
			return $response;
		}

		public function edit($parameters) {
			$response = array(
				'message' => 'Error editing node, please try again.',
				'status_valid' => false
			);

			// todo: combine authenticate and request_limit functions into edit() function

			if (empty($parameters['data']['type']) === false) {
				$response['status_valid'] = in_array($parameters['data']['type'], array(
					'nameserver',
					'proxy'
				));

				if ($response['status_valid'] === false) {
					$response['message'] = 'Invalid node type, please try again.';
					return $response;
				}
			}

			if (empty($parameters['data']['id']) === false) {
				$node = $this->fetch(array(
					'fields' => array(
						'node_id',
						'status_deployed'
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

			if (isset($parameters['data']['status_active']) === false) {
				$parameters['data']['status_active'] = boolval($parameters['data']['status_active']);

				if ($node['status_deployed'] === false) {
					$parameters['data']['status_active'] = false;
				}
			}

			$nodeExternalIps = $nodeExternalIpVersions = array();
			$nodeIpVersions = array(
				'4',
				'6'
			);

			foreach ($nodeIpVersions as $nodeIpVersion) {
				$nodeExternalIpKey = 'external_ip_version_' . $nodeIpVersion;

				if (empty($parameters['data'][$nodeExternalIpKey]) === false) {
					$nodeExternalIps[$nodeExternalIpKey] = $nodeExternalIpVersions[$nodeIpVersion][] = $parameters['data'][$nodeExternalIpKey];
				}
			}

			$response['status_valid'] = (empty($nodeExternalIps) === false);

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
					$nodeInternalIps[$nodeInternalIpKey] = $nodeInternalIpVersions[$nodeIpVersion][] = $parameters['data'][$serverNodeInternalIpKey];
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
			$conflictingNodeProcessCountParameters = array(
				'in' => 'node_processes',
				'where' => array(
					'OR' => array(
						array(
							'OR' => $nodeExternalIps
						),
						array(
							'node_id' => $nodeId,
							'OR' => $nodeIps
						)
					)
				)
			);

			if (empty($node['node_id']) === false) {
				$conflictingNodeCountParameters['where']['OR'][] = array(
					'id' => $node['node_id'],
					'OR' => $nodeIps
				);
				$conflictingNodeProcessCountParameters['where']['OR'][] = array(
					'node_id' => $node['node_id'],
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

			$nodeData = array_intersect_key($parameters['data'], array(
				'external_ip_version_4' => true,
				'external_ip_version_6' => true,
				'id' => true,
				'internal_ip_version_4' => true,
				'internal_ip_version_6' => true,
				'node_id' => true,
				'status_active' => true,
				'type' => true
			));
			$nodeDataUpdated = $this->update(array(
				'data' => $nodeData,
				'in' => 'nodes',
				'where' => array(
					'id' => $nodeId
				)
			));
			$response['status_valid'] = ($nodeDataUpdated === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response = array(
				'message' => 'Node edited successfully.',
				'status_valid' => true
			);
			return $response;
		}

		public function list($parameters) {
			// ..
			return array();
		}

		public function remove($parameters) {
			$response = array(
				'message' => 'Error removing nodes, please try again.',
				'status_valid' => false
			);

			if (empty($parameters['ids']['nodes']) === false) {
				$nodeIds = $parameters['ids']['nodes'];
				$nodeCount = $this->count(array(
					'in' => 'nodes',
					'where' => array(
						'id' => $nodeIds
					)
				));
				$response['status_valid'] = (is_int($nodeCount) === true);
			}

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response['status_valid'] = ($nodeCount === count($nodeIds));

			if ($response['status_valid'] === false) {
				$response['message'] = 'Invalid node IDs, please try again.';
				return $response;
			}

			$nodeDataDeleted = $this->delete(array(
				'from' => 'nodes',
				'where' => array(
					'id' => $nodeIds
				)
			));
			$nodeUserDataDeleted = $this->delete(array(
				'from' => 'node_users',
				'where' => array(
					'node_id' => $nodeIds
				)
			));
			$response['status_valid'] = (
				$nodeDataDeleted === true &&
				$nodeUserDataDeleted === true
			);

			if ($response['status_valid'] === false) {
				return $response;
			}

			$response = array(
				'message' => 'Nodes removed successfully.',
				'status_valid' => true
			);
			return $response;
		}

		public function search($parameters) {
			$response = array(
				'message' => 'Error searching nodes, please try again.',
				'status_valid' => false
			);
			// ..
			return $response;
		}

		public function view($parameters = array()) {
			$response = array(
				'message' => 'Error viewing node, please try again.',
				'status_valid' => false
			);

			if (
				empty($parameters['where']['id']) !== false &&
				is_string($parameters['where']['id']) === true
			) {
				// ..
			}

			return $response;
		}

	}

	if (!empty($configuration->parameters)) {
		$nodesModel = new NodesModel();
		$data = $nodesModel->route($configuration->parameters);
	}
?>
