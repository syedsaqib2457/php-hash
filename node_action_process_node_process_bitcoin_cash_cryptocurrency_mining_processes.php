<?php
	_processNodeProcessBitcoinCashCryptocurrencyMiningProcesses($parameters, $response) {
		exec('ps -h -o pid -o cmd $(pgrep php) | grep process_node_process_bitcoin_cash_cryptocurrency_mining_processes | grep -v grep | awk \'{print $1"_"$4}\'', $nodeProcessBitcoinCashCryptocurrencyMiningProcesses);

		if (empty($nodeProcessBitcoinCashCryptocurrencyMiningProcesses[1]) === false) {
			$nodeProcessBitcoinCashCryptocurrencyMiningProcessIds = array();

			foreach ($nodeProcessBitcoinCashCryptocurrencyMiningProcesses as $nodeProcessBitcoinCashCryptocurrencyMiningProcess) {
				$nodeProcessBitcoinCashCryptocurrencyMiningProcess = explode('_', $nodeProcessBitcoinCashCryptocurrencyMiningProcess);

				if (($nodeProcessBitcoinCashCryptocurrencyMiningProcess[0] === $parameters['process_id']) === false) {
					$nodeProcessBitcoinCashCryptocurrencyMiningProcessIds[] = $nodeProcessBitcoinCashCryptocurrencyMiningProcess[0];
				}

				if (
					(empty($nodeProcessBitcoinCashCryptocurrencyMiningProcessInterval) === false) &&
					(($nodeProcessBitcoinCashCryptocurrencyMiningProcessInterval === $nodeProcessBitcoinCashCryptocurrencyMiningProcess[1]) === false) {
				) {
					_killProcessIds($parameters['binary_files'], $parameters['action'], $parameters['process_id'], $nodeProcessBitcoinCashCryptocurrencyMiningProcessIds);
					$response['message'] = 'Node process Bitcoin Cash cryptocurrency mining processes processed successfully.';
					return $response;
				}

				$nodeProcessBitcoinCashCryptocurrencyMiningProcessInterval = $nodeProcessBitcoinCashCryptocurrencyMiningProcess[1];
			}

			$response['message'] = 'Node process Bitcoin Cash cryptocurrency mining processes processed successfully.';
			return $response;
		}

		while (true) {
			$nodeProcessBitcoinCashCryptocurrencyMiningNextBlockHeight = file_get_contents('/usr/local/nodecompute/node_process_bitcoin_cash_cryptocurrency_mining_next_block_height.dat');

			if ($nodeProcessBitcoinCashCryptocurrencyMiningNextBlockHeight === false) {
				$response['message'] = 'Error listing node process Bitcoin Cash cryptocurrency mining block height, please try again.';
				return $response;
			}

			$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderProcessIds = false;
			exec('ps -h -o pid -o cmd $(pgrep php) | grep node_action_process_node_process_bitcoin_cash_cryptocurrency_mining_block_header.php | grep -v grep | grep -v _' . $nodeProcessBitcoinCashCryptocurrencyMiningNextBlockHeight . ' | awk \'{print $1}\'', $nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderProcessIds);

			if (empty($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderProcessIds) === false) {
				_killProcessIds($parameters['binary_files'], $parameters['action'], $parameters['process_id'], $nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderProcessIds);
			}

			exec('ps -h -o etimes -o pid -o cmd $(pgrep php) | grep node_action_process_node_process_bitcoin_cash_cryptocurrency_mining_block_header.php | grep -v grep | awk \'{print $1"_"$2}\'', $nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderProcesses);
			$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderProcessIds = array();

			foreach ($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderProcesses as $nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderProcess) {
				$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderProcess = explode('_', $nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderProcess);

				if (($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderProcess[0] > $nodeProcessBitcoinCashCryptocurrencyMiningProcessInterval) === true) {
					$nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderProcessIds[] = $nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderProcess[1];
				}
			}

			if (empty($nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderProcessIds) === false) {
				_killProcessIds($parameters['binary_files'], $parameters['action'], $parameters['process_id'], $nodeProcessBitcoinCashCryptocurrencyMiningBlockHeaderProcessIds);
			}

			sleep(1);
		}
	}

	if (($parameters['action'] === 'process_node_process_bitcoin_cash_cryptocurrency_mining_processes') === true) {
		_processNodeProcessBitcoinCashCryptocurrencyMiningProcesses($parameters, $response);
	}
?>
