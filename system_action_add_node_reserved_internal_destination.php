<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _addNodeReservedInternalDestination($node, $nodeIpVersion) {
		$response = array(
			'message' => 'Error adding node reserved internal destination, please try again.',
			'status_valid' => false
		);
		$nodeIds = array_filter(array(
			$node['id'],
			$node['node_id']
		));
		$existingNodeReservedInternalDestination = _fetch(array(
			'fields' => array(
				'id',
				'ip_address'
			),
			'from' => 'node_reserved_internal_destination',
			'limit' => 1,
			'where' => array(
				'ip_version' => $nodeIpVersion,
				'status_added' => false,
				'OR' => array(
					'node_id' => $nodeIds,
					'node_node_id' => $nodeIds
				)
			),
			'sort' => array(
				'field' => 'ip_address',
				'order' => 'ASC'
			)
		));
		$response['status_valid'] = ($existingNodeReservedInternalDestination !== false);

		if ($response === false) {
			return $response;
		}

		if (empty($existingNodeReservedInternalDestination) === true) {
			$existingNodeReservedInternalDestination = array(
				'ip_version' => $nodeIpVersion,
				'node_id' => $node['id'],
				'node_node_id' => $node['node_id'],
				'status_added' => false
			);

			switch ($nodeIpVersion) {
				case 4:
					$existingNodeReservedInternalDestination['ip_address'] = '10.0.0.0';
					break;
				case 6:
					$existingNodeReservedInternalDestination['ip_address'] = 'fc10:0000:0000:0000:0000:0000:0000:0000';
					break;
			}

			$nodeReservedInternalDestinationIpAddress = $existingNodeReservedInternalDestination['ip_address'];

			while ($existingNodeReservedInternalDestination['status_added'] === false) {
				switch ($nodeIpVersion) {
					case 4:
						$nodeReservedInternalDestinationIpAddress = long2ip(ip2long($nodeReservedInternalDestinationIpAddress) + 1);
						break;
					case 6:
						$nodeReservedInternalDestinationIpAddressBlock = substr($nodeReservedInternalDestinationIpAddress, -29);
						$nodeReservedInternalDestinationIpAddressBlockInteger = intval(str_replace(':', '', $nodeReservedInternalDestinationIpAddressBlock));
						$nodeReservedInternalDestinationIpAddressBlockIntegerIncrement = ($nodeReservedInternalDestinationIpAddressBlockInteger + 1);
						$nodeReservedInternalDestinationIpAddress = 'fc10:0000:' . implode(':', str_split(str_pad($nodeReservedInternalDestinationIpAddressBlockIntegerIncrement, 24, '0', STR_PAD_LEFT), 4));
						break;
				}

				$existingNodeCount = _count(array(
					'in' => 'nodes',
					'where' => array(
						'OR' => array(
							array(
								'internal_ip_version_' . $nodeIpVersion => $nodeReservedInternalDestinationIpAddress,
								'OR' => array(
									'id' => $nodeIds,
									'node_id' => $nodeIds
								)
							),
							array(
								'external_ip_version_' . $nodeIpVersion => $nodeReservedInternalDestinationIpAddress,
								'external_ip_version_' . $nodeIpVersion . '_type' => 'reserved'
							)
						)
					)
				));
				$response['status_valid'] = (is_int($existingNodeCount) === true);

				if ($response['status_valid'] === false) {
					return $response;
				}

				if ($existingNodeCount === 0) {
					$existingNodeReservedInternalDestination['ip_address'] = $nodeReservedInternalDestinationIpAddress;
					$existingNodeReservedInternalDestination['status_added'] = true;
				}
			}
		} else {
			$existingNodeReservedInternalDestination['status_added'] = true;
		}

		$existingNodeReservedInternalDestinationData = array(
			$existingNodeReservedInternalDestination,
			$existingNodeReservedInternalDestination
		);
		$nodeReservedInternalDestinationIpAddress = $existingNodeReservedInternalDestination['ip_address'];

		while ($existingNodeReservedInternalDestinationData[0]['ip_address'] === $existingNodeReservedInternalDestinationData[1]['ip_address']) {
			switch ($nodeIpVersion) {
				case 4:
					$nodeReservedInternalDestinationIpAddress = long2ip(ip2long($nodeReservedInternalDestinationIpAddress) + 1);
					break;
				case 6:
					$nodeReservedInternalDestinationIpAddressBlock = substr($nodeReservedInternalDestinationIpAddress, -29);
					$nodeReservedInternalDestinationIpAddressBlockInteger = intval(str_replace(':', '', $nodeReservedInternalDestinationIpAddressBlock));
					$nodeReservedInternalDestinationIpAddressBlockIntegerIncrement = ($nodeReservedInternalDestinationIpAddressBlockInteger + 1);
					$nodeReservedInternalDestinationIpAddress = 'fc10:0000:' . implode(':', str_split(str_pad($nodeReservedInternalDestinationIpAddressBlockIntegerIncrement, 24, '0', STR_PAD_LEFT), 4));
					break;
			}

			$existingNodeCount = _count(array(
				'in' => 'nodes',
				'where' => array(
					'OR' => array(
						array(
							'internal_ip_version_' . $nodeIpVersion => $nodeReservedInternalDestinationIpAddress,
							'OR' => array(
								'id' => $nodeIds,
								'node_id' => $nodeIds
							)
						),
						array(
							'external_ip_version_' . $nodeIpVersion => $nodeReservedInternalDestinationIpAddress,
							'external_ip_version_' . $nodeIpVersion . '_type' => 'reserved'
						)
					)
				)
			));
			$response['status_valid'] = (is_int($existingNodeCount) === true);

			if ($response['status_valid'] === false) {
				return $response;
			}

			if ($existingNodeCount === 0) {
				$existingNodeReservedInternalDestinationData[1]['ip_address'] = $nodeReservedInternalDestinationIpAddress;
			}
		}

		unset($existingNodeReservedInternalDestinationData[1]['id']);
		$existingNodeReservedInternalDestinationData[1]['status_added'] = false;
		$nodeReservedInternalDestinationsSaved = _save(array(
			'data' => $existingNodeReservedInternalDestinationData,
			'to' => 'node_reserved_internal_destinations'
		));
		$response['status_valid'] = ($nodeReservedInternalDestinationsSaved !== false);

		if ($response['status_valid'] === false) {
			return $response;
		}

		$response['data']['node_reserved_internal_destination_ip_address'] = $existingNodeReservedInternalDestination['ip_address'];
		return $response;
	}
?>
