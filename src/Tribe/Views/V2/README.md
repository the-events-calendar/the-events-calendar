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
The view will look into the context, understand what's supposed to show and use that information to show the correct events.

## Implementing a View

Code is worth a thousand words, so below is the code of a really simple (and pretty ugly) 3-days View.
The view will show events in a range of 3 days and is meant more as an example in the possibilities of the View architecture than an example in style.

### The View class

The first step in implementing a new View is creating the class responsible for managing it.
Below the code of the `Three_Day_List_View` class with heavily commented code:

```php
<?php
/**
 * An example Views v2 implementation showing events in a 3-day period.
 *
 * @package Tribe\Events\Views\V2\Views
 */

namespace Tribe\Events\Views\V2\Views;

use DateInterval;
use Tribe\Events\Views\V2\View;
use Tribe__Context as Context;
use Tribe__Date_Utils as Dates;

/**
 * Class Three_Day_List_View
 *
 * @package Tribe\Events\Views\V2\Views
 */
class Three_Day_List_View extends View {
	/**
	 * A variable indicating whether this View is one site visitors will see or not.
	 *
	 * We have some "service" Views we use for debug and testing that we do not want visitors to see.
	 *
	 * @var bool The publicly visible flag.
	 */
	protected $publicly_visible = true;

	/**
	 * Returns the "pretty" name that will be visible in the View selector.
	 *
	 * @return string The "pretty" name that will be visible in the View selector.
	 */
	public function get_label() {
		return __( '3 Days', 'the-events-calendar' );
	}

	/**
	 * Returns the slug that will be used to try and find the templates (in our plugin, in themes, in other plugins and
	 * so on).
	 *
	 * @return string The slug that will be used to locate the View templates.
	 */
	public function get_template_slug() {
		return 'three_days';
	}

	/**
	 * A utility methods to build and return the start and end date \DateTime objects given the start date.
	 *
	 * @param string $start_date The start date in a format parse-able by the `strtotime` function.
	 *
	 * @return array An array containing the start and end dates \DateTime objects. The start object will have its time
	 *               set to `00:00`
	 */
	protected function build_date_objects_from( $start_date ) {
		// Let's take the end of day cutoff into account.
		$start_date_object = Dates::build_date_object( tribe_beginning_of_day( $start_date ) );
		/*
				 * Let's obtain the end date as the start date + 2 days.
				 * Why 2 and not 3? Our view includes the first day; if the start date was 2019-01-01 then the View should
				 * include 2019-01-01, 2019-01-02, 2019-01-03.
				 */
		$two_days  = new DateInterval( 'P2D' );
		$temp_date = clone $start_date_object;
		$temp_date->add( $two_days );

		// Again: let's take the end of day cutoff into account.
		$end_date_object = Dates::build_date_object( tribe_end_of_day( $temp_date->format( 'Y-m-d H:i:s' ) ) );

		return [ $start_date_object, $end_date_object ];
	}

	/**
	 * Overrides the base View method to set up template variables the way this view will need them.
	 *
	 * This method should contain all the logic required to provide the front-end templates with information.
	 * This is the point of contact between the site back-end and the site front-end; its data is filterable.
	 * Want a logic-less front-end template? Do it here.
	 * We're not filtering the variables here: the main View class will do that for us.
	 *
	 * @return array An associative array containing any value that will be available to the front-end template
	 *               at any level. The values we set here will be "global" and available to any template component/part
	 *               by using the (extracted) variable by the same name.
	 */
	protected function setup_template_vars() {
		/**
		 * The base View will fill in some common template variables for us like `events`, `today`, `now`
		 * and more.
		 */
		$default_template_vars = parent::setup_template_vars();

		// If the start date was explicitly set (e.g. from the bar), then use it, else default to today.
		$start_date = $this->context->get( 'event_date', 'today' );

		list( $start_date_object, $end_date_object ) = $this->build_date_objects_from( $start_date );

		$template_vars = wp_parse_args(
			[
				'start_date'   => $start_date_object->format( 'Y-m-d' ),
				'end_date'     => $end_date_object->format( 'Y-m-d' ),
				'keyword'      => $this->context->get( 'keyword', false ),
				'events'       => $this->repository->all(),
				'events_count' => $this->repository->count(),
			],
			$default_template_vars
		);

		return $template_vars;
	}

	/**
	 * Sets up the repository, and/or the repository arguments, that should be used to fetch the events for the View.
	 *
	 * We do not need to filter the arguments here as the main View class will do that for us.
	 *
	 * @param Context|null $context The context of the View request. To the View this is the World. Everything the view
	 *                              needs to know about its render context... lives in the context.
	 *
	 * @return array The
	 */
	protected function setup_repository_args( Context $context = null ) {
		if ( null === $context ) {
			// If we're not explicitly provided a context, then let's use the global one.
			$context = tribe_context();
		}

		/*
		 * The main View class will populate the "usual suspects", repository arguments we need to consider in each View
		 * like keywords, page, posts-per-page.
		 */
		$repository_args = parent::setup_repository_args( $context );

		$start_date = $context->get( 'event_date', 'today' );
		list( $start_date_object, $end_date_object ) = $this->build_date_objects_from( $start_date );

		// Any event overlapping the period should be there.
		$repository_args['date_overlaps'] = [ $start_date_object, $end_date_object ];

		// Finally set an ordering criteria.
		$repository_args['orderby'] = 'event_date';
		$repository_args['order']   = 'ASC';

		return $repository_args;
	}
}
```

Not **all** Views will be this simple and you might need to override more methods from the base View to make things work as you want.

### Creating the View templates

