<?php
	function _processNodeResourceUsageLogs($parameters, $response) {
		exec('getconf PAGE_SIZE 2>&1', $kernelPageSize);
		$parameters['kernel_page_size'] = current($kernelPageSize);
	}

	if (($parameters['action'] === 'process_node_resource_usage_logs') === true) {
		$response = _processNodeResourceUsageLogs($parameters, $response);
		_output($response);
	}
?>
