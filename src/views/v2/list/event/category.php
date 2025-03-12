<?php
/**
 * View: List Single Event Categories
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/list/event/category.php
 *
 * See more documentation about our views templating system.
 *
 * @link    http://evnt.is/1aiy
 *
 * @version TBD
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see     tribe_get_event() For the format of the event object.
 */

use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Meta_Keys;

// @todo - Move this logic somewhere else.
$categories = get_the_terms( $event->ID, 'tribe_events_cat' );

if ( empty( $categories ) || ! is_array( $categories ) ) {
	return;
}

// Retrieve all category priorities at once.
$meta_instance = tribe( Event_Category_Meta::class );
$priorities    = [];

// Get priorities and set default to -1 if missing.
foreach ( $categories as $category ) {
	$priority                         = $meta_instance->set_term( $category->term_id )->get( Meta_Keys::get_key( 'priority' ) );
	$priorities[ $category->term_id ] = is_numeric( $priority ) ? (int) $priority : -1;
}

// Sort categories by priority (descending, highest first).
usort(
	$categories,
	fn( $a, $b ) => $priorities[ $b->term_id ] <=> $priorities[ $a->term_id ]
);

// Get the most important category.

$category = reset( $categories );
?>

<div class="tec-events-calendar-list__event-categories">
	<div class="tec-events-calendar-list__category tribe-events-calendar__category--<?php echo sanitize_html_class( $category->slug ); ?>">
		<span class="tec-events-calendar-list__category-icon"></span>
		<?php echo esc_html( $category->name ); ?>
	</div>
</div>