Once the View class is in place it's time to give it a front-end by means of templates.
Looking at the View code above the `Three_Day_List_View::get_template_slug` method will return `three_days`.
Depending on where you're developing the View the code will look for the following files:
* `wp-content/plugins/the-events-calendar/src/views/v2/three_days.php`
* `wp-content/plugins/events-pro/src/views/v2/three_days.php`
* `wp-content/themes/<theme-folder>/tribe/events/views/v2/three_days.php`

And more locations you'll be able to control with the `tribe_template_path_list` filter.

In this example we've created the view files in The Events Calendar plugin, here is the file structure:
```
src/views/v2
├── three_days
│   ├── content
│   │   └── event.php
│   ├── content.php
│   └── title.php
└── three_days.php
```

There's really a ton of other files in there (feel free to explore) but, right now, we care only about our Views' files.
We've split the front-end rendering code into partials to keep the code tidy and be able to draw a nice ASCII tree.

### Passing information to the View template

Ideally (which means you should do anything in your power to make it so, and I believe in you) **frontend templates should be logic-less**.
This means that any calculation, interpolation, and "look-around"  should be done **in the View class** and provided to the templates ready-to-run.
Before diving into the template take a moment to go back to the View class and look at the `setup_template_vars` method: **anything** templates need should come from that method.

> Do you want to quickly scaffold a View and put demo content in it? Look no further than the `View::setup_template_vars` method; filter the `tribe_events_views_v2_view_<view_slug>_template_vars` and add/modify any data you need.

#### Global and local template data
When dealing with templates you will come across this distinction, willing and knowing or not, so it's worth taking some time to nail the basics:

* any variable set from the `setup_template_vars` method will be **global** and available to any template in the hierarchy, no matter how nested it is in the file structure.
* any variable not set there will be **local** and available only to the template receiving it and its children.

Confused? Good; time to look at examples.

The first template the Views code will load is the `three_days.php` one.

```php
<?php do_action( 'tribe_events_before_template' ); ?>

<?php $this->template( 'three_days/title' ); ?>

<?php $this->template( 'three_days/content' ); ?>

<?php do_action( 'tribe_events_after_template' );
```

This template acts as an entry point, loading the partials that make up the view front-end.
Moving to `three_days/title.php`:
```php
<?php
/**
 * Renders the Three Day View title.
 *
 * @var int $events_count The total number of events in the View, a global template variable.
 * @var string $start_date The start date, in the `Y-m-d H:i:s` format, a global template variable.
 * @var string $end_date The end date, in the `Y-m-d H:i:s` format, a global template variable.
 */
?>
<h3>
	Showing <?php echo esc_html( $events_count ) ?>
	events from <?php echo esc_html( $start_date ) ?>
	to <?php echo esc_html( $end_date ) ?>
</h3>
```

In this template we're using some variables: `$events_count`, `$start_date` and `$end_date`; where do those come from?
They are **global** template variables set by the View `setup_template_vars` method.
There is no special method, function or operation you need to do to get them.

Let's move to the next template, `three_days/content.php`:

```php
<?php
/**
 * Renders the Three Days View events.
 */
?>
<ul>
	<?php while ( have_posts() ): the_post(); ?>
		<li>
			<?php $this->template( 'three_days/content/event', [ 'event' => tribe_get_event( get_the_ID() ) ] ) ?>
		</li>
	<?php endwhile; ?>
</ul>
```

Hold on a second... `have_posts`? `the_post`? Yes, we're hacking (and then restoring, we're nice people) **the loop** to make the use of the default, known and comforting WordPress loop functions possible.
What's in that loop? The events for the View. Behind the scenes we're using the repository, set up in the `setup_repository_args` method, to fetch the events and "inject" them in the loop.
For each event, until we have some, we load the `three_days/content/event.php` partial and we pass it an `event` variable.
That variable is a `WP_Post` object decorated using the `tribe_get_event` function.
That function is like `get_post` on steroids for Events and will provide a wealth of information.
Note we're using `get_the_ID`: we're **in the loop**.

Finally let's look at the `three_days/content/event.php` template:

```php
<?php
/**
 * Renders a single Event in the Three Days View.
 *
 * @var WP_Post $event The event post, decorated by the `tribe_get_event` function, a local template variable, provided
 *                     to this template by the `three_days/content.php` one.
 */
?>
<article>
	<header>
		<h4><?php echo esc_html( get_the_title( $event->ID ) ) ?></h4>
	</header>
	<section class="content">
		<p>
			From <?php echo esc_html( $event->dates->start->format( 'Y-m-d H:i' ) ) ?> to
			<?php echo esc_html( $event->dates->end->format( 'Y-m-d H:i' ) ) ?>
		</p>

		<?php the_content(); ?>
	</section>
</article>
```

In this template we're using a combination of all we've seen so far:
* the `$event` variable is a **local** template variable provided to us from the `three_days/content.php` template
* that `$event` is not just a `WP_Post` instance but is *decorated* with a number of additional properties like `dates` that contains the event start and end dates (both UTC and not UTC) objects to allow for ablative method calls
* we can call `the_content()` function because we're **in the loop**.

### Adding the View to the registered Views
The final step is making sure the View controller/manager/dictator picks up our new View.
It's as simple as using a filter (if you're not editin the plugin code directly, which you should do only if you maintain it):

```php
add_filter( 'tribe_events_views', function( array $views ){
    $views['three_days'] = Three_Day_List_View::class;

    return $views;
} );
```

That's it, happy coding.

P.S. That's not **really** it. There's more stuff to find out about in the new Views architecture, but this is a start.
