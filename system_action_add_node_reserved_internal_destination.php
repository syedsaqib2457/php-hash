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
				'id',
				'ip_address',
				'status_added'
			),
			'in' => $parameters['databases']['node_reserved_internal_destinations'],
			'limit' => 1,
			'sort' => array(
				'key' => 'ip_address',
				'order' => 'ascending'
			),
			'where' => array(
				'either' => array(
					'node_id' => $parameters['node'][$nodeIpAddressVersion],
					'node_node_id' => $parameters['node'][$nodeIpAddressVersion]
				),
				'ip_address_version' => ($nodeIpAddressVersion = key($parameters['node'])),
				'status_added' => false,
				'status_processed' => true
			)
		), $response);
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

				if (($existingNodeCount === 0) === true) {
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

		while (($existingNodeReservedInternalDestinationData[0]['ip_address'] === $existingNodeReservedInternalDestinationData[1]['ip_address']) === true) {
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

			if (($existingNodeCount === 0) === true) {
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
