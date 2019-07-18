<?php
/**
 * View: Month View - Mobile Event Title
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/mobile-events/mobile-day/mobile-event/title.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */

// $event    = $this->get( 'event' );
// $event_id = $event->ID;

$classes = [ 'tribe-events-calendar-month-mobile-events__mobile-event-title' ];

/* @todo fix this once we make event dynamic */
// if ( tribe( 'tec.featured_events' )->is_featured( $event_id ) ) {
	$classes[] = 'tribe-common-h6';
// } else {
// 	$classes[] = 'tribe-common-h8';
// }

?>
<h3 class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<a
		href="#"
		title="Lorem Ipsum"
		rel="bookmark"
		class="tribe-events-calendar-month-mobile-events__mobile-event-title-link tribe-common-anchor"
	>
		Lorem Ipsum
	</a>
</h3>
