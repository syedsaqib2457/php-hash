# Add Node Process

## Introduction

The `add_node_process` API action adds a node process to an existing `node_id`.

## Request Example

This is an example `POST` request body made to the `/system_endpoint.php` path in `JSON` format.

All values are formatted as `string` types.

```json
{
    "action": "add_node_process",
    "data": {
        "node_id": "unique_id_1",
        "port_number": "1080",
        "type": "socks_proxy"
    },
    "system_user_authentication_token": "123456789"
}
```

## Request Parameters

These are descriptions for each request parameter.

### action

This is required for authenticating user scope and processing data for adding node processes.

The value must be `add_node_process`.

### data[node_id]

This is required to map IP addresses from an existing `node_id` to the node process `port_number`.

The value must be an `alphanumeric ID`.

### data[port_number]

This is required to assign a listening port to the process.

The value must be a `numeric port number` between `1` and `65535`.

Requests to a `port_number` will automatically load-balance between all node processes with the same `node_id`.

### data[type]

This is required to assign a `type` the node process `port_number`.

The value must be an `string`.

This is the list of possible values.

- `bitcoin_cash_cryptocurrency_blockchain`
- `http_proxy`
- `load_balancer`
- `recursive_dns`
- `socks_proxy`

### system_user_authentication_token

This is required for authenticating system user access.

The value must be a `string` in the `system_user_authentication_tokens` database.

## Response Example

This is an example response body from the example request in `JSON` format.

All values are formatted as `string` types.

```json
{
    "authenticated_status": "1",
    "data": {
        // todo
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

### message

This is the message for debugging after processing the request.

The value is a `string`.

### valid_status

This is the valid status indicator for the request.

The value is either `1` if the request is valid or `0` if the request isn't valid.
