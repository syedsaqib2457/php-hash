<?php
	require_once($configuration->settings['base_path'] . '/methods/' . end($configuration->parameters['route']['parts']) . '.php');
	echo json_encode($data);
?>
