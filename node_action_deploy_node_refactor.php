<?php
	if (
		(empty($_SERVER['argv'][1]) === true) ||
		(empty($_SERVER['argv'][2]) === true)
	) {
		echo 'Error deploying node, please try again.' . "\n";
		exit;
	}

	function _executeCommands($commands) {
		foreach ($commands as $command) {
			if (
				(empty($command) === false) &&
				(is_string($command) === true)
			) {
				echo shell_exec($command);
			}
		}

		return;
	}
?>
