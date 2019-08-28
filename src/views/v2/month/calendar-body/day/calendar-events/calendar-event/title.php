<?php
/**
 * View: Month View - Calendar Event Title
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/calendar-body/day/calendar-events/calendar-event/title.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTICLE_LINK_HERE}
 *
 * @version TBD
 *
 * @var WP_Post $event The event post object with properties added by the `tribe_get_event` function.
 *
 * @see tribe_get_event() For the format of the event object.
 */
$event_id = $event->ID;
?>
<h3 class="tribe-events-calendar-month__calendar-event-title tribe-common-h8 tribe-common-h--alt">
	<a
		href="<?php echo esc_url( $event->permalink ) ?>"
		title="<?php echo esc_attr( get_the_title( $event->ID ) ); ?>"
		rel="bookmark"
		class="tribe-events-calendar-month__calendar-event-title-link tribe-common-anchor-thin"
		data-js="tribe-events-tooltip"
		data-tooltip-content="#tribe-events-tooltip-content-<?php echo esc_attr( $event_id ); ?>"
		aria-describedby="tribe-events-tooltip-content-<?php echo esc_attr( $event_id ); ?>"
	>
		<?php echo esc_html( get_the_title($event_id) ); ?>
	</a>
</h3>
