<?php
/**
 * Map View Nav
 * This file contains the map view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/pro/map/nav.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$events_label_plural = tribe_get_event_label_plural();

?>

<h3 class="tribe-events-visuallyhidden"><?php printf( __( '%s List Navigation', 'tribe-events-calendar-pro' ), $events_label_plural ); ?></h3>
<ul class="tribe-events-sub-nav">
	<?php if ( tribe_has_previous_event() ) : ?>
		<!-- Display Previous Page Navigation -->
		<li class="tribe-events-nav-previous">
			<a href="#" class="tribe_map_paged"><?php printf( __( '<span>&laquo;</span> Previous %s', 'tribe-events-calendar-pro' ), $events_label_plural ); ?></a>
		</li>
	<?php endif; ?>

	<?php if ( tribe_has_next_event() ) : ?>
		<!-- Display Next Page Navigation -->
		<li class="tribe-events-nav-next">
			<a href="#" class="tribe_map_paged"><?php printf( __( 'Next %s <span>&raquo;</span>', 'tribe-events-calendar-pro' ), $events_label_plural ); ?></a>
		</li>
	<?php endif; ?>
</ul>
