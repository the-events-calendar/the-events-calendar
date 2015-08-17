<?php
/**
 * Photo View Nav
 * This file contains the photo view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/pro/photo/nav.php
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
	<!-- Display Previous Page Navigation -->
	<?php if ( tribe_has_previous_event() ) : ?>
		<li class="tribe-events-nav-previous">
			<a href="#" class="tribe_paged"><?php printf( __( '<span>&laquo;</span> Previous %s', 'tribe-events-calendar-pro' ), $events_label_plural ); ?></a>
		</li>
	<?php endif; ?>
	<!-- Display Next Page Navigation -->
	<?php if ( tribe_has_next_event() ) : ?>
		<li class="tribe-events-nav-next">
			<a href="#" class="tribe_paged"><?php printf( __( 'Next %s <span>&raquo;</span>', 'tribe-events-calendar-pro' ), $events_label_plural ); ?></a>
		</li>
	<?php endif; ?>
</ul>
