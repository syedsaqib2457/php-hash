# Add Node

## Introduction

This API action adds IP address data from an instance to GhostCompute so it can be deployed as a connected node with API automation.

IP addresses must already be routed to a node instance by the hosting provider.

## Request

### Example

This is an example *POST* request body made to the */endpoint.php* path in *JSON* format.

All values are formatted as *string* types.

``` javascript
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

### Example

### Parameters
