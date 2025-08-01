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
- **Interfaces**: `Readable_Endpoint`, `Creatable_Endpoint`
- **Operations**: GET, POST
- **Description**: Manage event collections
- **ORM**: Uses `tribe_events()` via `With_Events_ORM` trait
- **Model**: `Tribe\Events\Models\Post_Types\Event`
- **Post Type**: `tribe_events`
- **Operation IDs**: `getEvents` (GET), `createEvent` (POST)
- [Full Documentation](endpoints/events.md#collection-endpoint)

#### Single Entity Endpoint

- **Path**: `/events/{id}`
- **Class**: `TEC\Events\REST\TEC\V1\Endpoints\Event`
- **Interface**: `RUD_Endpoint`
- **Operations**: GET, PUT, DELETE
- **Description**: Manage individual events
- **ORM**: Uses `tribe_events()` via `With_Events_ORM` trait
- **Model**: `Tribe\Events\Models\Post_Types\Event`
- **Post Type**: `tribe_events`
- **Operation IDs**: `getEvent` (GET), `updateEvent` (PUT), `deleteEvent` (DELETE)
- [Full Documentation](endpoints/events.md#single-entity-endpoint)

### Organizers

#### Collection Endpoint

- **Path**: `/organizers`
- **Class**: `TEC\Events\REST\TEC\V1\Endpoints\Organizers`
- **Interfaces**: `Readable_Endpoint`, `Creatable_Endpoint`
- **Operations**: GET, POST
- **Description**: Manage organizer collections
- **ORM**: Uses `tribe_organizers()` via `With_Organizers_ORM` trait
- **Model**: `Tribe\Events\Models\Post_Types\Organizer`
- **Post Type**: `tribe_organizer`
- **Operation IDs**: `getOrganizers` (GET), `createOrganizer` (POST)
- [Full Documentation](endpoints/organizers.md#collection-endpoint)

#### Single Entity Endpoint

- **Path**: `/organizers/{id}`
- **Class**: `TEC\Events\REST\TEC\V1\Endpoints\Organizer`
- **Interface**: `RUD_Endpoint`
- **Operations**: GET, PUT, DELETE
- **Description**: Manage individual organizers
- **ORM**: Uses `tribe_organizers()` via `With_Organizers_ORM` trait
- **Model**: `Tribe\Events\Models\Post_Types\Organizer`
- **Post Type**: `tribe_organizer`
- **Operation IDs**: `getOrganizer` (GET), `updateOrganizer` (PUT), `deleteOrganizer` (DELETE)
- [Full Documentation](endpoints/organizers.md#single-entity-endpoint)

### Venues

#### Collection Endpoint

- **Path**: `/venues`
- **Class**: `TEC\Events\REST\TEC\V1\Endpoints\Venues`
- **Interfaces**: `Readable_Endpoint`, `Creatable_Endpoint`
- **Operations**: GET, POST
- **Description**: Manage venue collections
- **ORM**: Uses `tribe_venues()` via `With_Venues_ORM` trait
- **Model**: `Tribe\Events\Models\Post_Types\Venue`
- **Post Type**: `tribe_venue`
- **Operation IDs**: `getVenues` (GET), `createVenue` (POST)
- [Full Documentation](endpoints/venues.md#collection-endpoint)

#### Single Entity Endpoint

- **Path**: `/venues/{id}`
- **Class**: `TEC\Events\REST\TEC\V1\Endpoints\Venue`
- **Interface**: `RUD_Endpoint`
- **Operations**: GET, PUT, DELETE
- **Description**: Manage individual venues
- **ORM**: Uses `tribe_venues()` via `With_Venues_ORM` trait
- **Model**: `Tribe\Events\Models\Post_Types\Venue`
- **Post Type**: `tribe_venue`
- **Operation IDs**: `getVenue` (GET), `updateVenue` (PUT), `deleteVenue` (DELETE)
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
| `/docs` | ✓ | - | - | - |

## Implementation Details

### Controller Registration

The Events API is registered through:

- **Main Controller**: `TEC\Events\REST\TEC\V1\Controller`
- **Endpoints Controller**: `TEC\Events\REST\TEC\V1\Endpoints`

### Traits Used

The Events API endpoints use several traits for common functionality:

- **`With_Events_ORM`**: Provides access to `tribe_events()` ORM
- **`With_Organizers_ORM`**: Provides access to `tribe_organizers()` ORM
- **`With_Venues_ORM`**: Provides access to `tribe_venues()` ORM
- **`With_Transform_Organizers_And_Venues`**: Handles transformation of related entities
- **`Read_Archive_Response`**: Standard archive read operations
- **`Create_Entity_Response`**: Standard entity creation
- **`Read_Entity_Response`**: Standard single entity read
- **`Update_Entity_Response`**: Standard entity update
- **`Delete_Entity_Response`**: Standard entity deletion

### Tags

All Events Calendar endpoints are tagged with:

- **Tag Class**: `TEC\Events\REST\TEC\V1\Tags\TEC_Tag`
- **Tag Name**: "Events"
- **Description**: "These operations are introduced by The Events Calendar."

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
