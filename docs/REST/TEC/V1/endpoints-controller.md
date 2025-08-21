# Endpoints Controller

The Endpoints controller manages endpoint registration for The Events Calendar REST API.

## Class

`TEC\Events\REST\TEC\V1\Endpoints`

## Location

`/wp-content/plugins/the-events-calendar/src/Events/REST/TEC/V1/Endpoints.php`

## Overview

The Endpoints controller:

- Defines which endpoints are available
- Handles endpoint registration
- Manages endpoint dependencies
- Provides endpoint discovery

## Implementation

```php
namespace TEC\Events\REST\TEC\V1;

use TEC\Common\REST\TEC\V1\Contracts\Endpoints_Controller_Interface;

class Endpoints implements Endpoints_Controller_Interface {

    /**
     * Returns array of endpoint classes to register.
     *
     * @return array<class-string>
     */
    public function get_endpoints(): array {
        return [
            Endpoints\Events::class,
            Endpoints\Event::class,
            Endpoints\Organizers::class,
            Endpoints\Organizer::class,
            Endpoints\Venues::class,
            Endpoints\Venue::class,
        ];
    }

    /**
     * Registers all endpoints.
     */
    public function register(): void {
        foreach ( $this->get_endpoints() as $endpoint_class ) {
            $endpoint = tribe( $endpoint_class );
            $endpoint->register_routes();
        }
    }
}
```

## Adding New Endpoints

To add a new endpoint:

1. Create the endpoint class
2. Add it to the `get_endpoints()` array
3. Ensure it's registered in the service container

### Example

```php
public function get_endpoints(): array {
    return [
        // Existing endpoints
        Endpoints\Events::class,
        Endpoints\Event::class,

        // Add your new endpoint
        Endpoints\MyNewEndpoint::class,
    ];
}
```

## Service Container Registration

Endpoints should be registered in the service provider:

```php
// In Service_Provider.php
public function register() {
    // Register the controller
    $this->container->singleton( Endpoints::class );

    // Register individual endpoints
    $this->container->singleton( Endpoints\Events::class );
    $this->container->singleton( Endpoints\Event::class );
    $this->container->singleton( Endpoints\Organizers::class );
    $this->container->singleton( Endpoints\Organizer::class );
    $this->container->singleton( Endpoints\Venues::class );
    $this->container->singleton( Endpoints\Venue::class );
}
```

## Conditional Registration

You can conditionally register endpoints:

```php
public function get_endpoints(): array {
    $endpoints = [
        Endpoints\Events::class,
        Endpoints\Event::class,
    ];

    // Only add if feature is enabled
    if ( tribe_get_option( 'enable_organizers' ) ) {
        $endpoints[] = Endpoints\Organizers::class;
        $endpoints[] = Endpoints\Organizer::class;
    }

    return $endpoints;
}
```

## Endpoint Discovery

The controller can provide endpoint information:

```php
/**
 * Get endpoint URLs.
 */
public function get_endpoint_urls(): array {
    $urls = [];

    foreach ( $this->get_endpoints() as $endpoint_class ) {
        $endpoint = tribe( $endpoint_class );
        $urls[ $endpoint_class ] = $endpoint->get_url();
    }

    return $urls;
}
```

## Hook Integration

The controller fires hooks during registration:

```php
public function register(): void {
    /**
     * Fires before endpoints are registered.
     *
     * @param array $endpoints Array of endpoint classes
     */
    do_action( 'tec_rest_before_register_endpoints', $this->get_endpoints() );

    foreach ( $this->get_endpoints() as $endpoint_class ) {
        $endpoint = tribe( $endpoint_class );
        $endpoint->register_routes();
    }

    /**
     * Fires after endpoints are registered.
     *
     * @param array $endpoints Array of endpoint classes
     */
    do_action( 'tec_rest_after_register_endpoints', $this->get_endpoints() );
}
```

## Testing Endpoints

Verify endpoints are registered:

```php
// Get registered endpoints
$endpoints = tribe( Endpoints::class )->get_endpoints();

// Check specific endpoint
$event_endpoint = tribe( Endpoints\Event::class );
```

## Best Practices

1. **Use Service Container** - Always use `tribe()` for instantiation
2. **Interface Compliance** - Implement the interface correctly
3. **Organize by Feature** - Group related endpoints
4. **Document Endpoints** - Keep the array well-documented
5. **Test Registration** - Verify endpoints register correctly

## Debugging

Enable debug logging:

```php
public function register(): void {
    foreach ( $this->get_endpoints() as $endpoint_class ) {
        if ( ! class_exists( $endpoint_class ) ) {
            error_log( "Endpoint class not found: {$endpoint_class}" );
            continue;
        }

        try {
            $endpoint = tribe( $endpoint_class );
            $endpoint->register_routes();
            error_log( "Registered endpoint: {$endpoint_class}" );
        } catch ( \Exception $e ) {
            error_log( "Failed to register {$endpoint_class}: " . $e->getMessage() );
        }
    }
}
```

## Related Documentation

- [Creating Endpoints](creating-endpoints.md) - How to create endpoints
- [Available Endpoints](endpoints.md) - List of all endpoints
