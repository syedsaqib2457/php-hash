<?php
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
	// todo
?>
