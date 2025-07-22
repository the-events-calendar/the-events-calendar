# TEC REST API Documentation

This documentation covers The Events Calendar (TEC) REST API V1, a modern REST API implementation for managing events, venues, and organizers.

## Table of Contents

- [Architecture Overview](#architecture-overview)
- [Getting Started](#getting-started)
- [Creating Endpoints](creating-endpoints.md)
- [Available Endpoints](endpoints.md)
- [Definitions](definitions.md)

## Architecture Overview

The TEC REST API is built using a modular architecture with clear separation of concerns:

- **Endpoints**: Handle HTTP requests and responses
- **Definitions**: Define data structures for OpenAPI documentation
- **Parameters**: Type-safe parameter definitions
- **ORM**: Data access layer using TEC's ORM system
- **Traits**: Reusable functionality for common operations

## Getting Started

The API is available under the `/tec/v1` namespace. All endpoints support:

- **Authentication**: Uses WordPress REST API authentication
- **Permissions**: Role-based access control
- **OpenAPI Documentation**: Auto-generated API documentation
- **Type Safety**: Strong parameter validation

## Quick Links

### Endpoints

- [Events Endpoints](endpoints/events.md) - Manage events (collection and single)
- [Organizers Endpoints](endpoints/organizers.md) - Manage organizers (collection and single)
- [Venues Endpoints](endpoints/venues.md) - Manage venues (collection and single)

### Core Components

- [Interfaces Overview](interfaces.md) - Understanding endpoint interfaces
- [Creating New Endpoints](creating-endpoints.md) - Step-by-step guide

## Base URL

```bash
https://yoursite.com/wp-json/tec/v1
```

## Authentication

The API uses standard WordPress authentication methods:

- Basic authentication
- Application passwords

## Response Format

All responses follow a consistent JSON structure with appropriate HTTP status codes.
