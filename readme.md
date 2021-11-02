Currently in a pre-release development phase.

## Contents

Table of contents is in development

## Features

### An Essential API Framework for Cloud Privacy and Security

Develop secure applications using a free open-source backend API framework built with excessive simplicity.

### Build and Orchestrate Powerful Cloud Applications the Right Way

Use built-in API actions to automate deployment, monitoring and scaling for sensitive network infrastructure in containerless Linux environments without sacrificing performance, privacy or security. 

GhostCompute is a backend API framework for cloud applications as well as a standalone automation system for controlling cloud network infrastructure from the same API.

### Make Clean API Requests to a Single Endpoint in JSON Format

#### Request Example

```json
{
    "action": "add_node",
    "authentication_token": "123456789",
    "data": {
        "external_ip_address_version_4": "0.0.0.0",
        "internal_ip_address_version_4": "10.10.10.10"
    }
}
```

#### Response Example

```json
{
    "data": {
        "id": "unique_id_1",
        [ ... ]
    },
    "message": "Node added successfully.",
    "status_authenticated": "1",
    "status_valid": "1"
}
```

### Transform Servers Into Automated Nodes Programmatically

Remove complexity from these server-side components while adding valuable functionality with API automation.

#### Blockchain
#### Databases
#### Forwarding Proxies
#### Load Balancers
#### Recursive DNS
#### Reverse Proxies
#### Tor Relays
#### Virtualization
#### VPNs

### Write Better Backend Code for Cloud Applications

There shouldn't be a learning curve for developers when designing secure backend cloud systems at scale.

## Overview

GhostCompute is a minimal yet powerful framework designed to enhance performance, privacy and security for cloud applications with these concepts.

### Automated Cloud Infrastructure API

There shouldn't be a learning curve for developers when designing secure backend cloud systems at scale.

Write better backend code for cloud applications by using GhostCompute API automation with these built-in benefits,

#### Deployment

#### Monitoring

#### Performance

#### Privacy

#### Scaling

#### Security

### Isolated Containerless Environments

Containerization is essential for cloud applications, but not for all aspects.

GhostCompute deploys critical networking components such as recursive DNS in native, containerless Linux environments to avoid performance and security issues from emulated containers.

This simplifies firewall security and system optimization while enabling full control from a self-hosted API for authentication, logging, monitoring, rate limiting and specific component actions.

GhostCompute can be deployed in containers if required for scaling API requests.

### Minimized Dependence on External Services

Custom actions are built into GhostCompute for managing databases, monitoring performance and validating data.

Decreasing the usage of external services wherever possible for application monitoring, container hosting, load balancers, managed cloud databases, proxies and package management increases privacy and security for cloud applications.

### Redefined Coding Standards for Simplicity 

GhostCompute is built using native PHP with a consistent procedural style and clean modular design. Complexity with secure code structure is solved by avoiding fundamentals such as classes, controllers, namespaces, object-oriented design, routing and view templates.

Complexity with secure relational database structure is solved by using MySQL as "dumb storage" and processing relational data with fast PHP array indexes.

Raw SQL commands are fast and secure with a simplified PHP interface for complex query conditions while avoiding slow, complex prepared statements and SQL joins.

For rapid development using the PHP backend framework, developers can add new system actions as files in the root directory.

API requests to backend actions are fast and secure by only including database connections and validation functions specific to each action.

For automating backend database actions and network components using other programming languages, API requests are made to a single endpoint URL using POST data with a specified action and authentication_token in JSON format.

This allows clean, fast integrations and prevents complications caused by using REST APIs with multiple endpoint request URLs, request headers, request methods and versions.

## Usage

Usage instructions are in development

### API Documentation

API documentation is in development.

### PHP Framework Documentation

PHP framework documentation is in development.
