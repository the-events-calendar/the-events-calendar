# TEC REST API Endpoints

This document provides an overview of all available endpoints in the TEC REST API V1.

## Base URL

```bash
https://yoursite.com/wp-json/tec/v1
```

## Available Endpoints

### Events

#### Collection Endpoint

- **Path**: `/events`
- **Class**: `TEC\Events\REST\TEC\V1\Endpoints\Events`
- **Operations**: GET, POST
- **Description**: Manage event collections
- [Full Documentation](endpoints/events.md#collection-endpoint)

#### Single Entity Endpoint

- **Path**: `/events/{id}`
- **Class**: `TEC\Events\REST\TEC\V1\Endpoints\Event`
- **Operations**: GET, PUT, DELETE
- **Description**: Manage individual events
- [Full Documentation](endpoints/events.md#single-entity-endpoint)

### Organizers

#### Collection Endpoint

- **Path**: `/organizers`
- **Class**: `TEC\Events\REST\TEC\V1\Endpoints\Organizers`
- **Operations**: GET, POST
- **Description**: Manage organizer collections
- [Full Documentation](endpoints/organizers.md#collection-endpoint)

#### Single Entity Endpoint

- **Path**: `/organizers/{id}`
- **Class**: `TEC\Events\REST\TEC\V1\Endpoints\Organizer`
- **Operations**: GET, PUT, DELETE
- **Description**: Manage individual organizers
- [Full Documentation](endpoints/organizers.md#single-entity-endpoint)

### Venues

#### Collection Endpoint

- **Path**: `/venues`
- **Class**: `TEC\Events\REST\TEC\V1\Endpoints\Venues`
- **Operations**: GET, POST
- **Description**: Manage venue collections
- [Full Documentation](endpoints/venues.md#collection-endpoint)

#### Single Entity Endpoint

- **Path**: `/venues/{id}`
- **Class**: `TEC\Events\REST\TEC\V1\Endpoints\Venue`
- **Operations**: GET, PUT, DELETE
- **Description**: Manage individual venues
- [Full Documentation](endpoints/venues.md#single-entity-endpoint)

### Documentation

#### OpenAPI Documentation

- **Path**: `/docs`
- **Class**: `TEC\Common\REST\TEC\V1\Endpoints\OpenApiDocs`
- **Operations**: GET
- **Description**: OpenAPI 3.0 specification
- [Full Documentation](endpoints/openapi.md)

## Quick Reference

| Endpoint | GET | POST | PUT | DELETE |
|----------|-----|------|-----|--------|
| `/events` | ✓ | ✓ | - | - |
| `/events/{id}` | ✓ | - | ✓ | ✓ |
| `/organizers` | ✓ | ✓ | - | - |
| `/organizers/{id}` | ✓ | - | ✓ | ✓ |
| `/venues` | ✓ | ✓ | - | - |
| `/venues/{id}` | ✓ | - | ✓ | ✓ |

## Authentication

All endpoints support standard WordPress authentication:

- Basic authentication
- Application passwords

## Permissions

Permissions are checked based on WordPress capabilities:

- **Read**: Public or `read` capability
- **Create**: `create_posts` capability
- **Update**: `edit_posts` or `edit_post` capability
- **Delete**: `delete_posts` or `delete_post` capability

## Response Format

All endpoints return JSON responses with appropriate HTTP status codes:

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `403` - Forbidden
- `404` - Not Found

## Pagination

Collection endpoints support pagination:

- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 10, max: 100)

Response headers include:

- `X-WP-Total` - Total number of items
- `X-WP-TotalPages` - Total number of pages
- `Link` - RFC 5988 pagination links
