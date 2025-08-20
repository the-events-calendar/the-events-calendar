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

| Definition | Class | Type | Priority | Description |
|------------|-------|------|----------|-------------|
| Event | `Event_Definition` | `Event` | 1 | Event entity structure extending TEC_Post_Entity |
| Event Request Body | `Event_Request_Body_Definition` | `Event_Request_Body` | 1 | Event creation/update request structure |
| Organizer | `Organizer_Definition` | `Organizer` | 1 | Organizer entity structure extending TEC_Post_Entity |
| Organizer Request Body | `Organizer_Request_Body_Definition` | `Organizer_Request_Body` | 1 | Organizer creation/update request structure |
| Venue | `Venue_Definition` | `Venue` | 1 | Venue entity structure extending TEC_Post_Entity |
| Venue Request Body | `Venue_Request_Body_Definition` | `Venue_Request_Body` | 1 | Venue creation/update request structure |

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

**Extends**: `TEC_Post_Entity` (via allOf pattern)

Key properties:

- `id` - Event ID
- `title` - Event title
- `description` - Event content
- `excerpt` - Event excerpt
- `start_date` - Event start date/time (format: `YYYY-MM-DD HH:MM:SS`)
- `start_date_utc` - Event start date/time in UTC
- `end_date` - Event end date/time (format: `YYYY-MM-DD HH:MM:SS`)
- `end_date_utc` - Event end date/time in UTC
- `dates` - Date details object (references `Date_Details_Definition`)
- `timezone` - Event timezone (e.g., `Europe/Athens`)
- `duration` - Event duration in seconds
- `multiday` - Whether the event spans multiple days
- `is_past` - Whether the event is in the past
- `is_now` - Whether the event is happening now
- `all_day` - All-day event flag
- `starts_this_week` - Whether the event starts this week (nullable)
- `ends_this_week` - Whether the event ends this week (nullable)
- `happens_this_week` - Whether the event happens this week (nullable)
- `this_week_duration` - Duration of the event in the current week (nullable)
- `displays_on` - Array of dates the event displays on (format: `YYYY-MM-DD`)
- `featured` - Featured status
- `sticky` - Sticky status
- `cost` - Event cost (e.g., `$10`)
- `organizer_names` - Array of organizer names
- `organizers` - Array of full organizer objects
- `venues` - Array of full venue objects
- `ticketed` - Array of ticket providers or false
- `schedule_details` - Schedule details text
- `short_schedule_details` - Schedule details in HTML
- `tribe_events_cat` - Array of category term IDs
- `website` - Event website URL

### Organizer Definition

[Full Documentation](definitions/organizer.md)

**Extends**: `TEC_Post_Entity` (via allOf pattern)  
**Priority**: 2

Key properties (in addition to base entity):

- `phone` - Contact phone (format: tel, e.g., `123-456-7890`)
- `website` - Organizer website URL
- `email` - Contact email address

### Venue Definition

[Full Documentation](definitions/venue.md)

**Extends**: `TEC_Post_Entity` (via allOf pattern)  
**Priority**: 3

Key properties (in addition to base entity):

- `address` - Street address
- `country` - Country name
- `city` - City name
- `state_province` - State or Province
- `state` - State (separate field)
- `province` - Province (separate field)
- `zip` - Postal/ZIP code
- `phone` - Contact phone number
- `website` - Venue website URL

### Request Body Definitions

Request Body definitions are used for POST and PUT operations:

#### Event Request Body

Used for creating and updating events. Contains similar fields to the Event definition but optimized for input:

- Date fields use the same format (`YYYY-MM-DD HH:MM:SS`)
- Categories are specified as array of term IDs
- Venues and organizers are specified as arrays of IDs
- All fields from the base TEC_Post_Entity are available

#### Organizer Request Body

Used for creating and updating organizers. Includes:

- Standard post fields (title, content, excerpt, status)
- Contact information (phone, email, website)

#### Venue Request Body

Used for creating and updating venues. Includes:

- Standard post fields (title, content, excerpt, status)
- Address fields (address, city, state/province, zip, country)
- Contact information (phone, website)

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
