<?php
	// for debugging
	// sudo apt-get update && sudo apt-get -y install php wget && sudo wget -O deploy_monero.php https://raw.githubusercontent.com/ghostcompute/framework/main/node_action_deploy_monero_cryptocurrency_node_process.php?$RANDOM && sudo php deploy_monero.php
	// todo: verify successful dependency installation with package sources, delete duplicates
	shell_exec('sudo DEBIAN_FRONTEND=noninteractive apt-get -y install build-essential cmake pkg-config libssl-dev libzmq3-dev libunbound-dev libsodium-dev libunwind8-dev liblzma-dev libreadline6-dev libldns-dev libexpat1-dev libpgm-dev qttools5-dev-tools libhidapi-dev libusb-1.0-0-dev libprotobuf-dev protobuf-compiler libudev-dev libboost-chrono-dev libboost-date-time-dev libboost-filesystem-dev libboost-locale-dev libboost-program-options-dev libboost-regex-dev libboost-serialization-dev libboost-system-dev libboost-thread-dev ccache doxygen graphviz');
	shell_exec('sudo rm -rf /usr/src/monero/ && sudo mkdir -p /usr/src/monero/');
	shell_exec('cd /usr/src/monero/ && sudo ' . $binaryFiles['wget'] . ' -O monero.tar.gz --no-dns-cache --timeout=60 https://github.com/monero-project/monero/archive/refs/tags/v0.17.3.0.tar.gz');
	shell_exec('cd /usr/src/monero/ && sudo tar -xvzf monero.tar.gz');
	// todo: add submodules without git CLI
	$moneroSubmodules = array(
		'miniupnp' => 'https://github.com/miniupnp/miniupnp/archive/refs/tags/miniupnpc_2_1.tar.gz',
		'randomx' => 'https://github.com/tevador/RandomX/archive/refs/tags/v1.1.10.tar.gz',
		'rapidjson' => 'https://github.com/Tencent/rapidjson/archive/refs/tags/v1.1.0.tar.gz',
		'supercop' => 'https://github.com/monero-project/supercop/archive/refs/heads/monero.tar.gz',
		'trezor-common' => 'https://github.com/trezor/trezor-common/archive/refs/heads/master.tar.gz'
	);

	foreach ($moneroSubmodules as $moneroSubmoduleName => $moneroSubmoduleDestinationAddress) {
		shell_exec('cd /usr/src/monero/*/external/' . $moneroSubmoduleName . '/ && sudo ' . $binaryFiles['wget'] . ' -O ' . $moneroSubmoduleName . '.tar.gz --no-dns-cache --timeout=60 ' . $moneroSubmoduleDestinationAddress);
		shell_exec('cd /usr/src/monero/*/external/' . $moneroSubmoduleName . '/ && sudo tar -xvzf ' . $moneroSubmoduleName . '.tar.gz');
		shell_exec('sudo mv /usr/src/monero/*/external/' . $moneroSubmoduleName . '/*/* /usr/src/monero/*/external/' . $moneroSubmoduleName . '/*/.* /usr/src/monero/*/external/' . $moneroSubmoduleName . '/');
	}

	// todo: try reserve_size 1 when listing block header details since it defaults to 1 in the source code
	shell_exec('cd /usr/src/monero/*/ && sudo make');
?>
