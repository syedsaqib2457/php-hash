## Free Automation for Cloud Infrastructure Security

Note: It's recommended to wait until the first official release of version 19 when process auto-scaling, IPv6, proxy request logging, public-facing DNS, reverse proxies and system monitoring features are completed. It will require new installations and server deployments if upgrading from version 18 to version 19, but there will be automated update scripts available for version 19+.

**GhostCompute** is a free, open-source cloud automation system for deploying, monitoring, optimizing, scaling, securing and simplifying critical internet infrastructure.

The upcoming version 19 release is designed with these additional features and optimized for both bare-metal and cloud VM environments, with or without Docker and Kubernetes. 

+ Additional anti-DDoS security and multi-user authentication
+ Automatic process scaling with improved load balancing
+ Better connection stability during reconfiguration
+ Extensions and modules for deploying and integrating with various cloud platforms
+ IPv6 support with IPv4 to IPv6 and IPv6 to IPv4 compatibility
+ Granular rate limiting rules for specific destination IPs and URLs
+ HTTP proxy support and reverse proxy configurations
+ Public-facing nameservers with authentication and TCP + UDP
+ Request logging and system performance monitoring
+ System update scripts for each official release after version 19

Releases after version 19 will include VM instance and VPN node types

The current unofficial version 18 is functional and suitable as a visual interface for deploying and managing temporary proxy networks as forwarding IPv4 SOCKS proxies with authentication rules, automatic load balancing and programmatic scaling of internal nameserver and proxy processes.

## Version 18 Installation

Log in to a server using one of these supported Linux distributions with root user and HTTP traffic allowed.

+ Debian 9 Stretch
+ Debian 10 Buster
+ Ubuntu 18.04 Bionic Beaver
+ Ubuntu 20.04 Focal Fossa

Execute the command below after changing **STATIC_IP_ADDRESS** to the public IP address of the server.

> url=STATIC_IP_ADDRESS && cd /tmp && rm -rf /etc/cloud/ /var/lib/cloud/ ; apt-get update ; DEBIAN_FRONTEND=noninteractive apt-get -y install sudo ; sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk '{print $1}') ; sudo $(whereis telinit | awk '{print $2}') u ; sudo rm -rf /etc/cloud/ /var/lib/cloud/ ; sudo dpkg --configure -a ; sudo apt-get update && sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php wget --fix-missing && sudo wget -O website.php --no-dns-cache --retry-connrefused --timeout=60 --tries=2 "https://raw.githubusercontent.com/ghostcompute/ghostcompute/master/assets/php/website.php" && sudo wget -O database.php --no-dns-cache --retry-connrefused --timeout=60 --tries=2 "https://raw.githubusercontent.com/ghostcompute/ghostcompute/master/assets/php/database.php" && sudo php website.php $url && sudo php database.php $url

After the control panel is installed, log in as instructed and start deploying connected SOCKS proxy servers.

+ [Amazon EC2](https://gist.github.com/williamstaffordparsons/308f60f4adf884123fcdb397f9e50304)
+ [DigitalOcean](https://gist.github.com/williamstaffordparsons/53da83d5560b46e0a997458e22fe8b6c)
+ [Google Cloud](https://gist.github.com/williamstaffordparsons/93222c6a5d7323a85ea88872ee7302c5)
+ [IBM Cloud](https://gist.github.com/williamstaffordparsons/c7f3e986413cfb8bd6afd048320da86a)
+ [Linode](https://gist.github.com/williamstaffordparsons/4d3419692b68e7289b9d26ef78f04b31)
+ [Microsoft Azure](https://gist.github.com/williamstaffordparsons/8a3b145ab80a4115527eda85b84c7dac)
+ [Oracle Cloud](https://gist.github.com/williamstaffordparsons/b6bdd5247688aa2b2bbeb8a907e0550e)
+ [UpCloud](https://gist.github.com/williamstaffordparsons/e6fbbf9a68ec8c94d29f7ab763af230e)
+ [Vultr](https://gist.github.com/williamstaffordparsons/e73d940a4a7a142925e5bea5c8164faf)
