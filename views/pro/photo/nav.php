<?php
/**
 * Photo View Nav
 * This file contains the photo view navigation.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/photo/nav.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<h3 class="tribe-events-visuallyhidden"><?php _e( 'Events List Navigation', 'tribe-events-calendar-pro' ) ?></h3>
<ul class="tribe-events-sub-nav">
	<!-- Display Previous Page Navigation -->
	<?php if ( tribe_has_previous_event() ) : ?>
		<li class="tribe-events-nav-previous">
			<a href="#" class="tribe_paged"><?php _e( '<span>&laquo;</span> Previous Events', 'tribe-events-calendar-pro' ) ?></a>
		</li>
	<?php endif; ?>
	<!-- Display Next Page Navigation -->
	<?php if ( tribe_has_next_event() ) : ?>
		<li class="tribe-events-nav-next">
			<a href="#" class="tribe_paged"><?php _e( 'Next Events <span>&raquo;</span>', 'tribe-events-calendar-pro' ) ?></a>
		</li>
	<?php endif; ?>
</ul>