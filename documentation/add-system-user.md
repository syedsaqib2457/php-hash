<br>

### Add System User
---

This API action adds a system user below the current system user.

The added system user and all system users above the added system user have permissions to modify records belonging to the added system user.

### Request Example

This is an example `POST` request body made to the `/system-endpoint.php` path in `JSON` format.

All values are formatted as `string` types.

```json
{
    "action": "addSystemUser",
    "systemUserAuthenticationToken": "012345678901234567890123456789"
}
```

### Request Parameters

These are descriptions for each request parameter.

#### action

The value must be `addSystemUser`.

#### systemUserAuthenticationToken

This is required for authenticating system user access.

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
        "systemUserId": "012345678901234567890123456789"
    },
    "message": "System user added successfully.",
    "validatedStatus": "1"
}
```

### Response Parameters

These are descriptions for each response parameter.

#### authenticatedStatus

This is the authenticated status indicator for the request.

The value is either `1` if the request is authenticated or `0` if the request isn't authenticated.

The request must have a valid `systemUserAuthenticationToken` to be authenticated.

#### data [createdTimestamp]

This is the `Unix timestamp in seconds` of when the system user was added.

#### data [id]

This is the unique ID of the added system user.

The value is a `numeric ID` with a string length of `30 characters`.

#### data [modifiedTimestamp]

This is the `Unix timestamp in seconds` of when the system user was added.

The value changes to the current timestamp whenever the added system user is modified.

#### data [systemUserId]

This is the unique ID of the system user that the added system user belongs to.

The value is a `numeric ID` with a string length of `30 characters`.

#### message

This is the message for debugging after processing the request.

#### validatedStatus

This is the validated status indicator for the request.

The value is either `1` if the request is validated or `0` if the request isn't validated.
