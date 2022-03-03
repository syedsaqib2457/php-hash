### Delete Node
---

This API action deletes a node.

If the deleted node is a main node, all additional nodes belonging to the deleted node will be deleted.

Logs belonging to the deleted node will remain in the system API.

### Request Example

This is an example `POST` request body made to the `/system-endpoint.php` path in `JSON` format.

All values are formatted as `string` types and contained in `json=`.

``` json
{
    "action": "deleteNode",
    "systemUserAuthenticationToken": "012345678901234567890123456789",
    "where": {
        "id": "012345678901234567890123456789"
    }
}
```

### Request Parameters

These are descriptions for each request parameter.

#### action

The value must be `deleteNode`.

#### where.id

This is required for deleting a node by the `id`.

The value must be a `numeric ID` with a string length of `30 characters`.

#### systemUserAuthenticationToken

This is required for authenticating system user access.

The value must be a `numeric ID` with a string length of `30 characters`.

### Response Example

This is an example response body from the example request in `JSON` format.

All values are formatted as `string` types.

```json
{
    "authenticatedStatus": "1",
    "data": {},
    "message": "Node deleted successfully.",
    "validatedStatus": "1"
}
```

### Response Parameters

These are descriptions for each response parameter.

#### authenticatedStatus

This is the authenticated status indicator for the request.

The value is either `1` if the request is authenticated or `0` if the request isn't authenticated.

#### data

The value is an `empty array`.

#### message

This is the message for debugging after processing the request.

#### validatedStatus

This is the validated status indicator for the request.

The value is either `1` if the request is validated or `0` if the request isn't validated.
