<?php
/**
 * Week View Grid All Day Single Event
 * This file sets up the structure for the week view grid all day single event
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/week/single-event-allday.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
} ?>

<div id='tribe-events-event-<?php echo $event->ID; ?>' class='<?php echo tribe_events_event_classes( $event->ID ); ?> tribe-week-event' <?php tribe_events_the_week_event_attributes( $event ); ?> data-tribejson='<?php echo tribe_events_template_data( $event ); ?>'>
	<div>
		<h3 class="entry-title summary">
			<a href="<?php tribe_event_link( $event ); ?>" class="url" rel="bookmark"><?php echo $event->post_title; ?></a>
		</h3>
	</div>
</div>