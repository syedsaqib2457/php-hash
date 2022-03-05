### Firewall Security API
___

This is a free and open-source API to secure firewalls.

``` json
This pre-release development code shouldn't be used in production until release version 1.00.
```

```
Checklist features before version 1.00 release

- Adding API DDoS protection from unauthorized requests
- Addding automatic node file updating in node-action-process-node-processes.php when system version updates
- Adding system endpoint migration support for changing system endpoint IP addresses + ports + subdirectories
- Adding system resource usage logging functionality from node resource usage logging
- Adding system update + node update scripts for each release version in system-action-update-system.php
- Installing in a subdirectory
- Testing + bug fixes
- Writing Documentaion
- Writing Guides
```

### Get Started

Open the terminal console on either `Debian 10` or `Debian 11`.

Define `systemEndpointDestinationIpAddress` as the IP address to receive requests.

This example uses `10.10.10.10` to receive requests.

``` console
systemEndpointDestinationIpAddress=10.10.10.10
```

Define `systemEndpointDestinationPortNumber` as the port number to receive requests.

``` console
systemEndpointDestinationPortNumber=80
```

Install with this command.

``` console
cd /tmp && rm -rf /etc/cloud/ /var/lib/cloud/ ; apt-get update ; DEBIAN_FRONTEND=noninteractive apt-get -y install sudo ; sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk '{print $1}') ; sudo $(whereis telinit | awk '{print $2}') u ; sudo rm -rf /etc/cloud/ /var/lib/cloud/ ; sudo dpkg --configure -a ; sudo apt-get update && sudo DEBIAN_FRONTEND=noninteractive apt-get -y purge php* ; sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php wget --fix-missing && sudo rm system-action-deploy-system.php ; sudo wget -O system-action-deploy-system.php --no-dns-cache --retry-connrefused https://raw.githubusercontent.com/twexxor/firewall-security-api/main/system-action-deploy-system.php?$RANDOM && sudo php system-action-deploy-system.php $systemEndpointDestinationIpAddress $systemEndpointDestinationPortNumber && sudo php system-action-deploy-system.php $systemEndpointDestinationIpAddress systemEndpointDestinationPortNumber 1;
```

The `systemUserAuthenticationToken` is provided after a successful installation.

``` console
System deployed successfully.
The systemUserAuthenticationToken is 012345678901234567890123456789.
```

### Usage

First, [deploy a node](https://github.com/twexxor/firewall-security-api/blob/main/guides/deploy-a-node.md#user-content-deploy-a-node) after deploying the system.

Then, read the [documentation](https://github.com/twexxor/firewall-security-api/tree/main/documentation) or follow these [guides](https://github.com/twexxor/firewall-security-api/tree/main/guides).
