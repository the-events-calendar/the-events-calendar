<?php
/**
 * View: Month View - Calendar Event Title
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/month/calendar-body/day/calendar-events/calendar-event/title.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.9.4
 *
 */
$event    = $this->get( 'event' );
$event_id = $event->ID;
?>
<h3 class="tribe-events-calendar-month__calendar-event-title tribe-common-h8 tribe-common-h8--alt">
	<a
		href="#"
		title="<?php echo esc_attr( $event->title ); ?>"
		rel="bookmark"
		class="tribe-events-calendar-month__calendar-event-title-link tribe-common-anchor-thin"
		data-js="tribe-events-tooltip"
		data-tooltip-content="#tooltip_content-<?php echo esc_attr( $event_id ); ?>"
		aria-describedby="#tooltip_content-<?php echo esc_attr( $event_id ); ?>"
	>
		<?php echo esc_html( $event->title ); ?>
	</a>
</h3>
