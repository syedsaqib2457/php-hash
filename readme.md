## GhostCompute Framework

Currently in a pre-release development phase.

API documentation and usage tutorials are in the [documentation subdirectory](https://github.com/ghostcompute/framework/tree/main/documentation).

### An Essential API Framework for Cloud Privacy and Security

Develop secure applications using a backend API framework built with excessive simplicity.

### Build and Orchestrate Powerful Cloud Applications the Right Way

Use built-in API actions to automate deployment, monitoring and scaling for sensitive network infrastructure in containerless Linux environments without sacrificing performance, privacy or security. 

GhostCompute is a backend API framework for cloud applications as well as a standalone automation system for controlling cloud network infrastructure and mining cryptocurrency from the same API.

### Make Clean API Requests to a Single Endpoint in JSON Format

#### Request Example

```json
{
    "action": "add_node",
    "data": {
        "external_ip_address_version_4": "0.0.0.0",
        "external_ip_address_version_6": "::0",
        "internal_ip_address_version_4": "10.10.10.10",
        "internal_ip_address_version_6": "::1",
        "node_id": "unique_id_1"
    },
    "system_user_authentication_token": "123456789"
}

```

#### Response Example

```json
{
    "authenticated_status": "1",
    "data": {
        "activated_status": "0",
        "cpu_capacity_megahertz": "",
        "cpu_core_count": "",
        "deployed_status": "0",
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

### Transform Servers Into Automated Nodes Programmatically

Remove complexity from these server-side components while adding valuable functionality with API automation.

#### Blockchain

Blockchain features are in development.

#### Databases

Database features are in development.

#### Forwarding Proxies

Deploy forwarding proxies on any network or cloud host with built-in monitoring, performance, privacy, scaling and security features.

Both IPv4 and IPv6 addresses are supported with public and private networking.

Create user authentication groups with multiple username:password combinations and whitelisted source IPs for security.

Optionally limit access to specific listening ports and external websites to protect proxy IP addresses from blacklisting.

Forwarding proxy components use secure internal recursive DNS processes with optimized load balancing.

TCP and UDP requests are both supported where applicable.

#### Load Balancers

Deploy load balancers on any network or cloud host with built-in monitoring, performance, privacy, scaling and security features.

Both IPv4 and IPv6 addresses are supported with public and private networking.

Create custom load balancers for a list of destination IP addresses.

Configure rotation settings to automatically route each request evenly, randomly or with specific load priorities.

Load balancer performance can slow down when routing requests to a large cluster of nodes.

Split firewalls and internal forwarders allow a single load balancer node to forward TCP and UDP requests to hundreds of destinations.

#### Recursive DNS

Deploy recursive DNS on any network or cloud host with built-in monitoring, performance, privacy, scaling and security features.

Both IPv4 and IPv6 addresses are supported with public and private networking on TCP and UDP.

Create user authentication groups with custom listening IPs, listening ports and whitelisted source IPs for security.

Either configure recursive DNS processes with private access to use with other components or start an open recursive DNS service.

#### Reverse Proxies

Deploy reverse proxies on any network or cloud host with built-in monitoring, performance, privacy, scaling and security features.

Both IPv4 and IPv6 addresses are supported with public and private networking.

Create user authentication groups with multiple username:password combinations and whitelisted source IPs for security.

Optionally limit access to specific listening ports and external websites to protect proxy IP addresses from blacklisting.

Reverse proxy components use secure internal recursive DNS processes with optimized load balancing.

Public recursive DNS processes can be configured with custom IP addresses and ports to accept connections using HTTP, HTTPS and SOCKS simultaneously5.

TCP and UDP requests are both supported where applicable.

#### Tor Relays

Tor relay features are in development.

#### Virtualization

Virtualization features are in development.

#### VPNs

VPN features are in development.

### Understand the Benefits of an Automated Cloud Infrastructure API

Use GhostCompute API automation with these built-in benefits for deployed nodes.

#### Deployment

Simply transform dedicated servers and virtual machines with IPv4 and IPv6 addresses into powerfully-automated nodes.

Learning how to use GhostCompute is much easier than deploying, monitoring, securing and scaling network components manually.

Securely connect each node with one line of code that automatically downloads standalone open-source scripts for automation to add, edit and delete nodes and node security rules.

Easily deploy widely-used internet components such as forwarding proxies with HTTP and SOCKS support, recursive DNS and reverse proxies.

#### Monitoring

Monitor bandwidth usage, CPU usage, memory usage, storage capacity and performance metrics for each user on each node.

Receive proactive alerts, optimization suggestions and intelligent insights into potential bottlenecks while scaling.

Track bandwidth usage, destination IPs, destination URLs, latency, source IPs and more for each request through deployed node components.

Request logs can be enabled and disabled granularly for each node user to enhance privacy.

#### Performance

Node component processes are configured and reconfigured using native PHP code with fast indexing methods built for scale.

Each deployed node process will never access an external authentication database directly.

This creates faster response times for scaling requests, efficient resource usage for monitoring and enhanced security.

Nodes are fast and stable with a secure automatic load balancing method for internal processes to maximize throughput with low latency.

Sophisticated firewall rule sets automatically forward new connections to redundant internal processes when reconfiguring settings.

Settings aren’t applied to a node process until all existing connections to that process are closed to minimize connection errors.

#### Privacy

Add an extra layer of privacy by disabling or self-hosting logs for forwarding proxies, recursive DNS, and reverse proxies.

Become your own surveillance system by taking control of access logs on each node component.

Only deploy nodes on instances that you trust to avoid intermediary monitoring by third parties.

#### Scaling

Primary and additional IPv4 and IPv6 addresses can be configured for each deployed node in dynamic networking environments.

Create as many nodes as required to handle internet traffic demands at any scale.

Prevent wasting resources from underutilization with built-in performance optimizations and monitoring.

Automated internal IP assignment and validation prevents misconfiguration errors such as duplicate ports and IP addresses.

Each node component can have its own set of open and closed ports.

Opening additional ports improves performance at scale if node monitoring reveals port congestion as a constraint.

#### Security

Each deployed node is automated by interacting with open-source code to make sure there are no unauthorized backdoor actions.

Prevent IP addresses on each node from getting blacklisted on specific websites and networks with granular egress request limitation rules for forwarding proxies and reverse proxies.

After a request limit is exceeded, requests are automatically blocked for a custom amount of time.

Each node is protected with automated firewall rules to block cyber attacks and exploits without impacting node performance.

Unused ports are closed and multi-layer firewalls protect against brute-forcing, denial-of-service, ping-of-death and spoofing attacks.

Extensive validation is processed when nodes send monitoring data to make sure a compromised node can't attack other nodes with poisoned data.

### Write Better Backend Code for Cloud Applications

GhostCompute is a minimal yet powerful framework designed to enhance performance, privacy and security for cloud applications with these concepts.

#### Isolated Containerless Environments

Containerization is essential for cloud applications, but not for all aspects.

GhostCompute deploys critical networking components such as recursive DNS in native, containerless Linux environments to avoid performance and security issues from emulated containers.

This simplifies firewall security and system optimization while enabling full control from a self-hosted API for authentication, logging, monitoring, rate limiting and specific component actions.

GhostCompute can be deployed in containers if required for scaling API requests.

#### Minimized Dependence on External Services

Custom actions are built into GhostCompute for managing databases, monitoring performance and validating data.

Decreasing the usage of external services wherever possible for application monitoring, container hosting, load balancers, managed cloud databases, proxies and package management increases privacy and security for cloud applications.

#### Redefined Coding Standards for Simplicity

GhostCompute is built using native PHP with a consistent procedural style and clean modular design.

Complexity with secure code structure is solved by avoiding fundamentals such as classes, controllers, namespaces, object-oriented design, routing and view templates.

Complexity with secure relational database structure is solved by using MySQL as "dumb storage" with string types only and processing relational data with fast PHP array indexes.

Raw SQL commands are fast and secure with a simplified PHP interface for complex query conditions while avoiding slow, complex prepared statements and SQL joins.

For rapid development using the PHP backend framework, developers can add new system actions as files in the root directory.

API requests to backend actions are fast and secure by only including database connections and validation functions specific to each action.

For automating backend database actions and network components using other programming languages, API requests are made to a single endpoint URL using POST data with a specified action and authentication_token in JSON format.

This allows clean, fast integrations and prevents complications caused by using REST APIs with multiple endpoint request URLs, request headers, request methods and versions.
