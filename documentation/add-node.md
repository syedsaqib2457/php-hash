<br>

### Add Node
---

This API action adds a node to connect IP address data from a device to the system API.

Additional IP addresses on the same device are added as individual nodes belonging to the same `nodeId`.

### Request Example

This is an example `POST` request body made to the `/system-endpoint.php` path in `JSON` format.

All values are formatted as `string` types.

```json
{
    "action": "addNode",
    "data": {
        "externalIpAddressVersion4": "0.0.0.0",
        "externalIpAddressVersion6": "::0",
        "internalIpAddressVersion4": "10.10.10.10",
        "internalIpAddressVersion6": "::1",
        "nodeId": "012345678901234567890123456789"
    },
    "systemUserAuthenticationToken": "012345678901234567890123456789"
}
```

### Request Parameters

These are descriptions for each request parameter.

#### action

The value must be `addNode`.

#### data [externalIpAddressVersion4]

This is required if the node has an IPv4 address routed to the device.

The value must be a `public or reserved IPv4 address`.

#### data [externalIpAddressVersion6]

This is required if the node has an IPv6 address routed to the device.

The value must be a `public or reserved IPv6 address`.

#### data [internalIpAddressVersion4]

This is required if the node has a private IPv4 address for internal routing to external IP addresses on the device.

The value must be a `reserved IPv4 address`.

An `externalIpAddressVersion4` value must also be set.

#### data [internalIpAddressVersion6]

This is required if the node has a private IPv6 address for internal routing to external IP addresses on the device.

The value must be a `reserved IPv6 address`.

An `externalIpAddressVersion6` value must also be set.

#### data [nodeId]

This is required if the node is added as an additional node to an existing node.

The value must be a `numeric ID` with a string length of `30 characters`.

#### systemUserAuthenticationToken

This is required for authenticating system user access.

The value must be a `string` in the `systemUserAuthenticationTokens` database.

### Response Example

This is an example response body from the example request in `JSON` format.

All values are formatted as `string` types.

```json
{
    "authenticatedStatus": "1",
    "data": {
        "activatedStatus": "0",
        "cpuCapacityMegahertz": "",
        "cpuCoreCount": "",
        "createdTimestamp": "0123456789",
        "deployedStatus": "0",
        "externalIpAddressVersion4": "0.0.0.0",
        "externalIpAddressVersion4Type": "currentNetwork",
        "externalIpAddressVersion6": "0000:0000:0000:0000:0000:0000:0000:0000",
        "externalIpAddressVersion6Type": "loopback",
        "id": "012345678901234567890123456789",
        "internalIpAddressVersion4": "10.10.10.10",
        "internalIpAddressVersion4Type": "privateNetwork",
        "internalIpAddressVersion6": "0000:0000:0000:0000:0000:0000:0000:0001",
        "internalIpAddressVersion6Type": "loopback",
        "memoryCapacityMegabytes": "",
        "modifiedTimestamp": "0123456789",
        "nodeId": "012345678901234567890123456789",
        "processedStatus": "0",
        "processingProgressCheckpoint": "",
        "processingProgressPercentage": "0",
        "processingStatus": "0",
        "storageCapacityMegabytes": ""
    },
    "message": "Node added successfully.",
    "validatedStatus": "1"
}
```

### Response Parameters

These are descriptions for each response parameter.

#### authenticatedStatus

This is the authenticated status indicator for the request.

The value is either `1` if the request is authenticated or `0` if the request isn't authenticated.

The request must have a valid `systemUserAuthenticationToken` to be authenticated.

#### data [activatedStatus]

This is the activated status indicator in the added node.

The value is either `1` if the added node is activated or `0` if the added node isn't activated.

The added node must be deployed before it can be activated.

#### data [cpuCapacityMegahertz]

This is the CPU clock speed for a single core detected in the added node instance.

The value is either a `numeric count in megahertz` if the node is added to a deployed node or `empty` if the node isn't deployed.

#### data [cpuCoreCount]

This is the count of CPU cores detected in the added node instance.

The value is either a `numeric count` if the added node is added to a deployed node or `empty` if the added node isn't deployed.

#### data [createdTimestamp]

