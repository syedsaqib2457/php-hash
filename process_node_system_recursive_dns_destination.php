<?php
	class ProcessNodeSystemRecursiveDnsDestination {

		protected function _killProcessIds($processIds) {
			$commands = array(
				'#!/bin/bash'
			);
			$processIdParts = array_chunk($processIds, 10);

			foreach ($processIdParts as $processIds) {
				$commands[] = 'sudo kill -9 ' . implode(' ', $processIds);
			}

			$commands = array_merge($commands, array(
				'sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk \'{print $1}\')',
				'sudo ' . $this->nodeData['binary_files']['telinit'] . ' u'
			));
			$commandsFile = '/tmp/commands.sh';

			if (file_exists($commandsFile) === true) {
				unlink($commandsFile);
			}

			file_put_contents($commandsFile, implode("\n", $commands));
			chmod($commandsFile, 0755);
			shell_exec('cd /tmp/ && sudo ./' . basename($commandsFile));
			unlink($commandsFile);
			return;
		}

		public function process() {
			exec('ps -h -o pid -o cmd $(pgrep php) | grep "process.php node_system_recursive_dns_destination" | awk \'{print $1}\'', $nodeSystemRecursiveDnsDestinationProcessIds);
			$nodeSystemRecursiveDnsDestinationProcessIds = array_diff($nodeSystemRecursiveDnsDestinationProcessIds, array(
				getmypid()
			));

			if (empty($nodeSystemRecursiveDnsDestinationProcessIds) === false) {
				$this->_killProcessIds($nodeSystemRecursiveDnsDestinationProcessIds);
			}

			while (true) {
				shell_exec('sudo cp /usr/local/ghostcompute/resolv.conf /etc/resolv.conf');
				usleep(200000);
			}
		}

	}
?>
