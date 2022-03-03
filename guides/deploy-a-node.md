<br>

### Deploy a Node
---

This guide explains how to deploy a node after [deploying the system](https://github.com/twexxor/firewall-security-api#user-content-get-started).

A node represents either a main IP address or an additional IP address on a device.

For example, a node with 253 IP addresses will have 1 main node with 252 nodes belonging to the main `nodeId`.

A node can have both IPv4 and IPv6 addresses with external and internal IP addresses.

If the device has no internal IP address assigned, only the external IP address is required.

A node must be deployed in one of these Linux distributions with `all ports open`.

```
- Debian 10
- Debian 11
- Ubuntu 20.04
```

The system automatically closes and opens ports for each node process.

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

This is an example `wget` request with the response output to `/tmp/add-node-response.json`.

``` console
sudo wget -O /tmp/add-node-response.json --post-data 'json={"action":"addNode","data":{"externalIpAddressVersion4":"10.10.10.1","internalIpAddressVersion4":"10.10.10.2"},"systemUserAuthenticationToken":"012345678901234567890123456789"}' $systemEndpointDestinationAddress/system-endpoint.php?$RANDOM && sudo cat /tmp/debug.json && echo ""
```

### Activate Node

### Deploy Node
