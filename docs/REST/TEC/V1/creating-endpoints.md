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
use TEC\Common\REST\TEC\V1\Collections\QueryArgumentCollection;
use TEC\Common\REST\TEC\V1\Collections\RequestBodyCollection;
use TEC\Common\REST\TEC\V1\Documentation\OpenAPI_Schema;
use TEC\Common\REST\TEC\V1\Parameter_Types\Definition_Parameter;
use WP_REST_Response;

class MyEntity extends Post_Entity_Endpoint implements RUD_Endpoint {
    use With_MyEntity_ORM; // Your ORM trait

    /**
     * Returns the base path for the endpoint.
     */
    public function get_base_path(): string {
        return '/my-entities/{id}';
    }

    /**
     * Returns the path regex for ID parameter.
     */
    public function get_path_regex(): string {
        return '(?P<id>[\d]+)';
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

    // Implement read(), update(), delete() methods with array $params
    // Implement read_params(), update_params(), delete_params() methods
    // Implement read_schema(), update_schema(), delete_schema() methods
}
```

## Step 3: Implement Required Methods

### For Readable Endpoints

```php
// Note: Parameters are pre-sanitized through the validation pipeline
public function read( array $params = [] ): WP_REST_Response {
    $id = (int) $params['id'];
    $entity = get_post( $id );

    if ( ! $entity || $entity->post_type !== $this->get_post_type() ) {
        return new WP_REST_Response( [ 'error' => 'Not found' ], 404 );
    }

    return new WP_REST_Response( $this->get_formatted_entity( $entity ), 200 );
}

public function read_params(): QueryArgumentCollection {
    $collection = new QueryArgumentCollection();

    // Path parameters are defined separately
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
        $this->read_params()
    );

    // Add responses
    return $schema;
}
```

### For Creatable Endpoints

Use the ORM for creation:

```php
// Parameters are pre-sanitized through validation pipeline
public function create( array $params = [] ): WP_REST_Response {
    // Map pre-validated params to ORM fields
    $entity = $this->get_orm()->set_args( $params )->create();

    if ( ! $entity ) {
        return new WP_REST_Response( [ 'error' => 'Creation failed' ], 500 );
    }

    return new WP_REST_Response(
        $this->get_formatted_entity( get_post( $entity ) ),
        201
    );
}

// Use RequestBodyCollection for POST/PUT operations
public function create_params(): RequestBodyCollection {
    $collection = new RequestBodyCollection();
    $definition = new MyEntity_Request_Body_Definition();

    $collection[] = new Definition_Parameter($definition);

    return $collection
        ->set_description_provider(fn() => __('Entity data to create', 'text-domain'))
        ->set_required(true)
        ->set_example($definition->get_example());
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

Define your endpoint's parameters based on the operation:

### For GET/DELETE Operations (Query Parameters)

```php
public function read_params(): QueryArgumentCollection {
    $collection = new QueryArgumentCollection();

    $collection->add(
        new Positive_Integer('page', fn() => __('Page number', 'text-domain'), 1, 1)
    );

    $collection->add(
        new Text('search', fn() => __('Search term', 'text-domain'))
    );

    return $collection;
}
```

### For POST/PUT Operations (Request Body)

```php
public function create_params(): RequestBodyCollection {
    $collection = new RequestBodyCollection();

    // Use Definition_Parameter for complex objects
    $definition = new MyEntity_Request_Body_Definition();
    $collection[] = new Definition_Parameter($definition);

    return $collection
        ->set_description_provider(fn() => __('Entity data', 'text-domain'))
        ->set_required(true)
        ->set_example($definition->get_example());
}
```

### Creating a Request Body Definition

```php
class MyEntity_Request_Body_Definition extends Definition {
    public function get_definition(): array {
        return [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                    'description' => 'Entity title',
                    'required' => true,
                ],
                'featured' => [
                    'type' => 'boolean',
                    'description' => 'Is featured',
                    'default' => false,
                ],
            ],
        ];
    }

    public function get_example(): array {
        return [
            'title' => 'Example Entity',
            'featured' => true,
        ];
    }
}
```

## Best Practices

1. **Use the ORM**: Always use `tribe_events()`, `tribe_organizers()`, etc.
2. **Don't Override Base Methods**: Permission checks are in abstract classes
3. **Use Appropriate Collections**:
   - `QueryArgumentCollection` for GET/DELETE
   - `RequestBodyCollection` for POST/PUT/PATCH
4. **Leverage Validation Pipeline**: Parameters are pre-sanitized, don't re-validate
5. **Create Definition Classes**: For complex request bodies, create Definition classes
6. **Use Traits**: Create ORM traits for data access
7. **Follow Naming Conventions**: Use consistent naming for methods and properties
8. **Add OpenAPI Documentation**: Always implement schema methods
9. **Handle Errors Gracefully**: Return appropriate HTTP status codes
10. **Custom Filtering**: Implement `filter_{operation}_params()` for custom logic

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
