# Organizers Endpoints

The Organizers endpoints provide CRUD operations for managing event organizers.

## Collection Endpoint

### Overview

- **Path**: `/organizers`
- **Class**: `TEC\Events\REST\TEC\V1\Endpoints\Organizers`
- **Interfaces**: `Readable_Endpoint`, `Creatable_Endpoint`

### GET /organizers

Retrieve a paginated list of organizers.

#### Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | No | 1 | Page number |
| `per_page` | integer | No | 10 | Items per page (max: 100) |
| `search` | string | No | - | Search term |
| `event` | integer | No | - | Filter by event ID |
| `has_events` | boolean | No | - | Only organizers with events |
| `only_with_upcoming` | boolean | No | - | Only organizers with upcoming events |
| `status` | array | No | ['publish'] | Post status filter |

#### Response Headers

- `X-WP-Total`: Total number of organizers
- `X-WP-TotalPages`: Total number of pages
- `Link`: Pagination links (RFC 5988)

#### Example Request

```bash
GET /wp-json/tec/v1/organizers?search=tech&has_events=true
```

#### Example Response

```json
[
    {
        "id": 789,
        "name": "Tech Events Inc.",
        "description": "We organize technology conferences...",
        "email": "info@techevents.com",
        "phone": "+1-555-0123",
        "website": "https://techevents.com",
        "status": "publish",
        "link": "https://example.com/organizer/tech-events-inc/"
    }
]
```

### POST /organizers

Create a new organizer.

#### Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `name` | string | Yes | - | Organizer name |
| `description` | string | No | - | Organizer description |
| `email` | email | No | - | Contact email |
| `phone` | string | No | - | Contact phone |
| `website` | url | No | - | Organizer website |
| `status` | string | No | 'publish' | Post status |

#### Example Request

```bash
POST /wp-json/tec/v1/organizers
Content-Type: application/json

{
    "name": "Local Community Center",
    "description": "Organizing community events since 1995",
    "email": "events@communitycenter.org",
    "phone": "+1-555-0456",
    "website": "https://communitycenter.org"
}
```

#### Response

- **Status**: 201 Created
- **Body**: Created organizer object

## Single Entity Endpoint

### Overview

- **Path**: `/organizers/{id}`
- **Class**: `TEC\Events\REST\TEC\V1\Endpoints\Organizer`
- **Interface**: `RUD_Endpoint`

### GET /organizers/{id}

Retrieve a single organizer by ID.

#### Parameters

| Parameter | Type | Required | Location | Description |
|-----------|------|----------|----------|-------------|
| `id` | integer | Yes | Path | Organizer ID |

#### Example Request

```bash
GET /wp-json/tec/v1/organizers/789
```

#### Response

- **Status**: 200 OK
- **Body**: Organizer object

### PUT /organizers/{id}

Update an existing organizer.

#### Parameters

| Parameter | Type | Required | Location | Description |
|-----------|------|----------|----------|-------------|
| `id` | integer | Yes | Path | Organizer ID |

All POST parameters are available for updates (none required).

#### Example Request

```bash
PUT /wp-json/tec/v1/organizers/789
Content-Type: application/json

{
    "email": "newemail@techevents.com",
    "phone": "+1-555-9999"
}
```

#### Response

- **Status**: 200 OK
- **Body**: Updated organizer object

### DELETE /organizers/{id}

Delete an organizer.

#### Parameters

| Parameter | Type | Required | Location | Description |
|-----------|------|----------|----------|-------------|
| `id` | integer | Yes | Path | Organizer ID |

#### Example Request

```bash
DELETE /wp-json/tec/v1/organizers/789
```

#### Response

- **Status**: 200 OK
- **Body**:

```json
{
    "message": "Organizer deleted successfully.",
    "id": 789
}
```

## Error Responses

| Status | Error | Description |
|--------|-------|-------------|
| 400 | Bad Request | Invalid parameters |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Organizer not found |
| 500 | Internal Error | Server error |

## Permissions

- **Read**: Public or `read` capability
- **Create**: `create_posts` capability
- **Update**: `edit_post` capability for specific organizer
- **Delete**: `delete_post` capability for specific organizer

## Data Structure

### Organizer Object

```json
{
    "id": 789,
    "name": "Organizer Name",
    "description": "Detailed description...",
    "email": "contact@organizer.com",
    "phone": "+1-555-0123",
    "website": "https://organizer.com",
    "status": "publish",
    "date": "2024-01-15T10:00:00",
    "date_gmt": "2024-01-15T15:00:00",
    "modified": "2024-01-20T14:30:00",
    "modified_gmt": "2024-01-20T19:30:00",
    "link": "https://example.com/organizer/organizer-name/",
    "_links": {
        "self": [{
            "href": "https://example.com/wp-json/tec/v1/organizers/789"
        }],
        "collection": [{
            "href": "https://example.com/wp-json/tec/v1/organizers"
        }]
    }
}
```

## Related Endpoints

- [Events](events.md) - Events associated with organizers
- [Venues](venues.md) - Venues for events
