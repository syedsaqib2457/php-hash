<?php
	function _processNodeProcesses($parameters, $response) {
		$parameters['ip_address_versions'] = array(
			4 => array(
				'interface_type' => 'inet',
				'network_mask' => 32
			),
			6 => array(
				'interface_type' => 'inet6',
				'network_mask' => 128
			)
		);
		exec('getconf PAGE_SIZE 2>&1', $kernelPageSize);
		$parameters['kernel_page_size'] = current($kernelPageSize);
		exec('free -b | grep "Mem:" | grep -v free | awk \'{print $2}\'', $memoryCapacityBytes);
		$parameters['memory_capacity_bytes'] = current($memoryCapacityBytes);
		$parameters['node_process_type_firewall_rule_set_index'] = 0;

		if (file_exists('/usr/local/ghostcompute/system_action_process_node_response.json') === true) {
			$systemActionProcessNodeResponse = file_get_contents('/usr/local/ghostcompute/system_action_process_node_response.json');
			$systemActionProcessNodeResponse = json_decode($systemActionProcessNodeResponse, true);

			if (empty($systemActionProcessNodeResponse) === false) {
				$parameters['data']['current'] = $systemActionProcessNodeResponse;
			}
		}

		shell_exec('sudo wget -O /usr/local/ghostcompute/system_action_process_node_response.json --no-dns-cache --timeout=600 --post-data "json={\"action\":\"process_node\",\"node_authentication_token\":\"' . $parameters['node_authentication_token'] . '\"}" ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php');

		if (file_exists('/usr/local/ghostcompute/system_action_process_node_response.json') === false) {
			echo 'Error processing node, please try again.' . "\n";
			exit;
		}

		$nodeProcessResponseFileContents = file_get_contents('/usr/local/ghostcompute/system_action_process_node_response.json');
		$nodeProcessResponseFileContents = json_decode($nodeProcessResponseFileContents, true);

		if ($nodeProcessResponseFileContents === false) {
			echo 'Error processing node, please try again.' . "\n";
			exit;
		}

		// todo
	}

	if (($parameters['action'] === 'process_node_processes') === true) {
		_processNodeProcesses($parameters, $response);
		_output($response);
	}
?>
