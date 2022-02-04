<?php
	if (empty($parameters) === true) {
		exit;
	}

	if (file_exists('/usr/local/nodecompute/bitcoin_cash/bin/bitcoind') === false) {
		shell_exec('sudo apt-get update');
		shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install build-essential cmake libboost-all-dev libdb-dev libdb++-dev libevent-dev libssl-dev ninja-build python3');
		shell_exec('sudo rm -rf /usr/src/bitcoin_cash/');
		shell_exec('sudo mkdir -p /usr/src/bitcoin_cash/');
		shell_exec('cd /usr/src/bitcoin_cash/ && sudo ' . $parameters['binary_files']['wget'] . ' -O bitcoin_cash.tar.gz --no-dns-cache --timeout=60 https://github.com/bitcoin-cash-node/bitcoin-cash-node/archive/refs/tags/v24.0.0.tar.gz');
		shell_exec('cd /usr/src/bitcoin_cash/ && sudo tar -xvzf bitcoin_cash.tar.gz');
		shell_exec('cd /usr/src/bitcoin_cash/*/ && sudo mkdir build');
		shell_exec('cd /usr/src/bitcoin_cash/*/build/ && cmake -GNinja .. -DBUILD_BITCOIN_QT=OFF -DBUILD_BITCOIN_ZMQ=OFF -DENABLE_MAN=OFF -DENABLE_UPNP=OFF');
		shell_exec('cd /usr/src/bitcoin_cash/*/build/ && sudo ninja');
		shell_exec('cd /usr/src/bitcoin_cash/*/build/ && sudo ninja install');

		if (file_exists('/usr/local/bin/bitcoind') === false) {
			$response['message'] = 'Error processing node process Bitcoin Cash cryptocurrency blockchain, please try again.';
			return $response;
		}

		shell_exec('sudo mkdir -p /usr/local/nodecompute/bitcoin_cash/bin/');
		shell_exec('sudo mv /usr/local/bin/bitcoind /usr/local/bin/bitcoin-* /usr/local/nodecompute/bitcoin_cash/bin/');
		$nodeProcessBitcoinCashCryptocurrencyBlockchainSettings = array(
			'rpcbind=' . $nodeProcessCryptocurrencyBlockchain['ip_address'],
			'rpcpassword=nodecompute',
			'rpcport=' . $nodeProcessCryptocurrencyBlockchain['port_number'],
			'rpcuser=nodecompute'
		);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainSettings = implode("\n", $nodeProcessBitcoinCashCryptocurrencyBlockchainSettings);

		if (file_put_contents('/usr/local/nodecompute/bitcoin_cash/bitcoin.conf', $nodeProcessBitcoinCashCryptocurrencyBlockchainSettings) === false) {
			$response['message'] = 'Error adding node process Bitcoin Cash cryptocurrency blockchain settings, please try again.';
			return $response;
		}
	}

	$nodeProcessBitcoinCashCryptocurrencyBlockchainProcessIds = _listProcessIds('bitcoind', '/usr/local/nodecompute/bitcoin_cash/bin/bitcoind');

	if (empty($nodeProcessBitcoinCashCryptocurrencyBlockchainProcessIds) === false) {
		exec('sudo /usr/local/nodecompute/bitcoin_cash/bin/bitcoin-cli -conf=/usr/local/nodecompute/bitcoin_cash/bitcoin.conf getblockchaininfo 2>&1', $nodeProcessBitcoinCashCryptocurrencyBlockchainDetails);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainDetails = implode('', $nodeProcessBitcoinCashCryptocurrencyBlockchainDetails);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainDetails = json_decode($nodeProcessBitcoinCashCryptocurrencyBlockchainDetails, true);

		if (isset($nodeProcessBitcoinCashCryptocurrencyBlockchainDetails['initialblockdownload']) === false) {
			unset($nodeProcessBitcoinCashCryptocurrencyBlockchainDetails);
		}

		if (
			(isset($nodeProcessBitcoinCashCryptocurrencyBlockchainDetails['initialblockdownload']) === true) &&
			(($nodeProcessBitcoinCashCryptocurrencyBlockchainDetails['initialblockdownload'] === ($nodeProcessCryptocurrencyBlockchain['block_download_progress_percentage'] === '100')) === false)
		) {
			_killProcessIds($parameters['binary_files'], $parameters['action'], $parameters['process_id'], $nodeProcessBitcoinCashCryptocurrencyBlockchainProcessIds);
			unset($nodeProcessBitcoinCashCryptocurrencyBlockchainDetails);
		}
	}

	$nodeProcessBitcoinCashCryptocurrencyBlockchainSettings['maximum_database_batch_size_bytes'] = ceil((16777216 * 8) * 1.05);
	$nodeProcessBitcoinCashCryptocurrencyBlockchainSettings['maximum_transaction_memory_pool_megabytes'] = ceil((($parameters['memory_capacity_bytes'] / 1024) / 1024) * 0.30);
	$nodeProcessBitcoinCashCryptocurrencyBlockchainParameters = array(
		'blockmintxfee' => '0.00000001',
		'daemon' => '1',
		'datacarriersize' => '1000000',
		'datadir' => '/usr/local/nodecompute/bitcoin_cash/',
		'dbbatchsize' => $nodeProcessBitcoinCashCryptocurrencyBlockchainSettings['maximum_database_batch_size_bytes'],
		'dbcache' => '10',
		'keypool' => '1',
		'listen' => '0',
		'maxconnections' => '8',
		'maxmempool' => $nodeProcessBitcoinCashCryptocurrencyBlockchainSettings['maximum_transaction_memory_pool_megabytes'],
		'maxorphantx' => '1',
		'maxreceivebuffer' => '250',
		'maxsendbuffer' => '250',
		'maxtimeadjustment' => '10000',
		'maxuploadtarget' => $nodeProcessCryptocurrencyBlockchain['daily_sent_traffic_maximum_megabytes'],
		'mempoolexpiry' => '10',
		'minrelaytxfee' => '0.00000001',
		'persistmempool' => '0',
		'rpcthreads' => '4',
		'timeout' => '2000',
		'whitelistrelay' => '0'
	);

	if (empty($nodeProcessCryptocurrencyBlockchain['simultaneous_received_connection_maximum_count']) === false) {
		$nodeProcessBitcoinCashCryptocurrencyBlockchainParameters['maxconnections'] = min(8, $nodeProcessCryptocurrencyBlockchain['simultaneous_received_connection_maximum_count']);
	}

	if (
		(($nodeProcessCryptocurrencyBlockchain['block_download_progress_percentage'] === '100') === true) ||
		(
			(isset($nodeProcessBitcoinCashCryptocurrencyBlockchainDetails['initialblockdownload']) === true) &&
			($nodeProcessBitcoinCashCryptocurrencyBlockchainDetails['initialblockdownload'] === false)
		)
	) {
		$nodeProcessBitcoinCashCryptocurrencyBlockchainParameters['listen'] = $nodeProcessBitcoinCashCryptocurrencyBlockchainParameters['whitelistrelay'] = '1';

		if (empty($nodeProcessCryptocurrencyBlockchain['simultaneous_sent_connection_maximum_count']) === false) {
			$nodeProcessBitcoinCashCryptocurrencyBlockchainParameters['maxconnections'] += min(8, $nodeProcessCryptocurrencyBlockchain['simultaneous_sent_connection_maximum_count']);
		}
	}

	if (empty($nodeProcessCryptocurrencyBlockchain['socks_proxy_destination_address']) === false) {
		$nodeProcessBitcoinCashCryptocurrencyBlockchainParameters['proxy'] = $nodeProcessCryptocurrencyBlockchain['socks_proxy_destination_address'];
	}

	if (empty($nodeProcessCryptocurrencyBlockchain['storage_usage_maximum_megabytes']) === false) {
		$nodeProcessBitcoinCashCryptocurrencyBlockchainParameters['prune'] = max(1000, $nodeProcessCryptocurrencyBlockchain['storage_usage_maximum_megabytes']);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainParameters['prune'] = ceil($nodeProcessBitcoinCashCryptocurrencyBlockchainParameters['prune'] * 0.96);
	}

	foreach ($nodeProcessBitcoinCashCryptocurrencyBlockchainParameters as $nodeProcessBitcoinCashCryptocurrencyBlockchainParameterKey => $nodeProcessBitcoinCashCryptocurrencyBlockchainParameter) {
		$nodeProcessBitcoinCashCryptocurrencyBlockchainParameters[$nodeProcessBitcoinCashCryptocurrencyBlockchainParameterKey] = '-' .  $nodeProcessBitcoinCashCryptocurrencyBlockchainParameterKey . '=' . $nodeProcessBitcoinCashCryptocurrencyBlockchainParameter;
	}

	$nodeProcessBitcoinCashCryptocurrencyBlockchainParameters = implode(' ', $nodeProcessBitcoinCashCryptocurrencyBlockchainParameters);
	shell_exec('sudo /usr/local/nodecompute/bitcoin_cash/bin/bitcoind ' . $nodeProcessBitcoinCashCryptocurrencyBlockchainParameters);

	if (isset($nodeProcessBitcoinCashCryptocurrencyBlockchainDetails) === false) {
		exec('sudo /usr/local/nodecompute/bitcoin_cash/bin/bitcoin-cli -conf=/usr/local/nodecompute/bitcoin_cash/bitcoin.conf getblockchaininfo 2>&1', $nodeProcessBitcoinCashCryptocurrencyBlockchainDetails);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainDetails = implode('', $nodeProcessBitcoinCashCryptocurrencyBlockchainDetails);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainDetails = json_decode($nodeProcessBitcoinCashCryptocurrencyBlockchainDetails, true);
	}

	if (
		(isset($nodeProcessBitcoinCashCryptocurrencyBlockchainDetails['initialblockdownload']) === true) &&
		($nodeProcessBitcoinCashCryptocurrencyBlockchainDetails['initialblockdownload'] === false)
	) {
		$nodeProcessCryptocurrencyBlockchain['block_download_progress_percentage'] = '100';
		exec('sudo /usr/local/nodecompute/bitcoin_cash/bin/bitcoin-cli -conf=/usr/local/nodecompute/bitcoin_cash/bitcoin.conf getaddressesbylabel "" 2>&1', $nodeProcessBitcoinCashCryptocurrencyBlockchainWallets);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainWallets = implode('', $nodeProcessBitcoinCashCryptocurrencyBlockchainWallets);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainWallets = json_decode($nodeProcessBitcoinCashCryptocurrencyBlockchainWallets, true);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainWalletPublicKeyHash = key($nodeProcessBitcoinCashCryptocurrencyBlockchainWallets);
		exec('sudo /usr/local/nodecompute/bitcoin_cash/bin/bitcoin-cli -conf=/usr/local/nodecompute/bitcoin_cash/bitcoin.conf getaddressinfo "' . $nodeProcessBitcoinCashCryptocurrencyBlockchainWalletPublicKeyHash . '" 2>&1', $nodeProcessBitcoinCashCryptocurrencyBlockchainWallet);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainWallet = implode('', $nodeProcessBitcoinCashCryptocurrencyBlockchainWallet);
		$nodeProcessBitcoinCashCryptocurrencyBlockchainWallet = json_decode($nodeProcessBitcoinCashCryptocurrencyBlockchainWallet, true);
		$systemActionAddNodeProcessCryptocurrencyBlockchainWalletParameters = array(
			'action' => 'add_node_process_cryptocurrency_blockchain_wallet',
			'data' => array(
				'node_process_type' => 'bitcoin_cash_cryptocurrency_blockchain',
				'public_key' => $nodeProcessBitcoinCashCryptocurrencyBlockchainWallet['pubkey'],
				'public_key_hash' => $nodeProcessBitcoinCashCryptocurrencyBlockchainWallet['address'],
				'public_key_script' => $nodeProcessBitcoinCashCryptocurrencyBlockchainWallet['scriptPubKey']
			),
			'node_authentication_token' => $parameters['node_authentication_token']
		);
		$systemActionAddNodeProcessCryptocurrencyBlockchainWalletParameters = json_encode($systemActionAddNodeProcessCryptocurrencyBlockchainWalletParameters);

		if ($systemActionAddNodeProcessCryptocurrencyBlockchainWalletParameters === false) {
			$response['message'] = 'Error adding node process Bitcoin Cash cryptocurrency blockchain wallet, please try again.';
			return $response;
		}

		shell_exec('sudo ' . $parameters['binary_files']['wget'] . ' -O /usr/local/nodecompute/system_action_add_node_process_bitcoin_cash_cryptocurrency_blockchain_wallet_response.json --no-dns-cache --post-data \'json=' . $systemActionAddNodeProcessCryptocurrencyBlockchainWalletParameters . '\' --timeout=10 ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php');

		if (file_exists('/usr/local/nodecompute/system_action_add_node_process_bitcoin_cash_cryptocurrency_blockchain_wallet_response.json') === false) {
			$response['message'] = 'Error adding node process Bitcoin Cash cryptocurrency blockchain wallet, please try again.';
			return $response;
		}

		$systemActionAddNodeProcessCryptocurrencyBlockchainWalletResponse = file_get_contents('/usr/local/nodecompute/system_action_add_node_process_bitcoin_cash_cryptocurrency_blockchain_wallet_response.json');
		$systemActionAddNodeProcessCryptocurrencyBlockchainWalletResponse = json_decode($systemActionAddNodeProcessCryptocurrencyBlockchainWalletResponse, true);

		if ($systemActionAddNodeProcessCryptocurrencyBlockchainWalletResponse === false) {
			$response['message'] = 'Error adding node process Bitcoin Cash cryptocurrency blockchain wallet, please try again.';
			return $response;
		}

		if (($systemActionAddNodeProcessCryptocurrencyBlockchainWalletResponse['valid_status'] === '0') === true) {
			$response['message'] = $systemActionAddNodeProcessCryptocurrencyBlockchainWalletResponse['message'];
			return $response;
		}
	} elseif (isset($nodeProcessBitcoinCashCryptocurrencyBlockchainDetails['verificationprogress']) === true) {
		$nodeProcessCryptocurrencyBlockchain['block_download_progress_percentage'] = floor($nodeProcessBitcoinCashCryptocurrencyBlockchainDetails['verificationprogress'] * 100);
	}

	$systemActionEditNodeProcessCryptocurrencyBlockchainParameters = array(
		'action' => 'edit_node_process_cryptocurrency_blockchain',
		'data' => array(
			'block_download_progress_percentage' => $nodeProcessCryptocurrencyBlockchain['block_download_progress_percentage']
		),
		'node_authentication_token' => $parameters['node_authentication_token'],
		'where' => array(
			'node_process_type' => 'bitcoin_cash_cryptocurrency_blockchain'
		)
	);
	$systemActionEditNodeProcessCryptocurrencyBlockchainParameters = json_encode($systemActionEditNodeProcessCryptocurrencyBlockchainParameters);

	if ($systemActionEditNodeProcessCryptocurrencyBlockchainParameters === false) {
		$response['message'] = 'Error editing node process Bitcoin Cash cryptocurrency blockchain, please try again.';
		return $response;
	}

	shell_exec('sudo ' . $parameters['binary_files']['wget'] . ' -O /usr/local/nodecompute/system_action_edit_node_process_bitcoin_cash_cryptocurrency_blockchain_response.json --no-dns-cache --post-data \'json=' . $systemActionEditNodeProcessCryptocurrencyBlockchainParameters . '\' --timeout=10 ' . $parameters['system_endpoint_destination_address'] . '/system_endpoint.php');

	if (file_exists('/usr/local/nodecompute/system_action_edit_node_process_bitcoin_cash_cryptocurrency_blockchain_response.json') === false) {
		$response['message'] = 'Error editing node process Bitcoin Cash cryptocurrency blockchain, please try again.';
		return $response;
	}

	$systemActionEditNodeProcessCryptocurrencyBlockchainResponse = file_get_contents('/usr/local/nodecompute/system_action_edit_node_process_bitcoin_cash_cryptocurrency_blockchain_response.json');
	$systemActionEditNodeProcessCryptocurrencyBlockchainResponse = json_decode($systemActionEditNodeProcessCryptocurrencyBlockchainResponse, true);

	if ($systemActionEditNodeProcessCryptocurrencyBlockchainResponse === false) {
		$response['message'] = 'Error editing node process Bitcoin Cash cryptocurrency blockchain, please try again.';
		return $response;
	}

	if (($systemActionEditNodeProcessCryptocurrencyBlockchainResponse['valid_status'] === '0') === true) {
		$response['message'] = $systemActionEditNodeProcessCryptocurrencyBlockchainResponse['message'];
		return $response;
	}
?>
