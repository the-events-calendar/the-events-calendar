# Venue Definition

The Venue definition describes the structure of a venue object in the TEC REST API.

## Class

`TEC\Events\REST\TEC\V1\Documentation\Venue_Definition`

## Schema

```json
{
    "type": "object",
    "properties": {
        "id": {
            "type": "integer",
            "description": "Unique identifier for the venue"
        },
        "name": {
            "type": "string",
            "description": "Venue name"
        },
        "description": {
            "type": "string",
            "description": "Venue description"
        },
        "address": {
            "type": "string",
            "description": "Street address"
        },
        "city": {
            "type": "string",
            "description": "City"
        },
        "state_province": {
            "type": "string",
            "description": "State or province"
        },
        "zip": {
            "type": "string",
            "description": "Postal/ZIP code"
        },
        "country": {
            "type": "string",
            "description": "Country"
        },
        "phone": {
            "type": "string",
            "description": "Contact phone number"
        },
        "website": {
            "type": "string",
            "format": "uri",
            "description": "Venue website URL"
        },
        "status": {
            "type": "string",
            "enum": ["publish", "pending", "draft", "future", "private"],
            "description": "Venue post status"
        },
        "date": {
            "type": "string",
            "format": "date-time",
            "description": "Venue creation date"
        },
        "date_gmt": {
            "type": "string",
            "format": "date-time",
            "description": "Venue creation date (GMT)"
        },
        "modified": {
            "type": "string",
            "format": "date-time",
            "description": "Venue last modified date"
        },
        "modified_gmt": {
            "type": "string",
            "format": "date-time",
            "description": "Venue last modified date (GMT)"
        },
        "link": {
            "type": "string",
            "format": "uri",
            "description": "Venue permalink"
        },
        "_links": {
            "type": "object",
            "description": "HAL links for the venue"
        }
    },
    "required": ["id", "name"]
}
```

## Example

```json
{
    "id": 456,
    "name": "Grand Convention Center",
    "description": "<p>The Grand Convention Center is a state-of-the-art facility featuring 500,000 square feet of exhibition space, 50 meeting rooms, and a 5,000-seat auditorium.</p>",
    "address": "123 Main Street",
    "city": "New York",
    "state_province": "NY",
    "zip": "10001",
    "country": "United States",
    "phone": "+1-212-555-7890",
    "website": "https://grandconventioncenter.com",
    "status": "publish",
    "date": "2024-01-05T08:00:00",
    "date_gmt": "2024-01-05T13:00:00",
    "modified": "2024-01-25T10:15:00",
    "modified_gmt": "2024-01-25T15:15:00",
    "link": "https://example.com/venue/grand-convention-center/",
    "_links": {
        "self": [
            {
                "href": "https://example.com/wp-json/tec/v1/venues/456"
            }
        ],
        "collection": [
            {
                "href": "https://example.com/wp-json/tec/v1/venues"
            }
        ],
        "events": [
            {
                "href": "https://example.com/wp-json/tec/v1/events?venue=456"
            }
        ]
    }
}
```

## WordPress Meta Mapping

The venue location fields map to WordPress post meta:

- `address` → `_VenueAddress`
- `city` → `_VenueCity`
- `state_province` → `_VenueStateProvince`
- `zip` → `_VenueZip`
- `country` → `_VenueCountry`
- `phone` → `_VenuePhone`
- `website` → `_VenueURL`

## Usage

This definition is used for:

- Single venue responses (`GET /venues/{id}`)
- Venue creation responses (`POST /venues`)
- Venue update responses (`PUT /venues/{id}`)
- Venue collection items (`GET /venues`)
- Embedded venues in event responses

## Common Use Cases

### Creating a Venue

```json
{
    "name": "Community Theater",
    "description": "Historic theater in downtown",
    "address": "456 Arts Avenue",
    "city": "Boston",
    "state_province": "MA",
    "zip": "02134",
    "country": "United States",
    "phone": "+1-617-555-1234"
}
```

### Updating a Venue

```json
{
    "address": "456 Arts Avenue, Suite 200",
    "website": "https://communitytheater.org"
}
```

## Location Data

The venue definition includes comprehensive location data:

- Full address components for mapping
- International support (state_province vs state)
- Flexible postal code format
- Country field for international venues

## Related Definitions

- [Event Definition](event.md) - Events can have multiple venues
