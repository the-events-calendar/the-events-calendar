<?php
/**
 * Week View Grid All Day Single Event
 * This file sets up the structure for the week view grid all day single event
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/week/single-event-allday.php
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); } ?>

<?php if ( tribe_events_week_is_all_day_placeholder() ) : ?>
	<div class="tribe-event-placeholder hentry vevent " data-event-id="<?php tribe_events_week_get_event_id(); ?>">&nbsp;</div>
<?php else : $event = tribe_events_week_get_event(); ?>
	<div id="tribe-events-event-<?php echo $event->ID; ?>" class="<?php echo tribe_events_event_classes() ?>" <?php tribe_events_the_header_attributes( 'week-all-day' ); ?>>
		<div>
			<h3 class="entry-title summary"><a href="<?php tribe_event_link( $event ); ?>" class="url" rel="bookmark"><?php echo $event->post_title; ?></a></h3>
			<?php tribe_get_template_part( 'pro/week/single-event', 'tooltip' ); ?>
		</div>
	</div>
<?php endif; ?>
