<?php
/**
 * Week View Grid Hourly Single Event
 * This file sets up the structure for the week view grid hourly single event
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/week/single-event-hourly.php
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); } ?>

<?php $event = tribe_events_week_get_event(); ?>
<div id="tribe-events-event-<?php echo $event->ID; ?>" <?php tribe_events_the_header_attributes( 'week-hourly' ); ?> class="<?php tribe_events_event_classes() ?>">
	<div class="hentry vevent">
		<h3 class="entry-title summary"><a href="<?php tribe_event_link( $event ); ?>" class="url" rel="bookmark"><?php echo $event->post_title; ?></a></h3>
	</div>
	<?php tribe_get_template_part( 'pro/week/single-event', 'tooltip' ); ?>
</div>
