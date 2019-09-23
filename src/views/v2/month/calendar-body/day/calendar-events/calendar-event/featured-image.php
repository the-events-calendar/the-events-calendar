<?php
/**
 * View: Month View - Calendar Event Featured Image
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/calendar-body/day/calendar-events/calendar-event/featured-image.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.9
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 *
 */

if ( ! $event->featured || ! $event->thumbnail->exists ) {
	return;
}

?>
<div class="tribe-events-calendar-month__calendar-event-featured-image-wrapper">
	<a
		href="<?php echo esc_url( $event->permalink ); ?>"
		title="<?php echo esc_attr( get_the_title( $event->ID ) ); ?>"
		rel="bookmark"
		class="tribe-events-calendar-month__calendar-event-featured-image-link"
	>
		<div class="tribe-events-calendar-month__calendar-event-featured-image tribe-common-c-image tribe-common-c-image--bg">
			<div
				class="tribe-common-c-image__bg"
				style="background-image: url('<?php echo esc_url( $event->thumbnail->full->url ); ?>');"
				role="img"
				aria-label="<?php echo esc_attr( get_the_title( $event->ID ) ); ?>"
			>
			</div>
		</div>
	</a>
</div>
