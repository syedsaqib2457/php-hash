# Add System User Authentication Token Source

## Introduction

This API action adds a system user authentication token source to a system user authentication token.

System user authentication token sources are optional to grant permissions to specific ranges of source IP addresses only.

## Request Example

This is an example `POST` request body made to the `/system_endpoint.php` path in `JSON` format.

All values are formatted as `string` types.

```json
{
    "action": "add_system_user_authentication_token_source",
    "data": {
        "ip_address_range_start": "10.10.10.10",
        "ip_address_range_stop": "10.10.10.20",
        "system_user_authentication_token_id": "unique_id"
    },
    "system_user_authentication_token": "unique_id"
}
```

## Request Parameters

These are descriptions for each request parameter.

### action

The value must be `add_system_user_authentication_token_source`.

### data [ip_address_range_start]

This is required to identify the first IP address in a range of IP addresses.

The value must be a `public or reserved IPv4 or IPv6 address`.

### data [ip_address_range_stop]

This is required to identify the last IP address in a range of IP addresses.

The value must be a `public or reserved IPv4 or IPv6 address`.

If the system user authentication token source only has 1 IP address, the value should be the same as `ip_address_range_start`.

### data [system_user_authentication_token_id]

This is required to map the added system user authentication token source to a `system_user_authentication_token_id`.

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
        "ip_address_range_start": "10.10.10.10",
        "ip_address_range_stop": "10.10.10.20",
        "ip_address_range_version_number": "4",
        "modified_timestamp": "0000000000",
        "system_user_authentication_token_id": "unique_id",
        "system_user_id": "unique_id"
    },
    "message": "System user authentication token source added successfully.",
    "valid_status": "1"
}
```

## Response Parameters

These are descriptions for each response parameter.

### authenticated_status

This is the authenticated status indicator for the request.

The value is either `1` if the request is authenticated or `0` if the request isn't authenticated.

The request must have a valid `system_user_authentication_token` value to be authenticated.

### data [created_timestamp]

This is the `Unix timestamp in seconds` of when the system user authentication token source was added.

### data [id]

This is the unique ID of the added system user authentication token source.

### data [system_user_authentication_token_id]

This is the unique ID of the system user authentication token that the added system user authentication token source belongs to.

### data [modified_timestamp]

This is the `Unix timestamp in seconds` of when the system user authentication token source was added.

The value changes to the current timestamp whenever the added system user is modified.

### data [system_user_authentication_token_id]

This is the unique ID of the system user authentication token that the added system user authentication token source belongs to.

### data [system_user_id]

This is the unique ID of the system user that the added system user authentication token source belongs to.

### message

This is the message for debugging after processing the request.

### valid_status

This is the valid status indicator for the request.

The value is either `1` if the request is valid or `0` if the request isn't valid.
