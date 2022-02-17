# Add Node Process

## Introduction

This API action adds a node process to an existing node.

## Request Example

This is an example `POST` request body made to the `/system_endpoint.php` path in `JSON` format.

All values are formatted as `string` types.

```json
{
    "action": "add_node_process",
    "data": {
        "node_id": "unique_id",
        "port_number": "1080",
        "type": "socks_proxy"
    },
    "system_user_authentication_token": "123456789"
}
```

## Request Parameters

These are descriptions for each request parameter.

### action

The value must be `add_node_process`.

### data [node_id]

This is required to map IP addresses from an existing `node_id` to the node process `port_number`.

### data [port_number]

This is required to assign a listening port to the process.

The value must be a `numeric port number` between `1` and `65535`.

Requests to a `port_number` will automatically load-balance between all node processes with the same `node_id`.

### data [type]

This is required to assign a `type` the node process `port_number`.

This is the list of possible values.

- `bitcoin_cash_cryptocurrency_blockchain`
- `http_proxy`
- `load_balancer`
- `recursive_dns`
- `socks_proxy`

### system_user_authentication_token

This is required for authenticating system user access.

## Response Example

This is an example response body from the example request in `JSON` format.

All values are formatted as `string` types.

```json
{
    "authenticated_status": "1",
    "data": {
        "created_timestamp": "0000000000",
        "id": "unique_id",
        "modified_timestamp": "0000000000",
        "node_id": "unique_id",
        "node_node_id": "unique_id",
        "port_number": "1080",
        "type": "socks_proxy",
    },
    "message": "Node process added successfully.",
    "valid_status": "1"
}
```

## Response Parameters

These are descriptions for each response parameter.

### authenticated_status

This is the authenticated status indicator for the request.

The value is either `1` if the request is authenticated or `0` if the request isn't authenticated.

The request must have a valid `system_user_authentication_token` to be authenticated.

### data [created_timestamp]

This is the `Unix timestamp in seconds` of when the node process was added.

### data [id]

This is the unique ID of the added node process.

### data [modified_timestamp]

This is the `Unix timestamp in seconds` of when the node process was added.

The value changes to the current timestamp whenever the added system user is modified.

### data [node_id]

This is the unique ID of the node that the node process was assigned to.

### data [node_node_id]

This is the unique ID of the main node that the node process was assigned to.

### data [port_number]

This is the port number in the added node process.

The value is a `numeric port number` between `1` and `65535`.

Requests to a `port_number` will automatically load-balance between all node processes with the same `node_id`.

### data [type]

This is the type in the added node process.

This is the list of possible values.

- `bitcoin_cash_cryptocurrency_blockchain`
- `http_proxy`
- `load_balancer`
- `recursive_dns`
- `socks_proxy`

### message

This is the message for debugging after processing the request.

### valid_status

This is the valid status indicator for the request.

The value is either `1` if the request is valid or `0` if the request isn't valid.
