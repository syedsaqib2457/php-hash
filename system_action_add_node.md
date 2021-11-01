# Add Node

## Introduction

This API action adds IP address data from an instance to GhostCompute so it can be deployed as a connected node with API automation.

IP addresses must already be routed to a node instance by the hosting provider.

## Request

### Example

This is an example __POST__ request body made to the __/endpoint.php__ path in __JSON__ format.

All values are formatted as __string__ types.

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

### Parameters

## Response

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

### Example

### Parameters
