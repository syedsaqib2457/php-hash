<?php
	if (
		(empty($_SERVER['argv'][1]) === true) ||
		(empty($_SERVER['argv'][2]) === true)
	) {
		echo 'Error deploying node, please try again.' . "\n";
		exit;
	}


?>
