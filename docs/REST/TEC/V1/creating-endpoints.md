# Creating New Endpoints

This guide walks through creating new endpoints for the TEC REST API.

## Prerequisites

Before creating an endpoint, understand:

- Which operations you need (Read, Create, Update, Delete)
- Whether it's a collection or single entity endpoint
- The data model and validation requirements

## Step 1: Choose the Right Base Class and Interface

### For Post-Based Endpoints

Most TEC endpoints work with WordPress posts. Use:

```php
use TEC\Common\REST\TEC\V1\Abstracts\Post_Entity_Endpoint;
```

### Choose Your Interface

- **Single Entity (RUD)**: `implements RUD_Endpoint`
- **Collection with Read**: `implements Readable_Endpoint`
- **Collection with Create**: `implements Readable_Endpoint, Creatable_Endpoint`
- **Full CRUD Collection**: `implements Collection_Endpoint`

## Step 2: Create the Endpoint Class

Create your endpoint file in:

```bash
/wp-content/plugins/the-events-calendar/src/Events/REST/TEC/V1/Endpoints/
```

### Example: Single Entity Endpoint

```php
<?php
declare( strict_types=1 );

namespace TEC\Events\REST\TEC\V1\Endpoints;

use TEC\Common\REST\TEC\V1\Abstracts\Post_Entity_Endpoint;
use TEC\Common\REST\TEC\V1\Contracts\RUD_Endpoint;
use TEC\Common\REST\TEC\V1\Parameter_Types\Collection;
use TEC\Common\REST\TEC\V1\Documentation\OpenAPI_Schema;
use WP_REST_Request;
use WP_REST_Response;

class MyEntity extends Post_Entity_Endpoint implements RUD_Endpoint {
    use With_MyEntity_ORM; // Your ORM trait

    /**
     * Returns the base path for the endpoint.
     */
    public function get_base_path(): string {
        return '/my-entities/%s';
    }

    /**
     * Returns the model class.
     */
    public function get_model_class(): string {
        return MyEntity_Model::class;
    }

    /**
     * Returns the post type.
     */
    public function get_post_type(): string {
        return 'my_entity';
    }

    /**
     * Whether guests can read.
     */
    public function guest_can_read(): bool {
        return true;
    }

    // Implement read(), update(), delete() methods
    // Implement read_args(), update_args(), delete_args() methods
    // Implement read_schema(), update_schema(), delete_schema() methods
}
```

## Step 3: Implement Required Methods

### For Readable Endpoints

```php
public function read( WP_REST_Request $request ): WP_REST_Response {
    $id = (int) $request['id'];
    $entity = get_post( $id );

    if ( ! $entity || $entity->post_type !== $this->get_post_type() ) {
        return new WP_REST_Response( [ 'error' => 'Not found' ], 404 );
    }

    return new WP_REST_Response( $this->get_formatted_entity( $entity ), 200 );
}

public function read_args(): Collection {
    $collection = new Collection();

    $collection[] = new Positive_Integer(
        'id',
        fn() => __( 'Unique identifier', 'text-domain' ),
        null,
        1,
        null,
        true,
        null,
        null,
        null,
        Positive_Integer::LOCATION_PATH
    );

    return $collection;
}

public function read_schema(): OpenAPI_Schema {
    $schema = new OpenAPI_Schema(
        fn() => __( 'Get Entity', 'text-domain' ),
        fn() => __( 'Returns a single entity', 'text-domain' ),
        'getEntity',
        [ tribe( TEC_Tag::class ) ],
        $this->read_args()
    );

    // Add responses
    return $schema;
}
```

### For Creatable Endpoints

Use the ORM for creation:

```php
public function create( WP_REST_Request $request ): WP_REST_Response {
    $args = [
        'title' => $request['title'],
        'content' => $request['description'],
        // Map request fields to ORM fields
    ];

    $entity = $this->get_orm()->set_args( $args )->create();

    if ( ! $entity ) {
        return new WP_REST_Response( [ 'error' => 'Creation failed' ], 500 );
    }

    return new WP_REST_Response(
        $this->get_formatted_entity( get_post( $entity ) ),
        201
    );
}
```

## Step 4: Create an ORM Trait

Create a trait to provide ORM access:

```php
<?php
namespace TEC\Events\REST\TEC\V1\Traits;

trait With_MyEntity_ORM {
    public function get_orm() {
        return tribe( 'my-entity.orm' );
    }
}
```

## Step 5: Register the Endpoint

Add your endpoint to the Endpoints controller:

```php
// In /src/Events/REST/TEC/V1/Endpoints.php

public function get_endpoints(): array {
    return [
        // ... existing endpoints
        MyEntity::class,
    ];
}
```

## Step 6: Create Parameter Collections

Define your endpoint's parameters:

```php
public function create_args(): Collection {
    $collection = new Collection();

    $collection[] = new Text(
        'title',
        fn() => __( 'Entity title', 'text-domain' ),
        null,
        null,
        null,
        null,
        true // required
    );

    $collection[] = new Boolean(
        'featured',
        fn() => __( 'Is featured', 'text-domain' ),
        false // default value
    );

    return $collection;
}
```

## Best Practices

1. **Use the ORM**: Always use `tribe_events()`, `tribe_organizers()`, etc.
2. **Don't Override Base Methods**: Methods like `can_read()` are in the abstract class
3. **Use Traits**: Create ORM traits for data access
4. **Follow Naming Conventions**: Use consistent naming for methods and properties
5. **Add OpenAPI Documentation**: Always implement schema methods
6. **Handle Errors Gracefully**: Return appropriate HTTP status codes
7. **Validate Input**: Use parameter validation callbacks

## Common Patterns

### Collection Endpoints

- Implement `build_query()` method
- Use `Read_Archive_Response` trait
- Support pagination parameters

### Single Entity Endpoints

- Use path parameters for ID
- Implement RUD operations
- Use `get_formatted_entity()` for responses

### Parameter Types

- `Positive_Integer` - For IDs
- `Text` - For strings
- `Boolean` - For flags
- `Date_Time` - For dates
- `Array_Of_Type` - For arrays
- `URI` - For URLs
- `Email` - For email addresses

## Testing Your Endpoint

1. Check route registration:

   ```bash
   wp rest route list --namespace=tec/v1
   ```

2. Test with curl:

   ```bash
   curl -X GET https://yoursite.com/wp-json/tec/v1/your-endpoint
   ```

3. Verify OpenAPI documentation at `/wp-json/tec/v1/docs`
