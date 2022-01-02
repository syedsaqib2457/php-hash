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
	// todo: add full path to Bitcoin daemon
	// todo: dynamic parameter percentage should be based on framework system value in case other node processes / cryptocurrencies are used on the same node
	// todo: par=<n> should be (((total number of cores) - 1) * percentage of resources value to use for mining)
	$maximumConnections = ceil((($parameters['memory_capacity_bytes'] / 1024) / 1024) / 50);
	$maximumTransactionMemoryPoolMegabytes = ceil(($parameters['memory_capacity_bytes'] * 0.30);
	shell_exec('sudo bitcoind -blockmaxweight=100000000 -blockmintxfee=0.0000000001 -daemon -datacarriersize=1000000 -dbcache=10 -keypool=1 -maxconnection=' . $maximumConnections . ' -maxmempool=' . $maximumTransactionMemoryPoolMegabytes . ' -maxorphantx=1 -maxreceivebuffer=250 -maxsendbuffer=250 -maxtimeadjustment=10000 -maxuploadtarget=1024 -mempoolexpiry=10 -minrelaytxfee=0.0000000001 -persistmempool=0 -timeout=10000');
	// todo: try -blocksonly=1 and -blocksonly since default value is 0 but manpage doesn't have blocksonly=<value>
	// todo: compile with defaults, add configure options for low memory usage
?>
