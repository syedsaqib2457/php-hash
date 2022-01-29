<?php
	if (empty($parameters) === true) {
		exit;
	}

	// todo: use timeout command with crontab instead of managing process timeout based on passed parameter

	_processNodeProcessBitcoinCashCryptocurrencyProcesses($parameters, $response) {
		exec('ps -h -o pid -o cmd $(pgrep php) | grep process_node_process_bitcoin_cash_cryptocurrency_processes | grep -v grep | awk \'{print $1"_"$4}\'', $nodeProcessBitcoinCashCryptocurrencyProcesses);

		if (empty($nodeProcessBitcoinCashCryptocurrencyProcesses[1]) === false) {
			$nodeProcessBitcoinCashCryptocurrencyProcessIds = array();

			foreach ($nodeProcessBitcoinCashCryptocurrencyProcesses as $nodeProcessBitcoinCashCryptocurrencyProcess) {
				$nodeProcessBitcoinCashCryptocurrencyProcess = explode('_', $nodeProcessBitcoinCashCryptocurrencyProcess);

				if (($nodeProcessBitcoinCashCryptocurrencyProcess[0] === $parameters['process_id']) === false) {
					$nodeProcessBitcoinCashCryptocurrencyProcessIds[] = $nodeProcessBitcoinCashCryptocurrencyProcess[0];
				}

				if (
					(empty($nodeProcessBitcoinCashCryptocurrencyProcessInterval) === false) &&
					(($nodeProcessBitcoinCashCryptocurrencyProcessInterval === $nodeProcessBitcoinCashCryptocurrencyProcess[1]) === false) {
				) {
					_killProcessIds($parameters['binary_files'], $parameters['action'], $parameters['process_id'], $nodeProcessBitcoinCashCryptocurrencyProcessIds);
					$response['message'] = 'Node process Bitcoin Cash cryptocurrency processes processed successfully.';
					return $response;
				}

				$nodeProcessBitcoinCashCryptocurrencyProcessInterval = $nodeProcessBitcoinCashCryptocurrencyProcess[1];
			}

			$response['message'] = 'Node process Bitcoin Cash cryptocurrency processes processed successfully.';
			return $response;
		}

		while (true) {
			$nodeProcessBitcoinCashCryptocurrencyNextBlockHeight = file_get_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_next_block_height.dat');

			if ($nodeProcessBitcoinCashCryptocurrencyNextBlockHeight === false) {
				$response['message'] = 'Error listing node process Bitcoin Cash cryptocurrency block height, please try again.';
				return $response;
			}

			$nodeProcessBitcoinCashCryptocurrencyBlockHeaderProcessIds = false;
			exec('ps -h -o pid -o cmd $(pgrep php) | grep node_action_process_node_process_bitcoin_cash_cryptocurrency_block_header.php | grep -v grep | grep -v _' . $nodeProcessBitcoinCashCryptocurrencyNextBlockHeight . ' | awk \'{print $1}\'', $nodeProcessBitcoinCashCryptocurrencyBlockHeaderProcessIds);

			if (empty($nodeProcessBitcoinCashCryptocurrencyBlockHeaderProcessIds) === false) {
				_killProcessIds($parameters['binary_files'], $parameters['action'], $parameters['process_id'], $nodeProcessBitcoinCashCryptocurrencyBlockHeaderProcessIds);
			}

			exec('ps -h -o etimes -o pid -o cmd $(pgrep php) | grep node_action_process_node_process_bitcoin_cash_cryptocurrency_block_header.php | grep -v grep | awk \'{print $1"_"$2}\'', $nodeProcessBitcoinCashCryptocurrencyBlockHeaderProcesses);
			$nodeProcessBitcoinCashCryptocurrencyBlockHeaderProcessIds = array();

			foreach ($nodeProcessBitcoinCashCryptocurrencyBlockHeaderProcesses as $nodeProcessBitcoinCashCryptocurrencyBlockHeaderProcess) {
				$nodeProcessBitcoinCashCryptocurrencyBlockHeaderProcess = explode('_', $nodeProcessBitcoinCashCryptocurrencyBlockHeaderProcess);

				if (($nodeProcessBitcoinCashCryptocurrencyBlockHeaderProcess[0] > $nodeProcessBitcoinCashCryptocurrencyProcessInterval) === true) {
					$nodeProcessBitcoinCashCryptocurrencyBlockHeaderProcessIds[] = $nodeProcessBitcoinCashCryptocurrencyBlockHeaderProcess[1];
				}
			}

			if (empty($nodeProcessBitcoinCashCryptocurrencyBlockHeaderProcessIds) === false) {
				_killProcessIds($parameters['binary_files'], $parameters['action'], $parameters['process_id'], $nodeProcessBitcoinCashCryptocurrencyBlockHeaderProcessIds);
			}

			sleep(1);
		}
	}

	if (($parameters['action'] === 'process_node_process_bitcoin_cash_cryptocurrency_processes') === true) {
		_processNodeProcessBitcoinCashCryptocurrencyProcesses($parameters, $response);
	}
?>
