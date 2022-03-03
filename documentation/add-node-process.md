<br>

### Add Node Process
---

This API action adds a node process to an existing node.

### Request Example

This is an example `POST` request body made to the `/system-endpoint.php` path in `JSON` format.

All values are formatted as `string` types and contained in `json=`.

```json
{
    "action": "addNodeProcess",
    "data": {
        "nodeId": "012345678901234567890123456789",
        "portNumber": "1080",
        "type": "socksProxy"
    },
    "systemUserAuthenticationToken": "012345678901234567890123456789"
}
```

### Request Parameters

These are descriptions for each request parameter.

#### action

The value must be `addNodeProcess`.

#### data.nodeId

This is required to map IP addresses from an existing `nodeId` to the node process `portNumber`.

The value must be a `numeric ID` with a string length of `30 characters`.

#### data.portNumber

This is required to assign a listening port to the process.

The value must be a `numeric port number` between `1` and `65535`.

Requests to a `portNumber` will automatically load-balance between all node processes with the same `node_id`.

#### data.type

This is required to assign a `type` the node process `portNumber`.

This is the list of possible values.

```
httpProxy
loadBalancer
recursiveDns
socksProxy
```

#### systemUserAuthenticationToken

This is required for authenticating system user access.

The value must be a `numeric ID` with a string length of `30 characters`.

### Response Example

This is an example response body from the example request in `JSON` format.

All values are formatted as `string` types.

```json
{
    "authenticatedStatus": "1",
    "data": {
        "createdTimestamp": "0123456789",
        "id": "012345678901234567890123456789",
        "modifiedTimestamp": "0123456789",
        "nodeId": "012345678901234567890123456789",
        "nodeNodeId": "012345678901234567890123456789",
        "portNumber": "1080",
        "type": "socksProxy",
    },
    "message": "Node process added successfully.",
    "validatedStatus": "1"
}
```

### Response Parameters

These are descriptions for each response parameter.

#### authenticatedStatus

This is the authenticated status indicator for the request.

The value is either `1` if the request is authenticated or `0` if the request isn't authenticated.

The request must have a valid `systemUserAuthenticationToken` to be authenticated.

#### data.createdTimestamp

This is the `Unix timestamp in seconds` of when the node process was added.

#### data.id

This is the unique ID of the added node process.

The value is a `numeric ID` with a string length of `30 characters`.

#### data.modifiedTimestamp

This is the `Unix timestamp in seconds` of when the node process was added.

The value changes to the current timestamp whenever the added system user is modified.

#### data.nodeId

This is the unique ID of the node that the node process was assigned to.

The value is a `numeric ID` with a string length of `30 characters`.

#### data.nodeNodeId

This is the unique ID of the main node that the node process was assigned to.

The value is a `numeric ID` with a string length of `30 characters`.

#### data.portNumber

This is the port number in the added node process.

The value is a `numeric port number` between `1` and `65535`.

Requests to a `portNumber` will automatically load-balance between all node processes with the same `nodeId`.

#### data.type

This is the type in the added node process.

This is the list of possible values.

```
- httpProxy
- loadBalancer
- recursiveDns
- socksProxy
```

#### message

This is the message for debugging after processing the request.

#### validatedStatus

This is the validated status indicator for the request.

The value is either `1` if the request is validated or `0` if the request isn't validated.
