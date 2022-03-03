<br>

### Add System User Authentication Token Source
---

This API action adds a system user authentication token source to a system user authentication token.

System user authentication token sources are optional to grant permissions to specific ranges of source IP addresses only.

### Request Example

This is an example `POST` request body made to the `/system-endpoint.php` path in `JSON` format.

All values are formatted as `string` types.

```json
{
    "action": "addSystemUserAuthenticationTokenSource",
    "data": {
        "ipAddressRangeStart": "10.10.10.10",
        "ipAddressRangeStop": "10.10.10.20",
        "systemUserAuthenticationTokenId": "012345678901234567890123456789"
    },
    "systemUserAuthenticationToken": "012345678901234567890123456789"
}
```

### Request Parameters

These are descriptions for each request parameter.

#### action

The value must be `addSystemUserAuthenticationTokenSource`.

#### data [ipAddressRangeStart]

This is required to identify the first IP address in a range of IP addresses.

The value must be a `public or reserved IPv4 or IPv6 address`.

#### data [ipAddressRangeStop]

This is required to identify the last IP address in a range of IP addresses.

The value must be a `public or reserved IPv4 or IPv6 address`.

If the system user authentication token source has only 1 IP address, the value should be the same as `ipAddressRangeStart`.

#### data [systemUserAuthenticationTokenId]

This is required to map the added system user authentication token source to a `systemUserAuthenticationTokenId`.

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
        "ipAddressRangeStart": "10.10.10.10",
        "ipAddressRangeStop": "10.10.10.20",
        "ipAddressRangeVersionNumber": "4",
        "modifiedTimestamp": "0123456789",
        "systemUserAuthenticationTokenId": "012345678901234567890123456789",
        "systemUserId": "012345678901234567890123456789"
    },
    "message": "System user authentication token source added successfully.",
    "validatedStatus": "1"
}
```

### Response Parameters

These are descriptions for each response parameter.

#### authenticatedStatus

This is the authenticated status indicator for the request.

The value is either `1` if the request is authenticated or `0` if the request isn't authenticated.

The request must have a valid `systemUserAuthenticationToken` value to be authenticated.

#### data [createdTimestamp]

This is the `Unix timestamp in seconds` of when the system user authentication token source was added.

#### data [id]

This is the unique ID of the added system user authentication token source.

#### data [ipAddressRangeStart]

This is the first IPv4 or IPv6 address in the added system user authentication token source.

The value is a `public or reserved IPv4 or IPv6 address`.

#### data [ipAddressRangeStop]

This is the last IPv4 or IPv6 address in the added system user authentication token source.

The value is a `public or reserved IPv4 or IPv6 address`.

#### data [ipAddressRangeVersionNumber]

This is the numeric IP address range version number in the added system user authentication token source.

The value is either `4` or `6`.

#### data [modifiedTimestamp]

This is the `Unix timestamp in seconds` of when the system user authentication token source was added.

The value changes to the current timestamp whenever the added system user is modified.

#### data [systemUserAuthenticationTokenId]

This is the unique ID of the system user authentication token that the added system user authentication token source belongs to.

#### data [systemUserId]

This is the unique ID of the system user that the added system user authentication token source belongs to.

#### message

This is the message for debugging after processing the request.

#### validatedStatus

This is the validated status indicator for the request.

The value is either `1` if the request is validated or `0` if the request isn't validated.
