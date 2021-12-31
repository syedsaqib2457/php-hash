<?php
	shell_exec('sudo apt-get install build-essential libtool autotools-dev automake pkg-config bsdmainutils python3');
	shell_exec('sudo apt-get install libevent-dev libboost-dev libboost-system-dev libboost-filesystem-dev libboost-test-dev');
	shell_exec('sudo rm -rf /usr/src/bitcoin/ && sudo mkdir -p /usr/src/bitcoin/');
	shell_exec('cd /usr/src/bitcoin/ && sudo ' . $binaryFiles['wget'] . ' -O bitcoin.tar.gz --no-dns-cache --timeout=60 https://github.com/bitcoin/bitcoin/archive/refs/tags/v22.0.tar.gz');
	shell_exec('cd /usr/src/bitcoin/ && sudo tar -xvzf bitcoin.tar.gz');
	// todo
?>
