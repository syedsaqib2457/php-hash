<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'node_reserved_internal_destinations'
	), $parameters['databases'], $response);

	function _addNodeReservedInternalDestination($parameters, $response) {
		$existingNodeReservedInternalDestination = _list(array(
			'columns' => array(
				'added_status',
				'id',
				'ip_address'
			),
			'in' => $parameters['databases']['node_reserved_internal_destinations'],
			'limit' => 1,
			'sort' => array(
				'key' => 'ip_address',
				'order' => 'ascending'
			),
			'where' => array(
				'added_status' => '0'
				'either' => array(
					'node_id' => $parameters['node'][$nodeIpAddressVersion],
					'node_node_id' => $parameters['node'][$nodeIpAddressVersion]
				),
				'ip_address_version' => ($nodeIpAddressVersion = key($parameters['node'])),
				'processed_status' => '1'
			)
		), $response);
		$existingNodeReservedInternalDestination = current($existingNodeReservedInternalDestination);

		if (empty($existingNodeReservedInternalDestination) === true) {
			$existingNodeReservedInternalDestination = array(
				'added_status' => '0',
				'id' => $existingNodeReservedInternalDestination['id'],
				'ip_address_version' => $nodeIpAddressVersion,
				'node_id' => $parameters['node'][$nodeIpAddressVersion]['id'],
				'node_node_id' => $parameters['node'][$nodeIpAddressVersion]['node_id'],
				'processed_status' => '1'
			);

			switch ($nodeIpAddressVersion) {
				case '4':
					$existingNodeReservedInternalDestination['ip_address'] = '10.0.0.0';
					break;
				case '6':
					$existingNodeReservedInternalDestination['ip_address'] = 'fc10:0000:0000:0000:0000:0000:0000:0000';
					break;
			}

			$nodeReservedInternalDestinationIpAddress = $existingNodeReservedInternalDestination['ip_address'];

			while (($existingNodeReservedInternalDestination['added_status'] === '0') === true) {
				switch ($nodeIpAddressVersion) {
					case '4':
						$nodeReservedInternalDestinationIpAddress = long2ip(ip2long($nodeReservedInternalDestinationIpAddress) + 1);
						break;
					case '6':
						$nodeReservedInternalDestinationIpAddressBlock = substr($nodeReservedInternalDestinationIpAddress, -29);
						$nodeReservedInternalDestinationIpAddressBlockInteger = intval(str_replace(':', '', $nodeReservedInternalDestinationIpAddressBlock));
						$nodeReservedInternalDestinationIpAddressBlockIntegerIncrement = ($nodeReservedInternalDestinationIpAddressBlockInteger + 1);
						$nodeReservedInternalDestinationIpAddress = 'fc10:0000:' . implode(':', str_split(str_pad($nodeReservedInternalDestinationIpAddressBlockIntegerIncrement, 24, '0', STR_PAD_LEFT), 4));
						break;
				}

				$existingNodeCount = _count(array(
					'in' => $parameters['databases']['nodes'],
					'where' => array(
						'either' => array(
							array(
								'either' => array(
									'id' => $parameters['node'][$nodeIpAddressVersion],
									'node_id' => $parameters['node'][$nodeIpAddressVersion]
								),
								'internal_ip_address_version_' . $nodeIpAddressVersion => $nodeReservedInternalDestinationIpAddress
							),
							array(
								'external_ip_address_version_' . $nodeIpAddressVersion => $nodeReservedInternalDestinationIpAddress,
								'external_ip_address_version_' . $nodeIpAddressVersion . '_type !=' => 'public_network'
							)
						)
					)
				), $response);

				if (($existingNodeCount > 0) === false) {
					$existingNodeReservedInternalDestination['ip_address'] = $nodeReservedInternalDestinationIpAddress;
					$existingNodeReservedInternalDestination['added_status'] = "1";
				}
			}
		} else {
			$existingNodeReservedInternalDestination['added_status'] = true;
		}

		$existingNodeReservedInternalDestinationData = array(
			$existingNodeReservedInternalDestination,
			$existingNodeReservedInternalDestination
		);
		$existingNodeReservedInternalDestinationData[1]['added_status'] = "0";
		unset($existingNodeReservedInternalDestinationData[1]['id']);
		$nodeReservedInternalDestinationIpAddress = $existingNodeReservedInternalDestination['ip_address'];

		while (($existingNodeReservedInternalDestinationData[0]['ip_address'] === $existingNodeReservedInternalDestinationData[1]['ip_address']) === true) {
			switch ($nodeIpAddressVersion) {
				case '4':
					$nodeReservedInternalDestinationIpAddress = long2ip(ip2long($nodeReservedInternalDestinationIpAddress) + 1);
					break;
				case '6':
					$nodeReservedInternalDestinationIpAddressBlock = substr($nodeReservedInternalDestinationIpAddress, -29);
					$nodeReservedInternalDestinationIpAddressBlockInteger = intval(str_replace(':', '', $nodeReservedInternalDestinationIpAddressBlock));
					$nodeReservedInternalDestinationIpAddressBlockIntegerIncrement = ($nodeReservedInternalDestinationIpAddressBlockInteger + 1);
					$nodeReservedInternalDestinationIpAddress = 'fc10:0000:' . implode(':', str_split(str_pad($nodeReservedInternalDestinationIpAddressBlockIntegerIncrement, 24, '0', STR_PAD_LEFT), 4));
					break;
			}

			$existingNodeCount = _count(array(
				'in' => $parameters['databases']['nodes'],
				'where' => array(
					'either' => array(
						array(
							'either' => array(
								'id' => $parameters['node'][$nodeIpAddressVersion],
								'node_id' => $parameters['node'][$nodeIpAddressVersion]
							),
							'internal_ip_address_version_' . $nodeIpAddressVersion => $nodeReservedInternalDestinationIpAddress
						),
						array(
							'external_ip_address_version_' . $nodeIpAddressVersion => $nodeReservedInternalDestinationIpAddress,
							'external_ip_address_version_' . $nodeIpAddressVersion . '_type !=' => 'public_network'
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
			'to' => $parameters['databases']['node_reserved_internal_destinations']
		), $response);
		$response = $nodeReservedInternalDestinationIpAddress;
		return $response;
	}
?>
