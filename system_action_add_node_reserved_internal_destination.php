<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['system_databases'] += _connect(array(
		'node_reserved_internal_destinations'
	), $parameters['system_databases'], $response);

	function _addNodeReservedInternalDestination($parameters, $response) {
		$nodeIpAddressVersionNumber = key($parameters['node']);
		$existingNodeReservedInternalDestination = _list(array(
			'data' => array(
				'added_status',
				'id',
				'ip_address'
			),
			'in' => $parameters['system_databases']['node_reserved_internal_destinations'],
			'limit' => 1,
			'sort' => array(
				'ip_address' => 'ascending'
			),
			'where' => array(
				'added_status' => '0',
				'either' => array(
					'node_id' => $parameters['node'][$nodeIpAddressVersionNumber],
					'node_node_id' => $parameters['node'][$nodeIpAddressVersionNumber]
				),
				'ip_address_version_number' => $nodeIpAddressVersionNumber,
				'processed_status' => '1'
			)
		), $response);
		$existingNodeReservedInternalDestination = current($existingNodeReservedInternalDestination);

		if (empty($existingNodeReservedInternalDestination) === true) {
			$existingNodeReservedInternalDestination = array(
				'added_status' => '0',
				'id' => $existingNodeReservedInternalDestination['id'],
				'ip_address_version_number' => $nodeIpAddressVersionNumber,
				'node_id' => $parameters['node'][$nodeIpAddressVersionNumber]['id'],
				'node_node_id' => $parameters['node'][$nodeIpAddressVersionNumber]['node_id'],
				'processed_status' => '1'
			);

			switch ($nodeIpAddressVersionNumber) {
				case '4':
					$existingNodeReservedInternalDestination['ip_address'] = '10.0.0.0';
					break;
				case '6':
					$existingNodeReservedInternalDestination['ip_address'] = 'fc10:0000:0000:0000:0000:0000:0000:0000';
					break;
			}

			$nodeReservedInternalDestinationIpAddress = $existingNodeReservedInternalDestination['ip_address'];

			while (($existingNodeReservedInternalDestination['added_status'] === '0') === true) {
				switch ($nodeIpAddressVersionNumber) {
					case '4':
						$nodeReservedInternalDestinationIpAddress = ip2long($nodeReservedInternalDestinationIpAddress);
						$nodeReservedInternalDestinationIpAddress = long2ip($nodeReservedInternalDestinationIpAddress + 1);
						break;
					case '6':
						$nodeReservedInternalDestinationIpAddressBlock = substr($nodeReservedInternalDestinationIpAddress, -29);
						$nodeReservedInternalDestinationIpAddressBlockInteger = str_replace(':', '', $nodeReservedInternalDestinationIpAddressBlock);
						$nodeReservedInternalDestinationIpAddressBlockInteger = intval($nodeReservedInternalDestinationIpAddressBlockInteger);
						$nodeReservedInternalDestinationIpAddressBlockIntegerIncrement = ($nodeReservedInternalDestinationIpAddressBlockInteger + 1);
						$nodeReservedInternalDestinationIpAddress = str_pad($nodeReservedInternalDestinationIpAddressBlockIntegerIncrement, 24, '0', STR_PAD_LEFT);
						$nodeReservedInternalDestinationIpAddress = str_split($nodeReservedInternalDestinationIpAddress, 4);
						$nodeReservedInternalDestinationIpAddress = 'fc10:0000:' . implode(':', $nodeReservedInternalDestinationIpAddress, 4);
						break;
				}

				if ((_validateIpAddressType($nodeReservedInternalDestinationIpAddress, $nodeIpAddressVersionNumber) === 'public_network') === true) {
					continue;
				}

				$existingNodeCount = _count(array(
					'in' => $parameters['system_databases']['nodes'],
					'where' => array(
						'either' => array(
							array(
								'either' => array(
									'id' => $parameters['node'][$nodeIpAddressVersionNumber],
									'node_id' => $parameters['node'][$nodeIpAddressVersionNumber]
								),
								'internal_ip_address_version_' . $nodeIpAddressVersionNumber => $nodeReservedInternalDestinationIpAddress
							),
							array(
								'external_ip_address_version_' . $nodeIpAddressVersionNumber => $nodeReservedInternalDestinationIpAddress,
								'external_ip_address_version_' . $nodeIpAddressVersionNumber . '_type !=' => 'public_network'
							)
						)
					)
				), $response);

				if (($existingNodeCount > 0) === false) {
					$existingNodeReservedInternalDestination['added_status'] = '1';
					$existingNodeReservedInternalDestination['ip_address'] = $nodeReservedInternalDestinationIpAddress;
				}
			}
		} else {
			$existingNodeReservedInternalDestination['added_status'] = true;
		}

		$existingNodeReservedInternalDestinationData = array(
			$existingNodeReservedInternalDestination,
			$existingNodeReservedInternalDestination
		);
		unset($existingNodeReservedInternalDestinationData[1]['id']);
		$existingNodeReservedInternalDestinationData[1]['added_status'] = '0';
		$nodeReservedInternalDestinationIpAddress = $existingNodeReservedInternalDestination['ip_address'];

		while (($existingNodeReservedInternalDestinationData[0]['ip_address'] === $existingNodeReservedInternalDestinationData[1]['ip_address']) === true) {
			switch ($nodeIpAddressVersionNumber) {
				case '4':
					$nodeReservedInternalDestinationIpAddress = ip2long($nodeReservedInternalDestinationIpAddress);
					$nodeReservedInternalDestinationIpAddress = long2ip($nodeReservedInternalDestinationIpAddress + 1);
					break;
				case '6':
					$nodeReservedInternalDestinationIpAddressBlock = substr($nodeReservedInternalDestinationIpAddress, -29);
					$nodeReservedInternalDestinationIpAddressBlockInteger = str_replace(':', '', $nodeReservedInternalDestinationIpAddressBlock);
					$nodeReservedInternalDestinationIpAddressBlockInteger = intval($nodeReservedInternalDestinationIpAddressBlockInteger);
					$nodeReservedInternalDestinationIpAddressBlockIntegerIncrement = ($nodeReservedInternalDestinationIpAddressBlockInteger + 1);
					$nodeReservedInternalDestinationIpAddress = str_pad($nodeReservedInternalDestinationIpAddressBlockIntegerIncrement, 24, '0', STR_PAD_LEFT);
					$nodeReservedInternalDestinationIpAddress = str_split($nodeReservedInternalDestinationIpAddress, 4);
					$nodeReservedInternalDestinationIpAddress = 'fc10:0000:' . implode(':', $nodeReservedInternalDestinationIpAddress);
					break;
			}

			if ((_validateIpAddressType($nodeReservedInternalDestinationIpAddress, $nodeIpAddressVersionNumber) === 'public_network') === true) {
				continue;
			}

			$existingNodeCount = _count(array(
				'in' => $parameters['system_databases']['nodes'],
				'where' => array(
					'either' => array(
						array(
							'either' => array(
								'id' => $parameters['node'][$nodeIpAddressVersionNumber],
								'node_id' => $parameters['node'][$nodeIpAddressVersionNumber]
							),
							'internal_ip_address_version_' . $nodeIpAddressVersionNumber => $nodeReservedInternalDestinationIpAddress
						),
						array(
							'external_ip_address_version_' . $nodeIpAddressVersionNumber => $nodeReservedInternalDestinationIpAddress,
							'external_ip_address_version_' . $nodeIpAddressVersionNumber . '_type !=' => 'public_network'
						)
					)
				)
			), $response);

			if (($existingNodeCount > 0) === false) {
				$existingNodeReservedInternalDestinationData[1]['ip_address'] = $nodeReservedInternalDestinationIpAddress;
			}
		}

		_save(array(
			'data' => $existingNodeReservedInternalDestinationData,
			'to' => $parameters['system_databases']['node_reserved_internal_destinations']
		), $response);
		$response = $nodeReservedInternalDestinationIpAddress;
		return $response;
	}
?>
