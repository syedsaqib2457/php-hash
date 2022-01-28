<?php
	if (empty($parameters) === true) {
		exit;
	}

	// todo: pruned local installation to verify mining functionality
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
	shell_exec('sudo mkdir /usr/local/nodecompute/bitcoin_cash/');
	$bitcoinCashSettings = array(
		'rpcbind=' . $parameters['data']['next']['node_process_cryptocurrency_destinations']['bitcoin_cash_cryptocurrency']['ip_address'],
		'rpcpassword=nodecompute',
		'rpcport=' . current($parameters['data']['next']['node_processes']['bitcoin_cash_cryptocurrency'][0]),
		'rpcuser=nodecompute'
	);
	$bitcoinCashSettings = implode("\n", $bitcoinCashSettings);

	if (file_put_contents('/usr/local/nodecompute/bitcoin_cash/bitcoin.conf', $bitcoinCashSettings) === false) {
		$response['message'] = 'Error adding Bitcoin Cash settings, please try again.';
		return $response;
	}

	$maximumDatabaseBatchSize = (16777216 * 8);
	$maximumTransactionMemoryPoolMegabytes = ceil((($parameters['memory_capacity_bytes'] / 1024) / 1024) * 0.30);
	// todo: add binary full paths
	// todo: add 1 default block header row to system database
	shell_exec('sudo bitcoind -blockmintxfee=0.00000001 -daemon=1 -datacarriersize=1000000 -datadir=/usr/local/nodecompute/bitcoin_cash/ -dbbatchsize=' . $maximumDatabaseBatchSize . ' -dbcache=10 -keypool=1 -listen=0 -maxconnections=8 -maxmempool=' . $maximumTransactionMemoryPoolMegabytes . ' -maxorphantx=1 -maxreceivebuffer=250 -maxsendbuffer=250 -maxtimeadjustment=10000 -maxuploadtarget=1024 -mempoolexpiry=10 -minrelaytxfee=0.00000001 -persistmempool=0 -prune=11111 -rpcthreads=2 -timeout=10000 -whitelistrelay=0');
	exec('sudo bitcoin-cli -conf=/usr/local/nodecompute/bitcoin_cash/bitcoin.conf getblockchaininfo 2>&1', $bitcoinCashDetails);
	$bitcoinCashDetails = implode('', $bitcoinCashDetails);
	$bitcoinCashDetails = json_decode($bitcoinCashDetails, true);

	if (isset($bitcoinCashDetails['chain']) === false) {
		$response['message'] = 'Error listing Bitcoin Cash details, please try again.';
		return $response;
	}

	// todo: make sure verificationprogress=1 before opening maxconnections and listening IP:port for P2P
?>
