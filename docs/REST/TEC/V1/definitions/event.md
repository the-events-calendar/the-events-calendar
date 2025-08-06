# Event Definition

The Event definition describes the structure of an event object in the TEC REST API.

## Class

`TEC\Events\REST\TEC\V1\Documentation\Event_Definition`

## Schema

```json
{
    "type": "object",
    "properties": {
        "id": {
            "type": "integer",
            "description": "Unique identifier for the event"
        },
        "title": {
            "type": "string",
            "description": "Event title"
        },
        "description": {
            "type": "string",
            "description": "Full event description/content"
        },
        "excerpt": {
            "type": "string",
            "description": "Event excerpt"
        },
        "status": {
            "type": "string",
            "enum": ["publish", "pending", "draft", "future", "private"],
            "description": "Event status"
        },
        "date": {
            "type": "string",
            "format": "date-time",
            "description": "Event creation date"
        },
        "date_gmt": {
            "type": "string",
            "format": "date-time",
            "description": "Event creation date (GMT)"
        },
        "modified": {
            "type": "string",
            "format": "date-time",
            "description": "Event last modified date"
        },
        "modified_gmt": {
            "type": "string",
            "format": "date-time",
            "description": "Event last modified date (GMT)"
        },
        "start_date": {
            "type": "string",
            "format": "date-time",
            "description": "Event start date and time"
        },
        "end_date": {
            "type": "string",
            "format": "date-time",
            "description": "Event end date and time"
        },
        "all_day": {
            "type": "boolean",
            "description": "Whether this is an all-day event"
        },
        "timezone": {
            "type": "string",
            "description": "Event timezone (e.g., 'America/New_York')"
        },
        "featured": {
            "type": "boolean",
            "description": "Whether the event is featured"
        },
        "cost": {
            "type": "string",
            "description": "Event cost information"
        },
        "website": {
            "type": "string",
            "format": "uri",
            "description": "Event website URL"
        },
        "venues": {
            "type": "array",
            "items": {
                "$ref": "#/components/schemas/Venue"
            },
            "description": "Associated venues"
        },
        "organizers": {
            "type": "array",
            "items": {
                "$ref": "#/components/schemas/Organizer"
            },
            "description": "Associated organizers"
        },
        "categories": {
            "type": "array",
            "items": {
                "type": "object",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "name": {
                        "type": "string"
                    },
                    "slug": {
                        "type": "string"
                    }
                }
            },
            "description": "Event categories"
        },
        "tags": {
            "type": "array",
            "items": {
                "type": "object",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "name": {
                        "type": "string"
                    },
                    "slug": {
                        "type": "string"
                    }
                }
            },
            "description": "Event tags"
        },
        "link": {
            "type": "string",
            "format": "uri",
            "description": "Event permalink"
        },
        "_links": {
            "type": "object",
            "description": "HAL links for the event"
        }
    },
    "required": ["id", "title", "start_date", "end_date"]
}
```

## Example

```json
{
    "id": 123,
    "title": "Annual Tech Conference",
    "description": "<p>Join us for our annual technology conference featuring keynote speakers...</p>",
    "excerpt": "Three days of technology talks and networking",
    "status": "publish",
    "date": "2024-01-15T10:00:00",
    "date_gmt": "2024-01-15T15:00:00",
    "modified": "2024-01-20T14:30:00",
    "modified_gmt": "2024-01-20T19:30:00",
    "start_date": "2024-03-15T09:00:00",
    "end_date": "2024-03-17T17:00:00",
    "all_day": false,
    "timezone": "America/New_York",
    "featured": true,
    "cost": "$299 Early Bird, $399 Regular",
    "website": "https://conference.example.com",
    "venues": [
        {
            "id": 456,
            "name": "Convention Center",
            "address": "123 Main St",
            "city": "New York",
            "state_province": "NY",
            "zip": "10001",
            "country": "United States"
        }
    ],
    "organizers": [
        {
            "id": 789,
            "name": "Tech Events Inc.",
            "email": "info@techevents.com",
            "phone": "+1-555-0123",
            "website": "https://techevents.com"
        }
    ],
    "categories": [
        {
            "id": 10,
            "name": "Technology",
            "slug": "technology"
        }
    ],
    "tags": [
        {
            "id": 20,
            "name": "Conference",
            "slug": "conference"
        },
        {
            "id": 21,
            "name": "Networking",
            "slug": "networking"
        }
    ],
    "link": "https://example.com/event/annual-tech-conference/",
    "_links": {
        "self": [
            {
                "href": "https://example.com/wp-json/tec/v1/events/123"
            }
        ],
        "collection": [
            {
                "href": "https://example.com/wp-json/tec/v1/events"
            }
        ],
        "venues": [
            {
                "href": "https://example.com/wp-json/tec/v1/venues/456"
            }
        ],
        "organizers": [
            {
                "href": "https://example.com/wp-json/tec/v1/organizers/789"
            }
        ]
    }
}
```

## Usage

This definition is used for:

- Single event responses (`GET /events/{id}`)
- Event creation responses (`POST /events`)
- Event update responses (`PUT /events/{id}`)
- Event collection items (`GET /events`)

## Related Definitions

- [Venue Definition](venue.md) - For venue objects
- [Organizer Definition](organizer.md) - For organizer objects
