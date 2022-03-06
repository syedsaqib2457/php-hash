### Add System User Authentication Token Scope
---

This API action adds a system user authentication token scope to a system user authentication token.

System user authentication token scopes are required to grant permissions to each `systemAction`.

### Request Example

This is an example `POST` request body made to the `/system-endpoint.php` path in `JSON` format.

All values are formatted as `string` types and contained in `json=`.

``` json
{
    "action": "addSystemUserAuthenticationTokenScope",
    "data": {
        "systemAction": "addNode",
        "systemUserAuthenticationTokenId": "012345678901234567890123456789"
    },
    "systemUserAuthenticationToken": "012345678901234567890123456789"
}
```

### Request Parameters

These are descriptions for each request parameter.

#### action

The value must be `addSystemUserAuthenticationTokenScope`.

#### data.systemAction

This is required to map the added system user authentication token scope to a `systemAction`.

#### data.systemUserAuthenticationTokenId

This is required to map the added system user authentication token scope to a `systemUserAuthenticationTokenId

The value must be a `numeric ID` with a string length of `30 characters`.

#### systemUserAuthenticationToken

This is required for authenticating system user access.

The value must be a `numeric ID` with a string length of `30 characters`.

### Response Example

This is an example response body from the example request in `JSON` format.

All values are formatted as `string` types.

``` json
{
    "authenticatedStatus": "1",
    "data": {
        "createdTimestamp": "0123456789",
        "id": "012345678901234567890123456789",
        "modifiedTimestamp": "0123456789",
        "systemAction": "addNode",
        "systemUserAuthenticationTokenId": "012345678901234567890123456789",
        "systemUserId": "012345678901234567890123456789"
    },
    "message": "System user authentication token scope added successfully.",
    "validatedStatus": "1"
}
```

### Response Parameters

These are descriptions for each response parameter.

#### authenticatedStatus

This is the authenticated status indicator for the request.

The value is either `1` if the request is authenticated or `0` if the request isn't authenticated.

#### data.createdTimestamp

This is the `Unix timestamp in seconds` of when the system user authentication token scope was added.

#### data.id

This is the unique ID of the added system user authentication token scope.

The value is a `numeric ID` with a string length of `30 characters`.

#### data.modifiedTimestamp

This is the `Unix timestamp in seconds` of when the system user authentication token scope was added.

The value changes to the current timestamp whenever the added system user is modified.

#### data.systemAction

This is the system action that the added system user authentication token scope grants permissions to.

#### data.systemUserAuthenticationTokenId

This is the unique ID of the system user authentication token that the added system user authentication token scope belongs to.

The value is a `numeric ID` with a string length of `30 characters`.

#### data.systemUserId

This is the unique ID of the system user that the added system user authentication token scope belongs to.

The value is a `numeric ID` with a string length of `30 characters`.

#### message

This is the message for debugging after processing the request.

#### validatedStatus

This is the validated status indicator for the request.

The value is either `1` if the request is validated or `0` if the request isn't validated.
