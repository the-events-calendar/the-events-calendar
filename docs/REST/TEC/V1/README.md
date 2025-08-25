# TEC REST API Documentation

This documentation covers The Events Calendar (TEC) REST API V1, a modern OpenAPI 3.0-compliant REST API implementation for managing events, venues, organizers, and tickets across The Events Calendar plugin suite.

## Table of Contents

- [Architecture Overview](#architecture-overview)
- [Getting Started](#getting-started)
- [Creating Endpoints](creating-endpoints.md)
- [Available Endpoints](endpoints.md)
- [Definitions](definitions.md)

## Architecture Overview

The TEC REST API is built using a sophisticated, enterprise-level architecture:

### Core Components

- **Endpoints**: Interface-driven endpoint classes handling HTTP operations
- **Definitions**: OpenAPI 3.0 schema definitions with composition support
- **Parameters**: Strongly-typed parameter system with validation and sanitization
- **Collections**: Specialized collections for different contexts:
  - `QueryArgumentCollection`: For GET/DELETE query parameters
  - `RequestBodyCollection`: For POST/PUT/PATCH request bodies
  - `PropertiesCollection`: For schema property definitions
- **Validation Pipeline**: Multi-stage validation leveraging WordPress core
- **ORM Integration**: Leverages TEC's ORM system (`tribe_events()`, `tribe_venues()`, etc.)
- **Traits**: Reusable functionality for response handling and data access
- **Controllers**: Plugin-specific controllers managing endpoint registration

### Key Features

- **OpenAPI 3.0.4 Compliance**: Full specification support with auto-generated documentation
- **Interface-Driven Design**: Clear separation of concerns with operation interfaces
- **Strong Type System**: Parameter-based validation with comprehensive type support
- **WordPress Core Integration**: Leverages WordPress REST API validation/sanitization
- **Request Body Collections**: Structured request body handling with automatic bridging
- **Multi-Stage Validation**: Schema validation → Custom filtering → Operation handler
- **Experimental Endpoint Support**: Beta endpoints with acknowledgment headers
- **Caching System**: Version-aware caching with plugin fingerprinting
- **Permission Integration**: WordPress capability system integration

## Getting Started

The API is available under the `/tec/v1` namespace. All endpoints support:

- **Authentication**: WordPress REST API authentication (Basic Auth, Application Passwords)
- **Permissions**: WordPress capability-based access control
- **OpenAPI Documentation**: Auto-generated OpenAPI 3.0.4 specification at `/tec/v1/docs`
- **Type Safety**: Strong parameter validation with specialized parameter types
- **Response Formats**: Consistent JSON responses with appropriate HTTP status codes
- **Pagination**: Standard pagination for collection endpoints
- **Error Handling**: Standardized error responses with detailed messages

## Quick Links

### Plugin-Specific Documentation

#### The Events Calendar
- [Events Endpoints](endpoints/events.md) - Manage events (collection and single)
- [Organizers Endpoints](endpoints/organizers.md) - Manage organizers (collection and single)
- [Venues Endpoints](endpoints/venues.md) - Manage venues (collection and single)
- [Event Definitions](definitions/event.md) - Event data structures
- [Organizer Definitions](definitions/organizer.md) - Organizer data structures
- [Venue Definitions](definitions/venue.md) - Venue data structures

#### Event Tickets
- [Tickets Documentation](/wp-content/plugins/event-tickets/docs/REST/TEC/V1/README.md) - Complete tickets API documentation
- [Tickets Endpoints](/wp-content/plugins/event-tickets/docs/REST/TEC/V1/endpoints/tickets.md) - Manage tickets
- [Ticket Definitions](/wp-content/plugins/event-tickets/docs/REST/TEC/V1/definitions/ticket.md) - Ticket data structures

### Common Library Documentation

- [Interfaces Overview](/wp-content/plugins/the-events-calendar/common/docs/REST/TEC/V1/interfaces.md) - Understanding endpoint interfaces
- [Parameter Types](/wp-content/plugins/the-events-calendar/common/docs/REST/TEC/V1/parameter-types.md) - Type system and collections
- [Validation & Sanitization](/wp-content/plugins/the-events-calendar/common/docs/REST/TEC/V1/validation-sanitization.md) - Request processing pipeline
- [Traits](/wp-content/plugins/the-events-calendar/common/docs/REST/TEC/V1/traits.md) - Reusable functionality
- [Abstract Classes](/wp-content/plugins/the-events-calendar/common/docs/REST/TEC/V1/abstract-classes.md) - Base implementations
- [ORM Usage](/wp-content/plugins/the-events-calendar/common/docs/REST/TEC/V1/orm-usage.md) - Working with the ORM

### Developer Guides

- [Creating New Endpoints](creating-endpoints.md) - Step-by-step guide
- [Endpoints Controller](endpoints-controller.md) - Managing endpoint registration
- [All Endpoints Reference](endpoints.md) - Complete endpoint list

## Base URL

```bash
https://yoursite.com/wp-json/tec/v1
```

## Authentication

The API uses standard WordPress authentication methods:

- Basic authentication
- Application passwords

## Response Format

All responses follow a consistent JSON structure with appropriate HTTP status codes:

- `200 OK` - Successful GET, PUT, PATCH
- `201 Created` - Successful POST with Location header
- `204 No Content` - Successful DELETE (when applicable)
- `400 Bad Request` - Invalid parameters or request
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `410 Gone` - Resource permanently deleted
- `500 Internal Server Error` - Server error
- `501 Not Implemented` - Feature not available

## Supported Plugins

The REST API is available across multiple plugins in The Events Calendar suite:

1. **The Events Calendar** (TEC) - Core events functionality
2. **Event Tickets** (ET) - Ticketing functionality (requires Tickets Commerce)
3. **Events Calendar Pro** (ECP) - Advanced features (future)
4. **Event Tickets Plus** (ETP) - Enhanced ticketing (future)

## Version Information

- **API Version**: V1
- **OpenAPI Version**: 3.0.4
- **Namespace**: `tec/v1`
