<?php
	// for debugging
	// sudo apt-get update && sudo apt-get -y install php wget && sudo wget -O deploy_monero.php https://raw.githubusercontent.com/ghostcompute/framework/main/node_action_deploy_monero_cryptocurrency_node_process.php?$RANDOM && sudo php deploy_monero.php

	// todo: verify successful dependency installation with package sources, delete duplicates
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install build-essential cmake pkg-config libssl-dev libzmq3-dev libunbound-dev libsodium-dev libunwind8-dev liblzma-dev libreadline6-dev libldns-dev libexpat1-dev libpgm-dev qttools5-dev-tools libhidapi-dev libusb-1.0-0-dev libprotobuf-dev protobuf-compiler libudev-dev libboost-chrono-dev libboost-date-time-dev libboost-filesystem-dev libboost-locale-dev libboost-program-options-dev libboost-regex-dev libboost-serialization-dev libboost-system-dev libboost-thread-dev ccache doxygen graphviz');
	shell_exec('sudo rm -rf /usr/src/monero/ && sudo mkdir -p /usr/src/monero/');
	shell_exec('cd /usr/src/monero/ && sudo ' . $binaryFiles['wget'] . ' -O monero.tar.gz --no-dns-cache --timeout=60 https://github.com/monero-project/monero/archive/refs/tags/v0.17.3.0.tar.gz');
	shell_exec('cd /usr/src/monero/ && sudo tar -xvzf monero.tar.gz');
	// todo: download + compile monero from source without git CLI + submodules
	// todo: add submodules if RPC fails
	shell_exec('cd /usr/src/monero/*/external/supercop && sudo ' . $binaryFiles['wget'] . ' -O supercop.tar.gz --no-dns-cache --timeout=60 https://github.com/monero-project/supercop/archive/refs/heads/monero.tar.gz');
	shell_exec('cd /usr/src/monero/*/external/supercop && sudo tar -xvzf supercop.tar.gz');
	shell_exec('sudo mv /usr/src/monero/*/external/supercop/*/* /usr/src/monero/*/external/supercop/');
	// todo: try reserve_size 1 when listing block header details since it defaults to 1 in the source code
	shell_exec('cd /usr/src/monero/*/ && sudo make');
?>
