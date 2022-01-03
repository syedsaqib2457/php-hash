<?php
	shell_exec('sudo apt-get update');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install build-essential libtool autotools-dev automake pkg-config bsdmainutils python3');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install libevent-dev libboost-dev libboost-system-dev libboost-filesystem-dev libboost-test-dev');
	shell_exec('sudo rm -rf /usr/src/bitcoin/');
	shell_exec('sudo mkdir -p /usr/src/bitcoin/');
	shell_exec('cd /usr/src/bitcoin/ && sudo ' . $binaryFiles['wget'] . ' -O bitcoin.tar.gz --no-dns-cache --timeout=60 https://github.com/bitcoin/bitcoin/archive/refs/tags/v22.0.tar.gz');
	shell_exec('cd /usr/src/bitcoin/ && sudo tar -xvzf bitcoin.tar.gz');
	shell_exec('cd /usr/src/bitcoin/*/ && sudo ./autogen.sh');
	shell_exec('cd /usr/src/bitcoin/*/ && sudo ./configure --disable-bench --disable-hardening --disable-miniupnpc --disable-natpnp --disable-tests --disable-util-wallet --with-utils --without-bdb --without-gui --without-qrencode --without-sqlite');
	shell_exec('cd /usr/src/bitcoin/*/ && sudo make');
	shell_exec('cd /usr/src/bitcoin/*/ && sudo make install');
	shell_exec('sudo mkdir /usr/local/ghostcompute/bitcoin/');
	// todo: add full path to Bitcoin daemon
	// todo: dynamic parameter percentage should be based on framework system value in case other node processes / cryptocurrencies are used on the same node
	// todo: par=<n> should be (((total number of cores) - 1) * percentage of resources value to use for mining)
	$maximumDatabaseBatchSize = ((16777216 * 8) + 1000);
	// todo: restart daemon after IBD with listening + $maximumConnections = ceil((($parameters['memory_capacity_bytes'] / 1024) / 1024) / 50);
	$maximumTransactionMemoryPoolMegabytes = ceil($parameters['memory_capacity_bytes'] * 0.30);
	shell_exec('sudo bitcoind -blockmaxweight=100000000 -blockmintxfee=0.00000001 -daemon=1 -datacarriersize=1000000 -datadir=/usr/local/ghostcompute/bitcoin/ -dbbatchsize=' . $maximumDatabaseBatchSize . ' -dbcache=10 -keypool=1 -listen=0 -maxconnections=8 -maxmempool=' . $maximumTransactionMemoryPoolMegabytes . ' -maxorphantx=1 -maxreceivebuffer=250 -maxsendbuffer=250 -maxtimeadjustment=10000 -maxuploadtarget=1024 -mempoolexpiry=10 -minrelaytxfee=0.00000001 -persistmempool=0 -timeout=10000 -whitelistrelay=0');
	$bitcoinSettings = array(
		'rpcbind=' . $parameters['data']['next']['node_process_cryptocurrency_destinations']['bitcoin_cryptocurrency']['ip_address'],
		'rpcuser=ghostcompute',
		'rpcpassword=ghostcompute',
		'rpcport=' . current($parameters['data']['next']['node_processes']['bitcoin_cryptocurrency'][0])
	);
	$bitcoinSettings = implode("\n", $bitcoinSettings);

	if (file_put_contents('/usr/local/ghostcompute/bitcoin/bitcoin.conf', $bitcoinSettings) === false) {
		$response['message'] = 'Error adding Bitcoin settings, please try again.';
		return $response;
	}

	// todo: verify CLI function with bitcoin.conf (add user + pass parameters from bitcoin.conf)
?>
