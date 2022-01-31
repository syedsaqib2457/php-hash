<?php
	if (empty($parameters) === true) {
		exit;
	}

	if (file_exists('/usr/local/bin/bitcoin_cash/bitcoind') === false) {
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

		shell_exec('sudo mkdir /usr/local/bin/bitcoin_cash/');
		shell_exec('sudo mv /usr/local/bin/bitcoind /usr/local/bin/bitcoin-* /usr/local/bin/bitcoin_cash/');
		shell_exec('sudo mkdir /usr/local/nodecompute/bitcoin_cash/');
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

	$nodeProcessBitcoinCashCryptocurrencyBlockchainSettings['maximum_database_batch_size_bytes'] = ceil((16777216 * 8) * 1.05);
	$nodeProcessBitcoinCashCryptocurrencyBlockchainSettings['maximum_transaction_memory_pool_megabytes'] = ceil((($parameters['memory_capacity_bytes'] / 1024) / 1024) * 0.30);
	// todo: add 1 default block header row to system database
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
		'maxuploadtarget' => '1024',
		'mempoolexpiry' => '10',
		'minrelaytxfee' => '0.00000001',
		'persistmempool' => '0',
		'prune' => '11111',
		'rpcthreads' => '2',
		'timeout' => '10000',
		'whitelistrelay' => '0'
	);

	foreach ($nodeProcessBitcoinCashCryptocurrencyBlockchainParameters as $nodeProcessBitcoinCashCryptocurrencyBlockchainParameterKey => $nodeProcessBitcoinCashCryptocurrencyBlockchainParameter) {
		$nodeProcessBitcoinCashCryptocurrencyBlockchainParameters[$nodeProcessBitcoinCashCryptocurrencyBlockchainParameterKey] = '-' .  $nodeProcessBitcoinCashCryptocurrencyBlockchainParameterKey . '=' . $nodeProcessBitcoinCashCryptocurrencyBlockchainParameter;
	}

	$nodeProcessBitcoinCashCryptocurrencyBlockchainParameters = implode(' ', $nodeProcessBitcoinCashCryptocurrencyBlockchainParameters);
	shell_exec('sudo /usr/local/bin/bitcoin_cash/bitcoind ' . $nodeProcessBitcoinCashCryptocurrencyBlockchainParameters);
	// todo: make sure verificationprogress=1 before opening maxconnections and listening IP:port for P2P
	// todo: add default wallet info (scriptPubKey, address, etc) to database after initialblockdownload=false for sending block rewards to external addresses with the API
?>
