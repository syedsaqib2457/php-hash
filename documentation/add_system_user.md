# Add System User

## Introduction

This API action adds a system user below the current system user.

The added system user and all system users above the added system user have permissions to modify records belonging to the added system user.

## Request Example

This is an example `POST` request body made to the `/system_endpoint.php` path in `JSON` format.

All values are formatted as `string` types.

```json
{
    "action": "add_system_user",
    "system_user_authentication_token": "123456789"
}
```

## Request Parameters

These are descriptions for each request parameter.

### action

The value must be `add_system_user`.

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
        "system_user_id": "unique_id"
    },
    "message": "System user added successfully.",
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

This is the `Unix timestamp in seconds` of when the system user was added.

### data [id]

This is the unique ID of the added system user.

### data [modified_timestamp]

This is the `Unix timestamp in seconds` of when the system user was added.

The value changes to the current timestamp whenever the added system user is modified.

### data [system_user_id]

This is the unique ID of the system user that the added system user belongs to.

### message

This is the message for debugging after processing the request.

### valid_status

This is the valid status indicator for the request.

The value is either `1` if the request is valid or `0` if the request isn't valid.
