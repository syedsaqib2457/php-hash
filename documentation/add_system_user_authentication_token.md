# Add System User Authentication Token

## Introduction

This API action adds a system user authentication token to a system user.

## Request Example

This is an example `POST` request body made to the `/system_endpoint.php` path in `JSON` format.

All values are formatted as `string` types.

```json
{
    "action": "add_system_user_authentication_token",
    "data": {
        "system_user_id": "unique_id"
    },
    "system_user_authentication_token": "unique_id"
}
```

## Request Parameters

These are descriptions for each request parameter.

### action

This is required for authenticating user scope and processing data for adding system user authentication tokens.

The value must be `add_system_user_authentication_token`.

### data [system_user_id]

This is required to map the added system user authentication token to a `system_user_id`.

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
        "system_user_id": "unique_id",
        "value": "unique_id"
    },
    "message": "System user authentication token added successfully.",
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

This is the `Unix timestamp in seconds` of when the system user authentication token was added.

### data [id]

This is the unique ID of the added system user authentication token.

### data [modified_timestamp]

This is the `Unix timestamp in seconds` of when the system user authentication token was added.

The value changes to the current timestamp whenever the added system user is modified.

### data [system_user_id]

This is the unique ID of the system user that the added system user belongs to.

### data [value]

This is the system user authentication token value to use for the `system_user_authentication_token` request parameter.

### message

This is the message for debugging after processing the request.

### valid_status

This is the valid status indicator for the request.

The value is either `1` if the request is valid or `0` if the request isn't valid.
