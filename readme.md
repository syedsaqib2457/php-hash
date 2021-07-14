Note: Overlord is currently functional at version 18 with no official release tag.

The first official release will be at version 19 when process auto-scaling, IPv6, proxy request logging, public-facing DNS, reverse proxies and system monitoring features are completed. It will likely require new installations and server deployments if upgrading from version 18 to version 19.

## Installation

Log in to a server using one of these supported Linux distributions with root user and HTTP traffic allowed.

+ Debian 9 Stretch
+ Debian 10 Buster
+ Ubuntu 18.04 Bionic Beaver
+ Ubuntu 20.04 Focal Fossa

Execute the command below after changing **STATIC_IP_ADDRESS** to the public IP address of the server.

> url=STATIC_IP_ADDRESS && cd /tmp && rm -rf /etc/cloud/ /var/lib/cloud/ ; apt-get update ; DEBIAN_FRONTEND=noninteractive apt-get -y install sudo ; sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk '{print $1}') ; sudo $(whereis telinit | awk '{print $2}') u ; sudo rm -rf /etc/cloud/ /var/lib/cloud/ ; sudo dpkg --configure -a ; sudo apt-get update && sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php wget --fix-missing && sudo wget -O website.php --no-dns-cache --retry-connrefused --timeout=60 --tries=2 "https://raw.githubusercontent.com/willybombz/overlord/master/assets/php/website.php" && sudo wget -O database.php --no-dns-cache --retry-connrefused --timeout=60 --tries=2 "https://raw.githubusercontent.com/willybombz/overlord/master/assets/php/database.php" && sudo php website.php $url && sudo php database.php $url

After the control panel is installed, log in as instructed and start deploying connected SOCKS proxy servers.

+ [Amazon EC2](https://gist.github.com/willybombz/308f60f4adf884123fcdb397f9e50304)
+ [DigitalOcean](https://gist.github.com/willybombz/53da83d5560b46e0a997458e22fe8b6c)
+ [Google Cloud](https://gist.github.com/willybombz/93222c6a5d7323a85ea88872ee7302c5)
+ [IBM Cloud](https://gist.github.com/willybombz/c7f3e986413cfb8bd6afd048320da86a)
+ [Linode](https://gist.github.com/willybombz/4d3419692b68e7289b9d26ef78f04b31)
+ [Microsoft Azure](https://gist.github.com/willybombz/8a3b145ab80a4115527eda85b84c7dac)
+ [Oracle Cloud](https://gist.github.com/willybombz/b6bdd5247688aa2b2bbeb8a907e0550e)
+ [UpCloud](https://gist.github.com/willybombz/e6fbbf9a68ec8c94d29f7ab763af230e)
+ [Vultr](https://gist.github.com/willybombz/e73d940a4a7a142925e5bea5c8164faf)
