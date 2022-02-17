# Add Node

## Introduction

This API action adds a node to connect IP address data from a device to the system API.

## Request Example

This is an example `POST` request body made to the `/system_endpoint.php` path in `JSON` format.

All values are formatted as `string` types.

```json
{
    "action": "add_node",
    "data": {
        "external_ip_address_version_4": "0.0.0.0",
        "external_ip_address_version_6": "::0",
        "internal_ip_address_version_4": "10.10.10.10",
        "internal_ip_address_version_6": "::1",
        "node_id": "unique_id"
    },
    "system_user_authentication_token": "123456789"
}
```

## Request Parameters

These are descriptions for each request parameter.

### action

This is required for authenticating user scope and processing data for adding nodes.

The value must be `add_node`.

### data [external_ip_address_version_4]

This is required if the node has an IPv4 address routed to the device.

The value must be a `public or reserved IPv4 address`.

### data [external_ip_address_version_6]

This is required if the node has an IPv6 address routed to the device.

The value must be a `public or reserved IPv6 address`.

### data [internal_ip_address_version_4]

This is required if the node has a private IPv4 address for internal routing to external IP addresses on the device.

The value must be a `reserved IPv4 address`.

An `external_ip_address_version_4` value must also be set.

### data [internal_ip_address_version_6]

This is required if the node has a private IPv6 address for internal routing to external IP addresses on the device.

The value must be a `reserved IPv6 address`.

An `external_ip_address_version_6` value must also be set.

### data [node_id]

This is required if the node is added as an additional node to an existing node.

