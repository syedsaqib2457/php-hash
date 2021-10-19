<?php
	if (empty($parameters) === true) {
		exit;
	}

	function _addNodeReservedInternalDestination($parameters) {
		$response = array(
			'status_valid' => false
		);
		$existingNodeReservedInternalDestination = _list(array(
			'in' => $parameters['databases']['node_reserved_internal_destinations'],
			'limit' => 1,
			'sort' => array(
				'field' => 'ip_address',
				'order' => 'ASC'
			),
			'where' => array(
				'ip_address_version' => ($nodeIpAddressVersion = key($parameters['node'])),
				'status_added' => false,
				'status_processed' => true,
				'OR' => array(
					'node_id' => ($nodeIds = $parameters['node'][$nodeIpAddressVersion]),
					'node_node_id' => $nodeIds
				)
			)
		));

		if ($existingNodeReservedInternalDestination === false) {
			$response['message'] = 'Error listing data in node_reserved_internal_destinations database, please try again.';
			return $response;
		}

		$existingNodeReservedInternalDestination = current($existingNodeReservedInternalDestination);

		if (empty($existingNodeReservedInternalDestination) === true) {
			$existingNodeReservedInternalDestination = array(
				'id' => $existingNodeReservedInternalDestination['id'],
				'ip_address_version' => $nodeIpAddressVersion,
				'node_id' => $parameters['node'][$nodeIpAddressVersion]['id'],
				'node_node_id' => $parameters['node'][$nodeIpAddressVersion]['node_id'],
				'status_added' => false,
				'status_processed' => true
			);

			switch ($nodeIpAddressVersion) {
				case 4:
					$existingNodeReservedInternalDestination['ip_address'] = '10.0.0.0';
					break;
				case 6:
					$existingNodeReservedInternalDestination['ip_address'] = 'fc10:0000:0000:0000:0000:0000:0000:0000';
					break;
			}

			$nodeReservedInternalDestinationIpAddress = $existingNodeReservedInternalDestination['ip_address'];

			while ($existingNodeReservedInternalDestination['status_added'] === false) {
				switch ($nodeIpAddressVersion) {
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
					'in' => $parameters['databases']['nodes'],
					'where' => array(
						'OR' => array(
							array(
								'internal_ip_version_' . $nodeIpAddressVersion => $nodeReservedInternalDestinationIpAddress,
								'OR' => array(
									'id' => $nodeIds,
									'node_id' => $nodeIds
								)
							),
							array(
								'external_ip_version_' . $nodeIpAddressVersion => $nodeReservedInternalDestinationIpAddress,
								'external_ip_version_' . $nodeIpAddressVersion . '_type' => 'reserved'
							)
						)
					)
				));

				if (is_int($existingNodeCount) === false) {
					$response['message'] = 'Error counting data in nodes database, please try again.';
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
		$existingNodeReservedInternalDestinationData[1]['status_added'] = false;
		unset($existingNodeReservedInternalDestinationData[1]['id']);
		$nodeReservedInternalDestinationIpAddress = $existingNodeReservedInternalDestination['ip_address'];

		while ($existingNodeReservedInternalDestinationData[0]['ip_address'] === $existingNodeReservedInternalDestinationData[1]['ip_address']) {
			switch ($nodeIpAddressVersion) {
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
				'in' => $parameters['databases']['nodes'],
				'where' => array(
					'OR' => array(
						array(
							'internal_ip_version_' . $nodeIpAddressVersion => $nodeReservedInternalDestinationIpAddress,
							'OR' => array(
								'id' => $nodeIds,
								'node_id' => $nodeIds
							)
						),
						array(
							'external_ip_version_' . $nodeIpAddressVersion => $nodeReservedInternalDestinationIpAddress,
							'external_ip_version_' . $nodeIpAddressVersion . '_type' => 'reserved'
						)
					)
				)
			));

			if (is_int($existingNodeCount) === false) {
				$response['message'] = 'Error counting data in nodes database, please try again.';
				return $response;
			}

			if ($existingNodeCount === 0) {
				$existingNodeReservedInternalDestinationData[1]['ip_address'] = $nodeReservedInternalDestinationIpAddress;
			}
		}

		$nodeReservedInternalDestinationsSaved = _save(array(
			'data' => $existingNodeReservedInternalDestinationData,
			'to' => $parameters['databases']['node_reserved_internal_destinations']
		));

		if ($nodeReservedInternalDestinationsSaved === false) {
			$response['message'] = 'Error saving data in node_reserved_internal_destinations database, please try again.';
			return $response;
		}

		$response = array(
			'data' => array(
				'node_reserved_internal_destination_ip_address' => $nodeReservedInternalDestinationIpAddress
			),
			'status_valid' => true
		);
		return $response;
	}
?>
