# Views v2

This document is a work-in-progress meant to provide both an overview of the new Views architecture and an implementation guide for anyone working on a View implementation.

## The Repository-View-Context pattern
Without trying to create too complicated relations with better known patterns, this second version of The Events Calendar Views adopts an architecture aimed at minimizing the amount of code required to implement new Views and reduce the code duplication that existed in the previous View implementation.
Much of that duplication existed as a direct consequence of each View requiring to be rendered in, mainly, two different contexts:

1. PHP initial state request; e.g. a request triggered by entering `http://example.tribe/events` in the address bar
2. AJAX request triggered by Javascript code from the View front-end; e.g. a request triggered by a user applying a keyword to a View

### The Context
The two context provide pretty much the same information (who's looking at the view, what events should we show, what settings should we apply) in a way which is equivalent enough to have similar code but different enough torequire its duplication.
The biggest difference between the two request types is that the PHP initial state will have to filter the main query to return the correct events, while the AJAX request has more of a leeway starting with, pretty much, a clean slate in query terms.

In short the Repository-View-Context pattern tries to "hide" the context and request differences behind the API provided by the `tribe_context()` object; as an example:

```php
$events_per_page = tribe_context()->get( 'events_per_page', 10 );
```

The code above will, in a cascading style, top to bottom:

* look for a `posts_per_page` value explicitly set in the current request by means of a query variable
* look for a `posts_per_page` value in the `tribe_events_calendar_options`
* look for a `posts_per_page` WordPress option
* finally fallback on using the default value of `10` we provided as second argument

And this is just a simple example!
The Context abstraction is hiding way more complex "locations" (as in "a location one reads a value from") than this.

### The View
The view will look into the context, understand what's supposed to show and use that information to show the corect events.

In an extremely simplified code one could implement a basis list and month views like this:

```php
$context = tribe_context();

$view = $context->get( 'view' );

if( 'month' === $view ){
	$date = $context->get( 'event_date', 'today' );
	$first_grid_date = Month::calculate_first_cell_date( $date );
	$final_grid_date = Month::calculate_final_cell_date( $date );
} else {

}
```

## Implementing a View

### Creating the View class

* extend base View class
* extend setup_repository_args if required
* extend setup_template_vars if required
* extend prev/next URL methods if required

### Creating the View template

* folder structure
* template parts
* naming conventions
* common templates (e.g. bar, datepicker)


### Passing information to the View template

* preparing the data in the `setup_template_vars` method
* we're in the loop too
* global data  vs local data in templates

### Using the View information in the template

* template vars are extracted
* accesing global data
* accessing local template data
### The View
The view will look into the context, understand what's supposed to show and use that information to show the cor
ect events.

In an extremely simplified code one could implement a basis list and month views like this:

```php
$context = tribe_context();

$view = $context->get( 'view' );

if( 'month' === $view ){
    $date = $context->get( 'event_date', 'today' );
    $first_grid_date = Month::calculate_first_cell_date( $date );
    $final_grid_date = Month::calculate_final_cell_date( $date );
} else {

}
```

## Implementing a View

### Creating the View class

* extend base View class
* extend setup_repository_args if required
* extend setup_template_vars if required
* extend prev/next URL methods if required
