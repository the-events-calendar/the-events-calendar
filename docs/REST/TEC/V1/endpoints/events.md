# Events Endpoints

The Events endpoints provide CRUD operations for managing events in The Events Calendar.

## Collection Endpoint

### Overview

- **Path**: `/events`
- **Class**: `TEC\Events\REST\TEC\V1\Endpoints\Events`
- **Interfaces**: `Readable_Endpoint`, `Creatable_Endpoint`

### GET /events

Retrieve a paginated list of events.

#### Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | No | 1 | Page number |
| `per_page` | integer | No | 10 | Items per page (max: 100) |
| `start_date` | datetime | No | - | Events starting after this date |
| `end_date` | datetime | No | - | Events ending before this date |
| `search` | string | No | - | Search term |
| `categories` | array | No | - | Filter by category IDs |
| `tags` | array | No | - | Filter by tag IDs |
| `venue` | array | No | - | Filter by venue IDs |
| `organizer` | array | No | - | Filter by organizer IDs |
| `featured` | boolean | No | - | Filter featured events |
| `status` | array | No | ['publish'] | Post status filter |
| `post_parent` | integer | No | - | Parent post ID |
| `starts_before` | datetime | No | - | Events starting before |
| `starts_after` | datetime | No | - | Events starting after |
| `ends_before` | datetime | No | - | Events ending before |
| `ends_after` | datetime | No | - | Events ending after |
| `ticketed` | boolean | No | - | Events with tickets |
| `orderby` | string | No | 'event_date' | Sort field |
| `order` | string | No | 'ASC' | Sort order (ASC/DESC) |

#### Response Headers

- `X-WP-Total`: Total number of events
- `X-WP-TotalPages`: Total number of pages
- `Link`: Pagination links (RFC 5988)

#### Example Request

```bash
GET /wp-json/tec/v1/events?start_date=2024-01-01&featured=true&per_page=20
```

#### Example Response

```json
[
    {
        "id": 123,
        "title": "Annual Conference",
        "description": "Join us for our annual conference...",
        "start_date": "2024-03-15T09:00:00",
        "end_date": "2024-03-15T17:00:00",
        "all_day": false,
        "timezone": "America/New_York",
        "venues": [...],
        "organizers": [...],
        "featured": true,
        "cost": "$50",
        "website": "https://example.com/conference"
    }
]
```

### POST /events

Create a new event.

#### Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `title` | string | Yes | - | Event title |
| `description` | string | No | - | Event description/content |
| `excerpt` | string | No | - | Event excerpt |
| `start_date` | datetime | Yes | - | Event start date/time |
| `end_date` | datetime | Yes | - | Event end date/time |
| `all_day` | boolean | No | false | All-day event flag |
| `timezone` | string | No | - | Event timezone |
| `venue` | array | No | - | Venue IDs |
| `organizer` | array | No | - | Organizer IDs |
| `featured` | boolean | No | false | Featured status |
| `status` | string | No | 'publish' | Post status |
| `categories` | array | No | - | Category IDs |
| `tags` | array | No | - | Tag IDs |
| `website` | string | No | - | Event website URL |
| `cost` | string | No | - | Event cost |

#### Example Request

```bash
POST /wp-json/tec/v1/events
Content-Type: application/json

{
    "title": "Workshop: Introduction to REST APIs",
    "description": "Learn the basics of REST API development...",
    "start_date": "2024-04-01T14:00:00",
    "end_date": "2024-04-01T16:00:00",
    "venue": [456],
    "organizer": [789],
    "cost": "Free"
}
```

#### Response

- **Status**: 201 Created
- **Body**: Created event object

## Single Entity Endpoint

### Overview

- **Path**: `/events/{id}`
- **Class**: `TEC\Events\REST\TEC\V1\Endpoints\Event`
- **Interface**: `RUD_Endpoint`

### GET /events/{id}

Retrieve a single event by ID.

#### Parameters

| Parameter | Type | Required | Location | Description |
|-----------|------|----------|----------|-------------|
| `id` | integer | Yes | Path | Event ID |

#### Example Request

```bash
GET /wp-json/tec/v1/events/123
```

#### Response

- **Status**: 200 OK
- **Body**: Event object

### PUT /events/{id}

Update an existing event.

#### Parameters

| Parameter | Type | Required | Location | Description |
|-----------|------|----------|----------|-------------|
| `id` | integer | Yes | Path | Event ID |

All POST parameters are available for updates (none required).

#### Example Request

```bash
PUT /wp-json/tec/v1/events/123
Content-Type: application/json

{
    "title": "Updated Workshop Title",
    "featured": true
}
```

#### Response

- **Status**: 200 OK
- **Body**: Updated event object

### DELETE /events/{id}

Delete an event.

#### Parameters

| Parameter | Type | Required | Location | Description |
|-----------|------|----------|----------|-------------|
| `id` | integer | Yes | Path | Event ID |

#### Example Request

```bash
DELETE /wp-json/tec/v1/events/123
```

#### Response

- **Status**: 200 OK
- **Body**:
```json
{
    "message": "Event deleted successfully.",
    "id": 123
}
```

## Error Responses

| Status | Error | Description |
|--------|-------|-------------|
| 400 | Bad Request | Invalid parameters |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Event not found |
| 500 | Internal Error | Server error |

## Permissions

- **Read**: Public or `read` capability
- **Create**: `create_posts` capability
- **Update**: `edit_post` capability for specific event
- **Delete**: `delete_post` capability for specific event

## Related Endpoints

- [Organizers](organizers.md) - Manage event organizers
- [Venues](venues.md) - Manage event venues
