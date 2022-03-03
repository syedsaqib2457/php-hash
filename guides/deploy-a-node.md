### Deploy a Node
---

This guide explains how to deploy a node after [deploying the system](https://github.com/twexxor/firewall-security-api#user-content-get-started).

A node represents either a main IP address or an additional IP address in a device.

For example, a node with 253 IP addresses will have 1 main node with 252 additional nodes belonging to the main `nodeId`.

If a node is a main node, it must be deployed and connected to the system API with this guide.

If a node is an additional node, it will automatically update on the deployed node with no downtime.

A node can have both IPv4 and IPv6 addresses with external and internal IP addresses.

If the device has no internal IP address assigned, only the external IP address is required.

A node must be deployed in one of these Linux distributions with `all ports open`.

```
- Debian 10
- Debian 11
- Ubuntu 20.04
```

The system API automatically closes and opens ports for each node process.

This guide uses the following example data to deploy a node without any node processes.

```
- 10.10.10.1 as the external IP address
- 10.10.10.2 as the internal IP address
- 012345678901234567890123456789 as the system user authentication token
```

The system user authentication token must have the following scopes.

```
- activateNode
- addNode
- deployNode
```

### Add Node

This is an example `POST` request body made to the `/system-endpoint.php` path in `JSON` format.

All values are formatted as `string` types and contained in `json=`.

```json
{
    "action": "addNode",
    "data": {
        "externalIpAddressVersion4": "10.10.10.1",
        "internalIpAddressVersion4": "10.10.10.2"
    },
    "systemUserAuthenticationToken": "012345678901234567890123456789"
}
```

Open the terminal console in one of these Linux distributions.

```
- Debian 10
- Debian 11
- Ubuntu 20.04
```

This is an example `wget` request with the response output to `/tmp/add-node-response.json`.

``` console
sudo wget -O /tmp/add-node-response.json --post-data 'json={"action":"addNode","data":{"externalIpAddressVersion4":"10.10.10.1","internalIpAddressVersion4":"10.10.10.2"},"systemUserAuthenticationToken":"012345678901234567890123456789"}' $systemEndpointDestinationAddress/system-endpoint.php?$RANDOM
```

This is the example response in `/tmp/add-node-response.json`.

``` console
sudo cat /tmp/add-node-response.json && echo ""
```

``` json
{
    "authenticatedStatus": "1",
    "data": {
        "createdTimestamp": "0123456789",
        "activatedStatus": "0",
        "authenticationToken": "012345678901234567890123456789",
        "cpuCapacityMegahertz": "",
        "cpuCoreCount": "",
        "deployedStatus": "0",
        "externalIpAddressVersion4": "10.10.10.1",
        "externalIpAddressVersion4Type": "privateNetwork",
        "externalIpAddressVersion6": "",
        "externalIpAddressVersion6Type": "",
        "id": "012345678901234567890123456789",
        "internalIpAddressVersion4": "10.10.10.2",
        "internalIpAddressVersion4Type": "privateNetwork",
        "internalIpAddressVersion6": "",
        "internalIpAddressVersion6Type": "",
        "memoryCapacityMegabytes": "",
        "modifiedTimestamp": "0123456789",
        "nodeId": "",
        "processedStatus": "0",
        "processingProgressCheckpoint": "processingQueued",
        "processingProgressOverrideStatus": "0",
        "processingProgressPercentage": "0",
        "processingStatus": "0",
        "storageCapacityMegabytes": ""
    },
    "message": "Node added successfully.",
    "validatedStatus": "1"
}
```

Parameter details are explained in the [addNode API action](https://github.com/twexxor/firewall-security-api/blob/main/documentation/add-node.md) documentation.

### Activate Node

The `data.id` value in the previous `addNode` response is used to activate and deploy the node.

This is an example `POST` request body made to the `/system-endpoint.php` path in `JSON` format.

All values are formatted as `string` types and contained in `json=`.

```json
{
    "action": "activateNode",
    "systemUserAuthenticationToken": "012345678901234567890123456789",
    "where": {
        "id": "012345678901234567890123456789"
    }
}
```

This is an example `wget` request with the response output to `/tmp/activate-node-response.json`.

``` console
sudo wget -O /tmp/activate-node-response.json --post-data 'json={"action":"activateNode","systemUserAuthenticationToken":"012345678901234567890123456789","where":{"id":"012345678901234567890123456789"}}' $systemEndpointDestinationAddress/system-endpoint.php?$RANDOM
```

This is the example response in `/tmp/activate-node-response.json`.

``` console
sudo cat /tmp/activate-node-response.json && echo ""
```

``` json
{
    "authenticatedStatus": "1",
    "data": {
        "terminalConsoleCommand": "cd /tmp && rm -rf /etc/cloud/ /var/lib/cloud/ ; apt-get update ; DEBIAN_FRONTEND=noninteractive apt-get -y install sudo ; sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk '{print $1}') ; sudo $(whereis telinit | awk '{print $2}') u ; sudo rm -rf /etc/cloud/ /var/lib/cloud/ ; sudo dpkg --configure -a ; sudo apt-get update && sudo DEBIAN_FRONTEND=noninteractive apt-get -y purge php* ; sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php wget --fix-missing && sudo wget -O node-action-deploy-node.php --no-dns-cache 10.10.10.10/node-action-deploy-node.php?$RANDOM && sudo php node-action-deploy-node.php 012345678901234567890123456789 10.10.10.10"
    },
    "message": "Node is ready for activation and deployment.",
    "validatedStatus": "1"
}
```

### Deploy Node

The `data.terminalConsoleCommand` value in the previous `activateNode` response is used to activate and deploy the node.

Open the terminal console in the device to deploy with the node IP addresses.

Copy and paste the `data.terminalConsoleCommand` value.

``` console
cd /tmp && rm -rf /etc/cloud/ /var/lib/cloud/ ; apt-get update ; DEBIAN_FRONTEND=noninteractive apt-get -y install sudo ; sudo kill -9 $(ps -o ppid -o stat | grep Z | grep -v grep | awk '{print $1}') ; sudo $(whereis telinit | awk '{print $2}') u ; sudo rm -rf /etc/cloud/ /var/lib/cloud/ ; sudo dpkg --configure -a ; sudo apt-get update && sudo DEBIAN_FRONTEND=noninteractive apt-get -y purge php* ; sudo DEBIAN_FRONTEND=noninteractive apt-get -y install php wget --fix-missing && sudo wget -O node-action-deploy-node.php --no-dns-cache 10.10.10.10/node-action-deploy-node.php?$RANDOM && sudo php node-action-deploy-node.php 012345678901234567890123456789 10.10.10.10
```

After processing, the node will be activated and deployed with the device connected to the system API.

``` console
Node deployed successfully.
```
