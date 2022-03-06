### Add System User Authentication Token
---

### Introduction

This API action adds a system user authentication token to a system user.

### Request Example

This is an example `POST` request body made to the `/system-endpoint.php` path in `JSON` format.

All values are formatted as `string` types and contained in `json=`.

``` json
{
    "action": "addSystemUserAuthenticationToken",
    "data": {
        "systemUserId": "012345678901234567890123456789"
    },
    "systemUserAuthenticationToken": "012345678901234567890123456789"
}
```

### Request Parameters

These are descriptions for each request parameter.

#### action

The value must be `addSystemUserAuthenticationToken`.

#### data.systemUserId

This is required to map the added system user authentication token to a `systemUserId`.

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
        "systemUserId": "012345678901234567890123456789",
        "value": "012345678901234567890123456789"
    },
    "message": "System user authentication token added successfully.",
    "validatedStatus": "1"
}
```

### Response Parameters

These are descriptions for each response parameter.

#### authenticatedStatus

This is the authenticated status indicator for the request.

The value is either `1` if the request is authenticated or `0` if the request isn't authenticated.

#### data.createdTimestamp

This is the `Unix timestamp in seconds` of when the system user authentication token was added.

#### data.id

This is the unique ID of the added system user authentication token.

The value is a `numeric ID` with a string length of `30 characters`.

#### data.modifiedTimestamp

This is the `Unix timestamp in seconds` of when the system user authentication token was added.

The value changes to the current timestamp whenever the added system user is modified.

#### data.systemUserId

This is the unique ID of the system user that the added system user belongs to.

The value is a `numeric ID` with a string length of `30 characters`.

#### data.value

This is the system user authentication token value to use for the `systemUserAuthenticationToken` request parameter.

#### message

This is the message for debugging after processing the request.

#### validatedStatus

This is the validated status indicator for the request.

The value is either `1` if the request is validated or `0` if the request isn't validated.
