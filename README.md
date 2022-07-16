## About

#### Description
This is an automation tool to deploy, manage and secure large-scale cloud firewall systems with an API.

It's written in PHP and can be deployed on any major cloud platform.

This pre-release development code shouldn't be used in production until release version v1.0.0.

```
Features before version v1.0.0 release

- Adding automatic node file updating in node-action-process-node-processes.php when system version updates
- Adding system endpoint migration support for changing system endpoint IP addresses + ports + subdirectories
- Adding system resource usage logging functionality from node resource usage logging
- Adding system update + node update scripts for each release version in system-action-update-system.php
- Adding support for VMs without root
- Testing + bug fixes for node processes + logging
- Writing Documentaion
- Writing Guides
```

#### License
[MIT License](https://github.com/liamloads/firewall/blob/main/LICENSE)

## Installation

#### Debian 9 or 10
Open the terminal console on a clean VM with Debian 9 or 10.

Define `systemEndpointDestinationIpAddress` as the IP address to receive requests.

Change `10.10.10.10` to the actual IP address to receive requests.

``` console
systemEndpointDestinationIpAddress=10.10.10.10
```

Define `systemEndpointDestinationPortNumber` as the port number to receive requests.

Change `80` to the actual port number to receive requests.

``` console
systemEndpointDestinationPortNumber=80
```

Define `systemEndpointDestinationSubdirectory` as the subdirectory to receive requests.

Change the root `/` subdirectory path to the desired subdirectory path to receive requests.

``` console
systemEndpointDestinationSubdirectory=/
```

Install with this command.

``` console
cd /tmp && \
rm -rf /etc/cloud/ /var/lib/cloud/ ; \
apt-get update ; \
DEBIAN_FRONTEND=noninteractive apt-get -y install sudo ; \
sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk '{print $1}') ; \
sudo $(whereis telinit | awk '{print $2}') u ; \
sudo rm -rf /etc/cloud/ /var/lib/cloud/ ; \
sudo dpkg --configure -a ; \
sudo apt-get update && sudo DEBIAN_FRONTEND=noninteractive apt-get -y purge php* ; \
sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php wget --fix-missing && \
sudo rm system-action-deploy-system.php ; \
sudo wget -O system-action-deploy-system.php --no-dns-cache --retry-connrefused https://raw.githubusercontent.com/liamloads/firewall/main/system-action-deploy-system.php?$RANDOM && \
sudo php system-action-deploy-system.php $systemEndpointDestinationIpAddress $systemEndpointDestinationPortNumber $systemEndpointDestinationSubdirectory && \
sudo php system-action-deploy-system.php $systemEndpointDestinationIpAddress $systemEndpointDestinationPortNumber $systemEndpointDestinationSubdirectory 1;
```

```
System deployed successfully.

systemEndpointDestination
http://10.10.10.10:80/system-endpoint.php

systemUserAuthenticationToken
012345678901234567890123456789
```
