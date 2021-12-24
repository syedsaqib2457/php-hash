<?php
	shell_exec('sudo apt-get update');
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get install -y libgtest-dev && cd /usr/src/gtest/ && sudo cmake . && sudo make');
	// todo: build libgtest-dev as required on debian/ubuntu
	// todo: verify successful dependency installation with package sources
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get install -y build-essential cmake pkg-config libssl-dev libzmq3-dev libunbound-dev libsodium-dev libunwind8-dev liblzma-dev libreadline6-dev libldns-dev libexpat1-dev libpgm-dev qttools5-dev-tools libhidapi-dev libusb-1.0-0-dev libprotobuf-dev protobuf-compiler libudev-dev libboost-chrono-dev libboost-date-time-dev libboost-filesystem-dev libboost-locale-dev libboost-program-options-dev libboost-regex-dev libboost-serialization-dev libboost-system-dev libboost-thread-dev ccache doxygen graphviz');
	// todo: download + compile monero from source with local ledger
?>
