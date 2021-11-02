## Add Node

### Introduction

This API action adds IP address data from an instance to GhostCompute so it can be deployed as a connected node with API automation.

IP addresses must already be routed to a node instance by the hosting provider.

### Request Example

This is an example `POST` request body made to the `/endpoint.php` path in `JSON` format.

All values are formatted as `string` types.

```json
{
    "action": "add_node",
    "authentication_token": "123456789",
    "data": {
        "external_ip_address_version_4": "0.0.0.0",
        "external_ip_address_version_6": "::0",
        "internal_ip_address_version_4": "10.10.10.10",
        "internal_ip_address_version_6": "::1",
        "node_id": "unique_id_1"
    }
}
```

### Request Parameters

These are descriptions for each request parameter.

#### action

This is required for authenticating user scope and processing data for adding nodes.

The value must be `add_node`.

#### authentication_token

This is required for authenticating user access.

The value must be a `string` in the `system_user_authentication_tokens` database.

#### data[external_ip_address_version_4]

This is required if the node has an IPv4 address routed to the instance.

The value must be a `public or reserved IPv4 address`.

#### data[external_ip_address_version_6]

This is required if the node has an IPv6 address routed to the instance.

The value must be a `public or reserved IPv6 address`.

#### data[internal_ip_address_version_4]

This is required if the node has a private IPv4 address for internal routing to external IP addresses on the instance.

The value must be a `reserved IPv4 address`.

An `external_ip_version_4_address` value must also be set.

#### data[internal_ip_address_version_6]

This is required if the node has a private IPv6 address for internal routing to external IP addresses on the instance.

The value must be a `reserved IPv6 address`.

An `external_ip_version_6_address` value must also be set.

#### data[node_id]

This is required if the node is added as an additional node to an existing node.

The value must be an `alphanumeric ID`.

### Response Example

This is an example response body from the example request in `JSON` format.

All values are formatted as `string` types.

```json
{
    "data": {
        "cpu_capacity_megahertz": "",
        "cpu_core_count": "",
        "created_timestamp": "0000000000",
        "external_ip_address_version_4": "0.0.0.0",
        "external_ip_address_version_4_type": "current_network",
        "external_ip_address_version_6": "0000:0000:0000:0000:0000:0000:0000:0000",
        "external_ip_address_version_6_type": "loopback",
        "id": "unique_id_2",
        "internal_ip_address_version_4": "10.10.10.10",
        "internal_ip_address_version_4_type": "private_network",
        "internal_ip_address_version_6": "0000:0000:0000:0000:0000:0000:0000:0001",
        "internal_ip_address_version_6_type": "loopback",
        "memory_capacity_megabytes": "",
        "modified_timestamp": "0000000000",
        "node_id": "unique_id_1",
        "processing_progress_checkpoint": "",
        "processing_progress_percentage": "0",
        "status_active": "0",
        "status_deployed": "0",
        "status_processed": "0",
        "status_processing": "0",
        "storage_capacity_megabytes": ""
    },
    "message": "Node added successfully.",
    "status_authenticated": "1",
    "status_valid": "1"
}
```

### Response Parameters

These are descriptions for each response parameter.

#### data[cpu_capacity_megahertz]

This is the CPU clock speed for a single core detected in the added node instance.

The value is either a `numeric count in MHz` if the node is added to a deployed node or `empty` if the node isn't deployed.

#### data[cpu_core_count]

This is the count of CPU cores detected in the added node instance.

The value is either a `numeric count` if the added node is added to a deployed node or `empty` if the added node isn't deployed.

#### data[created_timestamp]

This is the timestamp of when the node was added.

The value is a `Unix timestamp in seconds`.

#### data[external_ip_address_version_4]

This is the added external IPv4 address in the added node.

The value is either a `public or reserved IPv4 address` or `empty`.

#### data [external_ip_address_version_4_type]

This is the added external IPv4 address type in the added node.

The value is either an `IP address type` or `empty`.

This is the list of possible values.

- current_network
- loopback
- private_network
- public_network
- [list_all_values]
