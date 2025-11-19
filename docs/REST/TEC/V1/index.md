# TEC REST API Documentation Index

Complete documentation for The Events Calendar REST API V1, including core functionality and plugin integrations.

## Documentation Structure

### Main Documentation

- [README](README.md) - Overview and getting started
- [Creating Endpoints](creating-endpoints.md) - Step-by-step guide
- [Endpoints Controller](endpoints-controller.md) - Managing endpoints
- [Endpoints Overview](endpoints.md) - List of all endpoints
- [Definitions Overview](definitions.md) - Data structures

### Endpoint Documentation

#### The Events Calendar

Located in `endpoints/`:

- [Events Endpoints](endpoints/events.md) - Event CRUD operations
- [Organizers Endpoints](endpoints/organizers.md) - Organizer CRUD operations
- [Venues Endpoints](endpoints/venues.md) - Venue CRUD operations
- [OpenAPI Endpoint](endpoints/openapi.md) - API documentation endpoint

### Definition Documentation

#### The Events Calendar

Located in `definitions/`:

- [Event Definition](definitions/event.md) - Event data structure
- [Organizer Definition](definitions/organizer.md) - Organizer data structure
- [Venue Definition](definitions/venue.md) - Venue data structure

#### Common Library

Located in `/wp-content/plugins/the-events-calendar/common/docs/REST/TEC/V1/definitions/`:

- [TEC Post Entity](../../../common/docs/REST/TEC/V1/definitions/tec-post-entity.md) - Base entity structure
- [Date Details](../../../common/docs/REST/TEC/V1/definitions/date-details.md) - Date/time structures

## Plugin Architecture

### Common Library Structure

```
TEC\Common\REST\TEC\V1\
├── Controller.php                    # Base REST controller
├── Documentation.php                 # OpenAPI generator
├── Endpoints.php                     # Base endpoints controller
├── Abstracts/                        # Abstract base classes
│   ├── Definition.php
│   ├── Endpoint.php
│   ├── Endpoints_Controller.php
│   ├── Parameter.php
│   ├── Post_Entity_Endpoint.php
│   └── Tag.php
├── Collections/                      # Parameter collections
│   ├── Collection.php
│   ├── HeadersCollection.php
│   ├── PathArgumentCollection.php
│   ├── PropertiesCollection.php
│   ├── QueryArgumentCollection.php
│   └── RequestBodyCollection.php
├── Contracts/                        # Interfaces
│   ├── Collection_Endpoint.php
│   ├── Creatable_Endpoint.php
│   ├── Deletable_Endpoint.php
│   ├── Readable_Endpoint.php
│   ├── RUD_Endpoint.php
│   └── Updatable_Endpoint.php
├── Parameter_Types/                  # Strong typing
│   ├── Array_Of_Type.php
│   ├── Boolean.php
│   ├── Date.php
│   ├── Date_Time.php
│   ├── Email.php
│   ├── Integer.php
│   ├── Number.php
│   ├── Positive_Integer.php
│   ├── Text.php
│   └── URI.php
└── Traits/                           # Response handlers
    ├── Create_Entity_Response.php
    ├── Delete_Entity_Response.php
    ├── Read_Archive_Response.php
    ├── Read_Entity_Response.php
    └── Update_Entity_Response.php
```

### Events Plugin Structure

```
TEC\Events\REST\TEC\V1\
├── Controller.php              # Main controller registering endpoints
├── Endpoints.php              # Endpoints controller managing all endpoints
├── Endpoints/                 # Individual endpoint implementations
│   ├── Event.php             # Single event endpoint (RUD operations)
│   ├── Events.php            # Events collection endpoint (Read/Create)
│   ├── Organizer.php         # Single organizer endpoint (RUD operations)
│   ├── Organizers.php        # Organizers collection endpoint (Read/Create)
│   ├── Venue.php             # Single venue endpoint (RUD operations)
│   └── Venues.php            # Venues collection endpoint (Read/Create)
├── Documentation/             # OpenAPI definitions
│   ├── Event_Definition.php
│   ├── Event_Request_Body_Definition.php
│   ├── Organizer_Definition.php
│   ├── Organizer_Request_Body_Definition.php
│   ├── Venue_Definition.php
│   └── Venue_Request_Body_Definition.php
├── Tags/                      # API tags for grouping
│   └── TEC_Tag.php           # Events tag
└── Traits/                    # Reusable functionality
    ├── With_Events_ORM.php
    ├── With_Organizers_ORM.php
    ├── With_Transform_Organizers_And_Venues.php
    └── With_Venues_ORM.php
```

## Quick Links

### For Developers

1. [Getting Started](README.md#getting-started)
2. [Creating Your First Endpoint](creating-endpoints.md)
3. [Using ORM Traits](../../../common/docs/REST/TEC/V1/orm-usage.md)
4. [Parameter Types](../../../common/docs/REST/TEC/V1/parameter-types.md)

### API Reference

1. [All Endpoints](endpoints.md)
2. [All Definitions](definitions.md)
3. [Authentication](README.md#authentication)
4. [Error Handling](README.md#error-handling)
5. [OpenAPI Documentation](endpoints/openapi.md)

### Common Patterns

1. [Abstract Classes](../../../common/docs/REST/TEC/V1/abstract-classes.md)
2. [Interfaces](../../../common/docs/REST/TEC/V1/interfaces.md)
3. [Traits](../../../common/docs/REST/TEC/V1/traits.md)

### Code Examples

1. [Endpoint Examples](creating-endpoints.md#step-2-create-the-endpoint-class)
2. [Definition Examples](definitions.md#creating-new-definitions)

## Key Features

### Endpoints

- **Collection Endpoints**: Implement `Collection_Endpoint` (combines `Readable_Endpoint` and `Creatable_Endpoint`)
- **Single Entity Endpoints**: Implement `RUD_Endpoint` (Read, Update, Delete)
- **Base Classes**: All extend `Post_Entity_Endpoint` from common library
- **ORM Integration**: Use dedicated ORM traits for each post type
- **Strong Typing**: Parameter collections with validation and sanitization
- **Experimental Support**: Beta endpoints with acknowledgment headers

### Definitions

- **Entity Definitions**: Describe response structures (Event, Organizer, Venue)
- **Request Body Definitions**: Describe request structures for POST/PUT
- **Base Definition**: All extend `TEC_Post_Entity` via allOf pattern
- **Priority System**: Controls registration order (Event=1, Organizer=2, Venue=3)

### Authentication & Permissions

- **Standard WordPress**: Uses WordPress authentication system
- **Capability Checks**: Built into abstract base classes
- **Guest Access**: Configurable per endpoint via `guest_can_read()`

### OpenAPI Integration

- **Version**: OpenAPI 3.0.4 specification
- **Auto-generated**: Definitions automatically included in OpenAPI spec
- **Documentation Endpoint**: Available at `/tec/v1/docs`
- **Type Safety**: Strong typing with parameter validation
- **Schema Composition**: Uses allOf pattern for definition inheritance
- **Operation IDs**: Standardized for SDK generation

## Available Endpoints Summary

| Plugin | Collection | Single Entity | Requirements |
|--------|------------|---------------|--------------|
| **The Events Calendar** | | | |
| Events | `/events` | `/events/{id}` | Always available |
| Organizers | `/organizers` | `/organizers/{id}` | Always available |
| Venues | `/venues` | `/venues/{id}` | Always available |
| **Common** | | | |
| Documentation | `/docs` | - | Always available |
