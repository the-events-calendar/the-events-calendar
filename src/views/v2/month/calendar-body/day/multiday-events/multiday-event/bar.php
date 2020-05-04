<?php
/**
 * View: Month View - Multiday Event Bar
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/v2/month/calendar-body/day/multiday-events/multiday-event/bar.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 * @var WP_Post $event An event post object with event-specific properties added from the the `tribe_get_event`
 *
 * @see tribe_get_event() For the format of the event object and its properties.
 *
 */

?>
<div class="tribe-events-calendar-month__multiday-event-bar">
	<div class="tribe-events-calendar-month__multiday-event-bar-inner">
		<?php if ( ! empty( $event->featured ) ) : ?>
			<?php $this->template( 'month/calendar-body/day/multiday-events/multiday-event/bar/featured' ); ?>
		<?php endif; ?>
		<h3 class="tribe-events-calendar-month__multiday-event-bar-title tribe-common-h8">
			<?php echo $event->title; // phpcs:ignore ?>
		</h3>
	</div>
</div>
