# TEC REST API Definitions

Definitions are used to describe data structures for OpenAPI documentation.

## Overview

Definitions provide:

- OpenAPI schema components
- Data structure documentation
- Type information for API responses
- Reusable schema references

## Plugin-Specific Definitions

### Events Calendar Definitions

Located in `/wp-content/plugins/the-events-calendar/src/Events/REST/TEC/V1/Documentation/`

| Definition | Class | Description |
|------------|-------|-------------|
| Event | `Event_Definition` | Event entity structure |
| Organizer | `Organizer_Definition` | Organizer entity structure |
| Venue | `Venue_Definition` | Venue entity structure |

### Common Definitions

Located in `/wp-content/plugins/the-events-calendar/common/src/Common/REST/TEC/V1/Documentation/`

| Definition | Class | Description |
|------------|-------|-------------|
| Date | `Date_Definition` | Date formatting structure |
| Date Details | `Date_Details_Definition` | Detailed date information |
| TEC Post Entity | `TEC_Post_Entity_Definition` | Base post entity structure |
| OpenAPI | `OpenApi_Definition` | OpenAPI specification |
| OpenAPI Path | `OpenApi_Path_Definition` | OpenAPI path definitions |

## Definition Details

### Event Definition

[Full Documentation](definitions/event.md)

Key properties:

- `id` - Event ID
- `title` - Event title
- `description` - Event content
- `start_date` - Event start date/time
- `end_date` - Event end date/time
- `all_day` - All-day event flag
- `timezone` - Event timezone
- `venues` - Associated venues
- `organizers` - Associated organizers
- `featured` - Featured status
- `cost` - Event cost
- `website` - Event website URL

### Organizer Definition

[Full Documentation](definitions/organizer.md)

Key properties:

- `id` - Organizer ID
- `name` - Organizer name
- `description` - Organizer description
- `email` - Contact email
- `phone` - Contact phone
- `website` - Organizer website

### Venue Definition

[Full Documentation](definitions/venue.md)

Key properties:

- `id` - Venue ID
- `name` - Venue name
- `description` - Venue description
- `address` - Street address
- `city` - City
- `state_province` - State/Province
- `zip` - Postal code
- `country` - Country
- `phone` - Contact phone
- `website` - Venue website

## Using Definitions

### In Endpoint Responses

```php
use TEC\Common\REST\TEC\V1\Parameter_Types\Definition_Parameter;
use TEC\Events\REST\TEC\V1\Documentation\Event_Definition;

public function read_schema(): OpenAPI_Schema {
    $schema = new OpenAPI_Schema(
        fn() => __( 'Get Event', 'the-events-calendar' ),
        fn() => __( 'Returns a single event', 'the-events-calendar' ),
        'getEvent',
        [ tribe( TEC_Tag::class ) ],
        $this->read_args()
    );

    $response = new Definition_Parameter( new Event_Definition() );

    $schema->add_response(
        200,
        fn() => __( 'Returns the event', 'the-events-calendar' ),
        null,
        'application/json',
        $response
    );

    return $schema;
}
```

### In Array Responses

```php
use TEC\Common\REST\TEC\V1\Parameter_Types\Array_Of_Type;

$response = new Array_Of_Type(
    'Event',
    null,
    Event_Definition::class
);
```

## Creating New Definitions

To create a new definition:

```php
namespace TEC\Events\REST\TEC\V1\Documentation;

use TEC\Common\REST\TEC\V1\Documentation\OpenApi_Definition;

class My_Entity_Definition extends OpenApi_Definition {
    public function get_documentation(): array {
        return [
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => 'Unique identifier',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Entity name',
                ],
                'created_date' => [
                    'type' => 'string',
                    'format' => 'date-time',
                    'description' => 'Creation date',
                ],
            ],
            'required' => ['id', 'name'],
        ];
    }
}
```

## OpenAPI Schema References

Definitions are automatically registered in the OpenAPI schema and can be referenced:

```json
{
    "$ref": "#/components/schemas/Event"
}
```

This allows for:

- Reusable schemas
- Consistent documentation
- Type validation
- API client generation
