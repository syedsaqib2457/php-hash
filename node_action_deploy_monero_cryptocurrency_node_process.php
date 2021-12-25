<?php
	shell_exec('sudo apt-get update');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install libgtest-dev');
	shell_exec('cd /usr/src/gtest/ && sudo cmake . && sudo make');
	shell_exec('sudo mv /usr/src/gtest/libg* /usr/lib/');
	shell_exec('sudo mv /usr/src/gtest/lib/libg* /usr/lib/');
	// todo: build libgtest-dev as required on debian/ubuntu
	// todo: verify successful dependency installation with package sources, delete duplicates
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install build-essential cmake pkg-config libssl-dev libzmq3-dev libunbound-dev libsodium-dev libunwind8-dev liblzma-dev libreadline6-dev libldns-dev libexpat1-dev libpgm-dev qttools5-dev-tools libhidapi-dev libusb-1.0-0-dev libprotobuf-dev protobuf-compiler libudev-dev libboost-chrono-dev libboost-date-time-dev libboost-filesystem-dev libboost-locale-dev libboost-program-options-dev libboost-regex-dev libboost-serialization-dev libboost-system-dev libboost-thread-dev ccache doxygen graphviz');
	shell_exec('sudo rm -rf /usr/src/monero/ && sudo mkdir -p /usr/src/monero/');
	shell_exec('cd /usr/src/monero/ && sudo ' . $binaryFiles['wget'] . ' -O monero.tar.gz --no-dns-cache --timeout=60 https://github.com/monero-project/monero/archive/refs/tags/v0.17.3.0.tar.gz');
	// todo: download + compile monero from source without git CLI + submodules
	// todo: add submodules if RPC fails
	shell_exec('cd /usr/src/monero/*/ && sudo make');
?>
