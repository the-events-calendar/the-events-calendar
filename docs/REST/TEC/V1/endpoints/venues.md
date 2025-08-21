# Venues Endpoints

The Venues endpoints provide CRUD operations for managing event venues.

## Collection Endpoint

### Overview

- **Path**: `/venues`
- **Class**: `TEC\Events\REST\TEC\V1\Endpoints\Venues`
- **Interfaces**: `Readable_Endpoint`, `Creatable_Endpoint`

### GET /venues

Retrieve a paginated list of venues.

#### Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `page` | integer | No | 1 | Page number |
| `per_page` | integer | No | 10 | Items per page (max: 100) |
| `search` | string | No | - | Search term |
| `event` | integer | No | - | Filter by event ID |
| `has_events` | boolean | No | - | Only venues with events |
| `only_with_upcoming` | boolean | No | - | Only venues with upcoming events |
| `status` | array | No | ['publish'] | Post status filter |

#### Response Headers

- `X-WP-Total`: Total number of venues
- `X-WP-TotalPages`: Total number of pages
- `Link`: Pagination links (RFC 5988)

#### Example Request

```bash
GET /wp-json/tec/v1/venues?search=convention&has_events=true
```

#### Example Response

```json
[
    {
        "id": 456,
        "name": "Convention Center",
        "description": "State-of-the-art convention facility...",
        "address": "123 Main Street",
        "city": "New York",
        "state_province": "NY",
        "zip": "10001",
        "country": "United States",
        "phone": "+1-555-7890",
        "website": "https://conventioncenter.com",
        "status": "publish",
        "link": "https://example.com/venue/convention-center/"
    }
]
```

### POST /venues

Create a new venue.

#### Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `name` | string | Yes | - | Venue name |
| `description` | string | No | - | Venue description |
| `address` | string | No | - | Street address |
| `city` | string | No | - | City |
| `state_province` | string | No | - | State or province |
| `zip` | string | No | - | Postal code |
| `country` | string | No | - | Country |
| `phone` | string | No | - | Contact phone |
| `website` | url | No | - | Venue website |
| `status` | string | No | 'publish' | Post status |

#### Example Request

```bash
POST /wp-json/tec/v1/venues
Content-Type: application/json

{
    "name": "Downtown Conference Hall",
    "description": "Modern conference facility in the heart of downtown",
    "address": "456 Business Ave",
    "city": "San Francisco",
    "state_province": "CA",
    "zip": "94105",
    "country": "United States",
    "phone": "+1-415-555-0123",
    "website": "https://downtownhall.com"
}
```

#### Response

- **Status**: 201 Created
- **Body**: Created venue object

## Single Entity Endpoint

### Overview

- **Path**: `/venues/{id}`
- **Class**: `TEC\Events\REST\TEC\V1\Endpoints\Venue`
- **Interface**: `RUD_Endpoint`

### GET /venues/{id}

Retrieve a single venue by ID.

#### Parameters

| Parameter | Type | Required | Location | Description |
|-----------|------|----------|----------|-------------|
| `id` | integer | Yes | Path | Venue ID |

#### Example Request

```bash
GET /wp-json/tec/v1/venues/456
```

#### Response

- **Status**: 200 OK
- **Body**: Venue object

### PUT /venues/{id}

Update an existing venue.

#### Parameters

| Parameter | Type | Required | Location | Description |
|-----------|------|----------|----------|-------------|
| `id` | integer | Yes | Path | Venue ID |

All POST parameters are available for updates (none required).

#### Example Request

```bash
PUT /wp-json/tec/v1/venues/456
Content-Type: application/json

{
    "address": "123 Main Street, Suite 100",
    "phone": "+1-555-7899"
}
```

#### Response

- **Status**: 200 OK
- **Body**: Updated venue object

### DELETE /venues/{id}

Delete a venue.

#### Parameters

| Parameter | Type | Required | Location | Description |
|-----------|------|----------|----------|-------------|
| `id` | integer | Yes | Path | Venue ID |

#### Example Request

```bash
DELETE /wp-json/tec/v1/venues/456
```

#### Response

- **Status**: 200 OK
- **Body**:
```json
{
    "message": "Venue deleted successfully.",
    "id": 456
}
```

## Error Responses

| Status | Error | Description |
|--------|-------|-------------|
| 400 | Bad Request | Invalid parameters |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Venue not found |
| 500 | Internal Error | Server error |

## Permissions

- **Read**: Public or `read` capability
- **Create**: `create_posts` capability
- **Update**: `edit_post` capability for specific venue
- **Delete**: `delete_post` capability for specific venue

## Data Structure

### Venue Object

```json
{
    "id": 456,
    "name": "Venue Name",
    "description": "Detailed description...",
    "address": "123 Main Street",
    "city": "New York",
    "state_province": "NY",
    "zip": "10001",
    "country": "United States",
    "phone": "+1-555-7890",
    "website": "https://venuename.com",
    "status": "publish",
    "date": "2024-01-10T09:00:00",
    "date_gmt": "2024-01-10T14:00:00",
    "modified": "2024-01-18T11:30:00",
    "modified_gmt": "2024-01-18T16:30:00",
    "link": "https://example.com/venue/venue-name/",
    "_links": {
        "self": [{
            "href": "https://example.com/wp-json/tec/v1/venues/456"
        }],
        "collection": [{
            "href": "https://example.com/wp-json/tec/v1/venues"
        }]
    }
}
```

## Location Fields

The venue location fields map to WordPress meta fields:

- `address` → `_VenueAddress`
- `city` → `_VenueCity`
- `state_province` → `_VenueStateProvince`
- `zip` → `_VenueZip`
- `country` → `_VenueCountry`

## Related Endpoints

- [Events](events.md) - Events at venues
- [Organizers](organizers.md) - Event organizers