The value must be an `alphanumeric ID` with a string length of `30 characters`.

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
        "activated_status": "0",
        "cpu_capacity_megahertz": "",
        "cpu_core_count": "",
        "created_timestamp": "0000000000",
        "deployed_status": "0",
        "external_ip_address_version_4": "0.0.0.0",
        "external_ip_address_version_4_type": "current_network",
        "external_ip_address_version_6": "0000:0000:0000:0000:0000:0000:0000:0000",
        "external_ip_address_version_6_type": "loopback",
        "id": "unique_id",
        "internal_ip_address_version_4": "10.10.10.10",
        "internal_ip_address_version_4_type": "private_network",
        "internal_ip_address_version_6": "0000:0000:0000:0000:0000:0000:0000:0001",
        "internal_ip_address_version_6_type": "loopback",
        "memory_capacity_megabytes": "",
        "modified_timestamp": "0000000000",
        "node_id": "unique_id",
        "processed_status": "0",
        "processing_progress_checkpoint": "",
        "processing_progress_percentage": "0",
        "processing_status": "0",
        "storage_capacity_megabytes": ""
    },
    "message": "Node added successfully.",
    "valid_status": "1"
}
```

## Response Parameters

These are descriptions for each response parameter.

### authenticated_status

This is the authenticated status indicator for the request.

The value is either `1` if the request is authenticated or `0` if the request isn't authenticated.

The request must have a valid `system_user_authentication_token` to be authenticated.

### data [activated_status]

This is the activated status indicator in the added node.

The value is either `1` if the added node is activated or `0` if the added node isn't activated.

The added node must be deployed before it can be activated.

### data [cpu_capacity_megahertz]

This is the CPU clock speed for a single core detected in the added node instance.

The value is either a `numeric count in megahertz` if the node is added to a deployed node or `empty` if the node isn't deployed.

### data [cpu_core_count]

This is the count of CPU cores detected in the added node instance.

The value is either a `numeric count` if the added node is added to a deployed node or `empty` if the added node isn't deployed.

### data [created_timestamp]

This is the `Unix timestamp in seconds` of when the node was added.

### data [deployed_status]

This is the deployed status indicator in the added node.

The value is either `1` if the added node is deployed or `0` if the added node isn't deployed.

### data [external_ip_address_version_4]

This is the added external IPv4 address in the added node.

The value is either a `public or reserved IPv4 address` or `empty`.

### data [external_ip_address_version_4_type]

This is the added external IPv4 address type in the added node.

The value is either an `IP address type` or `empty`.

This is the list of possible values.

- `current_network`
- `documentation`
- `ietf_protocol_assignments`
- `internet`
- `loopback`
- `private_network`
- `public_network`

### data [external_ip_address_version_6]

This is the added external IPv6 address in the added node.

The value is either a `public or reserved IPv6 address` or `empty`.

Abbreviated IPv6 address notation values are converted to full IPv6 address notation values.

### data [external_ip_address_version_6_type]

This is the added external IPv6 address type in the added node.

The value is either an `IP address type` or `empty`.

This is the list of possible values.

- `current_network`
- `documentation`
- `ietf_protocol_assignments`
- `internet`
- `loopback`
- `private_network`
- `public_network`

### data [id]

This is the unique ID of the added node.

### data [internal_ip_address_version_4]

This is the added internal IPv4 address in the added node.

The value is either a `reserved IPv4 address` or `empty`.

### data [internal_ip_address_version_4_type]

This is the added internal IPv4 address type in the added node.

The value is either an `IP address type` or `empty`.

This is the list of possible values.

- `current_network`
- `documentation`
- `ietf_protocol_assignments`
- `internet`
- `loopback`
- `private_network`
- `public_network`

### data [internal_ip_address_version_6]

This is the added internal IPv6 address in the added node.

The value is either a `reserved IPv6 address` or `empty`.

Abbreviated IPv6 address notation values are converted to full IPv6 address notation values.

### data [internal_ip_address_version_6_type]

This is the added internal IPv6 address type in the added node.

The value is either an `IP address type` or `empty`.

This is the list of possible values.

- `current_network`
- `documentation`
- `ietf_protocol_assignments`
- `internet`
- `loopback`
- `private_network`
- `public_network`

### data [memory_capacity_megabytes]

This is the total RAM capacity detected in the added node instance.

The value is either a `numeric count in megabytes` if the node is added to a deployed node or `empty` if the node isn't deployed.

### data [modified_timestamp]

This is the `Unix timestamp in seconds` of when the node was added.

It changes to the current timestamp whenever a value is modified in the added node.

### data [node_id]

This is the unique ID of the main node in the added node.

The value is either an `alphanumeric ID` with a string length of `30 characters` if the added node belongs to a main node or `empty` if the added node is a main node.

### data [processed_status]

This is the processed status indicator in the added node.

The value is either `1` if the added node is processed or `0` if the added node isn't processed.

The added node must be deployed before it can be processed.

### data [processing_progress_checkpoint]

This is the current progress checkpoint for diagnosing performance issues while processing and updating an added node.

For example, a user can delete some request destination URLs if node processing takes too long during the `proxy_node_request_destinations` processing progress checkpoint.

The value is either an `alphanumeric checkpoint` if the added node is deployed or `empty` if the added node isn't deployed.

This is the list of possible values.

- `processing_completed`
- `processing_cryptocurrency_blockchain_node_processes`
- `processing_node_processes`
- `processing_proxy_node_processes`
- `processing_queued`
- `processing_firewall`
- `processing_recursive_dns_node_processes`
- `verifying_node_processes`

### data [processing_progress_percentage]

This is the current progress percentage for processing and updating an added node.

The value is a `numeric progress percentage`.

### data [processing_status]

This is the processing status indicator in the added node.

The value is either `1` if the added node is processing or `0` if the added node isn't processing.

The added node must be deployed before it can be processed.

### data [storage_capacity_megabytes]

This is the disk storage capacity detected in the added node instance.

The value is either a `numeric count in megabytes` if the node is added to a deployed node or `empty` if the node isn't deployed.

### message

This is the message for debugging after processing the request.

### valid_status

This is the valid status indicator for the request.

The value is either `1` if the request is valid or `0` if the request isn't valid.
