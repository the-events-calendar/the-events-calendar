# Organizer Definition

The Organizer definition describes the structure of an organizer object in the TEC REST API.

## Class

`TEC\Events\REST\TEC\V1\Documentation\Organizer_Definition`

## Schema

```json
{
    "type": "object",
    "properties": {
        "id": {
            "type": "integer",
            "description": "Unique identifier for the organizer"
        },
        "name": {
            "type": "string",
            "description": "Organizer name"
        },
        "description": {
            "type": "string",
            "description": "Organizer description/bio"
        },
        "email": {
            "type": "string",
            "format": "email",
            "description": "Contact email address"
        },
        "phone": {
            "type": "string",
            "description": "Contact phone number"
        },
        "website": {
            "type": "string",
            "format": "uri",
            "description": "Organizer website URL"
        },
        "status": {
            "type": "string",
            "enum": ["publish", "pending", "draft", "future", "private"],
            "description": "Organizer post status"
        },
        "date": {
            "type": "string",
            "format": "date-time",
            "description": "Organizer creation date"
        },
        "date_gmt": {
            "type": "string",
            "format": "date-time",
            "description": "Organizer creation date (GMT)"
        },
        "modified": {
            "type": "string",
            "format": "date-time",
            "description": "Organizer last modified date"
        },
        "modified_gmt": {
            "type": "string",
            "format": "date-time",
            "description": "Organizer last modified date (GMT)"
        },
        "link": {
            "type": "string",
            "format": "uri",
            "description": "Organizer permalink"
        },
        "_links": {
            "type": "object",
            "description": "HAL links for the organizer"
        }
    },
    "required": ["id", "name"]
}
```

## Example

```json
{
    "id": 789,
    "name": "Tech Events Inc.",
    "description": "<p>Tech Events Inc. has been organizing premier technology conferences and workshops since 2010. We specialize in bringing together industry leaders, innovators, and enthusiasts.</p>",
    "email": "info@techevents.com",
    "phone": "+1-555-0123",
    "website": "https://techevents.com",
    "status": "publish",
    "date": "2024-01-10T09:00:00",
    "date_gmt": "2024-01-10T14:00:00",
    "modified": "2024-01-18T11:30:00",
    "modified_gmt": "2024-01-18T16:30:00",
    "link": "https://example.com/organizer/tech-events-inc/",
    "_links": {
        "self": [
            {
                "href": "https://example.com/wp-json/tec/v1/organizers/789"
            }
        ],
        "collection": [
            {
                "href": "https://example.com/wp-json/tec/v1/organizers"
            }
        ],
        "events": [
            {
                "href": "https://example.com/wp-json/tec/v1/events?organizer=789"
            }
        ]
    }
}
```

## WordPress Meta Mapping

The organizer fields map to WordPress post meta:

- `email` → `_OrganizerEmail`
- `phone` → `_OrganizerPhone`
- `website` → `_OrganizerWebsite`

## Usage

This definition is used for:

- Single organizer responses (`GET /organizers/{id}`)
- Organizer creation responses (`POST /organizers`)
- Organizer update responses (`PUT /organizers/{id}`)
- Organizer collection items (`GET /organizers`)
- Embedded organizers in event responses

## Common Use Cases

### Creating an Organizer

```json
{
    "name": "Local Community Center",
    "description": "Your neighborhood community center",
    "email": "events@community.org",
    "phone": "+1-555-9876"
}
```

### Updating an Organizer

```json
{
    "email": "newemail@techevents.com",
    "website": "https://new.techevents.com"
}
```

## Related Definitions

- [Event Definition](event.md) - Events can have multiple organizers
