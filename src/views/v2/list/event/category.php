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

$categories = get_the_terms( $event->ID, 'tribe_events_cat' );

if ( empty( $categories ) ) {
	return;
}

// Retrieve priority values from the Meta class and sort by highest priority first.
usort(
	$categories,
	function ( $a, $b ) {
		$meta_a = tribe( Event_Category_Meta::class )->set_term( $a->term_id );
		$meta_b = tribe( Event_Category_Meta::class )->set_term( $b->term_id );

		$priority_a = $meta_a->get( Meta_Keys::get_key( 'priority' ) );
		$priority_b = $meta_b->get( Meta_Keys::get_key( 'priority' ) );

		return (int) $priority_b <=> (int) $priority_a;
	}
);

// Get the highest-priority category.
$category = reset( $categories );

?>

<div class="tribe-events-calendar-list__event-categories">
	<div class="tribe-events-calendar-list__category tribe-events-calendar-list__category--<?php echo sanitize_html_class( $category->slug ); ?>">
		<span class="tribe-events-calendar-list__category-icon"></span>
		<?php echo esc_html( $category->name ); ?>
	</div>
</div>
