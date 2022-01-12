<?php
	// todo: pruned local installation to verify mining functionality
	shell_exec('sudo apt-get update');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install build-essential libtool autotools-dev automake pkg-config bsdmainutils python3');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install libevent-dev libboost-dev libboost-system-dev libboost-filesystem-dev libboost-test-dev');
	shell_exec('sudo rm -rf /usr/src/dogecoin/');
	shell_exec('sudo mkdir -p /usr/src/dogecoin/');
	shell_exec('cd /usr/src/dogecoin/ && sudo ' . $parameters['binary_files']['wget'] . ' -O dogecoin.tar.gz --no-dns-cache --timeout=60 https://github.com/dogecoin/dogecoin/archive/refs/tags/v22.0.tar.gz');
	shell_exec('cd /usr/src/dogecoin/ && sudo tar -xvzf dogecoin.tar.gz');
	shell_exec('cd /usr/src/dogecoin/*/ && sudo ./autogen.sh');
	shell_exec('cd /usr/src/dogecoin/*/ && sudo ./configure --disable-bench --disable-hardening --disable-miniupnpc --disable-natpnp --disable-tests --disable-util-wallet --with-utils --without-bdb --without-gui --without-qrencode --without-sqlite');
	shell_exec('cd /usr/src/dogecoin/*/ && sudo make');
	shell_exec('cd /usr/src/dogecoin/*/ && sudo make install');
	shell_exec('sudo mkdir /usr/local/ghostcompute/dogecoin/');
	// todo: dynamic parameter percentage should be based on framework system value in case other node processes / cryptocurrencies are used on the same node
	// todo: par=<n> should be (((total number of cores) - 1) * percentage of resources value to use for mining)
	$dogecoinSettings = array(
		'rpcbind=' . $parameters['data']['next']['node_process_cryptocurrency_destinations']['dogecoin_cryptocurrency']['ip_address'],
		'rpcuser=ghostcompute',
		'rpcpassword=ghostcompute',
		'rpcport=' . current($parameters['data']['next']['node_processes']['dogecoin_cryptocurrency'][0])
	);
	$dogecoinSettings = implode("\n", $dogecoinSettings);

	if (file_put_contents('/usr/local/ghostcompute/dogecoin/dogecoin.conf', $dogecoinSettings) === false) {
		$response['message'] = 'Error adding Dogecoin settings, please try again.';
		return $response;
	}

	$maximumDatabaseBatchSize = (16777216 * 8);
	$maximumTransactionMemoryPoolMegabytes = ceil((($parameters['memory_capacity_bytes'] / 1024) / 1024) * 0.30);
	// todo: add binary full paths
	shell_exec('sudo dogecoind -blockmaxweight=100000000 -blockmintxfee=0.00000001 -daemon=1 -datacarriersize=1000000 -datadir=/usr/local/ghostcompute/dogecoin/ -dbbatchsize=' . $maximumDatabaseBatchSize . ' -dbcache=10 -keypool=1 -listen=0 -maxconnections=8 -maxmempool=' . $maximumTransactionMemoryPoolMegabytes . ' -maxorphantx=1 -maxreceivebuffer=250 -maxsendbuffer=250 -maxtimeadjustment=10000 -maxuploadtarget=1024 -mempoolexpiry=10 -minrelaytxfee=0.00000001 -persistmempool=0 -rpcthreads=2 -timeout=10000 -whitelistrelay=0');
	exec('sudo dogecoin-cli -rpcuser=ghostcompute -rpcpassword=ghostcompute getblockchaininfo 2>&1', $dogecoinDetails);
	$dogecoinDetails = implode('', $dogecoinDetails);
	$dogecoinDetails = json_decode($dogecoinDetails, true);

	if (isset($dogecoinDetails['chain']) === false) {
		$response['message'] = 'Error listing Dogecoin details, please try again.';
		return $response;
	}

	// todo: make sure verificationprogress=1 before opening maxconnections and listening IP:port for P2P
?>
