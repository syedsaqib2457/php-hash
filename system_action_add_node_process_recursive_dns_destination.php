<?php
	if (empty($parameters) === true) {
		exit;
	}

	$parameters['databases'] += _connect(array(
		'node_process_recursive_dns_destinations'
	), $parameters['databases'], $response);

	function _addNodeProcessRecursiveDnsDestination($parameters, $response) {
		return $response;
	}
?>
