<br>

### Deploy a Node
---

This guide explains how to deploy a node after [deploying the system](https://github.com/twexxor/firewall-security-api#user-content-get-started).

A node represents a main IP address or an additional IP address on a device.

For example, a node with 253 IP addresses will have 1 main node with 252 nodes belonging to the main `nodeId`.

A node can have both IPv4 and IPv6 addresses with external and internal IP addresses.

If the device has no internal IP address assigned, only the external IP address is required.

This guide uses the following example data to deploy a node without any node processes.

``` console
10.10.10.1 as the external IP address
10.10.10.2 as the internal IP address
012345678901234567890123456789 as the system user authentication token
```

The system user authentication token must have the following scopes.

``` console
activateNode
addNode
deployNode
```