This is the `Unix timestamp in seconds` of when the node was added.

#### data [deployedStatus]

This is the deployed status indicator in the added node.

The value is either `1` if the added node is deployed or `0` if the added node isn't deployed.

#### data [externalIpAddressVersion4]

This is the external IPv4 address in the added node.

The value is either a `public or reserved IPv4 address` or `empty`.

#### data [externalIpAddressVersion4Type]

This is the external IPv4 address type in the added node.

The value is either an `IP address type` or `empty`.

This is the list of possible values.

```
currentNetwork
documentation
ietfProtocolAssignments
internet
loopback
privateNetwork
publicNetwork
```

#### data [externalIpAddressVersion6]

This is the external IPv6 address in the added node.

The value is either a `public or reserved IPv6 address` or `empty`.

Abbreviated IPv6 address notation values are converted to full IPv6 address notation values.

#### data [externalIpAddressVersion6Type]

This is the external IPv6 address type in the added node.

The value is either an `IP address type` or `empty`.

This is the list of possible values.

```
currentNetwork
documentation
ietfProtocolAssignments
internet
loopback
privateNetwork
publicNetwork
```

#### data [id]

This is the unique `numeric ID` of the added node.

The value is a `numeric ID` with a string length of `30 characters`.

#### data [internalIpAddressVersion4]

This is the internal IPv4 address in the added node.

The value is either a `reserved IPv4 address` or `empty`.

#### data [internalIpAddressVersion4Type]

This is the internal IPv4 address type in the added node.

The value is either an `IP address type` or `empty`.

This is the list of possible values.

```
currentNetwork
documentation
ietfProtocolAssignments
internet
loopback
privateNetwork
```

#### data [internalIpAddressVersion6]

This is the internal IPv6 address in the added node.

The value is either a `reserved IPv6 address` or `empty`.

Abbreviated IPv6 address notation values are converted to full IPv6 address notation values.

#### data [internalIpAddressVersion6Type]

This is the internal IPv6 address type in the added node.

The value is either an `IP address type` or `empty`.

This is the list of possible values.

```
currentNetwork
documentation
ietfProtocolAssignments
internet
loopback
privateNetwork
```

#### data [memoryCapacityMegabytes]

This is the total RAM capacity detected in the added node instance.

The value is either a `numeric count in megabytes` if the node is added to a deployed node or `empty` if the node isn't deployed.

#### data [modifiedTimestamp]

This is the `Unix timestamp in seconds` of when the node was added.

It changes to the current timestamp whenever a value is modified in the added node.

#### data [nodeId]

This is the unique ID of the main node in the added node.

The value is either an `numeric ID` with a string length of `30 characters` if the added node belongs to a main node or `empty` if the added node is a main node.

#### data [processedStatus]

This is the processed status indicator in the added node.

The value is either `1` if the added node is processed or `0` if the added node isn't processed.

The added node must be deployed before it can be processed.

#### data [processingProgressCheckpoint]

This is the current progress checkpoint for diagnosing performance issues while processing and updating an added node.

For example, a user can delete some request destination URLs if node processing takes too long during the `proxyNodeRequestDestinations` processing progress checkpoint.

The value is either an `alphanumeric checkpoint` if the added node is deployed or `empty` if the added node isn't deployed.

This is the list of possible values.

```
processingCompleted
processingNodeProcesses
processingProxyNodeProcesses
processingQueued
processingFirewall
processingRecursiveDnsNodeProcesses
verifyingNodeProcesses
```

#### data [processingProgressPercentage]

This is the current progress percentage for processing and updating an added node.

The value is a `numeric progress percentage`.

#### data [processingStatus]

This is the processing status indicator in the added node.

The value is either `1` if the added node is processing or `0` if the added node isn't processing.

The added node must be deployed before it can be processed.

#### data [storageCapacityMegabytes]

This is the disk storage capacity detected in the added node instance.

The value is either a `numeric count in megabytes` if the node is added to a deployed node or `empty` if the node isn't deployed.

#### message

This is the message for debugging after processing the request.

#### validatedStatus

This is the validated status indicator for the request.

The value is either `1` if the request is validated or `0` if the request isn't validated.
